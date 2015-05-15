<?php

require_once dirname(__FILE__).'/util.php';
require_once dirname(__FILE__).'/TweetAnalyzor.php';

if(isset($argv[1]) && !is_nan((int)$argv[1])){
	$tid = $argv[1];
}else{
	error_log("Argument 1 is not a number.");
	exit;
}

global $db;
if(!isset($db)){
  connectDB();
}

$query = "SELECT * FROM SearchDetails WHERE tid=".$tid;
$result = $db->query($query);
if(!$result){
	error_log("Failed to get data from database");
	exit();
}

$rs = $result->fetchAll(PDO::FETCH_ASSOC);
if(isset($rs[0])){
  
  	$searchQuery = $rs[0]["query"];
  	$keywords = json_decode($rs[0]["keywords"]);

	$tws = TweetAnalyzor::getTweetsByQueries($searchQuery);

	$selectedTweets = TweetAnalyzor::selectTweets($tws, $keywords, 10);

	if(count($selectedTweets)==0){
		error_log("No relevant tweet was extracted.");
		exit;
	}else{
		$tweetsJSON = json_encode($selectedTweets);
		file_put_contents(TWITTERDATA_DIRECTORY_PATH."/"."tweets_".$tid.".json", $tweetsJSON);
	}

}else{
	error_log("Query not found");
	exit;	
}


?>