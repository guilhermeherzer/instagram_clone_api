<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class SeguirController extends Controller
{
    public function store(Request $request){
    	/* Resgata as informações do usuário auth */
    	$user_auth = DB::table('users')
    		->select('users.full_name', 'users.username', 'users.profile_pic_url', 'seguidos.lista_seguidos')
    		->leftJoin('seguidos', 'seguidos.user_id', 'users.id')
    		->where('users.id', auth()->user()->id)
    		->first();

    	/* Verifica se o usuário auth já esta seguindo ou não */
    	if($user_auth->lista_seguidos):
    		$seguidos = unserialize($user_auth->lista_seguidos);
    	else:
    		$seguidos = array();
    	endif;

    	/* Resgata as informações do usuário */
    	$user = DB::table('users')
    		->select('users.full_name', 'users.username', 'users.profile_pic_url', 'seguidores.lista_seguidores')
    		->leftJoin('seguidores', 'seguidores.user_id', 'users.id')
    		->where('users.id', $request->id)
    		->first();

    	/* Verifica se o usuário já esta sendo seguido ou não */
    	if($user->lista_seguidores):
    		$seguidores = unserialize($user->lista_seguidores);
    	else:
    		$seguidores = array();
    	endif;

    	/* Faz o teste para a validação dos dados */
    	if(!in_array($request->id, $seguidos) && !in_array(auth()->user()->id, $seguidores)):
    		array_push($seguidos, intval($request->id));
    		$seguidos = serialize($seguidos);

    		array_push($seguidores, intval(auth()->user()->id));
    		$seguidores = serialize($seguidores);

    		$seguidos_data = array(
    			'user_id' => auth()->user()->id,
    			'lista_seguidos' => $seguidos,
    		);

    		$seguidores_data = array(
    			'user_id' => $request->id,
    			'lista_seguidores' => $seguidores,
    		);

    		$seguidos = DB::table('seguidos')->where('user_id', auth()->user()->id)->update($seguidos_data);
    		$seguidores = DB::table('seguidores')->where('user_id', $request->id)->update($seguidores_data);

    		if($seguidos && $seguidores):
    			$responseData = array('success'=>'1', 'message'=>"Seguindo com sucesso!");
    		endif;
    	else:
    		$responseData = array('success'=>'0', 'message'=>"Erro ao seguir!");
    	endif;

    	return response()->json(compact('responseData'));
    }

    public function destroy(Request $request){
    	$user_auth = DB::table('users')
    		->leftJoin('seguidos', 'seguidos.user_id', 'users.id')
    		->where('users.id', auth()->user()->id)
    		->first();

    	$seguidos = unserialize($user_auth->lista_seguidos);

    	$user = DB::table('users')
    		->leftJoin('seguidores', 'seguidores.user_id', 'users.id')
    		->where('users.id', $request->id)
    		->first();

    	$seguidores = unserialize($user->lista_seguidores);

    	if(in_array($request->id, $seguidos) && in_array(auth()->user()->id, $seguidores)):
    		$myid = intval(array_search(auth()->user()->id, $seguidores));
    		$userid = intval(array_search($request->id, $seguidos));

    		array_splice($seguidores, $myid, 1);
    		array_splice($seguidos, $userid, 1);

    		$seguidos = serialize($seguidos);
    		$seguidores = serialize($seguidores);

    		$seguidos_data = array(
    			'lista_seguidos' => $seguidos,
    		);

    		$seguidores_data = array(
    			'lista_seguidores' => $seguidores
    		);

    		$seguidos = DB::table('seguidos')->where('user_id', auth()->user()->id)->update($seguidos_data);
    		$seguidores = DB::table('seguidores')->where('user_id', $request->id)->update($seguidores_data);

    		if($seguidos && $seguidores):
    			$success = '1';
    		else:
    			$success = '0';
    		endif;

    		$responseData = array('success'=>$success);
    	else:
            $responseData = array('success'=>'0');
    	endif;

    	return response()->json(compact('responseData'));
    }
}
