<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class PostController extends Controller
{
    //
    public function meus_posts(Request $request){
    	$posts = DB::table("posts")
    		->where('user_id', $request->id)
    		->get();

    	$responseData = array('data' => $posts);

    	return response()->json(compact('responseData'));
    }
}
