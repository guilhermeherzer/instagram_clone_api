<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class FeedController extends Controller
{
    public function feed(Request $request){
        $user = DB::table('users')
            ->select('users.*', 'seguidos.lista_seguidos')
            ->leftJoin('seguidos', 'seguidos.user_id', 'users.id')
            ->where('users.id', auth()->user()->id)
            ->first();

        $users_in_feed = unserialize($user->lista_seguidos);

        array_push($users_in_feed, auth()->user()->id);

        $stories_edges = array();

        $stories = DB::table('users')
            ->whereIn('id', $users_in_feed)
            ->get();

        foreach($stories as $s):
            if(strlen($s->username) > 9):
                $username = substr($s->username, 0, 9);
                $username = $username."...";
            else:
                $username = $s->username;
            endif;

            $stories_edges[] = [
                "node" => [
                    "id" => $s->id,
                    "username" => $username,
                    "profile_pic_url" => $s->profile_pic_url
                ]
            ];
        endforeach;

        $posts = DB::table('posts')
            ->select('posts.*', 'posts.id as postId', 'users.*', 'users.id as userId')
            ->leftJoin('users', 'users.id', 'posts.user_id')
            ->whereIn('posts.user_id', $users_in_feed)
            ->orderBy('posts.created_at', 'desc')
            ->get();

        $posts_edges = array();

        foreach($posts as $p):
            $comments = DB::table('comentarios')
                ->select('comentarios.*', 'comentarios.id as commentId', 'users.*', 'users.id as userId')
                ->leftJoin('users', 'users.id', 'comentarios.user_id')
                ->where('post_id', $p->postId)
                ->get();

            $comments_edges = array();

            foreach($comments->slice(0, 2) as $c):
                $comments_edges[] = [
                    "node" => [
                        "username" => $c->username,
                        "text" => $c->text
                    ]
                ];
            endforeach;

            $is_liked = unserialize($p->likes);
            $is_liked = in_array(auth()->user()->id, $is_liked);

            $posts_edges[] = [
                "node" => [
                    "id" => $p->postId,
                    "display_url" => $p->display_url,
                    "owner" => [
                        "id" => $p->userId,
                        "username" => $p->username,
                        "profile_pic_url" => $p->profile_pic_url
                    ],
                    "text" => $p->text,
                    "edge_media_to_comment" => [
                        "count" => count($comments)
                    ],
                    "edge_like_by" => [
                        "count" => count(unserialize($p->likes))
                    ],
                    "location" => $p->location,
                    "is_liked" => $is_liked,
                    "comments" => $comments_edges
                ]
            ];
        endforeach;

        $responseData = [
            "stories" => $stories_edges,
            "posts" => $posts_edges
        ];

    	return response()->json(compact('responseData'));
    }
}
