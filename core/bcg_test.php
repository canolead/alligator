<?php
require_once dirname(__FILE__).'/ArticleController.php';
require_once dirname(__FILE__).'/pageConfig.php';
require_once dirname(__FILE__).'/BlogContentGenerator.php';
require_once dirname(__FILE__).'/BlogContentGeneratorWithTwitter.php';

$newArticles = ArticleController::getArticlesFromDB(array("journal"=>36));
//var_dump($newArticles);
$a = $newArticles[38];
$tags = ArticleController::addTagsToArticle($a, ArticleController::tagFromTitle, true);

if(count($tags)==0){
	echo "No tag has beed added";
	exit;
}


$headerGenerated = ArticleController::generateArticleContentHeader($a);
$imagesGenerated = ArticleController::extractImagesFromArticleContent($a);

$t = $tags[0];

$pageConfig = getPageConfig($t);
$blogURL = $pageConfig['blogURL'];

switch ($pageConfig['blogType']) {
	case 'twitter':
		$bcg = new BlogContentGeneratorWithTwitter($a, $t, $blogURL);
		break;
	
	default:
		$bcg = new BlogContentGenerator($a, $t, $blogURL);
		break;
}


if(!$bcg->isValid())	exit;
if(isset($pageConfig['blogTitleAppendedStr'])){
	$newPostTitle = $bcg->generateBlogTitle($a).$pageConfig['blogTitleAppendedStr'];
}else{
	$newPostTitle = $bcg->generateBlogTitle($a);
}
$newPostContent = $bcg->generateBlogContent($a);

var_dump($newPostTitle);
var_dump($newPostContent);

?>