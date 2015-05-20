<?php
require_once dirname(__FILE__).'/requestData.php';
require_once dirname(__FILE__).'/pageConfig.php';
date_default_timezone_set('Asia/Tokyo');

class AlligatorAPI{

	static function generateArticleJSONFiles($t){

		$pageConfig = getPageConfig($t);

		$condition = array();
		if(isset($pageConfig['dispDuration']))
			$condition['date'] = date("Y-m-d H:i:s", strtotime($pageConfig['dispDuration']));
		if(isset($pageConfig['maxNumOfArticles']))
			$condition['numOfArticles'] = $pageConfig['maxNumOfArticles'];
		if(isset($pageConfig['withThumbnailOnly']))
			$condition['withThumbnailOnly'] = $pageConfig['withThumbnailOnly'];

		$needContentHeaderFile = isset($pageConfig['needContentHeaderFile']) ? $pageConfig['needContentHeaderFile'] : false;		

		$articles = getArticlesByTagId($t, $condition, $pageConfig["sortMethod"]); 

		if($needContentHeaderFile){
			$as = array();
			foreach ($articles as $a) {
				if(isset($a['headerFile'])){
					$as[] = $a;
				}
			}
		}else{

			$as = $articles;
		}
		file_put_contents(API_DIRECTORY_PATH."/articlesData_".$t.".json", json_encode($as));

		if(isset($pageConfig['application'])){
			foreach ($pageConfig['application'] as $app => $tags) {
				$data = array();
				foreach ($tags as $t) {
					$filename = API_DIRECTORY_PATH."/articlesData_".$t.".json";
					if (file_exists($filename)) {
						$contentFileJSON = file_get_contents($filename);
						$data[$t] = json_decode($contentFileJSON);	 
					}
					file_put_contents(API_DIRECTORY_PATH."/articlesData_".$app.".json", json_encode($data));
				}
			}
		}

	}

}

?>