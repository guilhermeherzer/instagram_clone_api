<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class LikeController extends Controller
{
    public function like(Request $request){
        $likes = DB::table('likes')
            ->where('post_id', $request->post_id)
            ->first();

        $users = unserialize($likes->user_id);

        $user_in_array = in_array($request->my_id, $users);

        if(!$user_in_array):
                $users[] = intval($request->my_id);

                $users = serialize($users);

                $like_dados = array(
                    'user_id' => $users,
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $like = DB::table('likes')->where('post_id', $request->post_id)->update($like_dados);

                if($like):
                    $responseData = array('success' => 1, 'is_liked' => true);
                else:
                    $responseData = array('success' => 0);
                endif;
        else:
                $users_id = array_search($request->my_id, $users);

                array_splice($users, $users_id, 1);

                $users = serialize($users);

                $like_dados = array(
                    'user_id' => $users,
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $like = DB::table('likes')->where('post_id', $request->post_id)->update($like_dados);

                if($like):
                    $responseData = array('success' => 1, 'is_liked' => false);
                else:
                    $responseData = array('success' => 0);
                endif;
        endif;
        
        return response()->json(compact('responseData'));
    }
}
