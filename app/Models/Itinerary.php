<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('title','summary','slug','user_id')]
class Itinerary extends Model
{
    protected $table = 'itineraries';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itineraryblock(){
        return $this->hasMany(ItinerariyBlocks::class, 'itinerary_id');
    }

   
    
}