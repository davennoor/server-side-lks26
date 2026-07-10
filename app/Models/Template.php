<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('name','slug')]
class Template extends Model
{
    // public function itineraryblock()
    // {
    //     return $this->belongsTo(ItinerariyBlocks::class);
    // }

    public function templatefield()
    {
        return $this->hasMany(TemplateField::class);
    }
}
