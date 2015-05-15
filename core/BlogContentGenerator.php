<?php
require_once dirname(__FILE__).'/ArticleController.php';
require_once dirname(__FILE__).'/BlogContentGeneratorAbstract.php';

class BlogContentGenerator extends BlogContentGeneratorAbstract{

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
			$this->thumbnailPath = IMAGE_DIRECTORY_PATH."/thumbnail_".$img."_w.jpg";
		}else{
			$imgContents = $article->getContentImages();
			if(isset($imgContents[0])){
				$this->thumbnail = IMAGE_URL."/content_image_".$imgContents[0].".jpg";
				$this->thumbnailWide = IMAGE_URL."/content_image_".$imgContents[0]."_w.jpg";
				$this->thumbnailPath = IMAGE_DIRECTORY_PATH."/content_image_".$imgContents[0]."_w.jpg";
			}else{
				error_log("Thumbnail not found");
				$this->valid = false;
			}
		}


		$this->contentHeader = ArticleController::generateArticleContentHeader($article, array("contentKeywordFile"=>$pageConfig["contentKeywordFile"]));

		if(!isset($this->contentHeader) || strlen($this->contentHeader)==0){
			error_log("Content header file not found.");
			$this->valid = false;
		}

/*
		$contentHeaderPath = CONTENT_DIRECTORY_PATH."/header_".$article->getId().".txt";
		if(file_exists($contentHeaderPath)){
			$this->contentHeader = file_get_contents($contentHeaderPath);
		}else{
			error_log("Content header file not found.");
			$this->valid = false;
		}
*/

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

$firstBlock = <<<EOT

	<div id="target-article-title-wrapper" style="width: 100%; background: #ffffff url({$this->thumbnailWide}) no-repeat; background-position: center; background-size: cover">
		<div id="target-article-title">
			<a href="{$this->url}"><h2 class="entry-title" >{$this->title}</h2></a><span id="target-journal-title">{$this->jounalName}</span>
		</div>
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
	<div id="recommended-articles">
		<div id="recommended-articles-message">関連記事</div>
		<ul class="article-list">
EOT;
	
		if(isset($this->keywordId)){

			$articles = ArticleController::getArticlesFromDB(array('journal'=>$this->journalId, 'tag'=>$this->tagId, 'keyword'=>$this->keywordId, 'limit'=> ((int)self::maxNumOfArticlesPerQuery)));			
			$articles_kw = ArticleController::getArticlesFromDB(array('tag'=>$this->tagId, 'keyword'=>$this->keywordId, 'limit'=> ((int)self::maxNumOfArticlesPerQuery)));
			foreach($articles_kw as $akw){
				$articles[] = $akw;
			}
			$articles_j = ArticleController::getArticlesFromDB(array('tag'=>$this->tagId, 'journal'=>$this->journalId, 'limit'=> ((int)self::maxNumOfArticlesPerQuery)));		
			foreach($articles_j as $aj){
				$articles[] = $aj;
			}

		}else{
			$articles = ArticleController::getArticlesFromDB(array('journal'=>$this->journalId, 'tag'=>$tagId, 'limit'=> ((int)self::maxNumOfArticles)*2));
		}
				
		$addedArticles = array();
		$content = "";
		$numOfArticles = 0;
		foreach ($articles as $a) {

			if($numOfArticles > (int)self::maxNumOfArticles){

				break;
			}

			if($a->getId() == $this->articleId || isset($addedArticles[$a->getId()])){
				
				continue;
			}

			$imgPath = $a->getImagePath();
			if(isset($imgPath)){
				$thumbnail = IMAGE_URL."/thumbnail_".$imgPath.".jpg";
			}else{
				continue;
			}

      		$content .= '<a href="'.$a->getUrl().'" target="_blank">
			        		<li>
			        			<div class="leftBox" >
				        			<div class="title">'.$a->getTitle().'</div>
				        			<div class="blog">'.$a->getJournalName().'</div>
								</div>
			        			<img src="'.$thumbnail.'" />
			        		</li>
			        	</a>';

			$addedArticles[$a->getId()] = 1;
			$numOfArticles++;
		}
		$content .= '</ul></div>';
		

		return $firstBlock.$content;
	}


}

?>