<?php
	require_once dirname(__FILE__)."/TextProcessor.php";
	$str = "【画像あり】　ぱるるのサングラス姿が可愛すぎるンゴｗｗｗｗｗｗｗｗ";
	$str2 = " 【動画像】　市川美織さん(処女)がラッスンゴレライする姿が可愛すぎると話題にｗｗｗｗｗｗｗｗｗ";

	$str = TextProcessor::preprocessor($str);
    $kp  = TextProcessor::getKeyphrase($str);
    $mp  = TextProcessor::getMorphemes($str);
    $words = TextProcessor::selectWords($mp);
    $score = TextProcessor::calcMatchingScore($str2 , $words);
  
?>