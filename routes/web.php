<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthenticationsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\TopicController as AdminTopicController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\SpeechController;
use App\Http\Controllers\BallotController;
use App\Models\Post;
use App\Models\Topic;
use App\Http\Controllers\pages\PoliticalInterviewController;

use App\Http\Controllers\NewsController;
// Redirect homepage to dashboard
Route::redirect('/', '/dashboard');

// --- Authentication Routes ---
Route::get('login', [AuthenticationsController::class, 'showLoginPage'])->middleware('guest')->name('login');
Route::post('login', [AuthenticationsController::class, 'storeLogin'])->middleware('guest');
Route::post('logout', [AuthenticationsController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('register', [AuthenticationsController::class, 'showRegistrationPage'])->middleware('guest')->name('register');
Route::post('register', [AuthenticationsController::class, 'storeRegistration'])->middleware('guest');


// --- Main Application Routes ---
Route::middleware('auth')->group(function () {

  // --- AI NAVIGATOR ---
    Route::post('/ai/navigate', [App\Http\Controllers\AiNavigatorController::class, 'navigate'])->name('ai.navigate');
    // --- SEARCH ROUTES ---
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::post('/search', [SearchController::class, 'search'])->name('search.perform');

    // Dashboard
    Route::get('/dashboard', function () {
        $posts = Post::with('user', 'comments', 'media', 'topics', 'likers', 'bookmarkers')->latest()->take(20)->get();
        $topics = Topic::withCount('posts')->orderBy('name')->get();
        Log::info('[CivicUtopia] Loading dashboard view.', ['post_count' => $posts->count(), 'topic_count' => $topics->count()]);
        return view('dashboard', compact('posts', 'topics'));


    })->name('dashboard');

    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/summarize', [PostController::class, 'summarize'])->name('posts.summarize');
    Route::post('/posts/{post}/like', [PostController::class, 'toggleLike'])->name('posts.like');
    Route::post('/posts/{post}/bookmark', [PostController::class, 'toggleBookmark'])->name('posts.bookmark');
    Route::post('/posts/{post}/explain', [PostController::class, 'explain'])->name('posts.explain');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- COMMENTS ---
    Route::get('/posts/{post}', function (Post $post) {
        $post->load('comments.user', 'user', 'media', 'topics', 'likers', 'bookmarkers');
        return view('posts.show', compact('post'));
    })->name('posts.show');
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');

    // --- TOPICS ---
    Route::get('/topics/{topic:slug}', [TopicController::class, 'show'])->name('topics.show');

    // --- ADMIN ROUTES ---
    Route::prefix('admin')->name('admin.')->group(function() {
        Route::resource('documents', DocumentController::class)->only(['index', 'store']);
        Route::resource('topics', AdminTopicController::class)->except(['show', 'edit', 'update']);
    });

    // --- SPEECH ROUTE ---
    Route::post('/speech/generate', [SpeechController::class, 'generate'])->name('speech.generate');

    // --- BALLOT ROUTES ---
    Route::get('/ballots', [BallotController::class, 'index'])->name('ballots.index');
    Route::get('/ballots/create', [BallotController::class, 'create'])->name('ballots.create');
    Route::post('/ballots', [BallotController::class, 'store'])->name('ballots.store');
    Route::get('/ballots/{ballot}', [BallotController::class, 'show'])->name('ballots.show');
    Route::post('/ballots/{ballot}/analyze', [BallotController::class, 'analyze'])->name('ballots.analyze');
    Route::post('/ballots/{ballot}/ask', [BallotController::class, 'askBot'])->name('ballots.ask');
    Route::post('/ballots/{ballot}/translate', [BallotController::class, 'translate'])->name('ballots.translate'); // <-- NEW TRANSLATE ROUTE

    // --- CANDIDATE COMPARISON ROUTES ---
    Route::get('/candidates/compare', [App\Http\Controllers\CandidateController::class, 'compareSelect'])->name('candidates.compare.select');
    Route::post('/candidates/compare', [App\Http\Controllers\CandidateController::class, 'compareAnalyze'])->name('candidates.compare.analyze');

    // --- CANDIDATE ROUTES ---
    Route::get('/candidates', [App\Http\Controllers\CandidateController::class, 'index'])->name('candidates.index');
    Route::get('/candidates/create', [App\Http\Controllers\CandidateController::class, 'create'])->name('candidates.create'); // <-- New
    Route::post('/candidates', [App\Http\Controllers\CandidateController::class, 'store'])->name('candidates.store');       // <-- New
    Route::get('/candidates/{candidate}', [App\Http\Controllers\CandidateController::class, 'show'])->name('candidates.show');
    Route::post('/candidates/{candidate}/analyze', [App\Http\Controllers\CandidateController::class, 'analyze'])->name('candidates.analyze');
    Route::post('/candidates/{candidate}/ask', [App\Http\Controllers\CandidateController::class, 'askBot'])->name('candidates.ask'); // <-- New Chat
    Route::post('/candidates/research', [App\Http\Controllers\CandidateController::class, 'research'])->name('candidates.research');
    Route::get('/candidates/{candidate}/edit', [App\Http\Controllers\CandidateController::class, 'edit'])->name('candidates.edit');
    Route::put('/candidates/{candidate}', [App\Http\Controllers\CandidateController::class, 'update'])->name('candidates.update');
    Route::post('/candidates/{candidate}/research-stance', [App\Http\Controllers\CandidateController::class, 'researchStance'])->name('candidates.researchStance');
    Route::post('/candidates/{candidate}/translate', [App\Http\Controllers\CandidateController::class, 'translate'])->name('candidates.translate');

    // --- CIVIC LENS (ISSUES) ROUTES ---
    Route::get('/issues', [App\Http\Controllers\IssueController::class, 'index'])->name('issues.index');
    Route::get('/issues/create', [App\Http\Controllers\IssueController::class, 'create'])->name('issues.create');
    Route::post('/issues', [App\Http\Controllers\IssueController::class, 'store'])->name('issues.store');
    Route::get('/issues/{issue}', [App\Http\Controllers\IssueController::class, 'show'])->name('issues.show');
    Route::post('/issues/{issue}/analyze', [App\Http\Controllers\IssueController::class, 'analyze'])->name('issues.analyze');

// --- SMART DOCUMENT ROUTES ---
    Route::get('/documents', [App\Http\Controllers\DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/create', [App\Http\Controllers\DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents/{document}/chat', [App\Http\Controllers\DocumentController::class, 'chat'])->name('documents.chat');
    Route::post('/documents/{document}/annotate', [App\Http\Controllers\DocumentController::class, 'annotate'])->name('documents.annotate');
    Route::post('/documents/{document}/regenerate', [App\Http\Controllers\DocumentController::class, 'regenerate'])->name('documents.regenerate');

    Route::post('/documents/{document}/publish', [App\Http\Controllers\DocumentController::class, 'togglePublic'])->name('documents.publish');

    // --- CIVIC NEWS AGENT ---
    Route::post('/news/fetch-local', [NewsController::class, 'fetchLocal'])->name('news.fetch');

 // Political Interview Agent
    Route::get('/interview', [PoliticalInterviewController::class, 'index'])->name('interview.index');
    Route::post('/interview/chat', [PoliticalInterviewController::class, 'chat'])->name('interview.chat');
    Route::post('/interview/speech', [PoliticalInterviewController::class, 'speech'])->name('interview.speech');





    // Add to routes/web.php temporarily
Route::get('/test-bing', function() {
    $bingKey = config('services.rapidapi.key');
    $bingHost = config('services.rapidapi.host');

    Log::info("Testing Bing API with key: " . substr($bingKey, 0, 8) . "...");

    try {
        $response = Http::timeout(15)
            ->withHeaders([
                'x-rapidapi-key' => $bingKey,
                'x-rapidapi-host' => $bingHost,
            ])
            ->get('https://bing-search-apis.p.rapidapi.com/api/rapid/web_search', [
                'q' => 'Jamaica political parties',
                'keyword' => 'Jamaica political parties',
                'count' => 5,
                'mkt' => 'en-US'
            ]);

        return response()->json([
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->json()
        ], 200, [], JSON_PRETTY_PRINT);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
});

});
