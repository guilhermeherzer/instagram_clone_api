<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', 									'Api\UserController@login');

Route::post('/cadastrar', 								'Api\UserController@cadastrar');

Route::get('/meu_perfil/{id}',							'Api\PerfilController@meu_perfil');

Route::get('/feed/{my_id}',								'Api\FeedController@feed');

Route::get('/buscar/{texto?}',							'Api\BuscarController@buscar');

Route::get('/ver_perfil/{myid}/{userid}',				'Api\PerfilController@ver_perfil');

Route::post('/seguir/{myid}/{userid}',					'Api\SeguirController@seguir');

Route::post('/desseguir/{myid}/{userid}',				'Api\SeguirController@desseguir');

Route::get('/comentarios/{my_id}/{post_id}',			'Api\ComentariosController@comentarios');

Route::post('/comentar/{post_id}/{user_id}/{texto}',	'Api\ComentariosController@comentar');

Route::post('/like/{post_id}/{my_id}',					'Api\LikeController@like');

Route::post('/publicar/upload-img',						'Api\PostController@upload_img');