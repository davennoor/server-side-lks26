<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('name','type','template_id')]
class TemplateField extends Model
{
    public function template(){
        return $this->belongsTo(Template::class);
    }

    // public function blockfieldvalue(){
    //     return $this->belongsTo(BlockFieldValue::class);
    // }
}
