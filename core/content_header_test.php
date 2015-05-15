<?php
require_once dirname(__FILE__).'/ArticleAnalyzor.php';
$str = ArticleAnalyzor::extractArticleImages("../data/contents/1735.html","article-body-more");
var_dump($str);

//ArticleController::createThumbnailImage("testImgSrc.jpg","jpg");
?>