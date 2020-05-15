<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class SearchController extends Controller
{
    public function index(Request $request){
    	if(!is_null($request->text)):
	    	$users = DB::table('users')
	    		->select('id', 'full_name', 'username', 'profile_pic_url')
	    		->where('username', 'LIKE', '%'.$request->text.'%')
	    		->get();
    	else:
    		$users = array();
    	endif;

    	$responseData = array('users'=>$users);

    	return response()->json(compact('responseData'));
    }
}
