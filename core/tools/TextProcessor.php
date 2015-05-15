<?php
	
class TextProcessor{

	const APP_ID = 'dj0zaiZpPU1CdzNnMmtXRDVHNCZzPWNvbnN1bWVyc2VjcmV0Jng9YTQ-';

	static function escapestring($str) {
	    return htmlspecialchars($str, ENT_QUOTES);
	}
		
	static function preprocessor($sentence){
		$sentence = mb_convert_encoding($sentence, 'utf-8', 'auto');
		$NGwordsJSON = file_get_contents(dirname(__FILE__)."/../files/origTitleNGwords.json");
		$NGwords = json_decode($NGwordsJSON);

		mb_internal_encoding("UTF-8");
		mb_regex_encoding("UTF-8");
		$NGphrase = array(preg_quote("【")."[^".preg_quote("】")."]*".preg_quote("】"), preg_quote("（")."[^".preg_quote("）")."]*".preg_quote("）"));
		foreach($NGphrase as $ngp){
			$sentence = preg_replace("/".$ngp."/u", "", $sentence);
		}

		foreach($NGwords as $ngw){
			$sentence = preg_replace("/".preg_quote($ngw)."/u", "", $sentence);
		}
		return $sentence;
	}

	static function getKeyphrase($sentence){

		$output = "xml";
		$request  = "http://jlp.yahooapis.jp/KeyphraseService/V1/extract?";
		$request .= "appid=".self::APP_ID."&sentence=".urlencode($sentence)."&output=".$output;
		  
		$responsexml = simplexml_load_file($request);  
		$result_num = count($responsexml->Result);

		$keywordArray = array();
	  	if($result_num > 0){
	  	
		    for($i = 0; $i < $result_num; $i++){
		      	$result = $responsexml->Result[$i];
		      	$keywordArray[] = array("keyphrase"=>self::escapestring($result->Keyphrase), "score"=>self::escapestring($result->Score));
		    } 
	  	}
	  	return $keywordArray;
	}

	static function getMorphemes($sentence){

		$url = "http://jlp.yahooapis.jp/MAService/V1/parse?appid=".self::APP_ID."&results=ma";
    	$url .= "&sentence=".urlencode($sentence);
    	$xml  = simplexml_load_file($url);
    	$morphemes = array();
    	foreach ($xml->ma_result->word_list->word as $cur){
    		$morphemes[] = $cur->surface;
    	}
    	return $morphemes;
	}

	static function selectWords($mp){
		$wordsArray = array();
		$NGwords = array("^[ぁ-ん]$","^[\s|\x{3000}]+$","^".preg_quote('ｗ')."+$",preg_quote("【"), preg_quote("】"), preg_quote("（"), preg_quote("）"), preg_quote("「"), preg_quote("」"));

		foreach ($mp as $mp => $w) {
			$isValid = true;
			foreach ($NGwords as $ngw) {
				if (preg_match("/".$ngw."/u", $w)) {
			    	$isValid = false;
			    	break;
				}	
			}
			if($isValid){
				if(!in_array($w, $wordsArray))
					$wordsArray[] = $w;
			}
		}
		return $wordsArray;
	}

	static function calcMatchingScore($title, $words){
		$numOfWords = count($words);
		$wpos = array();
		$posMatch = 0;
		foreach ($words as $w) {
			$pos = mb_strpos($title, $w);
			if ($pos !== false) {
				if(count($wpos)>0){
					$lastPos = end($wpos); 
					if($lastPos<$pos){
						$posMatch++;
					}
				}
				
				$wpos[] = $pos;
				
			}	
		}

		$posScore = (count($wpos)>1) ? $posMatch/(count($wpos)-1) : 0;
		$score = count($wpos)/$numOfWords;
		$totalScore = $score * count($wpos) * $posScore;

		return $totalScore;
	}


}

?>