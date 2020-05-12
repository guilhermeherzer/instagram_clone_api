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

	Route::post('/logout', 									'Api\AuthController@logout');

	Route::get('/meu_perfil/',								'Api\PerfilController@meu_perfil');

	Route::get('/feed/',									'Api\FeedController@feed');

	Route::get('/buscar/{texto?}',							'Api\BuscarController@buscar');

	Route::get('/ver_perfil/{userid}',						'Api\PerfilController@ver_perfil');

	Route::post('/seguir/{userid}',							'Api\SeguirController@seguir');

	Route::post('/desseguir/{userid}',						'Api\SeguirController@desseguir');

	Route::get('/comentarios/{post_id}',					'Api\ComentariosController@comentarios');

	Route::post('/comentar/{post_id}/{texto}',				'Api\ComentariosController@comentar');

	Route::post('/like/{post_id}',							'Api\LikeController@like');

	Route::post('/publicar/{legenda?}',						'Api\PostController@publicar');

	Route::post('/publicar/delete/{post_id}',				'Api\PostController@delete');
