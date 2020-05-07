<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;

class PostController extends Controller
{
    //
	public function upload_img(Request $request){
		if($request->hasFile('photo')):

			$target_dir = "assets/img/posts/users/";
			$target_file = $target_dir . basename($_FILES["photo"]["name"]);
			$uploadOk = 1;
			$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])):
			$check = getimagesize($_FILES["photo"]["tmp_name"]);
			if($check !== false):
				echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			else:
				echo "File is not an image.";
				$uploadOk = 0;
			endif;
		endif;

		// Check if file already exists
		if (file_exists($target_file)):
			echo "Sorry, file already exists.";
			$uploadOk = 0;
		endif;

    	// Check file size
		if ($_FILES["photo"]["size"] > 500000):
			echo "Sorry, your file is too large.";
			$uploadOk = 0;
		endif;

		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" ):
				echo "Sorry, only JPG, JPEG, PNG files are allowed.";
			$uploadOk = 0;
		endif;

		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0):
			echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
		else:
			if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)):
				echo "The file ". basename( $_FILES["photo"]["name"]). " has been uploaded.";
			else:
				echo "Sorry, there was an error uploading your file.";
			endif;
		endif;

		$error = $_FILES["photo"];
		$responseData = array('success'=>'1', 'message'=>$error);
	else:
		$error = $_FILES["photo"];
		$responseData = array('success'=>'0', 'message'=>$error);
	endif;

	return response()->json(compact('responseData'));
}
}