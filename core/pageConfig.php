<?php
	require_once dirname(__FILE__).'/requestData.php';
	function getPageConfig($tid){
		$sortMethod = sortByIndex;
		switch ($tid) {
			case 3:
				$dispDuration = "-7 days";
				$blogDuration = "today";
				$blogId = "fxcolumn-animekansou";
				$blogUser = "fxcolumn";
				$blogKey = "ALrbZ6G9n4";
				$blogTitleSeparator = "話";
				$blogTitleAppendedStr = "";
				$withThumbnailOnly = true; 
				$sortMethod = sortByDateAndIndex;
				$blogURL = "http://animedi.net";
				$blogType = "normal";
				$contentKeywordFile = "contentKeywords.json";
				break;
			case 4:
				$dispDuration = "-7 days";
				$blogDuration = "today";
				$blogTitleSeparator = "話";
				$blogTitleAppendedStr = "";
				$withThumbnailOnly = true; 
				$sortMethod = sortByDateAndIndex;
				$blogURL = "http://drama.animedi.net";
				$blogType = "normal";
				$contentKeywordFile = "contentKeywords.json";
				break;

			case 5:
				$sortMethod = sortByIndexAndDate;
				$blogType = "twitter";
				$blogURL = "http://viewer.animedi.net";
				$blogTitleAppendedStr = "を見る";
				break;

			case 6:
				$sortMethod = sortByIndexAndDate;
				$blogType = "twitter";
				$blogURL = "http://dramaviewer.animedi.net";
				$blogTitleAppendedStr = "を見る";
				break;

			case 7:
				$sortMethod = sortByDateAndIndex;
				$dispDuration = "-1days";
				$blogDuration = "today";
				$blogType = "normal";
				$blogURL = "http://fate.animedi.net";
				$blogTitleAppendedStr = "";
				$contentKeywordFile = "contentKeywords.json";
				break;

			case 8:
				$sortMethod = sortByDateAndIndex;
				$dispDuration = "-1days";
				$blogDuration = "today";
				$blogType = "monster";
				$blogURL = "http://aggligator.com";
				$blogTitleAppendedStr = "";
				$contentKeywordFile = "contentKeywordsMonster.json";
				break;

			case 9:
				$sortMethod = sortByDateAndIndex;
				$dispDuration = "-1days";
				$blogDuration = "today";
				$blogType = "monster";
				$blogURL = "http://gazo.aggligator.com";
				$blogTitleAppendedStr = "";
				$contentKeywordFile = "contentKeywordsMonster.json";
				break;

			case 10:
				$sortMethod = sortByDateAndIndex;
				$dispDuration = "-1days";
				$blogDuration = "today";
				$blogType = "monster";
				$blogURL = "http://zannen.o0o0.jp/";
				$blogTitleAppendedStr = "";
				$contentKeywordFile = "contentKeywordsMonster.json";
				break;
				
			case 11:
				$sortMethod = sortByDateAndIndex;
				$dispDuration = "-1days";
				$blogDuration = "today";
				$blogType = "monster";
				$blogURL = "http://yaba.aggligator.com";
				$blogTitleAppendedStr = "";
				$contentKeywordFile = "contentKeywordsMonster.json";
				break;

			case 12:
				$sortMethod = sortByDate;
				$dispDuration = "-1days";
				$blogDuration = "today";
				$blogType = "monster2";
				$blogId = "gossipmatome";
				$blogId2 = "gossipmatome-akb";
				$blogUser = "gossipmatome";
				$blogKey = "we8Umc1Ysu";
				$withThumbnailOnly = true; 
				$blogTitleAppendedStr = "";
				$contentKeywordFile = "contentKeywordsMonster.json";
				$fullText = true;
				$needContentHeaderFile = true;
				$application = array("akb"=>array(12,13,14,15));
				break;

			case 13:
				$sortMethod = sortByDateAndIndex;
				$dispDuration = "-1days";
				$blogDuration = "today";
				$blogType = "monster2";
				$blogId = "gossipmatome-idols";
				$blogId2 = "gossipmatome-idolsant";
				$blogUser = "gossipmatome";
				$blogKey = "we8Umc1Ysu";
				$withThumbnailOnly = true; 
				$blogTitleAppendedStr = "";
				$contentKeywordFile = "contentKeywordsMonster.json";
				$fullText = true;
				$needContentHeaderFile = true;
				$application = array("akb"=>array(12,13,14,15));
				break;

			case 14:
				$sortMethod = sortByDateAndIndex;
				$dispDuration = "-1days";
				$blogDuration = "today";
				$blogType = "monster2";
				$blogId = "gossipmatome-nmb";
				$blogId2 = "gossipmatome-nmbant";
				$blogUser = "gossipmatome";
				$blogKey = "we8Umc1Ysu";
				$withThumbnailOnly = true; 
				$blogTitleAppendedStr = "";
				$contentKeywordFile = "contentKeywordsMonster.json";
				$fullText = true;
				$needContentHeaderFile = true;
				$application = array("akb"=>array(12,13,14,15));
				break;

			case 15:
				$sortMethod = sortByDateAndIndex;
				$dispDuration = "-1days";
				$blogDuration = "today";
				$blogType = "monster2";
				$blogId = "gossipmatome-ske";
				$blogId2 = "gossipmatome-skeant";
				$blogUser = "gossipmatome";
				$blogKey = "we8Umc1Ysu";
				$withThumbnailOnly = true; 
				$blogTitleAppendedStr = "";
				$contentKeywordFile = "contentKeywordsMonster.json";
				$fullText = true;
				$needContentHeaderFile = true;
				$application = array("akb"=>array(12,13,14,15));
				break;

			default:
				break;
		}

		$result = array();
		if(isset($dispDuration))
			$result['dispDuration'] = $dispDuration;

		if(isset($blogDuration))
			$result['blogDuration'] = $blogDuration;

		if(isset($blogId))
			$result['blogId'] = $blogId;

		if(isset($blogId2))
			$result['blogId2'] = $blogId2;

		if(isset($blogUser))
			$result['blogUser'] = $blogUser;

		if(isset($blogKey))
			$result['blogKey'] = $blogKey;

		if(isset($blogTitleSeparator))
			$result['blogTitleSeparator'] = $blogTitleSeparator;

		if(isset($blogTitleAppendedStr))
			$result['blogTitleAppendedStr'] = $blogTitleAppendedStr;

		if(isset($blogURL))
			$result['blogURL'] = $blogURL;

		if(isset($blogType))
			$result['blogType'] = $blogType;

		if(isset($fullText))
			$result['fullText'] = $fullText;

		if(isset($maxNumOfArticles))
			$result['maxNumOfArticles'] = $maxNumOfArticles;

		if(isset($withThumbnailOnly))
			$result['withThumbnailOnly'] = $withThumbnailOnly;

		if(isset($contentKeywordFile))
			$result['contentKeywordFile'] = $contentKeywordFile;

		if(isset($needContentHeaderFile))
			$result['needContentHeaderFile'] = $needContentHeaderFile;
 
		if(isset($application))
			$result['application'] = $application;

		$result['sortMethod'] = $sortMethod;

		return $result;

	}
?>