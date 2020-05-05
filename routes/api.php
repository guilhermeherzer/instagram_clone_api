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

Route::post('/login', 						'Api\UserController@login');
Route::post('/cadastrar', 					'Api\UserController@cadastrar');

Route::get('/meu_perfil/{id}',				'Api\PostController@meu_perfil');
Route::get('/feed/{my_id}',					'Api\PostController@feed');

Route::get('/buscar/{texto?}',				'Api\PostController@buscar');

Route::get('/ver_perfil/{myid}/{userid}',	'Api\PostController@ver_perfil');

Route::post('/seguir/{myid}/{userid}',		'Api\PostController@seguir');
Route::post('/desseguir/{myid}/{userid}',	'Api\PostController@desseguir');

Route::get('/comentarios/{my_id}/{post_id}',			'Api\PostController@comentarios');
Route::post('/comentar/{post_id}/{user_id}/{texto}',		'Api\PostController@comentar');