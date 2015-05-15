<?php
require_once dirname(__FILE__).'/ArticleController.php';
require_once dirname(__FILE__).'/pageConfig.php';
require_once dirname(__FILE__).'/BlogContentGenerator.php';
require_once dirname(__FILE__).'/BlogContentGeneratorWithTwitter.php';


if(isset($argv[1])){
	$journalid = $argv[1];
}


if(isset($argv[2])){
	$tid = (int)$argv[2];
}

$newArticles = ArticleController::getArticlesFromDB(array("journal"=>$journalid));

$alist = array();
$tlist = array();
foreach($newArticles as $a){
	$tags = ArticleController::addTagsToArticle($a, ArticleController::tagFromTitle, true);
	if(count($tags)>0){
		$alist[] = $a;
		$tlist[] = $tags;
	}
}

if(count($alist)==0){
	echo "No tag has beed added\n";
	exit;
}

$index = array_rand($alist, 1);
$a = $alist[$index];

$tagFound = false;
foreach ($tlist[$index] as $tag) {
	if($tag==$tid)$tagFound = true;
}

if($tagFound)	
	$t = $tid;
else{
	echo "tag ".$tid." has not been added";
	exit;
}


$imagesGenerated = ArticleController::extractImagesFromArticleContent($a);

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
			
if(!$bcg->isValid())	exit;
if(isset($pageConfig['blogTitleAppendedStr'])){
	$newPostTitle = $bcg->generateBlogTitle($a).$pageConfig['blogTitleAppendedStr'];
}else{
	$newPostTitle = $bcg->generateBlogTitle($a);
}
$newPostContent = $bcg->generateBlogContent($a);
$newPostThumbnailPath = $bcg->getThumbnailPath();

$testContentHeader = '<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0"><link rel="stylesheet" type="text/css" href="articletest.css" media="all"></head><body>';
$testContentFooter = '</body>';

file_put_contents("../articletest.html", $testContentHeader."<div>".$newPostTitle."</div>".$newPostContent);

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


//var_dump($newPostTitle);
//var_dump($newPostContent);

?>