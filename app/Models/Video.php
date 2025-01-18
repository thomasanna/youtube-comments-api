<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{   
    use HasFactory;

    protected $fillable = ['title', 'description','url'];

    // A video has many comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    

}
