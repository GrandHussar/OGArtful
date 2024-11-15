<?php

use App\Http\Controllers\ArtworkController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LikedPostController;
use App\Http\Controllers\GoogleController;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\CollageController;
use App\Http\Controllers\CommentController;
use App\Https\Controllers\MessagesController;
use Illuminate\Support\Facades\Broadcast;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Middleware\CheckRestriction;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\UploadTemporaryImageController;
use App\Http\Controllers\DeleteTemporaryImageController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\ChatifyController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TherapySessionController;
use App\Http\Controllers\AvailableDateController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SessionReportController;

Route::get('/assessment', [DashboardController::class, 'getAssessment']);
Route::post('/assessment', [DashboardController::class, 'storeAssessment']);
Route::put('/assessment/{id}', [DashboardController::class, 'updateAssessment']);
Route::delete('/assessment/{id}', [DashboardController::class, 'destroyAssessment']);


Auth::routes(['verify' => true]);
Broadcast::routes(['middleware' => ['auth:api']]);

// Route::middleware(['auth', CheckRestriction::class])->group(function () {
//     Route::get('/chat', function () {
//         return Inertia::render('Chat/ChatPage');
//     })->name('chat');

//     Route::post('/chat/send', [MessagesController::class, 'send'])->name('chat.send');
//     Route::post('/chat/fetch', [MessagesController::class, 'fetch'])->name('chat.fetch');
//     Route::post('/chat/seen', [MessagesController::class, 'seen'])->name('chat.seen');
//     Route::post('/chat/search', [MessagesController::class, 'search'])->name('chat.search');
//     Route::post('/chat/getContacts', [MessagesController::class, 'getContacts'])->name('chat.getContacts');
//     Route::post('/chat/updateContactItem', [MessagesController::class, 'updateContactItem'])->name('chat.updateContactItem');
//     Route::post('/chat/favorite', [MessagesController::class, 'favorite'])->name('chat.favorite');
//     Route::post('/chat/getFavorites', [MessagesController::class, 'getFavorites'])->name('chat.getFavorites');
//     Route::post('/chat/sharedPhotos', [MessagesController::class, 'sharedPhotos'])->name('chat.sharedPhotos');
//     Route::post('/chat/deleteConversation', [MessagesController::class, 'deleteConversation'])->name('chat.deleteConversation');
//     Route::post('/chat/deleteMessage', [MessagesController::class, 'deleteMessage'])->name('chat.deleteMessage');
//     Route::post('/chat/updateSettings', [MessagesController::class, 'updateSettings'])->name('chat.updateSettings');
//     Route::post('/chat/setActiveStatus', [MessagesController::class, 'setActiveStatus'])->name('chat.setActiveStatus');
// });

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/homepage', [PostController::class, 'index'])->name('dashboard');
Route::get('/restricted', function () {
    return Inertia::render('Restricted');
})->name('restricted');

Route::post('/post/{post}/like', [LikedPostController::class, 'toggle']);
Route::post('/save-drawing', [ArtworkController::class, 'saveDrawing'])->name('saveDrawing');
Route::get('/load-drawing/{id}', [ArtworkController::class, 'loadDrawing']);
Route::get('/load-saved-items', [ArtworkController::class, 'loadSavedItems'])->name('loadSavedItems');

Route::post('/upload', [CollageController::class, 'upload'])->name('upload');

Route::post('/upload-image', [UploadTemporaryImageController::class, 'upload']);
Route::delete('/revert/{folder}', [DeleteTemporaryImageController::class, 'delete']);

Route::post('/users/{user}/toggle', [FollowerController::class, 'toggle'])->name('followUser');
Route::get('/users/{user}/following', [FollowerController::class, 'fetchFollowingUsers']);
Route::post('/send-predefined-message', [ChatifyController::class, 'sendPredefinedMessage'])->name('send.predefined.message');
Route::get('/users1', [ChatifyController::class, 'index1'])->name('users.index');
Route::get('/users2', [DashboardController::class, 'index2'])->name('users.index2');
Route::get('/users3', [DashboardController::class, 'index3'])->name('users.index3');
Route::get('/available-dates', [AvailableDateController::class, 'index']);
Route::post('/available-dates', [AvailableDateController::class, 'store']);
Route::get('/available-dates/all', [AvailableDateController::class, 'getAvailableDates']);
Route::put('/available-dates/{id}', [AvailableDateController::class, 'update'])->name('available-dates.update');
Route::delete('/available-dates/{id}', [AvailableDateController::class, 'destroy'])->name('available-dates.destroy');
Route::post('/appointments', [AppointmentController::class, 'store']);
Route::put('/appointments/{id}', [AppointmentController::class, 'updateStatus']);
Route::put('/appointments/{id}/link', [AppointmentController::class, 'updateLink']);
// web.php
Route::get('/users/all-appointments', [AppointmentController::class, 'allAppointmentsIndex'])->middleware('auth');
Route::post('/appointments/{appointmentId}/session-report', [SessionReportController::class, 'store'])
    ->middleware('auth');
Route::get('/users/pending-appointments', [AppointmentController::class, 'pendingAppointmentsIndex'])
    ->middleware('auth');

    Route::get('/therapist/appointments', [AppointmentController::class, 'indexForTherapist']);
Route::middleware(['auth'])->group(function () {
    Route::post('/upload', [CollageController::class, 'upload'])->name('upload');
    Route::post('/api/save-collage-json', [CollageController::class, 'saveCollageJson']);
    Route::get('/api/load-collage/{id}', [CollageController::class, 'loadCollage']);
    Route::get('/api/collages', [CollageController::class, 'index']);
    Route::delete('/api/collages/{id}', [CollageController::class, 'destroy']);
});

// Route::post('/users/{user}/unfollow', [FollowerController::class, 'unfollow'])->name('unfollowUser');

Route::middleware(['auth', CheckRestriction::class])->group(function () {



    Route::get('/therapy-sessions', [TherapySessionController::class, 'index'])->name('therapy-sessions.index');
    Route::post('/therapy-sessions/{id}', [TherapySessionController::class, 'update'])->name('therapy-sessions.update');

    Route::post('/announcements', [DashboardController::class, 'storeAnnouncement']);
    Route::get('/announcements', [DashboardController::class, 'getAnnouncement']);
    Route::put('/announcements/{announcement}', [DashboardController::class, 'updateAnnouncement']);
    Route::delete('/announcements/{announcement}', [DashboardController::class, 'destroyAnnouncement']);

    Route::get('/users', [RegisteredUserController::class, 'indexTherapist'])->name('users.index'); // Controller method to get all users

    Route::get('/draw', function () {
        return Inertia::render('Draw');
    })->middleware(['auth'])->name('draw');

    Route::get('/collage', function () {
        return Inertia::render('CollageMaker');
    })->middleware(['auth'])->name('collage');

    Route::get('/mindfulcoloring', function () {
        return Inertia::render('MindfulColoring');
    })->middleware(['auth', 'verified'])->name('mindfulcoloring');

    Route::post('/post/comment', [CommentController::class, 'createComment'])->middleware(['auth', 'verified'])
        ->name('createComment');

    Route::delete('/post-delete/{id}', [PostController::class, 'deletePost'])
        ->name("deletePost");

    Route::patch('/post-update/{id}', [PostController::class, 'updatePost'])->middleware(['auth', 'verified'])
        ->name("updatePost");

    Route::get('/profile/{user}', [ProfileController::class, 'page'])->name('profile.page');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/post-create', [PostController::class, 'showCreatePost'])->middleware(['auth'])->name('post.showCreatePost');
    Route::post('/post-upload', [PostController::class, 'uploadPost'])->middleware(['auth'])->name('post.uploadPost');
    Route::post('/post-share', [PostController::class, 'sharePost'])->name('post.sharePost');
    Route::get('/post-show/{post}', [PostController::class, 'showPost'])->name('post.showPost');


    Route::get('/chat', function () {
        return Inertia::render('Chat/ChatPage');
    })->name('chat');
});

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);


Route::post('/api/upload-image', [CollageController::class, 'uploadImage'])->name('upload.image');

Route::middleware(['auth', CheckRestriction::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('newdashboard');

    // Route to fetch moods data (GET)
    Route::get('/moods', [DashboardController::class, 'getMoods'])->name('moods.get');

    // Route to store or update mood data (POST)
    Route::post('/moods', [DashboardController::class, 'store'])->name('moods.store');

    Route::post('/moods/update', [DashboardController::class, 'updateMoods'])->name('moods.update');

    // Middleware for Super Admin
    Route::middleware([RoleMiddleware::class . ':super_admin'])->group(function () {
        Route::get('/super-admin', function () {
            return 'Hello, Super Admin';
        });
        // Route::post('/edit-website', [WebsiteController::class, 'edit']);
    });

    // Middleware for IT Admin
    Route::middleware([RoleMiddleware::class . ':it_admin'])->group(function () {
        Route::get('/it-admin', function () {
            return 'Hello, IT Admin';
        });
    });

    Route::middleware([RoleMiddleware::class . ':super_admin|it_admin'])->group(function () {
        Route::get('/manage-users', [RegisteredUserController::class, 'index'])->name('manage.users');
        Route::put('/manage-users/{user}', [RegisteredUserController::class, 'update'])->name('manage.users.update');
        Route::post('/manage-users/{user}/restrict', [RegisteredUserController::class, 'restrict'])->name('manage.users.restrict');
    });
    Route::middleware(['auth', RoleMiddleware::class . ':super_admin'])->group(function () {
        Route::get('/site-settings', [SiteSettingController::class, 'edit'])->name('site-settings.edit');
        Route::post('/site-settings', [SiteSettingController::class, 'update'])->name('site-settings.update');
    });
});
Route::get('/restricted', function () {
    $restriction_end_at = session('restriction_end_at');
    return Inertia::render('Restricted', ['restriction_end_at' => $restriction_end_at]);
})->name('restricted');


Route::get('/api/site-settings', function () {
    return \App\Models\SiteSetting::first();
});
Route::get('/searchBar', [SearchController::class, 'search'])->name('searchBar');
Route::post('/integrateTherapist', 'MessagesController@integrateTherapist')->name('integrateTherapist');
Route::get('/completed-sessions/{patientId}', [SessionReportController::class, 'getCompletedSessions'])
    ->name('sessions.completed');
Route::get('/session-report/{sessionId}', [SessionReportController::class, 'getSessionReport'])
    ->name('session.report');
Route::get('/mental-state-counts/{appointmentId}/{userId}', [SessionReportController::class, 'getMentalStateCounts']);
Route::get('/therapists/{therapistId}/patients/{patientId}/session-summary', [SessionReportController::class, 'getPatientSessionSummary']);
require __DIR__ . '/auth.php';
