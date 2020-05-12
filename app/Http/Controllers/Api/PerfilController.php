<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class PerfilController extends Controller
{
    public function meu_perfil(Request $request){
    	$user = DB::table('users')
    		->select('name', 'user', 'user_img')
    		->where('id', auth()->user()->id)
    		->first();

    	/* Resgata todos os seguidores e faz a conta de quantos tem */

    	$seguidores = DB::table('seguidores')
    		->where('user_id', auth()->user()->id)
    		->first();

    	if($seguidores->lista_seguidores):
    		$num_seguidores = unserialize($seguidores->lista_seguidores);
    		$num_seguidores = count($num_seguidores);
    	else:
    		$num_seguidores = 0;
    	endif;

    	/* Resgata todos os seguidos e faz a conta de quantos tem */

    	$seguidos = DB::table('seguidos')
    		->where('user_id', auth()->user()->id)
    		->first();

    	if($seguidos->lista_seguidos):
    		$num_seguidos = unserialize($seguidos->lista_seguidos);
    		$num_seguidos = count($num_seguidos);
    	else:
    		$num_seguidos = 0;
    	endif;

    	/* Resgata todos os posts do usuário */

    	$posts = DB::table("posts")
    		->where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
    		->get();

    	$num_posts = count($posts);

    	$responseData = array(
    		'user' => $user, 
            'teste' => auth()->user()->id,
    		'posts' => $posts, 
    		'num_posts' => $num_posts, 
    		'seguidores' => $seguidores,
    		'num_seguidores' => $num_seguidores,
    		'seguidos' => $seguidos,
    		'num_seguidos' => $num_seguidos
    	);

    	return response()->json(compact('responseData'));
    }

    public function ver_perfil(Request $request){
    	/* Resgata as informações do usuário */
    	$user_auth = DB::table('users')
    		->select('users.name', 'users.user', 'users.user_img', 'seguidos.lista_seguidos')
    		->leftJoin('seguidos', 'seguidos.user_id', 'users.id')
    		->where('users.id', auth()->user()->id)
    		->first();

    	/* Verifica se o usuário já esta sendo seguido ou não */
		if($user_auth->lista_seguidos):
	    	$seguidos_auth = unserialize($user_auth->lista_seguidos);
	    else:
	    	$seguidos_auth = array();
	    endif;

    	/* Resgata as informações do usuário */
    	$user = DB::table('users')
    		->select('users.name', 'users.user', 'users.user_img', 'seguidores.lista_seguidores', 'seguidos.lista_seguidos')
    		->leftJoin('seguidores', 'seguidores.user_id', 'users.id')
    		->leftJoin('seguidos', 'seguidos.user_id', 'users.id')
    		->where('users.id', $request->userid)
    		->first();

    	/* Verifica se o usuário já esta sendo seguido ou não */
    	if($user->lista_seguidores):
	    	$seguidores = unserialize($user->lista_seguidores);
	    else:
	    	$seguidores = array();
	    endif;

	    if($user->lista_seguidos):
	    	$seguidos = unserialize($user->lista_seguidos);
	    else:
	    	$seguidos = array();
	    endif;

    	/* Faz os testes para ver o status */
    	
    	if(in_array($request->userid, $seguidos_auth) && in_array(auth()->user()->id, $seguidores)):
    		$status_seguir = array(
    			'id' => 1,
    			'texto' => "Seguindo"
    		);
    	elseif(!in_array($request->userid, $seguidos_auth) && in_array(auth()->user()->id, $seguidos) && !in_array(auth()->user()->id, $seguidores)):
    		$status_seguir = array(
    			'id' => 2,
    			'texto' => "Seguir de Volta"
    		);
    	elseif(in_array($request->userid, $seguidos_auth) && !in_array(auth()->user()->id, $seguidos) && in_array(auth()->user()->id, $seguidores)):
    		$status_seguir = array(
    			'id' => 1,
    			'texto' => "Seguindo"
    		);
    	else:
    		$status_seguir = array(
    			'id' => 0,
    			'texto' => "Seguir"
    		);
    	endif;

    	/* Resgata todos os posts do usuário */
    	$posts = DB::table('posts')
    		->where('user_id', $request->userid)
    		->get();

    	$num_posts = count($posts);

    	$num_seguidos = count($seguidos);

    	$num_seguidores = count($seguidores);

    	$responseData = array(
    		'status_seguir' => $status_seguir,
    		'user' => $user, 
    		'posts' => $posts, 
    		'num_posts' => $num_posts, 
    		'num_seguidores' => $num_seguidores, 
    		'num_seguidos' => $num_seguidos
    	);

    	return response()->json(compact('responseData'));
    }
}
