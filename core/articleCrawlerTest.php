<?php
require_once dirname(__FILE__).'/ArticleController.php';
require_once dirname(__FILE__).'/WordpressAPI.php';
require_once dirname(__FILE__).'/BlogContentGenerator.php';
require_once dirname(__FILE__).'/BlogContentGeneratorWithTwitter.php';
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


/*
//Add tags to the new articles
foreach($journalList as $journal){

	$newArticles = ArticleController::getArticlesFromDB(array("processed"=>false , "journal"=>$journal['id']));
//	$newArticles = ArticleController::getArticlesFromDB(array("journal"=>$journal['id']));
	foreach($newArticles as $a){
	
		ArticleController::generateArticleContentHeader($a);
		ArticleController::extractImagesFromArticleContent($a);

		$tags = ArticleController::addTagsToArticle($a, ArticleController::tagFromTitle, true);

		foreach($tags as $t){

			$pageConfig = getPageConfig($t);
			$blogURL = $pageConfig['blogURL'];
			if(!isset($blogURL)) continue;


			switch ($pageConfig['blogType']) {
				case 'twitter':
					$bcg = new BlogContentGeneratorWithTwitter($a, $t, $blogURL);
					break;
				
				default:
					$bcg = new BlogContentGenerator($a, $t, $blogURL);
					break;
			}
			
			if(!$bcg->isValid())	continue;

			if(isset($pageConfig['blogTitleAppendedStr'])){
				$newPostTitle = $bcg->generateBlogTitle($a).$pageConfig['blogTitleAppendedStr'];
			}else{
				$newPostTitle = $bcg->generateBlogTitle($a);
			}
			$newPostContent = $bcg->generateBlogContent($a);
		
			$wps = new WordpressAPI();
			$wps->setBlogURL($blogURL);

			if(!$wps->postArticle($newPostTitle, $newPostContent)){
				error_log("post failed");
			}
			sleep(1);
		}
		
	}
}
*/
?>