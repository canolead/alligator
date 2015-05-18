<?php

require_once dirname(__FILE__).'/Article.php';
require_once dirname(__FILE__).'/util.php';
require_once dirname(__FILE__).'/ArticleAnalyzor.php';
require_once dirname(__FILE__).'/Image.php';
require_once dirname(__FILE__)."/tools/TextProcessor.php";
require_once dirname(__FILE__)."/tools/2chlogScraper.php";
date_default_timezone_set('Asia/Tokyo');

class ArticleController{

  const tagFromTitle = 1;

  static function getJournals($category=NULL){
      global $db;
      if(!isset($db)){
        connectDB();
      }

      $journalList = array();

      if(isset($category)){
        $query = "SELECT * FROM Journal WHERE type='rss' and category='".$category."'";
      }else{
        $query = "SELECT * FROM Journal WHERE type='rss'";
      }
      $result = $db->query($query);
      while ($rs = $result->fetch(PDO::FETCH_ASSOC)) {
        $journalList[] = array('id'=>$rs['id'], 'name'=>$rs['name'], 'url'=>$rs['url'], 'thumbnail'=>$rs['thumbnail']);
      }
      return $journalList;
  }

  static function insertArticleToDB($article){
      global $db;
      if(!isset($db)){
        connectDB();
      }

      $query = "SELECT id FROM Article WHERE url='".$article->getUrl()."' LIMIT 1";
      $result = $db->query($query);
      $rs = $result->fetchAll(PDO::FETCH_ASSOC);
      if(isset($rs[0])){
        return false;
      }

      $imgPath = $article->getImagePath();
      $imgExt = $article->getImageExtension();
      $imagePathDB = NULL;
      if(isset($imgPath) && isset($imgExt)){
        $imagePathDB = self::createThumbnailImage($imgPath, $imgExt);
    
        $query2 = "INSERT INTO `Article` (`id`, `journalId`, `url`, `title`, `description`, `imagePath`, `pubDate`) VALUES (NULL, '".$article->getJournalId()."', '".$article->getUrl()."', '".$article->getTitle()."',  '".$article->getDescription()."', '".$imagePathDB."', '".$article->getPubDate()."');";
      }else{
        $query2 = "INSERT INTO `Article` (`id`, `journalId`, `url`, `title`, `description`, `pubDate`) VALUES (NULL, '".$article->getJournalId()."', '".$article->getUrl()."', '".$article->getTitle()."',  '".$article->getDescription()."', '".$article->getPubDate()."');";
      }

      if($db->query($query2)){

        $contentFile = file_get_contents($article->getUrl());
        file_put_contents(CONTENT_DIRECTORY_PATH."/".$db->lastInsertId().".html", $contentFile);
      }

  }

  static function getArticlesFromDB($condition=NULL){

    global $db;
    if(!isset($db)){
      connectDB();
    }
 
    $limitClause = "";
    $whereClause = "";
    if(isset($condition)){

      if(isset($condition['processed']) && is_bool($condition['processed'])){
        $whereClause .= " WHERE Article.processed = ".($condition['processed'] ? 1 : 0);
      }
      if(isset($condition['date']) && is_string($condition['date'])){
        $whereClause .= strlen($whereClause)>0 ?  " AND Article.pubDate > '".$condition['date']."'" : " WHERE Article.pubDate > '".$condition['date']."'"; 
      }

      if(isset($condition['journal']) && is_numeric($condition['journal'])){
        $whereClause .= strlen($whereClause)>0 ? " AND Article.journalId = ".$condition['journal'] : " WHERE Article.journalId = ".$condition['journal']; 
      }

      if(isset($condition['limit']) && is_numeric($condition['limit'])){
        $limitClause .= " LIMIT ".$condition['limit'];
      }

      if(isset($condition['tag']) && is_numeric($condition['tag'])){

        $whereClause .= strlen($whereClause)>0 ? " AND Article_Tag.tid = ".$condition['tag'] : " WHERE Article_Tag.tid = ".$condition['tag']; 
        if(isset($condition['keyword']) && is_numeric($condition['keyword'])){
          $whereClause .= " AND Article_Tag.keywordId = ".$condition['keyword'];
        }

        $query = "SELECT Article.id, Article.title, Article.url, Article.pubDate, Article.journalId, Article.isNew, Article.processed, Journal.name,  Journal.category, Journal.contentClass, Journal.itemClass, Article.description, Article.imagePath FROM Article 
          INNER JOIN Article_Tag ON Article.id = Article_Tag.aid
          INNER JOIN Journal ON Article.journalId = Journal.id";

      }else{
        $query = "SELECT Article.id, Article.title, Article.url, Article.pubDate, Article.journalId, Article.isNew, Article.processed, Journal.name, Journal.category, Journal.contentClass, Journal.itemClass, Article.description, Article.imagePath FROM Article INNER JOIN Journal ON Article.journalId = Journal.id"; 
      }

    }else{
      $query = "SELECT Article.id, Article.title, Article.url, Article.pubDate, Article.journalId, Article.isNew, Article.processed, Journal.name, Journal.category, Journal.contentClass, Journal.itemClass, Article.description, Article.imagePath FROM Article INNER JOIN Journal ON Article.journalId = Journal.id"; 
    }
    if(strlen($whereClause)>0){
      $query .= $whereClause;   
    }
    $query .= " ORDER BY Article.pubDate DESC".$limitClause; 

    $articles = array();
    $result = $db->query($query);
 
    while ($rs = $result->fetch(PDO::FETCH_ASSOC)) {

      $a = new Article();
      $a->setId($rs['id']);
      $a->setTitle($rs['title']);
      $a->setUrl($rs['url']);
      $a->setPubDate($rs['pubDate']);
      $a->setJournalId($rs['journalId']);
      $a->setJournalName($rs['name']);
      $a->setJournalCategory($rs['category']);
      $a->setIsNew($rs['isNew']);
      $a->setProcessed($rs['processed']);
      if(isset($rs['contentClass']))
        $a->setContentClassName($rs['contentClass']);
      if(isset($rs['itemClass']))  
        $a->setItemClassName($rs['itemClass']);  
      if(isset($rs['description']))     
        $a->setDescription($rs['description']);
      if(isset($rs['imagePath'])) 
        $a->setImagePath($rs['imagePath']);

      $articles[] = $a;

    }

    return $articles;

  }

  static function getArticles($journal){

    $xmlDoc = new DOMDocument();
    $xmlDoc->load($journal['url']);

    //get elements from "<channel>"
    $channel=$xmlDoc->getElementsByTagName('channel')->item(0);

    $channel_title_obj = $channel->getElementsByTagName('title')->item(0)->childNodes->item(0);
    $channel_title = isset($channel_link_obj) ? $channel_title_obj->nodeValue : NULL;
    $channel_link_obj = $channel->getElementsByTagName('link')->item(0)->childNodes->item(0);
    $channel_link = isset($channel_link_obj) ? $channel_link_obj->nodeValue : NULL;
    $channel_desc_obj = $channel->getElementsByTagName('description')->item(0)->childNodes->item(0);
    $channel_desc = isset($channel_desc_obj) ? $channel_desc_obj->nodeValue : NULL;

    $channelInfo = array('title'=>$channel_title, 'link'=>$channel_link, 'description'=>$channel_desc);


    $articles = array(); 
    //get and output "<item>" elements
    $x=$xmlDoc->getElementsByTagName('item');

    for ($i=0; $i<$x->length; $i++) {
      $item_title=$x->item($i)->getElementsByTagName('title')
      ->item(0)->childNodes->item(0)->nodeValue;
      $item_link=$x->item($i)->getElementsByTagName('link')
      ->item(0)->childNodes->item(0)->nodeValue;
      $item_desc_obj=$x->item($i)->getElementsByTagName('description')->item(0)->childNodes;
      $item_desc = ($item_desc_obj->length!=0) ? $item_desc_obj->item(0)->nodeValue : NULL;
      $item_content = isset($x->item($i)->nodeValue) ? $x->item($i)->nodeValue : NULL;

      $item_pubdate_obj=$x->item($i)->getElementsByTagName('pubDate');
      if($item_pubdate_obj->length==0){
        $item_pubdate_obj=$x->item($i)->getElementsByTagName('date');
      }
      $item_pubdate = $item_pubdate_obj->item(0)->childNodes->item(0)->nodeValue;

      $a = new Article();
      $a->setTitle($item_title);
      $a->setUrl($item_link);
      $a->setDescription((strlen($item_desc)>1000) ? substr($item_desc, 0, 997)."..." : $item_desc);
      $a->setPubdate(pubDateToMySql($item_pubdate));
      $a->setJournalId($journal['id']);

      if(isset($item_content)){
        $imagePath = self::extractContentImage($item_content);
        if(isset($imagePath["path"]) && isset($imagePath["extension"])){
          $a->setImagePath($imagePath["path"]);
          $a->setImageExtension($imagePath["extension"]);
        }
        $a->setContentHeader((strlen($item_content)>1000) ? substr($item_content, 0, 1000) : $item_content);
      }
      $articles[] = $a;

    }

    return array('channel'=>$channelInfo, 'articles'=>$articles);

  }

  static function addTagsToArticle($article, $mode=0, $insertInDB=true){

    global $db;
    if(!isset($db)){
      connectDB();
    }

    $tagIds = array();
    $metadata = array();
    $keywordIds = array();
    $priorities = array();
    switch ($mode) {
        case ArticleController::tagFromTitle:
          $resOfAnalysis = ArticleAnalyzor::getTagFromTitle($article->getTitle(), $article->getJournalCategory());
          $tagIds = $resOfAnalysis["tagIds"];
          $metadata = $resOfAnalysis["articleIndices"];
          $keywordIds = $resOfAnalysis["keywordIds"]; 
          $priorities = $resOfAnalysis["priorities"]; 
          break;

        default:
          error_log("mode not selected");
          break;
    }

    $tagAdded = false;
    foreach($tagIds as $tid){
      $article->addTag($tid);
      if(count($metadata)>0){
        $article->setMetadata($metadata);
      }
      if(count($keywordIds)>0){
        $article->setKeywordIds($keywordIds);
      }

      $tagAdded = true;
    }

    if($tagAdded && $insertInDB){
      ArticleController::addTagInDB($article);
    }

    $query = "UPDATE Article SET processed=1 WHERE id=".$article->getId();
    $result = $db->query($query);

    arsort($priorities);
    return array_keys($priorities);

  }

  static function addTagInDB($article){
    global $db;
    if(!isset($db)){
      connectDB();
    }

    if(!is_numeric($article->getId())) return false;

    $tags = $article->getTags();

    foreach ($tags as $tag) {
      $query = "SELECT aid FROM Article_Tag WHERE aid='".$article->getId()."' AND tid='".$tag."' LIMIT 1";
      $result = $db->query($query);
      $rs=$result->fetchAll(PDO::FETCH_ASSOC);
      if(isset($rs[0])){
        continue;
      }

      $query2 = "";
      $keywordId = $article->getKeywordIds();
      $metadata = $article->getMetadata();

      if(isset($metadata[$tag])){
        $metadataJSON = json_encode($metadata[$tag], JSON_UNESCAPED_UNICODE);
      }

      $query2 = "INSERT INTO `Article_Tag` (`aid`, `tid`".(isset($metadataJSON) ? ", `metadata`" : "").(isset($keywordId[$tag]) ? ", `keywordId`" : "").") VALUES (".$article->getId().", ".(int)$tag. (isset($metadataJSON) ? ", '".$metadataJSON."'" : "") . (isset($keywordId[$tag]) ? ", ".(int)$keywordId[$tag] : "").");";
      if(!$db->query($query2)){
        error_log("Failed to insert data in the Article_Tag table");
      }

    }
    return true;
  }

  private static function extractContentImage($rssContentHeader){

    if(!isset($rssContentHeader) || !is_string($rssContentHeader)){
       return NULL;
    }

    preg_match("/<img([^\>]*)>/", $rssContentHeader, $imgs);
    
    if(isset($imgs[0])){
      preg_match("/(http|https):\/\/[\S]+\.(jpg|jpeg|png|gif)/", $imgs[0], $imgPathTemp);
    
      if(isset($imgPathTemp[0]) && isset($imgPathTemp[2])){
        return array("path"=>$imgPathTemp[0], "extension"=>$imgPathTemp[2]);
      }else{
        return NULL;
      }
    }

    return NULL;
  } 

  private static function createThumbnailImage($imagePath, $imageExtension){

    if(isset($imagePath) && isset($imageExtension)){

      try {
        if($imageExtension=="jpg" || $imageExtension=="jpeg" ){
          $image = @imagecreatefromjpeg($imagePath);
        }else if($imageExtension=="png"){
          $image = @imagecreatefrompng($imagePath);
        }else if($imageExtension=="gif"){
          $image = @imagecreatefromgif($imagePath);
        }else{
          return NULL;
        }

        $imgId = (int)round(microtime(true) * 1000).rand(0,1000);

        $thumb_1 = Image::resizeAndCropImage($image, array("width"=>300,"height"=>225)); 
        $imgPathToSave = IMAGE_DIRECTORY_PATH."/thumbnail_".$imgId.".jpg";

        imagejpeg($thumb_1, $imgPathToSave, 80);
        
        $thumb_2 = Image::resizeAndCropImage($image, array("width"=>600,"height"=>300)); 
        $imgPathToSave_2 = IMAGE_DIRECTORY_PATH."/thumbnail_".$imgId."_w.jpg";

        imagejpeg($thumb_2, $imgPathToSave_2, 80);

        return $imgId;

      }catch(Exception $e) {
        echo $e->getMessage(); 
        return NULL;
      }
    }else{
      return NULL;
    }

  }

  public static function extractImagesFromArticleContent($article){

    global $db;
    if(!isset($db)){
      connectDB();
    }

    $contentClassName = $article->getContentClassName();
    if(!isset($contentClassName)) return false;

    $htmlpath = CONTENT_DIRECTORY_PATH."/".$article->getId().".html";
    
    if(file_exists($htmlpath)){
      $imageSet = ArticleAnalyzor::extractArticleImages($htmlpath, $contentClassName);

      $numOfTrial = 0;
      $numOfSavedImages = 0;
      $savedImages = array();
      foreach($imageSet as $imgElem){
        if($numOfTrial > 5 || $numOfSavedImages > 3) break;

        $numOfTrial++;

        try {
            if($imgElem["extension"]=="jpg" || $imgElem["extension"]=="jpeg" ){
              $image = @imagecreatefromjpeg($imgElem["path"]);
            }else if($imgElem["extension"]=="png"){
              $image = @imagecreatefrompng($imgElem["path"]);
            }else if($imgElem["extension"]=="gif"){
              $image = @imagecreatefromgif($imgElem["path"]);
            }else{
              continue;
            }

            if(!Image::validateImage($image)) continue;

            $imgId = (int)round(microtime(true) * 1000).rand(0,1000);

            $thumb_1 = Image::resizeAndCropImage($image, array("width"=>300,"height"=>225)); 
            $imgPathToSave_1 = IMAGE_DIRECTORY_PATH."/content_image_".$imgId.".jpg";
            imagejpeg($thumb_1, $imgPathToSave_1, 80);

            $thumb_2 = Image::resizeAndCropImage($image, array("width"=>600,"height"=>300)); 
            $imgPathToSave_2 = IMAGE_DIRECTORY_PATH."/content_image_".$imgId."_w.jpg";
            imagejpeg($thumb_2, $imgPathToSave_2, 80);
            
            $savedImages[] = $imgId;                 
            $numOfSavedImages++;

        }catch(Exception $e) {
            error_log($e->getMessage());  
        }
      }

      if(count($savedImages)>0){

        $contentImagesJSON = json_encode($savedImages, JSON_UNESCAPED_UNICODE);
        $query = "UPDATE Article SET contentImages='".$contentImagesJSON."' WHERE id=".$article->getId();
        $result = $db->query($query);
        
        $article->setContentImages($savedImages);
        return true;
      }
     
    }

    return false;
  } 



  static function generateArticleContentHeader($article, $params){

    $contentClassName = $article->getContentClassName();
    if(!isset($contentClassName))  return NULL;

    $itemClassName = $article->getItemClassName();

    $path = CONTENT_DIRECTORY_PATH."/".$article->getId().".html";
    
    if(file_exists($path)){

      if(isset($params["contentKeywordFile"])){
        $contentKeywordFilePath = dirname(__FILE__)."/files/".$params["contentKeywordFile"];
        if(file_exists($contentKeywordFilePath)){
          $contentKeywordsJSON = file_get_contents($contentKeywordFilePath);
          $contentKeywords = json_decode($contentKeywordsJSON);
          $contentKeywordArray = $contentKeywords->keywords;
          $contentNGwordArray = $contentKeywords->NGwords;
        }
      }

      $fullText = isset($params["fullText"]) ? $params["fullText"] : false;
/*
      $contentKeywordArray = $params["contentKeywordFile"];
      //$contentNGwordArray = array('/&gt;&gt;[0-9]+/','/^[\s|\x{3000}]+$/u');
      $contentNGwordArray = array('/^[\&nbsp\;|\s|\x{3000}]+$/u');
      */

      $contentHeaders = ArticleAnalyzor::extractArticleHeader($path, $contentClassName, $itemClassName, $contentKeywordArray, $contentNGwordArray);

      if(isset($contentHeaders["headerStr"]) && strlen($contentHeaders["headerStr"])>0){
        $headerPath = CONTENT_DIRECTORY_PATH."/header_".$article->getId().".html";
        file_put_contents($headerPath, $contentHeaders["headerStr"]));
      }

      if(isset($contentHeaders["fullTextStr"]) && strlen($contentHeaders["fullTextStr"])>0){
        $headerPath2 = CONTENT_DIRECTORY_PATH."/fulltext_".$article->getId().".html";
        file_put_contents($headerPath2, $contentHeaders["fullTextStr"]);
      }
      
 //   $article->setContentHeader($header);
      return $fullText ? $contentHeaders["fullTextStr"] : $contentHeaders["headerStr"];
    }
    return NULL;
  }

  static function cacheContentImages($string){

    $cachedImages = array();
    $replacedString = $string;
    preg_match_all("/<img([^\>]*)>/", $string, $imgs);
    if(isset($imgs[0]) && is_array($imgs[0])){
    
      foreach ($imgs[0] as $imgElem) {

        preg_match("/(http|https):\/\/[\S]+\.(jpg|jpeg|png)/", $imgElem, $origImgPath);

        if(isset($origImgPath[0]) && isset($origImgPath[2])){ 

          $imagePath = $origImgPath[0];
          $imageExtension = $origImgPath[2];

          try {
            if($imageExtension=="jpg" || $imageExtension=="jpeg" ){
              $image = @imagecreatefromjpeg($imagePath);
            }else if($imageExtension=="png"){
              $image = @imagecreatefrompng($imagePath);
            }else if($imageExtension=="gif"){
              $image = @imagecreatefromgif($imagePath);
            }else{
              continue;
            }

            $imgId = (int)round(microtime(true) * 1000).rand(0,1000);

            $cimg = Image::resizeImageByWidth($image, 480); 
            $imgPathToSave = IMAGE_DIRECTORY_PATH."/article_images_".$imgId.".jpg";
            imagejpeg($cimg, $imgPathToSave, 80);

            $imgPathToAccess = IMAGE_URL."/article_images_".$imgId.".jpg";
            $replacedString = str_replace($imagePath, $imgPathToAccess, $replacedString);
            
            $cachedImages[] = $imgPathToAccess;

          }catch(Exception $e) {
            echo $e->getMessage(); 
          }

        }
   
      }
    }
    return array('content'=>$replacedString, 'cachedImages'=>$cachedImages);
  }

  static function findOriginalTitle($article){

    global $db;
    if(!isset($db)){
      connectDB();
    }

    $str = TextProcessor::preprocessor($article->getTitle());

    if(!isset($str) || strlen($str)==0) return;

    $kp  = TextProcessor::getKeyphrase($str);
    $mp  = TextProcessor::getMorphemes($str);
    $words = TextProcessor::selectWords($mp);
    $titleObj =  getTitleFromKeyphrases($kp, $words);

    echo("id:".$article->getId()."\n");
    echo("title:".$article->getTitle()."\n");
    echo("pubDate:".$article->getPubDate()."\n");

    if(isset($titleObj)){

      $origTitle = $titleObj["title"];
      $origUrl = $titleObj["link"];

      $article->setOriginalTitle($origTitle);
      $article->setOriginalUrl($origUrl);

      echo("original title:".$article->getOriginalTitle()."\n");
      echo("original url:".$article->getOriginalUrl()."\n");
      echo("score:".$titleObj["score"]."\n");
      
      return array("originalTitle"=>$article->getOriginalTitle(), "originalUrl"=>$article->getOriginalUrl());

    }else{
      return NULL;
    }
  
  }

  static function checkBlogTitleUnique($article, $tid){

    global $db;
    if(!isset($db)){
      connectDB();
    }

    $origTitle = $article->getOriginalTitle();
    $origUrl = $article->getOriginalUrl();

    $query = "SELECT id FROM Article WHERE originalUrl='{$origUrl}' AND id IN (SELECT aid FROM Article_Tag WHERE tid={$tid} GROUP BY aid)";
    $result = $db->query($query);
    $rs=$result->fetchAll(PDO::FETCH_ASSOC);
    if(isset($rs[0])){
      return false;
    }else{
      $query = "UPDATE Article SET originalTitle='".$origTitle."', originalUrl='".$origUrl."' WHERE id=".$article->getId();
      $result = $db->query($query);
      return true;
    }
  }

}

function pubDateToMySql($str) {
    return date('Y-m-d H:i:s', strtotime($str));
}


?>