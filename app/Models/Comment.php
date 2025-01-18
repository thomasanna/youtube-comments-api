<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['video_id', 'user_id', 'content','parent_comment_id'];

    // A comment belongs to a video
    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    // A comment can have many replies
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    // A comment can belong to one parent (if it's a reply)
    public function parentComment()
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }
    
    // a comment belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // a comment liked by many users
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'comment_likes');
    }
    
    // a comment has many likes
    public function likes()
    {
        return $this->hasMany(CommentLike::class, 'comment_id');
    }



}