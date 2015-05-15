<?php
require_once dirname(__FILE__).'/ArticleController.php';
require_once dirname(__FILE__).'/BlogContentGeneratorAbstract.php';

class BlogContentGeneratorMonster2 extends BlogContentGeneratorAbstract{


	private $thumbnailPath;
	private $contentHeader;
	private $blogId;
	private $fullText;

	function __construct($article, $tagId, $pageConfig){
	
		parent::__construct($article, $tagId);	

		$origData = ArticleController::findOriginalTitle($article);
		if(isset($origData)){
			$this->title = $origData["originalTitle"];
			$this->url = $origData["originalUrl"];
			if(!ArticleController::checkBlogTitleUnique($article, $tagId)){
				error_log("The original article is not unique.");
				$this->valid = false;	
				return;	
			}

			$this->fullText = true;
			$this->blogId = $pageConfig["blogId"];

		}else{
			$this->fullText = false;
			$this->blogId = $pageConfig["blogId2"];		
		}

		$contentStr = ArticleController::generateArticleContentHeader($article, array("contentKeywordFile"=>$pageConfig["contentKeywordFile"], "fullText"=>$this->fullText));

		if(!isset($contentStr) || strlen($contentStr)==0){
			error_log("Content header file not found.");
			$this->valid = false;
			return;	
		}

		$contentObj = ArticleController::cacheContentImages($contentStr);
		$this->contentHeader = htmlspecialchars($contentObj["content"]);

		if(!isset($this->contentHeader) || strlen($this->contentHeader)==0){
			error_log("Content header file not found.");
			$this->valid = false;
			return;	
		}

		if(isset($contentObj["cachedImages"][0])){
			$this->thumbnailPath = $contentObj["cachedImages"][0];
			if($this->fullText){
				//hack to replace title
				if(strpos($this->title, "【")===false){
					$this->title = "【画像】".$this->title;
				}
			}
		}else{
			$this->thumbnailPath = NULL;
			echo("No image found in this article\n");
			$this->valid = false;
			return;	
		}


	}

	public function getBlogId(){
		return $this->blogId;
	}

	public function generateBlogTitle(){

		return $this->title;
	}

	public function getThumbnailPath(){
		return $this->thumbnailPath;
	}

	public function generateBlogContent(){

		if($this->fullText){

$content = <<<EOT

	<div id="target-article-content">
		{$this->contentHeader}
	</div>

	<style>

		#target-article-content{
		  margin: 0;
		}

		#target-article-content .d_h{
		  font-weight: normal;
		  margin: 7px 0 0 0;
		}

		#target-article-content .d_b{
		  font-size: 24px;
		  font-weight: bold;
		  margin: 5px 0 20px 0;
		}

		font.red{
		  color: #F00;
		}

		font.small{
		  color: #777;
		  font-size: 14px;
		}

		font.middle{
		  font-size: 20px;
		}

	</style>

EOT;
		
		}else{

$content = <<<EOT

	<div class="target-article-link">
		<div class="target-article-link-inner"><a href="{$this->url}"><span class="target-article-link-text">「{$this->jounalName}」で全文を読む<span></a></div>
	</div>	
	<div id="target-article-content">
		{$this->contentHeader}
	</div>
	<div class="target-article-link">
		<div class="target-article-link-inner"><a href="{$this->url}"><span class="target-article-link-text">「{$this->jounalName}」で続きを読む<span></a></div>
	</div>	
	<style>

		#target-article-content{
		  margin: 0;
		}

		#target-article-content .d_h{
		  font-weight: normal;
		  margin: 7px 0 0 0;
		}

		#target-article-content .d_b{
		  font-size: 24px;
		  font-weight: bold;
		  margin: 5px 0 20px 0;
		}

		font.red{
		  color: #F00;
		}

		font.small{
		  color: #777;
		  font-size: 14px;
		}

		font.middle{
		  font-size: 20px;
		}

		.target-article-link{
			width: 100%;
			margin: 30px 0 30px; 0;
			
		}

		.target-article-link-inner{
			display: block;
			width: 70%;
			text-align:center; 
			margin: 0 auto;
		}

		.target-article-link a{
			color: #FFF;
			text-decoration:none; 
		}

		.target-article-link-text{
			display: block;
		    padding: 30px 5px 30px 5px;
			background-color: #ff69b4;
			font-weight: bold;
			border-radius: 5px;
			width: 100%;
			height: 100%;
			text-decoration:none; 
			font-size: 22px;
		}

		.target-article-link-text:hover{
			background-color: #ccc;
		}


	</style>

EOT;

		}

		return $content;

	}

	public function isFullText(){
		return $this->fullText;
	}
}

?>