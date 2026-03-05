<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\cmsUserController;
use App\Http\Controllers\cmsPostsController;
use Illuminate\Support\Facades\Storage;

// view
Route::get('/', [cmsPostsController::class, 'home']);
Route::post('/post/{id}', [cmsPostsController::class, 'getPostbyID']);

// CRU no D - users
Route::post('/register', [cmsUserController::class, 'register']);
Route::post('/login', [cmsUserController::class, 'login']);
Route::post('/logout', [cmsUserController::class, 'logout']);

// CRUD - posts to supabase
Route::post('/create-post', [cmsPostsController::class, 'createPost']);
Route::delete('/delete-post', [cmsPostsController::class, 'deletePost']);