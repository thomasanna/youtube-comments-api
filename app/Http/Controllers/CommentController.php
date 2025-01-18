<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Video;
use App\Models\CommentLike;

class CommentController extends Controller
{   
    // Fetches comments with user and likes , dislikes and replies count for a specific video
    public function index($video_id)
    {   
        try{
            $comments = Comment::where('video_id', $video_id)
            ->whereNull('parent_comment_id') // Only get comments that are not replies 
            ->with('user:id,name,email') 
            ->withCount([
                'likes as like_count' => function ($query) {
                    $query->where('liked', true); // Count only the likes
                },
                'likes as dislike_count' => function ($query) {
                    $query->where('liked', false); // Count only the dislikes
                },
                'replies as replies_count' // Count the number of replies for each comment
                
            ])
            ->orderByDesc('created_at')
            ->get();
            if ($comments->isEmpty()) {
                return response()->json(['error' => false, 'message' => 'No comments found for this video'], 404);
            }

            $response =[
                'error'=> false,
                'data'   => $comments,
                'message'=> 'success'
            ];
            return response()->json($response, 200);
        }
        catch(\Exception $e){
            return response()->json([
                'error'  => true,
                'message' => $e->getMessage
            ],500);
        }
        
    }
    
    // Creates a new comment for a video.
    public function store(Request $request)
    {   
        $request->validate([
            'video_id' => 'required|exists:videos,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);  
        try{                     
    
            $comment = Comment::create($request->all());
            $response =[
                'error'=> false,
                'data' => $comment,
                'message'=> 'success'
            ];
            return response()->json($response, 201);
        }
        catch(\Exception $e){
            return response()->json([
                'error'  => true,
                'message' => $e->getMessage
            ],500);
        }
        
    }

    // Updates the content of an existing comment.

    public function update(Request $request, $id)
    {   
        
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['error'  => true, 'message' => 'Comment not found'], 404);
        }
        $request->validate(['content' => 'required|string']);
        try{
            
            $comment->update($request->only('content'));
            $response =[
                'error'=> false,
                'data' => $comment,
                'message'=> 'success'
            ];
            return response()->json($response, 200);
        }
        catch(\Exception $e){
            return response()->json([
                'error'  => true,
                'message' => $e->getMessage
            ],500);
        }
        
    }

    public function destroy($id)
    {   try{
            $comment = Comment::find($id);
            if (!$comment) {
                return response()->json(['error'  => true, 'message' => 'Comment not found'], 404);
            }

            $comment->delete();
            return response()->json(['error'  => false,'message' => 'Comment deleted successfully'], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'error'  => true,
                'message' => $e->getMessage
            ],500);
        }
        
    }
    
    // store replies
    public function storeReply(Request $request, $commentId)
    {
        // Validate the incoming data
        $request->validate([
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            // Find the parent comment by ID
            $parentComment = Comment::findOrFail($commentId);

            // Create the reply
            $reply = Comment::create([
                'user_id' => $request->user_id,
                'video_id' => $parentComment->video_id, 
                'content' => $request->content,
                'parent_comment_id' => $parentComment->id, // Set the parent_comment_id to the original comment
            ]);

            $response = [
                'error' => false,
                'message' => 'Reply added successfully',
                'data' => $reply,
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    // Fetches replies for a comment
    public function listReplies($commentId)
    {
        try {
            // Fetch replies to the given comment ID
            $replies = Comment::where('parent_comment_id', $commentId)
                            ->with('user:id,name,email') 
                            ->withCount([
                                'likes as like_count' => function ($query) {
                                    $query->where('liked', true); // Count only the likes
                                },
                                'likes as dislike_count' => function ($query) {
                                    $query->where('liked', false); // Count only the dislikes
                                },
                                'replies as replies_count'
                            ])
                            ->orderByDesc('created_at') 
                            ->get();                            
            
            if ($replies->isEmpty()) {
                return response()->json([
                    'error' => false,
                    'message' => 'No replies found for this comment.',
                ], 404);
            }

            return response()->json([
                'error' => false,
                'message' => 'Replies fetched successfully.',
                'data' => $replies,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    // like and unlike the comment
    public function likeComment(Request $request, $comment_id)
    {
        try {

            // Check if the user has already liked or disliked the comment
            $existingLike =  CommentLike::where('user_id',$request->user_id)->where('comment_id', $comment_id)->first();
            if ($existingLike) {
                // If the user has previously disliked the comment, update it to 'liked'
                if ($existingLike->liked == false) {
                    $existingLike->liked = true;
                    $existingLike->save();  
                    return response()->json(['error' => false, 'message' => 'Comment liked successfully.'], 200);

                }
                else{ // if already liked, unlike and remove the row
                    $existingLike->delete();
                    return response()->json(['error' => false, 'message' => 'Comment unliked successfully.'], 200);
                }
            }
            else{
                // If no record exists, create a new like
                $like = new CommentLike();
                $like->user_id = $request->user_id;
                $like->comment_id = $comment_id;
                $like->liked = true;  // Set liked to true
                $like->save();  
            }           

            return response()->json(['error' => false, 'message' => 'Comment liked successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
    
    // dislike and remove the dislike for the comment
    public function dislikeComment(Request $request, $comment_id)
    {
        try {            
            $existingLike = CommentLike::where('user_id', $request->user_id)
                                        ->where('comment_id', $comment_id)
                                        ->first();
            if ($existingLike) {
                // If the user has previously liked the comment, update it to 'disliked'
                if ($existingLike->liked == true) {
                    $existingLike->liked = false;
                    $existingLike->save();  
                    return response()->json(['error' => false, 'message' => 'Comment disliked successfully.'], 200);

                }
                else{
                    // if already disliked, remove the dislike
                    $existingLike->delete();
                    return response()->json(['error' => false, 'message' => 'Removed the dislike successfully.'], 200);
                }
            }
            else{
                // If no record exists, create a new dislike
                $like = new CommentLike();
                $like->user_id = $request->user_id;
                $like->comment_id = $comment_id;
                $like->liked = false;  // Set liked to false for dislike
                $like->save(); 
                return response()->json(['error' => false, 'message' => 'Comment disliked successfully.'], 200);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
    
    // Fetch the top comments based on likes count and replies count for a specific video

    public function topComments($videoId)
    {
        try {
            $topComments = Comment::where('video_id', $videoId)
                ->whereNull('parent_comment_id') // Only get comments that are not replies 
                ->with('user:id,name,email') 
                ->withCount([
                    'likes' => function ($query) {
                        $query->where('liked', true); // Only count "liked" actions                
                    },
                    'likes as dislike_count' => function ($query) {
                        $query->where('liked', false); // Count only the dislikes
                    },
                    'replies as replies_count' // Count the number of replies for each comment
                ])
                ->orderByDesc('likes_count') // orders the comments by the number of likes, with the most liked comments first.
                ->orderByDesc('replies_count') // Order by the number of replies in descending order
                ->orderByDesc('created_at') 
                ->get();

            if ($topComments->isEmpty()) {
                return response()->json([
                    'error' => false,
                    'message' => 'No comments found for this video.',
                ], 404);
            }

            return response()->json([
                'error' => false,
                'message' => 'Top comments fetched successfully.',
                'data' => $topComments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
