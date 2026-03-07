<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\cmsUserController;
use App\Http\Controllers\cmsPostsController;
use App\Http\Controllers\adminController;

// view
Route::get('/', [cmsPostsController::class, 'home']);
Route::get('/posts', [cmsPostsController::class, 'getPosts']);
Route::post('/post/{id}', [cmsPostsController::class, 'getPostbyID']);
Route::get('/admin', [adminController::class, 'getAdmin']);

// CRUD - users
Route::get('/users', [cmsUserController::class, 'getAllUsers']);
Route::post('/register', [cmsUserController::class, 'register']);
Route::post('/login', [cmsUserController::class, 'login']);
Route::post('/logout', [cmsUserController::class, 'logout']);
Route::post('/edit-profile', [cmsUserController::class, 'editProfile']);

// CRUD - posts to supabase & r2
Route::post('/create-post', [cmsPostsController::class, 'createPost']);
Route::delete('/delete-post', [cmsPostsController::class, 'deletePost']);