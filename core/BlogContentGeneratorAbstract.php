<?php

require_once dirname(__FILE__).'/ArticleController.php';
require_once dirname(__FILE__).'/util.php';

abstract Class BlogContentGeneratorAbstract{

	protected $journalId;
	protected $jounalName;
	protected $tagId;
	protected $articleId;
	protected $keywordId;
	protected $title;
	protected $url;
	protected $valid;

	function __construct($article, $tagId){
		$this->valid = true;
		$this->journalId = $article->getJournalId();
		$this->jounalName = $article->getJournalName();
		$this->articleId = $article->getId();
		$this->title = $article->getTitle();
		$this->url = $article->getUrl();
		$this->tagId = $tagId;

		$keywordIds =  $article->getKeywordIds();

		if(isset($keywordIds[$tagId]))	$this->keywordId = $keywordIds[$tagId];


		/*
		if(isset($tagId) && !is_nan((int)$tagId)){
			
			global $db; 
	      	if(!isset($db)){
	        	connectDB();
	      	}

	      	$query = "SELECT name FROM Tag WHERE id=".$tagId." LIMIT 1";
			$result = $db->query($query);
			$rs = $result->fetchAll(PDO::FETCH_ASSOC);
			
			if($rs){
				$this->tagname = $rs[0]["name"];
			}else{
				error_log("Tag name not found");
				$this->valid = false;
			}

		}else{
			error_log("Tag not found");
			$this->valid = false;
		}
		*/
	}

	public function isValid(){
		return $this->valid;
	}

	abstract public function generateBlogContent();
	abstract public function generateBlogTitle();
	abstract public function getThumbnailPath();
}

?>