<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthenticationsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;

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

    // Dashboard
    Route::get('/dashboard', function () {
        $posts = \App\Models\Post::latest()->take(20)->get();
        \Illuminate\Support\Facades\Log::info('[CivicUtopia] Loading dashboard view with ' . $posts->count() . ' posts.');
        return view('dashboard', ['posts' => $posts]);
    })->name('dashboard');

    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // --- ROUTES FOR COMMENTS ---

    // Show a single post and its comments
    Route::get('/posts/{post}', function (\App\Models\Post $post) {
        return view('posts.show', [
            'post' => $post->load('comments')
        ]);
    })->name('posts.show');

    // Store a new comment for a specific post
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
     //
     // --- NEW AI ROUTE ---
    Route::post('/posts/{post}/summarize', [PostController::class, 'summarize'])->name('posts.summarize');
});
