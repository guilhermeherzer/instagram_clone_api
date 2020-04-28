<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class PostController extends Controller
{
    //
    public function meu_perfil(Request $request){
    	$user = DB::table('users')
    		->select('name', 'user', 'user_img')
    		->where('id', $request->id)
    		->first();

    	/* Resgata todos os seguidores e faz a conta de quantos tem */

    	$seguidores = DB::table('seguidores')
    		->where('user_id', $request->id)
    		->first();

    	if($seguidores->lista_seguidores != ""):
    		$num_seguidores = count(explode(',', $seguidores->lista_seguidores));
    	else:
    		$num_seguidores = 0;
    	endif;

    	/* Resgata todos os seguidos e faz a conta de quantos tem */

    	$seguidos = DB::table('seguidos')
    		->where('user_id', $request->id)
    		->first();

    	if($seguidos->lista_seguidos != ""):
    		$num_seguidos = count(explode(',', $seguidos->lista_seguidos));
    	else:
    		$num_seguidos = 0;
    	endif;

    	/* Resgata todos os posts do usuário */

    	$posts = DB::table("posts")
    		->where('user_id', $request->id)
    		->get();

    	$num_posts = count($posts);

    	$responseData = array(
    		'user' => $user, 
    		'posts' => $posts, 
    		'num_posts' => $num_posts, 
    		'seguidores' => $seguidores,
    		'num_seguidores' => $num_seguidores,
    		'seguidos' => $seguidos,
    		'num_seguidos' => $num_seguidos
    	);

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
