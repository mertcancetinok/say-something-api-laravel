<?php

use App\Http\Controllers\CategoriesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\PostCommentsController;
use App\Http\Controllers\UsersController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('not-login',function (){
    return response()->json([],401);
})->name('not-login');

Route::group([
    'middleware' => ['api','changeLanguage'],
], function () {
    Route::group([
        'prefix' => "auth"
    ],function (){
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/send-reset-password-link', [AuthController::class, 'sendResetPasswordLink']);
        Route::post('/confirm-reset-password-link', [AuthController::class, 'confirmResetPasswordLink']);
        Route::post('/reset-password-set-new-password', [AuthController::class, 'resetPasswordSetNewPassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
    });

    Route::group([
        'prefix' => "users",
        'middleware' => ['bannedUserHandler']
    ],function (){
        Route::post('/me', [UsersController::class, 'me']);
        Route::put('/update', [UsersController::class, 'update']);
        Route::put('/update-password', [UsersController::class, 'updatePassword']);
        Route::post('/confirm-email', [UsersController::class, 'confirmEmail']);
        Route::post('/confirm-email-code', [UsersController::class, 'confirmEmailCode']);
        Route::put('/ban', [UsersController::class, 'banUser'])->middleware('admin');
    });

    Route::group([
        'prefix' => "posts",
        'middleware' => ['bannedUserHandler']
    ],function (){
        Route::get('/', [PostsController::class, 'index']);
        Route::get('/{id}', [PostsController::class, 'show']);
        Route::post('/', [PostsController::class, 'store'])->middleware('admin');
        Route::put('/{id}', [PostsController::class, 'update'])->middleware('admin');
        Route::delete('/{id}', [PostsController::class, 'destroy'])->middleware('admin');
    });

    Route::group([
        'prefix' => "post-comments",
        'middleware' => ['bannedUserHandler']
    ],function (){
        Route::get('/', [PostCommentsController::class, 'index']);
        Route::get('/{id}', [PostCommentsController::class, 'show']);
        Route::post('/', [PostCommentsController::class, 'store']);
        Route::put('/{id}', [PostCommentsController::class, 'update']);
        Route::delete('/{id}', [PostCommentsController::class, 'destroy']);
    });

    Route::group([
        'prefix' => "categories",
        'middleware' => ['bannedUserHandler']
    ],function (){
        Route::get('/', [CategoriesController::class, 'index']);
        Route::get('/{id}', [CategoriesController::class, 'show']);
        Route::post('/', [CategoriesController::class, 'store']);
        Route::put('/{id}', [CategoriesController::class, 'update']);
        Route::delete('/{id}', [CategoriesController::class, 'destroy']);
    });

});
