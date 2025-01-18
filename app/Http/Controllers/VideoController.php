<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; 


use App\Models\Video;

class VideoController extends Controller
{   

    // Fetches all videos
    public function index()
    {  
        try{
            // check if videos cached or not
            $videos = cache::remember('videos',3600, function(){
                return Video::all();
            });

            if ($videos->isNotEmpty()) {
                $response =['error'=> false,
                       'data' => $videos,
                       'message'=> 'success'];
                return response()->json($response, 200);
            }
            $response =[
                'error'=> false,
                'message'=> 'videos not found'
            ];         
            
            return response()->json($response,404);
        }
        catch(\Exception $e){
            return response()->json([
                'error'  => true,
                'message' => $e->getMessage
            ],500);
        }
        
    } 
    
    // Fetches a specific video by its Id
    public function show($id)
    {   
        try{
            $video = Video::find($id);
            if (!$video) {
                return response()->json(['error' => true,'message' => 'Video not found'], 404);
            }
            $response =[
                'error'=> false,
                'data'   => $video,
                'message'=> 'success'];
            return response()->json($response, 200);
        }
        catch(\Exception $e){
            return response()->json([
                'error'  => true,
                'message' => $e->getMessage
            ],500);
        }
        
    }


}
