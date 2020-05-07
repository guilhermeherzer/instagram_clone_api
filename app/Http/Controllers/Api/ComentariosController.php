<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class ComentariosController extends Controller
{
    public function comentarios(Request $request){
        $post = DB::table('posts')
            ->where('id', $request->post_id)
            ->first();

        $data = array(
            'id' => $post->id, 
            'legenda' => $post->legenda, 
            'criado_ha' => ''
        );

        $user = DB::table('users')
            ->select('id', 'name', 'user', 'user_img')
            ->where('id', $post->user_id)
            ->first();

        $owner_post = array(
            'id' => $user->id, 
            'username' => $user->user, 
            'profile_pic_url' => $user->user_img, 
            'full_name' => $user->name
        );

        $data['owner_post'] = $owner_post;
            
        $comentarios = DB::table('comentarios')
            ->where('post_id', $request->post_id)
            ->get();

        if($comentarios):
            foreach($comentarios as $c):
                $user = DB::table('users')
                    ->select('id', 'user', 'user_img')
                    ->where('id', $c->user_id)
                    ->first();

                $comentario = array(
                    'id' => $c->id, 
                    'texto' => $c->texto, 
                    'criado_ha' => ''
                );

                $comentario['user'] = array(
                    'id' => $user->id, 
                    'username' => $user->user, 
                    'profile_pic_url' => $user->user_img
                );

                $data['comentarios'][] = $comentario;
            endforeach;
        else:
        endif;

        $user = DB::table('users')
            ->select('user_img')
            ->where('id', $request->my_id)
            ->first();

        $data['user_auth'] = array(
            'profile_pic_url' => $user->user_img
        );

        $responseData = array(
            'data' => $data
        );

        return response()->json(compact('responseData'));
    }

    public function comentar(Request $request){
        $post = DB::table('posts')
            ->where('id', $request->post_id)
            ->first();

        if($post):
            $comentario_dados = array(
                'post_id' => $request->post_id,
                'user_id' => $request->user_id,
                'texto' => $request->texto,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $comentario = DB::table('comentarios')->insert($comentario_dados);

            if($comentario):
                return redirect('api/comentarios/'.$request->user_id.'/'.$request->post_id);
            else:
                $responseData = array('success'=>'0');
            endif;

        else:
                $responseData = array('success'=>'0');
        endif;
        
        return response()->json(compact('responseData'));
    }
}
