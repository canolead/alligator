<?php
require_once dirname(__FILE__).'/ArticleController.php';
require_once dirname(__FILE__).'/WordpressAPI.php';
require_once dirname(__FILE__).'/pageConfig.php';
date_default_timezone_set('Asia/Tokyo');

if(isset($argv[1])){
	$journalCategory = $argv[1];
}else{
	$journalCategory = NULL;
}

//Get new articles from the journals and insert them into the DB
$journalList = ArticleController::getJournals($journalCategory);
foreach($journalList as $journal){
	$parsedRss = ArticleController::getArticles($journal);
	foreach($parsedRss['articles'] as $article){
		ArticleController::insertArticleToDB($article);
	}
}

//Add tags to the new articles
foreach($journalList as $journal){

	$newArticles = ArticleController::getArticlesFromDB(array("processed"=>false , "journal"=>$journal['id']));
//	$newArticles = ArticleController::getArticlesFromDB(array("journal"=>$journal['id']));
	foreach($newArticles as $a){
	
		ArticleController::extractImagesFromArticleContent($a);
		$tags = ArticleController::addTagsToArticle($a, ArticleController::tagFromTitle, true);


		foreach($tags as $t){

			$pageConfig = getPageConfig($t);
		
			switch ($pageConfig['blogType']) {
				case 'twitter':
					require_once dirname(__FILE__).'/BlogContentGeneratorWithTwitter.php';
					$bcg = new BlogContentGeneratorWithTwitter($a, $t, $pageConfig);
					break;
				case 'monster':
					require_once dirname(__FILE__).'/BlogContentGeneratorMonster.php';
					$bcg = new BlogContentGeneratorMonster($a, $t, $pageConfig);
					break;	
				case 'monster2':
					require_once dirname(__FILE__).'/BlogContentGeneratorMonster2.php';
					$bcg = new BlogContentGeneratorMonster2($a, $t, $pageConfig);
					break;		
				default:
					require_once dirname(__FILE__).'/BlogContentGenerator.php';
					$bcg = new BlogContentGenerator($a, $t, $pageConfig);
					break;
			}
			
			if(!$bcg->isValid())	continue;

			if(isset($pageConfig['blogTitleAppendedStr'])){
				$newPostTitle = $bcg->generateBlogTitle($a).$pageConfig['blogTitleAppendedStr'];
			}else{
				$newPostTitle = $bcg->generateBlogTitle($a);
			}
			$newPostContent = $bcg->generateBlogContent($a);
			$newPostThumbnailPath = $bcg->getThumbnailPath();

			switch ($pageConfig['blogType']) {

				case 'monster2':
					require_once dirname(__FILE__).'/LivedoorAPI.php';
					$blogAPI = new LivedoorAPI($bcg->getBlogId(), $pageConfig['blogUser'], $pageConfig['blogKey']);
					break;
				default:
					require_once dirname(__FILE__).'/WordpressAPI.php';
					$blogAPI = new WordpressAPI();
					$blogAPI->setBlogURL($pageConfig["blogURL"]);
					break;
			}
			if(!$blogAPI->postArticle($newPostTitle, $newPostContent, $newPostThumbnailPath)){
				error_log("post failed");
			}

			sleep(1);
		}
		
	}
}

?>