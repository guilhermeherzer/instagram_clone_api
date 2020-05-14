<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;
use Hash;

class PostController extends Controller
{
    //
	public function publicar(Request $request){
		$name = Hash::make(date('Y-m-d H:i:s'));

		if($request->hasFile('photo')):
			$image = imagecreatefromjpeg($_FILES["photo"]["tmp_name"]);
			$target_dir = "assets/img/posts/users/" . auth()->user()->id . "/";
			$target_file = $target_dir . basename($name) . '.jpg';
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
			if ($_FILES["photo"]["size"] > 5000000):
				echo $_FILES["photo"]["size"];
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
				if (imagejpeg($image, $target_file, 50)):
					$post_data = array(
						'user_id' => auth()->user()->id,
						'text' => $request->legenda,
						'display_url' => $target_file,
						'location' => '',
						'pessoas_marcadas' => '',
						'likes' => serialize(array()),
						'created_at' => date('Y-m-d H:i:s'),
						'updated_at' => date('Y-m-d H:i:s')
					);

					$post = DB::table('posts')->insert($post_data);

					$responseData = array('success'=>'1', 'message'=>"Postagem feita com sucesso!");
				else:
					$responseData = array('success'=>'0', 'message'=>"Sorry, there was an error uploading your file.");
				endif;
			endif;
		else:
			$responseData = array('success'=>'0', 'message'=>'Sem imagem');
		endif;
		

		return response()->json(compact('responseData'));
	}

	public function delete(Request $request) {
		$post = DB::table('posts')
			->where('id', $request->id)
			->first();

		if($post->user_id == auth()->user()->id):
			if(unlink($post->display_url) && DB::table('posts')->where('id', $request->id)->delete() && DB::table('comentarios')->where('post_id', $request->id)->delete()):
				$responseData = array('success'=>'1', 'message'=>"Sucesso ao deletar a postagem");
			else:
				$responseData = array('success'=>'0', 'message'=>"Erro ao deletar a postagem!");
			endif;
		else:
			$responseData = array('success'=>'0', 'message'=>"Erro ao deletar a postagem!");
		endif;

		return response()->json(compact('responseData'));
	}


}