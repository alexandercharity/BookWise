<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['isbn', 'title', 'author', 'publisher', 'year_of_publication', 'image_url'];

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function histories()
    {
        return $this->hasMany(UserBookHistory::class);
    }

    public function avgRating(): float
    {
        return round($this->ratings()->avg('rating') ?? 0, 1);
    }
}
