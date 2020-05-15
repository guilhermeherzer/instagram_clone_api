<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class CommentsController extends Controller
{
    public function show(Request $request){
        $post = DB::table('posts')
            ->select('users.*', 'users.id as userId', 'posts.*', 'posts.id as postId')
            ->leftJoin('users', 'users.id', 'posts.user_id')
            ->where('posts.id', $request->id)
            ->first();

        $comments = DB::table('comentarios')
            ->select('users.*', 'users.id as userId', 'comentarios.*', 'comentarios.id as comentarioId')
            ->leftJoin('users', 'users.id', 'comentarios.user_id')
            ->where('post_id', $request->id)
            ->get();

        $nodes = array();

        foreach($comments as $c):
            $nodes[] = [
                "node" => [
                    "id" => $c->comentarioId,
                    "text" => $c->text,
                    "owner" => [
                        "id" => $c->userId,
                        "profile_pic_url" => $c->profile_pic_url,
                        "username" => $c->username
                    ],
                ]
            ];
        endforeach;

        $responseData = array(
            "id" => $post->postId,
            "text" => $post->text,
            "owner" => [
                "id" => $post->userId,
                "profile_pic_url" => $post->profile_pic_url,
                "username" => $post->username,
                "full_name" => $post->full_name,
            ],
            "comments" => [
                "count" => count($comments),
                "edges" => $nodes
            ]
        );

        return response()->json(compact('responseData'));
    }

    public function store(Request $request){
        $post = DB::table('posts')
            ->where('id', $request->id)
            ->first();

        if($post):
            $comentario_dados = array(
                'post_id' => $request->id,
                'user_id' => auth()->user()->id,
                'text' => $request->text,
                'likes' => serialize(array()),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $comentario = DB::table('comentarios')->insert($comentario_dados);

            if($comentario):
                return redirect('api/comentarios/'.$request->id);
            else:
                $responseData = array('success'=>'0');
            endif;

        else:
                $responseData = array('success'=>'0');
        endif;
        
        return response()->json(compact('responseData'));
    }
}
