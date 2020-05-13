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
            ->leftJoin('seguidos', 'seguidos.user_id', 'users.id')
            ->leftJoin('seguidores', 'seguidores.user_id', 'users.id')
            ->where('users.id', auth()->user()->id)
            ->first();

        $posts = DB::table('posts')
            ->where('user_id', auth()->user()->id)
            ->get();

        $edges = array();

        foreach($posts as $p):
            $edges[] = [
                "node" => [
                    "id" => $p->id,
                    "display_url" => $p->display_url,
                    "owner" => [
                        "id" => $user->id,
                        "username"=> $user->username
                    ],
                    "text" => $p->text,
                    "edge_media_to_comment" => [
                        "count" => 3
                    ],
                    "location" => $p->location,
                ]
            ];
        endforeach;

        $responseData = [
            "user" => [
                "id" => $user->id,
                "profile_pic_url" => $user->profile_pic_url,
                "username" => $user->username,
                "full_name" => $user->full_name,
                "biography" => $user->biography,
                "edge_followed_by" => [
                    "count" => count(unserialize($user->lista_seguidores))
                ],
                "edge_follow" => [
                    "count" => count(unserialize($user->lista_seguidos))
                ],
                "posts" => [
                    "count" => count($posts),
                    "edges" => $edges
                ]
            ]
        ];

        return response()->json(compact('responseData'));
    }

    public function ver_perfil(Request $request){
        $user = DB::table('users')
            ->leftJoin('seguidos', 'seguidos.user_id', 'users.id')
            ->leftJoin('seguidores', 'seguidores.user_id', 'users.id')
            ->where('users.id', $request->id)
            ->first();

        $follow_status = in_array(auth()->user()->id, unserialize($user->lista_seguidores));

        $posts = DB::table('posts')
            ->where('user_id', $request->id)
            ->get();

        $edges = array();

        foreach($posts as $p):
            $edges[] = [
                "node" => [
                    "id" => $p->id,
                    "display_url" => $p->display_url,
                    "owner" => [
                        "id" => $user->id,
                        "username"=> $user->username
                    ],
                    "text" => $p->text,
                    "edge_media_to_comment" => [
                        "count" => 3
                    ],
                    "location" => $p->location,
                ]
            ];
        endforeach;

    	$responseData = [
            "follow_status" => $follow_status,
            "user" => [
                "id" => $user->id,
                "profile_pic_url" => $user->profile_pic_url,
                "username" => $user->username,
                "full_name" => $user->full_name,
                "biography" => $user->biography,
                "edge_followed_by" => [
                    "count" => count(unserialize($user->lista_seguidores))
                ],
                "edge_follow" => [
                    "count" => count(unserialize($user->lista_seguidos))
                ],
                "posts" => [
                    "count" => count($posts),
                    "edges" => $edges
                ]
            ]
    	];

    	return response()->json(compact('responseData'));
    }
}
