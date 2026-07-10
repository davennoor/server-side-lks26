<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('itinerary_id','template_id','position')]
class ItinerariyBlocks extends Model
{
    protected $table = 'itinerary_blocks';
    public function template(){
        return $this->belongsTo(Template::class);
    }
    public function itinerary(){
        return $this->belongsTo(Itinerary::class);
    }

    public function blockfieldvalue(){
        return $this->hasMany(BlockFieldValue::class);
    }


}
