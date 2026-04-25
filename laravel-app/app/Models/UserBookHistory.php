<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBookHistory extends Model
{
    protected $fillable = ['user_id', 'book_id', 'action'];

    public function user() { return $this->belongsTo(User::class); }
    public function book() { return $this->belongsTo(Book::class); }
}
