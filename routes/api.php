<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\CommentController;


Route::get('/videos', [VideoController::class, 'index']);
Route::get('/videos/{id}', [VideoController::class, 'show']);
Route::get('/videos/{video_id}/comments', [CommentController::class, 'index']);
Route::post('/comments', [CommentController::class, 'store']);
Route::put('/comments/{id}', [CommentController::class, 'update']);
Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
Route::post('comments/{commentId}/reply', [CommentController::class, 'storeReply']);
Route::get('comments/{commentId}/replies', [CommentController::class, 'listReplies']);
Route::post('comments/{commentId}/like', [CommentController::class, 'likeComment']);
Route::post('comments/{commentId}/dislike', [CommentController::class, 'dislikeComment']);
Route::get('/videos/{video_id}/top-comments', [CommentController::class, 'topComments']);



