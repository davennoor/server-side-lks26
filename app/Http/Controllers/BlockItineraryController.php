<?php

namespace App\Http\Controllers;

use App\Models\BlockFieldValue;
use App\Models\ItinerariyBlocks;
use App\Models\Itinerary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\map;

class BlockItineraryController extends Controller
{
    public function postblocks(Request $request, $slug)
    {
        $validator = Validator::make($request->all(),[
            'template_id'=>'required|integer|exists:templates,id',
            'position'=>'required|integer|min:1'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>'error',
                'message'=>'Invalid field',
                'errors'=>$validator->errors()
            ], 403);
        }

        $itineraries = Itinerary::where('slug', $slug)->with('itineraryblock.template.templatefield')->first();
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

        $data = ItinerariyBlocks::create([
            'itinerary_id'=>$itineraries->id,
            'template_id'=>$request->template_id,
            'position'=>$request->position
        ]);

        // 🔥 INI KUNCI PERBAIKANNYA: Muat relasi biar $data->template->id TIDAK error null lagi
        $data->load('template.templatefield');

        return response()->json([
            'status'=>'success',
            'message'=>'Block added successful',
            'data'=>[
                'id'=>$data->id,
                'template_id'=>$data->template->id,
                'position'=>$data->position,
                'template'=>[
                        'id'=>$data->template->id,
                        'name'=>$data->template->name,
                        'slug'=>$data->template->slug
                    ],
                'fields'=>$itineraries->itineraryblock->first()->template->templatefield->map( function($field) {
                    return[
                        'id'=>$field->id,
                        'name'=>$field->name,
                        'slug'=>\Illuminate\Support\Str::slug($field->name),
                        'type'=>$field->type,
                        'value'=>null
                    ];
                }),
                'created_at'=>$data->created_at,
                'upddated_at'=>$data->updated_at
            ]
        ], 201);
    }

    public function putblocks(Request $request, $slug, $blockId)
    {
        // $validator = Validator::make($request->all(),[
        //     'fields'=>'required|array',
        //     'fields.*.field_id'=>'required|integer|exists:template_fields,id',
        //     'fields.*.value'=>'required|string',
        // ]);

        // if($validator->fails())
        //     {
        //         return response()->json([
        //             'status'=>'error',
        //             'message'=>'Invalid field',
        //             'errors'=>$validator->errors()
        //         ], 422);
        //     }

        // $itinerary = Itinerary::where('slug',$slug)
        // ->where('user_id',Auth::id())
        // ->with('itineraryblock.template.templatefield')
        // ->first();

        // if(!$itinerary)
        //     {
        //         return response()->json([
        //             'status'=>'error',
        //             'message'=>'Not found'
        //         ], 404);
        //     }

        // $itineraryblock = ItinerariyBlocks::where('itinerary_id',$itinerary->id)
        // ->where('id',$blockId)
        // ->with('template.templatefield')
        // ->first();

        // if(!$itineraryblock)
        //     {
        //         return response()->json([
        //             'status'=>'error',
        //             'message'=>'Not found'
        //         ], 404);
        //     }

        
        
        // if($itinerary->user_id !== Auth::id())
        //     {
        //         return response()->json([
        //             'status'=>'error',
        //             'message'=>'Forbidden access'
        //         ], 403);
        //     }


        //     // 3. PROSES LOOPING UPDATE (BAGIAN YANG PALING TEPAT), pelajari lagi bagian ini
        //     foreach ($request->fields as $field) {
        //         // Menggunakan updateOrCreate agar jika datanya belum ada akan dibuat baru, 
        //         // jika sudah ada akan langsung diupdate nilainya.
        //         BlockFieldValue::updateOrCreate(
        //             [
        //                 'itinerary_block_id' => $itineraryblock->id,
        //                 'template_field_id'  => $field['field_id']
        //             ],
        //             [
        //                 'value' => $field['value']
        //             ]
        //         );
        //     }
        //     // 4. KEMBALIKAN RESPONSE SUKSES (FORMAT SESUAI SOAL LKS)
        //     // Muat ulang relasi agar data terupdate ikut terbawa
        //     $itineraryblock->load('template.templatefield');
        // //$itineraryblock->update($validator->validated()); gak cocok buat data aray yang banyak

        // // 1. Ambil semua nilai pada block ini, jadikan array dengan format: [field_id => value],pelajari lagi
        // $savedValues = BlockFieldValue::where('itinerary_block_id', $itineraryblock->id)
        //     ->pluck('value', 'template_field_id')
        //     ->toArray();
        // // Hasil dari $savedValues strukturnya akan instan seperti ini: [7 => "Pantai", 8 => "08:00"]
        

        // return response()->json([
        //     'status'=>'success',
        //     'message'=>'Block  fields updated successful',
        //     'data'=>[
        //         'id'=>$itineraryblock->id,
        //         'template_id'=>$itineraryblock->template->id,
        //         'position'=>$itineraryblock->position,
        //         'fields'=>$itineraryblock->template->templatefield->map(function($item) use ($savedValues){
        //         return[
        //             'id'=>$item->id,
        //             'name'=>$item->name,
        //             'slug'=>$item->name,
        //             'type'=>$item->type,
        //             'value'=>$savedValues[$item->id] ?? null

        //         ];
        //     }),
        //     ],
        // ], 200);

        // 1. Validasi Input Array fields dari Postman
    $validator = Validator::make($request->all(), [
        'fields' => 'required|array',
        'fields.*.field_id' => 'required|integer|exists:template_fields,id',
        'fields.*.value' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid field',
            'errors' => $validator->errors()
        ], 422);
    }

    // 2. LANGSUNG TO THE POINT: Cari block berdasarkan ID yang dikirim (misal: 34)
    // Sekaligus kunci agar block ini hanya bisa diakses oleh pemilik itinerary-nya
    $itineraryblock = ItinerariyBlocks::where('id', $blockId)
        ->whereHas('itinerary', function($query) {
            $query->where('user_id', Auth::id());
        })
        ->with('template.templatefield')
        ->first();

    // Jika Block ID tersebut tidak ditemukan di database
    if (!$itineraryblock) {
        return response()->json([
            'status' => 'error',
            'message' => 'Block not found'
        ], 404);
    }

    // 3. PROSES LOOPING SIMPAN DATA (Kolom tanpa 's': template_field_id)
    foreach ($request->fields as $field) {
        BlockFieldValue::updateOrCreate(
            [
                'itinerary_block_id' => $itineraryblock->id,
                'template_field_id'  => $field['field_id'] 
            ],
            [
                'value' => $field['value']
            ]
        );
    }

    // 4. Ambil semua nilai yang baru disimpan untuk dilempar ke response
    $savedValues = BlockFieldValue::where('itinerary_block_id', $itineraryblock->id)
        ->pluck('value', 'template_field_id')
        ->toArray();

    // 5. Return Response JSON Sukses Sesuai Standar Ujian LKS
    return response()->json([
        'status' => 'success',
        'message' => 'Block fields updated successful',
        'data' => [
            'id'          => $itineraryblock->id,
            'template_id' => $itineraryblock->template->id,
            'position'    => $itineraryblock->position,
            'fields'      => $itineraryblock->template->templatefield->map(function($item) use ($savedValues) {
                return [
                    'id'          => $item->id,
                    'template_id' => $item->template_id,
                    'name'        => $item->name,
                    'slug'        => \Illuminate\Support\Str::slug($item->name),
                    'type'        => $item->type,
                    'value'       => $savedValues[$item->id] ?? null
                ];
            }),
        ],
    ], 200);
    }

    public function reorderblocks(Request $request, $slug)
    {
        $validator = Validator::make($request->all(),[
            'block'=>'required|array',
            'block.*'=>'required|integer|exists:itinerary_blocks,id'
        ]);

        if($validator->fails())
            {
                return response()->json([
                    'status'=>'error',
                    'message'=>'Invalid field',
                    'errors'=>$validator->errors()
                ], 404);
            }

        $itinerary = Itinerary::where('user_id',Auth::id())
        ->where('slug',$slug)
        ->first();

        if(!$itinerary){
            return response()->json([
                'status'=>'error',
                'message'=>'Not Found'
            ], 404);
        }

        if($itinerary->user_id !== Auth::id()){
            return response()->json([
                'status'=>'error',
                'message'=>'Forbidden access'
            ], 403);
        }

        // 3. Proses Mengubah Posisi (Reorder) di Database
    // Kita looping array ID yang dikirim. $index dimulai dari 0, jadi urutan posisinya adalah $index + 1
       foreach ($request->blocks as $index => $blockId) {
        $itineraryblock = ItinerariyBlocks::where('itinerary_id',$itinerary->id)
        ->where('id',$blockId)
        ->update([
            'position' => $index + 1
        ]);
        }

        return response()->json([
            'status'=>'success',
            'message'=>'Blocks reordered successful'
        ], 200);
    }

    public function deleteblocks($slug , $blockId)
    {
        $itinerary = Itinerary::where('slug',$slug)
        ->where('user_id',Auth::id())
        ->with('itineraryblock.template.templatefield')
        ->first();

        if(!$itinerary)
            {
                return response()->json([
                    'status'=>'error',
                    'message'=>'Not found'
                ], 404);
            }

        $itineraryblock = ItinerariyBlocks::where('itinerary_id',$itinerary->id)
        ->where('id',$blockId)
        ->with('template.templatefield')
        ->first();

        if(!$itineraryblock)
            {
                return response()->json([
                    'status'=>'error',
                    'message'=>'Not found'
                ], 404);
            }

        
        
        if($itinerary->user_id !== Auth::id())
            {
                return response()->json([
                    'status'=>'error',
                    'message'=>'Forbidden access'
                ], 403);
            }
        
        $itineraryblock->delete();
        return response()->json([
            'status'=>'success',
            'message'=>'Block Removed successful'
        ],200);
    }
}