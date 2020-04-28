<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use Hash;

use DB;

class UserController extends Controller
{
    public function login(Request $request){
    	$email = $request->email;
        $password = $request->password;

        $user = DB::table('users')
            ->where('email', '=', $email)
            ->first();

        if($user):
            if(Hash::check($password, $user->password)):
                $credentials = $request->only('email', 'password');
                try {
                    if (! $token = JWTAuth::attempt($credentials)) {
                        return response()->json(['error' => 'invalid_credentials'], 401);
                    }
                } catch (JWTException $e) {
                    return response()->json(['error' => 'could_not_create_token'], 500);
                }

                $userId = DB::table('users')
                    ->select('id')
                    ->where('email', '=', $email)
                    ->first();

                $responseData = array('success'=>'1', 'token'=>$token, 'data'=>$userId, 'message'=>"Logado com sucesso!");
            else:
                $responseData = array('success'=>'0', 'message'=>"Senha incorreta!");
            endif;
        else:
            $responseData = array('success'=>'0', 'message'=>"E-mail inexistente ou incorreto!");
        endif;
        return response()->json(compact('responseData'));
    }

    public function cadastrar(Request $request){
        $name     		= $request->name;
        $user_name      = $request->user;
        $email          = $request->email;
        $password       = $request->password;

        $user = DB::table('users')->where('email', '=', $email)->get();

        if(count($user) == '1'):
            $responseData = array('success'=>'0', 'message'=>"E-mail já cadastrado!");
        else:
            /* Cadastra o usuário e resgata seu id */
            $user_data = array(
                'name'                        		=>  $name,
                'user'                              =>  $user_name,
                'email'                             =>  $email,
                'password'                          =>  Hash::make($password),
                'created_at'                        =>  date('Y-m-d H:i:s'),
                'updated_at'                        =>  date('Y-m-d H:i:s')
            );

            $user_id = DB::table('users')->insertGetId($user_data);

            /* Cadastra o usuário na tabela de seguidores */
            $seguidores_data = array(
                'user_id'                           =>  $user_id,
                'created_at'                        =>  date('Y-m-d H:i:s'),
                'updated_at'                        =>  date('Y-m-d H:i:s')
            );

            DB::table('seguidores')->insert($seguidores_data);

            /* Cadastra o usuário na tabela de seguidos */
            $seguidos_data = array(
                'user_id'                           =>  $user_id,
                'created_at'                        =>  date('Y-m-d H:i:s'),
                'updated_at'                        =>  date('Y-m-d H:i:s')
            );

            DB::table('seguidos')->insert($seguidos_data);

            /* Resgata os dados do usuário, cria o token e realiza o login */
            $userId = DB::table('users')
                ->select('id')
                ->where('id', '=', $user_id)
                ->first();

            $credentials = $request->only('email', 'password');

            $token = JWTAuth::attempt($credentials);

            $responseData = array('success'=>'1', 'token'=>$token, 'data'=>$userId, 'message'=>"Cadastrado com sucesso!");
        endif;
        return response()->json(compact('responseData'));
    }
}
