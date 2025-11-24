<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthenticationsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\TopicController as AdminTopicController;
use App\Http\Controllers\TopicController; // Frontend Topic Controller
use App\Models\Post;
use App\Models\Topic;

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
});
