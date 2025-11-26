<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Wav;

class VoiceController extends Controller
{
    public function transcribe(Request $request)
    {
        if (!$request->hasFile('audio')) {
            return response()->json(['error' => 'No audio file uploaded'], 400);
        }

        // 1. SETUP FFMPEG DRIVERS
        // We pull paths from .env or default to system 'ffmpeg' command
        $ffmpegConfig = [
            'ffmpeg.binaries'  => env('FFMPEG_PATH', 'ffmpeg'),
            'ffprobe.binaries' => env('FFPROBE_PATH', 'ffprobe'),
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ];

        try {
            $file = $request->file('audio');

            // 2. Save temp file (WebM from browser)
            $inputPath = $file->storeAs('temp', 'input_' . uniqid() . '.webm');
            $inputAbsPath = storage_path('app/' . $inputPath);
            $outputPath = storage_path('app/temp/output_' . uniqid() . '.wav');

            // 3. Convert WebM to WAV
            try {
                $ffmpeg = FFMpeg::create($ffmpegConfig);
                $audio = $ffmpeg->open($inputAbsPath);

                $format = new Wav();
                $format->setAudioChannels(1)->setAudioKiloBitrate(256);

                $audio->save($format, $outputPath);
            } catch (\Exception $e) {
                Log::error("FFMpeg Conversion Failed. Check your .env paths! Error: " . $e->getMessage());
                return response()->json(['error' => 'Server audio conversion failed. Is FFMpeg installed?'], 500);
            }

            // 4. Send to Azure Speech-to-Text
            $region = config('services.azure.speech.region');
            $key = config('services.azure.speech.key');

            $url = "https://{$region}.stt.speech.microsoft.com/speech/recognition/conversation/cognitiveservices/v1?language=en-US";

            $audioData = file_get_contents($outputPath);

            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $key,
                'Content-Type' => 'audio/wav; codecs=audio/pcm; samplerate=16000',
                'Accept' => 'application/json',
            ])->withBody($audioData, 'audio/wav')->post($url);

            // Cleanup
            @unlink($inputAbsPath);
            @unlink($outputPath);

            if ($response->failed()) {
                Log::error("Azure Speech Error: " . $response->body());
                return response()->json(['error' => 'Azure Transcription failed'], 500);
            }

            $data = $response->json();
            $text = $data['DisplayText'] ?? '';

            return response()->json(['text' => $text]);

        } catch (\Exception $e) {
            Log::error("Voice Controller Error: " . $e->getMessage());
            return response()->json(['error' => 'System error during transcription'], 500);
        }
    }

     public function getToken()
    {
        $key = config('services.azure.speech.key');
        $region = config('services.azure.speech.region');

        try {
            // Request a Token from Azure STS (Security Token Service)
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $key
            ])->post("https://{$region}.api.cognitive.microsoft.com/sts/v1.0/issueToken");

            if ($response->failed()) {
                return response()->json(['error' => 'Could not generate token'], 500);
            }

            return response()->json([
                'token' => $response->body(),
                'region' => $region
            ]);
        } catch (\Exception $e) {
            Log::error("Voice Token Error: " . $e->getMessage());
            return response()->json(['error' => 'System error'], 500);
        }
    }
}
