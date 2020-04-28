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
    
    public function feed(Request $request){
    	$seguidos = DB::table('seguidos')
    		->where('user_id', $request->id)
    		->first();

    	$lista_seguidos = explode(',', $seguidos->lista_seguidos);

    	array_push($lista_seguidos, $request->id);
    	
    	$posts = DB::table("posts")
    		->select('posts.*', 'users.user', 'users.user_img')
    		->leftJoin('users', 'users.id', 'posts.user_id')
    		->whereIn('posts.user_id', $lista_seguidos)
    		->orderBy('posts.created_at', 'desc')
    		->get();

    	$responseData = array('data' => $posts);

    	return response()->json(compact('responseData'));
    }
}
