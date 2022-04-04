<?php

use App\Http\Controllers\CategoriesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\PostCommentsController;
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
    'middleware' => ['api','changeLanguage']
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
        'prefix' => "posts"
    ],function (){
        Route::get('/', [PostsController::class, 'index']);
        Route::get('/{id}', [PostsController::class, 'show']);
        Route::post('/', [PostsController::class, 'store']);
        Route::put('/{id}', [PostsController::class, 'update']);
        Route::delete('/{id}', [PostsController::class, 'destroy']);
    });

    Route::group([
        'prefix' => "post-comments"
    ],function (){
        Route::get('/', [PostCommentsController::class, 'index']);
        Route::get('/{id}', [PostCommentsController::class, 'show']);
        Route::post('/', [PostCommentsController::class, 'store']);
        Route::put('/{id}', [PostCommentsController::class, 'update']);
        Route::delete('/{id}', [PostCommentsController::class, 'destroy']);
    });

    Route::group([
        'prefix' => "categories"
    ],function (){
        Route::get('/', [CategoriesController::class, 'index']);
        Route::get('/{id}', [CategoriesController::class, 'show']);
        Route::post('/', [CategoriesController::class, 'store']);
        Route::put('/{id}', [CategoriesController::class, 'update']);
        Route::delete('/{id}', [CategoriesController::class, 'destroy']);
    });

});
