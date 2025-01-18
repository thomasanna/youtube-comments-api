<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CommentLike extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'comment_id','liked'];


}
