<?php

namespace App\Http\Controllers;

use App\Models\Itinerary;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    public function gettemplate()
    {
        $itineraries = Itinerary::where('user_id',Auth::id())
        ->has('itineraryblock')
        ->with('itineraryblock.template.templatefield')
        ->first();

        
            /* 1. Cek dulu apakah itinerary-nya ada
            if (!$itineraries) {
                return response()->json(['message' => 'Itinerary not found'], 404);
            }*/

        // 2. Cek apakah itinerary tersebut punya block pertama
        //$firstBlock = $itineraries->itineraryblock->first();


            // if (!$firstBlock) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'This itinerary doesn\'t have any blocks yet'
            //     ], 404);
            // }

        $result = [
            'status'=>'success',
            'message'=>'Get all templates successful',
            'data'=> [
                'template'=> [
                    'id'=>$itineraries->itineraryblock->first()->template->id,
                    'name'=>$itineraries->itineraryblock->first()->template->name,
                    'slug'=>$itineraries->itineraryblock->first()->template->slug,
                    'fields'=>$itineraries->itineraryblock->first()->template->templatefield->map( function($field) {
                        return[
                            'id'=> $field->id,
                            'template_id'=>$field->template_id,
                            'name'=>$field->name,
                            'slug'=>$field->name,//idkkkkkk
                            'type'=>$field->type
                        ];
                    }),
                ]
            ]
        ];

        return response()->json($result,200);
    }

    public function getslugtemplate($slug)
    {
        $itineraries = Itinerary::where('slug',$slug)
        ->where('user_id',Auth::id())
        ->has('itineraryblock')
        ->with('itineraryblock.template.templatefield')
        ->first();

        if (!$itineraries) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Not found',
            ], 404);
        }

        $result = [
            'status'=>'success',
            'message'=>'Get all templates successful',
            'data'=> [
                'template'=> [
                    'id'=>$itineraries->itineraryblock->first()->template->id,
                    'name'=>$itineraries->itineraryblock->first()->template->name,
                    'slug'=>$itineraries->itineraryblock->first()->template->slug,
                    'fields'=>$itineraries->itineraryblock->first()->template->first()->templatefield->map(function($fields){
                    return[
                        'id'=>$fields->id,
                        'template_id'=>$fields->template_id,
                        'name'=>$fields->name,
                        'slug'=>$fields->template->slug,
                        'type'=>$fields->type
                    ];
                }),
                ]
            ]
        ];

        return response()->json($result,200);
        // Simpan objek template ke variabel biar makin pendek kodenya
        //$template = $firstBlock->template;

    }
}
