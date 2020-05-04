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
        $feed = array();

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

        foreach($posts as $p):
            $post = array(
                'id' => $p->user_id,
                'user' => $p->user,
                'img' => $p->user_img,
                'post' => array(
                    'id' => $p->id,
                    'legenda' => $p->legenda,
                    'img' => $p->img,
                    'criado_em' => $p->created_at,
                    'comentarios' => array()
                )
            );

            $comentarios = DB::table('comentarios')
                ->where('post_id', $p->id)
                ->first();

            $comentarios = unserialize($comentarios->dados);

            foreach($comentarios['comentarios'] as $c):
                $user = DB::table('users')
                    ->where('id', $c['user_id'])
                    ->first();

                $comentario = array(
                    'id' => $c['id'],
                    'texto' => $c['texto'],
                    'user' => array(
                        'id' => $user->id,
                        'user' => $user->user,
                        'img' => $user->user_img
                    )
                );

                array_push($post['post']['comentarios'], $comentario);
            endforeach;

            array_push($feed, $post);

        endforeach;

    	$responseData = array('data' => $feed);

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

    public function desseguir(Request $request){
    	$user_auth = DB::table('users')
    		->leftJoin('seguidos', 'seguidos.user_id', 'users.id')
    		->where('users.id', $request->myid)
    		->first();

    	$seguidos = unserialize($user_auth->lista_seguidos);

    	$user = DB::table('users')
    		->leftJoin('seguidores', 'seguidores.user_id', 'users.id')
    		->where('users.id', $request->userid)
    		->first();

    	$seguidores = unserialize($user->lista_seguidores);

    	if(in_array($request->userid, $seguidos) && in_array($request->myid, $seguidores)):
    		$myid = intval(array_search($request->myid, $seguidores));
    		$userid = intval(array_search($request->userid, $seguidos));

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

    		$seguidos = DB::table('seguidos')->where('user_id', $request->myid)->update($seguidos_data);
    		$seguidores = DB::table('seguidores')->where('user_id', $request->userid)->update($seguidores_data);

    		if($seguidos && $seguidores):
    			$success = 1;
    		else:
    			$success = 0;
    		endif;

    		$responseData = array('success'=>$success);
    	else:
    	endif;

    	return response()->json(compact('responseData'));
    }

    public function buscar(Request $request){
    	if(!is_null($request->texto)):
	    	$users = DB::table('users')
	    		->select('id', 'name', 'user', 'user_img')
	    		->where('user', 'LIKE', $request->texto.'%')
	    		->get();
    	else:
    		$users = array();
    	endif;

    	$responseData = array('users'=>$users);

    	return response()->json(compact('responseData'));
    }

    public function comentarios(Request $request){
        $user_auth = DB::table('users')
            ->where('id', $request->my_id)
            ->first();

        $user_auth = array('user_img' => $user_auth->user_img);

        $post = DB::table('posts')
            ->where('id', $request->post_id)
            ->first();

        $user_post = DB::table('users')
            ->where('id', $post->user_id)
            ->first();

        $user_post = array('user' => $user_post->user, 'user_img' => $user_post->user_img);

        $post = array('legenda' => $post->legenda, 'user' => $user_post);

        $comentarios_dados = array();

        $comentarios = DB::table('comentarios')
            ->where('post_id', $request->post_id)
            ->first();

        $comentarios = unserialize($comentarios->dados);

        foreach($comentarios['comentarios'] as $c):
            $user = DB::table('users')
                ->where('id', $c['user_id'])
                ->first();

            $user_dado = array('id' => $user->id, 'user' => $user->user, 'user_img' => $user->user_img);

            $dado = array('id' => $c['id'], 'texto' => $c['texto'], 'criado_em' => '1', 'user' => $user_dado);

            $comentario = array('comentario' => $dado);

            array_push($comentarios_dados, $comentario);

        endforeach;

        $responseData = array('user_auth' => $user_auth, 'post' => $post, 'comentarios'=>$comentarios_dados);

        return response()->json(compact('responseData'));
    }

    public function comentar(Request $request){
        $comentarios = DB::table('comentarios')
            ->where('post_id', $request->post_id)
            ->first();

        $comentarios = unserialize($comentarios->dados);

        $quantidade = count($comentarios['comentarios']) + 1;

        $dado = array('id' => $quantidade, 'user_id' => intval($request->my_id), 'texto' => $request->texto);

        array_push($comentarios['comentarios'], $dado);

        $comentarios = serialize($comentarios);

        if($comentarios):
            $comentario_data = array(
                'dados' => $comentarios,
                'updated_at' => date('Y-m-d H:i:s')
            );

            DB::table('comentarios')->where('post_id', $request->post_id)->update($comentario_data);
            
            $responseData = array('success'=>'1');
        else:
            $responseData = array('success'=>'0');
        endif;
        //$comentarios = serialize(array('comentarios' => array()));
        //$responseData = array('data'=>$comentarios);


        return response()->json(compact('responseData'));
    }
}