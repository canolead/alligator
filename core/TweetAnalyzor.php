<?php

require_once dirname(__FILE__).'/TwitterAPI.php'; 

Class TweetAnalyzor{

	public static function getTweetsByQueries($query){

		$queries = self::formQueries($query);

		$twitterAPI = new TwitterAPI();

		$tweets = array();
		foreach($queries as $qkey => $q){

			$queryToSubmit = array(
			  "q" => $q,
			  "count" => 100,
			  "lang"=>"ja",
			  "locale"=>"ja",
			  "result_type"=>"recent"
			);
			$results = $twitterAPI->search($queryToSubmit);

			$tweets[$qkey] = array(); 
			foreach ($results->statuses as $result) {
				$tweets[$qkey][] = htmlspecialchars($result->text);
			}
		}
		return $tweets; 
	}

	private static function formQueries($str){

		$queries = array();
		$queries[0] = $str." "."面白い";
		$queries[1] = $str." "."かわいい";
		$queries[2] = $str." "."好き";
		$queries[3] = $str." "."エロい";
		$queries[4] = $str." "."楽しみ";
		return $queries;

	}

	public static function selectTweets($tweets, $keywords, $numberOfTweets=10){
		$NGwords = array("/(http|https):\/\/[\S]+/", "/\n|\r/","/#/","/\&[\S]+;/","/RT/","/\@/");

		$selectedTweetsTemp = array();
		foreach($tweets as $twkey => $tws){
			$selectedTweetsTemp[$twkey] = array();

			foreach($tws as $tw){
				
				if(is_array($keywords)){
					$containKeyword = false;
					foreach ($keywords as $keyword) {
				
						if(strpos($tw, $keyword)!==false){
							$containKeyword = true;
							break;
						}
					}
					if(!$containKeyword) continue;					

				}else{
					if(strpos($tw, $keywords)===false) continue;
				}

				if(strlen($tw) > 200) continue;

				$isGood = true;
				foreach ($NGwords as $ngw) {
					preg_match($ngw, $tw, $match);
					if(count($match)>0){
					
						$isGood = false;
						break;
					}
				}
				if(!$isGood)	continue;

				$selectedTweetsTemp[$twkey][] = $tw;

			}

		}

		$selectedTweets = array();
		$prevNumOfSelectedTweets = -1;
		while($prevNumOfSelectedTweets < count($selectedTweets)){
			$prevNumOfSelectedTweets = count($selectedTweets);
			foreach ($selectedTweetsTemp as $key => $value) {
				
				if(count($selectedTweetsTemp[$key])==0){
					continue;
				}
				$selectedTweets[] = end($selectedTweetsTemp[$key]);
				array_pop($selectedTweetsTemp[$key]);	
				if(count($selectedTweets) >= $numberOfTweets){
					break 2;
				}
			}	
		}

		return $selectedTweets;

	}



}



?>