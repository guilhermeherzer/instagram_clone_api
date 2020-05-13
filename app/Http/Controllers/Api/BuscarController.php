<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class BuscarController extends Controller
{
    public function buscar(Request $request){
    	if(!is_null($request->texto)):
	    	$users = DB::table('users')
	    		->select('id', 'full_name', 'username', 'profile_pic_url')
	    		->where('username', 'LIKE', '%'.$request->texto.'%')
	    		->get();
    	else:
    		$users = array();
    	endif;

    	$responseData = array('users'=>$users);

    	return response()->json(compact('responseData'));
    }
}
