<?php
require_once dirname(__FILE__)."/../libs/simplehtmldom/simple_html_dom.php";
date_default_timezone_set('Asia/Tokyo');

const SCORE_THRESHOLD = 2;

function getTitleFromKeyphrases($kp, $words){

//crawl the following website and pick up the five best proxies http://lab.magicvox.net/proxy/ 
/*
	$proxy = array(
	      "http" => array(
	         "proxy" => "tcp://157.7.48.92:3128",
	         'request_fulluri' => true,
	      ),
	);
	$proxy_context = stream_context_create($proxy);
*/
	$titleArray = array();
	$linkArray = array();
	$scoreArray = array();
	$searchQuery = "";
	$numOfKps = 0;
	foreach ($kp as $value) {
		/*
		if(strlen($searchQuery)==0)
			$searchQuery = $value['keyphrase'];
		else
			$searchQuery .= " ".$value['keyphrase'];
		*/

		$searchQuery = $value['keyphrase'];
		$numOfKps++;
		if($numOfKps > 3)	break;

		$url = "http://www.logsoku.com/search?q=".urlencode($searchQuery);
		sleep(1);

		$html = @file_get_html($url);
		//$filestr = file_get_contents($url,false,$proxy_context);
		//$html = @str_get_html($filestr);
		if(!$html){
			continue;
		}

		$container = $html->find("[id*='search_result_threads']",0);
		$trs = $container->find("tr");

		$isFirstRow = true;
		foreach ($trs as $row) {
			if($isFirstRow){
				$isFirstRow = false;
				continue;
			}
			$titleTd = $row->find("td.title",0);
			$dateTd = $row->find("td.date",0);
			if(isset($titleTd) && isset($dateTd)){
				$title = $titleTd->plaintext;
				$pubTime = strtotime(trim($dateTd->plaintext));
				if($pubTime < strtotime("-30days")){
					continue;
				}
				if(strpos($title,"転載禁止")!==false)	{
					continue;
				}

				$score = TextProcessor::calcMatchingScore($title, $words);

				if($score > SCORE_THRESHOLD){

					$link = "http://www.logsoku.com".$titleTd->find("a",0)->getAttribute("href");
					if(in_array($link, $linkArray))	continue;

					$titleArray[] = $title;
					$scoreArray[] = $score;
					$linkArray[] = $link;
				}
			}
		}
	}

	if(count($scoreArray)>0){
		arsort($scoreArray);
		foreach ($scoreArray as $key => $value) {
			$firstIndex = $key;
			break;
		}

		return array("title"=>$titleArray[$firstIndex], "link"=>$linkArray[$firstIndex], "score"=>$scoreArray[$firstIndex]);

	}else{
		return NULL;
	}
}


?>