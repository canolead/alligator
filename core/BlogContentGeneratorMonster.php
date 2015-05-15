<?php
require_once dirname(__FILE__).'/ArticleController.php';
require_once dirname(__FILE__).'/BlogContentGeneratorAbstract.php';

class BlogContentGeneratorMonster extends BlogContentGeneratorAbstract{

	const maxNumOfArticles = 6;
	const maxNumOfArticlesPerQuery = 4;

	private $thumbnailPath;
	private $thumbnail;
	private $thumbnailWide;
	private $contentHeader;
	private $blogURL;

	function __construct($article, $tagId, $pageConfig){
	
		parent::__construct($article, $tagId);	

		$img = $article->getImagePath();
		if(isset($img)){
			$this->thumbnail = IMAGE_URL."/thumbnail_".$img.".jpg";
			$this->thumbnailWide = IMAGE_URL."/thumbnail_".$img."_w.jpg";
			$this->thumbnailPath = IMAGE_DIRECTORY_PATH."/thumbnail_".$img.".jpg";
		}else{
			$imgContents = $article->getContentImages();
			if(isset($imgContents[0])){
				$this->thumbnail = IMAGE_URL."/content_image_".$imgContents[0].".jpg";
				$this->thumbnailWide = IMAGE_URL."/content_image_".$imgContents[0]."_w.jpg";
				$this->thumbnailPath = IMAGE_DIRECTORY_PATH."/content_image_".$imgContents[0].".jpg";
			}else{
				error_log("Thumbnail not found");
				$this->valid = false;
			}
		}
		$this->contentHeader = ArticleController::generateArticleContentHeader($article, array("contentKeywordFile"=>$pageConfig["contentKeywordFile"], "fullText"=>(isset($pageConfig["fullText"]) ? $pageConfig["fullText"] : false)));

		if(!isset($this->contentHeader) || strlen($this->contentHeader)==0){
			error_log("Content header file not found.");
			$this->valid = false;
		}


		if(isset($pageConfig["blogURL"])){
			$this->blogURL = $pageConfig["blogURL"];
		}


	}

	public function generateBlogTitle(){

		return $this->title;
	}

	public function getThumbnailPath(){
		return $this->thumbnailPath;
	}

	public function generateBlogContent(){
$content = <<<EOT

	<div id="target-journal-title-wrapper">
		<a href="{$this->url}"><span id="target-journal-title">{$this->jounalName}</span></a>
	</div>
	<div id="target-article-content">
		{$this->contentHeader}
	</div>
	<div id="target-article-link">
		<div id="target-article-link-inner"><a href="{$this->url}"><span id="target-article-link-text">「{$this->jounalName}」で続きを読む<span></a></div>
	</div>	
	<div id="home-link">
		<div id="home-link-inner"><a href="{$this->blogURL}"><span id="home-link-text">一覧に戻る</span></a></div>
	</div>
EOT;

		return $content;
	}
}

?>