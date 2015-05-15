<?php

	require_once dirname(__FILE__).'/../alligator/core/requestData.php';
	require_once dirname(__FILE__).'/../alligator/core/pageConfig.php';

	date_default_timezone_set('Asia/Tokyo');

	if($_SERVER['REQUEST_METHOD']=="GET") {
		$function = $_GET['a'];
		if(function_exists($function)) {        
		    call_user_func($function);
		} else {
		    echo 'Function Not Exists!!';
		}
	}else if($_SERVER['REQUEST_METHOD']=="POST") {
		$function = $_POST['a'];
		if(function_exists($function)) {        
		    call_user_func($function);
		} else {
		    echo 'Function Not Exists!!';
		}

	}

	function getArticles(){

		$tagId = $_GET['tag'];
		if(!isset($tagId) || !is_numeric($tagId)){
			echo json_encode(array("ok"=>0, "error"=>"Invalid tag ID"));
			return;
		}

		$pageConfig = getPageConfig($tagId);

		$condition = array();
		if(isset($pageConfig['dispDuration']))
			$condition['date'] = date("Y-m-d H:i:s", strtotime($pageConfig['dispDuration']));
		if(isset($pageConfig['maxNumOfArticles']))
			$condition['numOfArticles'] = $pageConfig['maxNumOfArticles'];
		if(isset($pageConfig['withThumbnailOnly']))
			$condition['withThumbnailOnly'] = $pageConfig['withThumbnailOnly'];

		$data = getArticlesByTagId($tagId, $condition, $pageConfig["sortMethod"]); 

		echo json_encode(array("ok"=>1, "result"=>$data));
		return;

	}

	function setKeywords(){

		global $db;
		if(!$db){
			connectDB();
		} 	

		$tagId = $_POST['tag'];
		if(!isset($tagId) || !is_numeric($tagId)){
			echo json_encode(array("ok"=>0, "error"=>"Invalid tag ID"));
			return;
		}

		$journalCategory = $_POST['journalCategory'];
		if(!isset($journalCategory) || !is_string($journalCategory)){
			echo json_encode(array("ok"=>0, "error"=>"Invalid journal category"));
			return;
		}

		$inputData = $_POST['data'];
		if(!isset($inputData) || !is_array($inputData) || count($inputData)==0){
			echo json_encode(array("ok"=>0, "error"=>"Invalid input data"));
			return;
		}

		foreach($inputData as $d){

			$keywords = $d['keywords'];
			if(!isset($keywords) || !is_string($keywords) ||  strlen($keywords)==0){
				error_log("Invalid keywords");
				continue;
			}
	
			$keywordArray = preg_split("/[\s|\x{3000}]+/u", trim(mb_convert_kana($keywords,'s')), -1, PREG_SPLIT_NO_EMPTY);
			$keywordJSON = json_encode($keywordArray, JSON_UNESCAPED_UNICODE);

			$articleIndex = $d['articleIndex'];
			if(!isset($articleIndex) || !is_string($articleIndex) ||  strlen($articleIndex)==0){
	        	error_log("Invalid articleIndex");
				continue;
			}

			$priority = (int)$d['priority'];
			if(!isset($priority) || is_nan($priority)){
				$priority = 0;
			}

			$keywordTitle = $d['keywordTitle'];
			$searchQuery = $d['searchQuery'];

	      	$query = "SELECT id FROM Tag_Keywords WHERE keywords='".$keywordJSON."' AND tid='".$tagId."' LIMIT 1";
	     	$result = $db->query($query);
	      	$rs=$result->fetchAll(PDO::FETCH_ASSOC);
	      	if(isset($rs[0])){
	        	error_log("The input keywords already exist in DB");
	        	continue;
	      	}

			$query = "INSERT INTO `Tag_Keywords` (`id`, `tid`, `keywords`, `articleIndex`". (isset($keywordTitle) ? ", `keywordTitle`" : "" ) . (isset($searchQuery) ? ", `searchQuery`" : "" ) . (isset($journalCategory) ? ", `journalCategory`" : "" ) .", `priority`) VALUES (NULL, '".$tagId."', '".$keywordJSON."', '".$articleIndex."'". (isset($keywordTitle) ? ", '".$keywordTitle."' " : ""). (isset($searchQuery) ? ", '".$searchQuery."' " : ""). (isset($journalCategory) ? ", '".$journalCategory."' " : "").", ".$priority.");";

			if(!$db->query($query)){
	        	error_log("Failed to insert data in the Tag_Keywords");
	        	continue;
	      	}

      	}

      	echo json_encode(array("ok"=>1));
      	return;

	}

	function getTwitterData(){

		$tagId = $_GET['tag'];
		if(!isset($tagId) || !is_numeric($tagId)){
			echo json_encode(array("ok"=>0, "error"=>"Invalid tag ID"));
			return;
		}

		$tweetsJSON = file_get_contents(TWITTERDATA_DIRECTORY_PATH."/"."tweets_".$tagId.".json");
		$tweets = json_decode($tweetsJSON);
		echo json_encode(array("ok"=>1, "result"=>$tweets));
	}

/*
	function getArticlesForRSS(){

		$tagId = $_GET['tag'];
		if(!isset($tagId) || !is_numeric($tagId)){
			echo json_encode(array("ok"=>0, "error"=>"Invalid tag ID"));
			return;
		}

		$numOfArticles = $_GET['num'];

		$data = getArticlesForRSS($tagId, isset($numOfArticles) ? $numOfArticles : NULL);

		$retArray = array();
		$i=0;
		$prevNum = 0;
		while(true){

			foreach ($data as $value) {
				if(is_array($value) && count($value)>$i){
					if(isset($value[$i]))
						$retArray[] = $value[$i];	
					if(count($retArray)>=$numOfArticles)	
						break 2;
				}
			}
			if(count($retArray)==$prevNum)	break;

			$i++;
			$prevNum = count($retArray);
		}

		echo json_encode(array("ok"=>1, "result"=>$retArray));
		return;

	}
*/

?>