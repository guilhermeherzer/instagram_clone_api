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

    	if($seguidores->lista_seguidores):
    		$num_seguidores = unserialize($seguidores->lista_seguidores);
    		$num_seguidores = count($num_seguidores);
    	else:
    		$num_seguidores = 0;
    	endif;

    	/* Resgata todos os seguidos e faz a conta de quantos tem */

    	$seguidos = DB::table('seguidos')
    		->where('user_id', $request->id)
    		->first();

    	if($seguidos->lista_seguidos):
    		$num_seguidos = unserialize($seguidos->lista_seguidos);
    		$num_seguidos = count($num_seguidos);
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

    	if($seguidos->lista_seguidos):
    		$lista_seguidos = unserialize($seguidos->lista_seguidos);
    	else:
    		$lista_seguidos = array();
    	endif;

    	array_push($lista_seguidos, intval($request->id));
    	
    	$posts = DB::table("posts")
    		->select('posts.*', 'users.user', 'users.user_img')
    		->leftJoin('users', 'users.id', 'posts.user_id')
    		->whereIn('posts.user_id', $lista_seguidos)
    		->orderBy('posts.created_at', 'desc')
    		->get();

    	$responseData = array('data' => $posts);

    	return response()->json(compact('responseData'));
    }

    public function ver_perfil(Request $request){
    	/* Resgata as informações do usuário */
    	$user_auth = DB::table('users')
    		->select('users.name', 'users.user', 'users.user_img', 'seguidos.lista_seguidos')
    		->leftJoin('seguidos', 'seguidos.user_id', 'users.id')
    		->where('users.id', $request->myid)
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
    	
    	if(in_array($request->userid, $seguidos_auth) && in_array($request->myid, $seguidores)):
    		$status_seguir = array(
    			'id' => 1,
    			'texto' => "Seguindo"
    		);
    	elseif(!in_array($request->userid, $seguidos_auth) && in_array($request->myid, $seguidos) && !in_array($request->myid, $seguidores)):
    		$status_seguir = array(
    			'id' => 2,
    			'texto' => "Seguir de Volta"
    		);
    	elseif(in_array($request->userid, $seguidos_auth) && !in_array($request->myid, $seguidos) && in_array($request->myid, $seguidores)):
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

    public function seguir(Request $request){
    	/* Resgata as informações do usuário auth */
    	$user_auth = DB::table('users')
    		->select('users.name', 'users.user', 'users.user_img', 'seguidos.lista_seguidos')
    		->leftJoin('seguidos', 'seguidos.user_id', 'users.id')
    		->where('users.id', $request->myid)
    		->first();

    	/* Verifica se o usuário auth já esta seguindo ou não */
    	if($user_auth->lista_seguidos):
    		$seguidos = unserialize($user_auth->lista_seguidos);
    	else:
    		$seguidos = array();
    	endif;

    	/* Resgata as informações do usuário */
    	$user = DB::table('users')
    		->select('users.name', 'users.user', 'users.user_img', 'seguidores.lista_seguidores')
    		->leftJoin('seguidores', 'seguidores.user_id', 'users.id')
    		->where('users.id', $request->userid)
    		->first();

    	/* Verifica se o usuário já esta sendo seguido ou não */
    	if($user->lista_seguidores):
    		$seguidores = unserialize($user->lista_seguidores);
    	else:
    		$seguidores = array();
    	endif;

    	/* Faz o teste para a validação dos dados */
    	if(!in_array($request->userid, $seguidos) && !in_array($request->myid, $seguidores)):
    		array_push($seguidos, intval($request->userid));
    		$seguidos = serialize($seguidos);

    		array_push($seguidores, intval($request->myid));
    		$seguidores = serialize($seguidores);

    		$seguidos_data = array(
    			'user_id' => $request->myid,
    			'lista_seguidos' => $seguidos,
    		);

    		$seguidores_data = array(
    			'user_id' => $request->userid,
    			'lista_seguidores' => $seguidores,
    		);

    		$seguidos = DB::table('seguidos')->where('user_id', $request->myid)->update($seguidos_data);
    		$seguidores = DB::table('seguidores')->where('user_id', $request->userid)->update($seguidores_data);

    		if($seguidos && $seguidores):
    			$responseData = array('success'=>'1', 'message'=>"Seguindo com sucesso!");
    		endif;
    	else:
    		$responseData = array('success'=>'0', 'message'=>"Erro ao seguir!");
    	endif;

    	return response()->json(compact('responseData'));
    }

    public function buscar(Request $request){
    	if(!is_null($request->texto)):
	    	$users = DB::table('users')
	    		->select('name', 'user', 'user_img')
	    		->where('user', 'LIKE', $request->texto.'%')
	    		->get();
    	else:
    		$users = array();
    	endif;

    	$responseData = array('users'=>$users);

    	return response()->json(compact('responseData'));
    }
}
