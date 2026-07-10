<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlockItineraryController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\TemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function(){
    Route::post('/register','register');
    Route::post('/login','login');
    Route::post('/logout','logout')->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function(){
    Route::controller(ItineraryController::class)->group(function(){
        Route::post('/itineraries','itinerariespost');
        Route::get('/itineraries','itinerariesget');
        Route::get('/itineraries/{slug}','itinerariesslug');
        Route::put('/itineraries/{slug}','itinerariesput');
        Route::delete('/itineraries/{slug}','itinerarydelete');
    });

    Route::controller(TemplateController::class)->group(function(){
        Route::get('/template','gettemplate');
        Route::get('/template/{slug}','getslugtemplate');
    });

    Route::controller(BlockItineraryController::class)->group(function(){
        Route::post('/itineraries/{slug}/blocks','postblocks');
        Route::put('/itineraries/{slug}/blocks/{blockId}/fields','putblocks');
        Route::put('/itineraries/{slug}/blocks/reorder','reorderblocks');
        Route::delete('/itineraries/{slug}/blocks/{blockid}','deleteblocks');
    });
});

