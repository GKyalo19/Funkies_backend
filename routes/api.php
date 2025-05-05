<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
//Route::post('forgotPassword', [AuthController::class, 'forgotPassword']);

// Private Routes (Authenticated with Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Notifications
    Route::get('/user/notifications', function(){
        return response()->json(Auth::user()->unreadNotifications);
    });
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);

    // Users
    Route::get('user', [UserController::class, 'index']);
    Route::post('user', [UserController::class, 'store']);
    // Route::get('user/{id}', [UserController::class, 'showUsers']);
    Route::get('user/{id}', [UserController::class, 'getUser']);
    Route::put('user/{id}', [UserController::class, 'updateUser']);
    Route::delete('user/{id}',[UserController::class, 'deleteUser']);
    Route::post('/restore-user/{email}', [UserController::class, 'restoreUser']);

    // Events
    Route::get('event', [EventController::class, 'getEvents']);
    Route::post('event', [EventController::class, 'createEvent']);
    Route::get('event/{id}', [EventController::class, 'getEvent']);
    Route::put('event/{id}', [EventController::class, 'editEvent']);
    Route::delete('event/{id}', [EventController::class, 'deleteEvent']);

    //Likes
    Route::post('like/{id}', [LikeController::class, 'likeEvent']);
    Route::get('like', [LikeController::class, 'getLikedEvents']);
    Route::delete('like/{id}', [LikeController::class, 'unlikeEvent']);

    // Galleries
    Route::get('gallery', [GalleryController::class, 'getGalleries']);
    Route::post('gallery', [GalleryController::class, 'createGallery']);
    Route::get('gallery/{id}', [GalleryController::class, 'getGallery']);
    Route::get('gallery/event/{event_id}', [GalleryController::class, 'getEventGallery']);
    Route::put('gallery/{id}', [GalleryController::class, 'updateGallery']);
    Route::delete('gallery/{id}', [GalleryController::class, 'deleteGallery']);
});

// Roles
Route::post('role', [RoleController::class, 'createRole']);
Route::get('role', [RoleController::class, 'index']);
Route::get('role/{id}', [RoleController::class, 'getRole']);
Route::put('role/{id}', [RoleController::class, 'updateRole']);
Route::delete('role/{id}', [RoleController::class, 'deleteRole']);





// Route::group(function () {
//     Route::get('/login', )
// })
