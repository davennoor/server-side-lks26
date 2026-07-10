<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('itinerary_block_id','template_field_id','value')]
class BlockFieldValue extends Model
{
    protected $table = 'block_field_values';
    public function itineraryblock(){
        return $this->belongsTo(ItinerariyBlocks::class);
    }

    // public function templatefields(){
    //     return $this->hasMany(TemplateField::class);
    // }

    public function templatefield(){
        return $this->belongsTo(TemplateField::class);
    }

}
