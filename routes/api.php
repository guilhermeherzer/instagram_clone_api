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

	Route::post('/auth/login', 									'Api\AuthController@login');

	Route::post('/auth/cadastrar', 								'Api\AuthController@cadastrar');

	Route::group(['middleware' => ['apiJwt']], function(){
		Route::post('/auth/logout', 							'Api\AuthController@logout');
		
		Route::post('/auth/me', 								'Api\AuthController@me');

		Route::get('/feed',										'Api\FeedController@index');

		Route::get('/profile',									'Api\ProfileController@index');

		Route::get('/profile/{id}',								'Api\ProfileController@show');

		Route::get('/search/{text?}',							'Api\SearchController@index');

		Route::post('/follow/{id}',								'Api\FollowController@store');

		Route::post('/unfollow/{id}',							'Api\FollowController@destroy');

		Route::get('/comments/{id}',							'Api\CommentsController@show');

		Route::post('/comments/store/{id}/{text}',				'Api\CommentsController@store');

		Route::post('/like/{id}',								'Api\LikeController@store');

		Route::post('/publish/{text?}',							'Api\PostController@store');

		Route::post('/publish/destroy/{id}',					'Api\PostController@destroy');
	});