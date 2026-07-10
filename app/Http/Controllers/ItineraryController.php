<?php

namespace App\Http\Controllers;

use App\Models\ItinerariyBlocks;
use App\Models\Itinerary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ItineraryController extends Controller
{
    public function itinerariespost(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title'=>'required|string',
            'slug'=>'required|string|unique:itineraries,slug|regex:/^[a-z0-9-]+$/',
            'summary'=>'nullable|string'
        ]);


        if($validator->fails())
            {
                return response()->json([
                    'status'=>'error',
                    'message'=>'Unauthenticated.',
                    'errors'=>$validator->errors()
                ],401);
            }
           
            $itineraries = Itinerary::create([
                'title'=>$request->title,
                'slug'=>$request->slug,
                'summary'=>$request->summary,
                'user_id'=>$request->user()->id
            ]);

            return response()->json([
                'status'=>'success',
                'message'=>'Itinerary created successful',
                'data'=>[
                    'id'=>$itineraries->id,
                    'user_id'=>$itineraries->user_id,
                    'title'=>$itineraries->title,
                    'slug'=>$itineraries->slug,
                    'summary'=>$itineraries->summary,
                    'created_at'=>$itineraries->created_at,
                    'updated_at'=>$itineraries->updated_at
                ]
            ], 201);
    }

    public function itinerariesget()
    {
        
        $itinerary = Itinerary::where('user_id', Auth::id())->get();
        $data = $itinerary->map(function($item){
            return [
                    'id'=>$item->id,
                    'user_id'=>$item->user_id,
                    'title'=>$item->title,
                    'slug'=>$item->slug,
                    'summary'=>$item->summary,
                    'created_at'=>$item->created_at,
                    'updated_at'=>$item->updated_at   
            ];
        });
        return response()->json([
            'status'=>'success',
            'message'=>'Get all Itineraries successful',
            'data'=>[
                'itineraries' => $data
            ]
        ], 200);   
    }

    public function itinerariesslug($slug)
    {
        $itinerary = Itinerary::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->with('itineraryblock.template', 'itineraryblock.blockfieldvalue')
            ->first();

        if (!$itinerary) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Not found',
            ], 404);
        }

        $result = [
            'status'  => 'success',
            'message' => 'Get itinerary successful',
            'data'    => [
                'id'         => $itinerary->id,
                'user_id'    => $itinerary->user_id,
                'title'      => $itinerary->title,
                'slug'       => $itinerary->slug,
                'summary'    => $itinerary->summary,
                'blocks'     => $itinerary->itineraryblock->map(function ($block) {
                    return [
                        'id'       => $block->id,
                        'position' => $block->position,
                        'template' => [
                            'id'   => $block->template->id,
                            'name' => $block->template->name,
                            'slug' => $block->template->slug,
                        ],
                        'fields'   => $block->blockfieldvalue->map(function ($fieldValue) {
                            return [
                                'id'    => $fieldValue->id,
                                'name'  => $fieldValue->templateField->name,
                                'slug'  => $fieldValue->templateField->slug,
                                'type'  => $fieldValue->templateField->type,
                                'value' => $fieldValue->value,
                            ];
                        }),
                    ];
                }),
                'created_at' => $itinerary->created_at,
                'updated_at' => $itinerary->updated_at,
            ],
        ];

        return response()->json($result,200);
    }

    public function itinerariesput(Request $request, $slug)
    {
        $itineraries = Itinerary::where('slug', $slug)->first();
        if(!$itineraries){
            return response()->json([
                'status'=>'error',
                'message'=>'Not found'
            ], 404);
        }

        if($itineraries->user_id !== Auth::id()){
            return response()->json([
                'status'=>'error',
                'message'=>'Forbidden access'
            ], 403);
        }

        $validator = Validator::make($request->all(),[
            'title'=>'nullable|string',
            'slug'=>'nullable|string|regex:/^[a-z0-9-]+$/|unique:itineraries,slug,' . $itineraries->slug,
            'summary'=>'nullable|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>'error',
                'message'=>'Invalid field',
                'errors'=>$validator->errors()
            ], 422);
        }

        
        $itineraries->update($validator->validated());

        return response()->json([
                'status'=>'success',
                'message'=>'Itinerary created successful',
                'data'=>[
                    'id'=>$itineraries->id,
                    'user_id'=>$itineraries->user_id,
                    'title'=>$itineraries->title,
                    'slug'=>$itineraries->slug,
                    'summary'=>$itineraries->summary,
                    'created_at'=>$itineraries->created_at,
                    'updated_at'=>$itineraries->updated_at
                ]
            ], 201);
    }

    public function itinerarydelete($slug)
    {
        $itinerary = Itinerary::where('slug', $slug)->first();
        if(!$itinerary){
            return response()->json([
                'status'=>'error',
                'message'=>'Not found'
            ], 404);
        }

       
        if($itinerary->user_id !== Auth::id()){
            return response()->json([
                'status'=>'error',
                'message'=>'Forbidden access'
            ], 403);
        }

        

        $itinerary->delete();
        return response()->json([
            'status'=>'success',
            'message'=>'Itinerary deleted successful'
        ],200);
    }
}

