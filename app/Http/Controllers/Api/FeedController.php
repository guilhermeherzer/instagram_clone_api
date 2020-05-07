<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class FeedController extends Controller
{
    public function feed(Request $request){
        $data = array();

    	$seguidos = DB::table('seguidos')
    		->where('user_id', auth()->user()->id)
    		->first();

    	if($seguidos->lista_seguidos):
    		$lista_seguidos = unserialize($seguidos->lista_seguidos);
    	else:
    		$lista_seguidos = array();
    	endif;

        /* Informações para os Stories do Feed */

        $user = DB::table('users')
            ->select('id', 'user', 'user_img')
            ->whereIn('id', $lista_seguidos)
            ->get();

        foreach($user as $u):
            if(strlen($u->user) > 9):
                $user_name = substr($u->user, 0, 9);
                $user_name = $user_name."...";
            else:
                $user_name = $u->user;
            endif;

            $data['stories'][] = [
                'id' => $u->id,
                'user' => $user_name,
                'user_img' => $u->user_img
            ];
        endforeach;

        /* Informações para os Posts do Feed */

    	array_push($lista_seguidos, intval(auth()->user()->id));

        $posts = DB::table('posts')
            ->select('users.id as user_id', 'users.user', 'users.user_img', 'posts.*')
            ->leftJoin('users', 'users.id', 'posts.user_id')
            ->whereIn('posts.user_id', $lista_seguidos)
            ->orderBy('posts.created_at', 'desc')
            ->get();

        if($posts):
            foreach($posts as $p):
                $p_criado_ha = date('d', (strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($p->created_at)))));

                $comentarios = DB::table('comentarios')
                    ->where('post_id', $p->id)
                    ->get();

                $comentarios_dados = array();

                $quantidade = count($comentarios);

                foreach($comentarios->slice(0, 2) as $c):
                    $c_criado_ha = date('d', (strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($c->created_at)))));
                    
                    $user = DB::table('users')
                        ->where('id', $c->user_id)
                        ->first();

                    $comentarios_dados[] = [
                        'id' => $c->id,
                        'texto' => $c->texto,
                        'criado_ha' => $c_criado_ha,
                        'user' => [
                            'username' => $user->user,
                            'profile_pic_url' => $user->user_img
                        ]
                    ];
                endforeach;

                $likes = DB::table('likes')
                    ->where('post_id', $p->id)
                    ->first();

                $likes = unserialize($likes->user_id);
                
                $is_liked = in_array(auth()->user()->id, $likes);

                $user = DB::table('users')
                	->select('user as username')
                	->whereIn('id', $likes)
                	->first();

                $data['posts'][] = [
                    'id' => $p->id, 
                    'display_url' => $p->img, 
                    'legenda' => $p->legenda, 
                    'criado_ha' => $p_criado_ha, 
                    'owner_post' => [
                        'id' => $p->user_id,
                        'username' => $p->user, 
                        'profile_pic_url' => $p->user_img
                    ],
                    'is_liked' => $is_liked,
                    'preview_likes' => $user,
                    'count_likes' => count($likes),
                    'comentarios_contagem' => $quantidade,
                    'comentarios' => $comentarios_dados
                ];
            endforeach;
        else:
        endif;

    	$responseData = array('data' => $data);

    	return response()->json(compact('responseData'));
    }
}
