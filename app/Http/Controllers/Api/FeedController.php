<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
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
            ->select('id', 'username', 'profile_pic_url')
            ->whereIn('id', $lista_seguidos)
            ->get();

        foreach($user as $u):
            if(strlen($u->username) > 9):
                $user_name = substr($u->username, 0, 9);
                $user_name = $user_name."...";
            else:
                $user_name = $u->username;
            endif;

            $data['stories'][] = [
                'id' => $u->id,
                'user' => $user_name,
                'user_img' => $u->profile_pic_url
            ];
        endforeach;

        /* Informações para os Posts do Feed */

    	array_push($lista_seguidos, intval(auth()->user()->id));

        $posts = DB::table('posts')
            ->select('users.id as user_id', 'users.username', 'users.profile_pic_url', 'posts.*')
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
                            'username' => $user->username,
                            'profile_pic_url' => $user->profile_pic_url
                        ]
                    ];
                endforeach;

                $likes = unserialize($p->likes);
                
                $is_liked = in_array(auth()->user()->id, $likes);

                $user = DB::table('users')
                	->select('username')
                	->whereIn('id', $likes)
                	->first();

                $data['posts'][] = [
                    'id' => $p->id, 
                    'display_url' => $p->display_url, 
                    'legenda' => $p->text, 
                    'criado_ha' => $p_criado_ha, 
                    'owner_post' => [
                        'id' => $p->user_id,
                        'username' => $p->username, 
                        'profile_pic_url' => $p->profile_pic_url
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
