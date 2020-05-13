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

	Route::post('/login', 									'Api\AuthController@login');

	Route::post('/cadastrar', 								'Api\AuthController@cadastrar');

	Route::group(['middleware' => ['apiJwt']], function(){
		Route::post('/logout', 									'Api\AuthController@logout');
		
		Route::post('/me', 										'Api\AuthController@me');

		Route::get('/meu_perfil/',								'Api\PerfilController@meu_perfil');

		Route::get('/feed/',									'Api\FeedController@feed');

		Route::get('/buscar/{texto?}',							'Api\BuscarController@buscar');

		Route::get('/ver_perfil/{id}',							'Api\PerfilController@ver_perfil');

		Route::post('/seguir/{id}',								'Api\SeguirController@seguir');

		Route::post('/desseguir/{id}',							'Api\SeguirController@desseguir');

		Route::get('/comentarios/{id}',							'Api\ComentariosController@comentarios');

		Route::post('/comentar/{id}/{texto}',					'Api\ComentariosController@comentar');

		Route::post('/like/{post_id}',							'Api\LikeController@like');

		Route::post('/publicar/{legenda?}',						'Api\PostController@publicar');

		Route::post('/publicar/delete/{post_id}',				'Api\PostController@delete');
	});