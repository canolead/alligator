<?php
require_once dirname(__FILE__).'/pageConfig.php';
include_once('libs/IXR_Library.php');

Class WordpressAPI{


	private $blogURL;

	public function setBlogURL($blogURL){
		$this->blogURL = $blogURL;
	}

	public function postArticle($title, $content, $imgPath){

		if(!is_string($title) || strlen($title)==0 || !is_string($content) || strlen($content)==0){
		 return false;
		}
	
		$wp_username = "alligator";
		$wp_password = "kncl4620";
		echo("Posting on ".$this->blogURL);

var_dump($title);
var_dump($content);

/*
		$client = new IXR_Client($this->blogURL ."/xmlrpc.php");

		$status = $client->query(
		  "wp.newPost", //使うAPIを指定（wp.newPostは、新規投稿）
		  1, // blog ID: 通常は１、マルチサイト時変更
		  $wp_username, // ユーザー名
		  $wp_password, // パスワード
		  array(
		    'post_author' => 1, // 投稿者ID 未設定の場合投稿者名なしになる。
		    'post_status' => 'publish', // 投稿状態
		    'post_title' => $title, // タイトル
		    'post_content' => $content, 
		    'terms' => array('category' => array(1))
		    )
		);
		if(!$status){

		  error_log("Failed to post an article: ".$title);
		  return false;

		} else {

			$post_id = $client->getResponse(); //返り値は投稿ID
			if(!isset($imgPath) || !file_exists($imgPath))	return true;

			$imgInfo = getimagesize($imgPath);
			$type = $imgInfo['mime'];

			$bits = new IXR_Base64(file_get_contents($imgPath));
			$status2 = $client->query(
			  "wp.uploadFile",
			  1,
			  $wp_username,
			  $wp_password,
			  array(
			    'name' => $post_id.'.jpg',
			    'type' => $type,
			    'bits' => $bits,
			    'overwrite' => false,
			    'post_id' => $post_id
			  )
			);
			$img = $client->getResponse();


			$status3 = $client->query(
				"wp.editPost",
			 	1,
			 	$wp_username, 
			 	$wp_password, 
			 	$post_id,
			 	array("post_thumbnail" => $img['id'])
			);
			$thumb = $client->getResponse();

		}
*/

		return true;
	}
}
?>
