<?php

require_once dirname(__FILE__).'/requestData.php';
require_once dirname(__FILE__).'/pageConfig.php';
require_once dirname(__FILE__).'/LivedoorAPI.php';

date_default_timezone_set('Asia/Tokyo');

if(isset($argv[1]) && !is_nan((int)$argv[1])){
	$tagID = $argv[1];
}else{
	echo "Error: invalid tag id.\n";
	exit;
}

$pageConfig = getPageConfig($tagID);
$condition = array();
if(isset($pageConfig['dispDuration']))
	$condition['date'] = date ("Y-m-d H:i:s", strtotime($pageConfig['blogDuration']));
if(isset($pageConfig['maxNumOfArticles']))
	$condition['numOfArticles'] = 50;

$data = getArticlesByTagId($tagID, $condition); 
$rdata = restructData($data, $pageConfig["sortMethod"]);

if(count($data)==0){
	echo "No articles.\n";
	exit;
}

$content = <<<EOT
	<ul class="article-list">
EOT;

foreach ($rdata as $value) {
	$content .= '<li><span style="display: inline-block; vertical-align: middle; height: 100%"></span><span class="title"><a class="" href="'.$value["url"].' target="_blank">'.$value["title"].'</a><span class="blog">'.$value["name"].'</span></span></li>';
}
$content .= '</ul>';

$blogTitle = generateTitle($rdata, $pageConfig['blogTitleSeparator'], $pageConfig['blogTitleAppendedStr']);

$blogAPI = new LivedoorAPI($pageConfig['blogId'], $pageConfig['blogUser'], $pageConfig['blogKey']);
$blogAPI->postArticle($blogTitle, $content);

?>