<?php
require_once dirname(__FILE__).'/BlogContentGeneratorAbstract.php';
require_once dirname(__FILE__).'/util.php';
require_once dirname(__FILE__).'/TweetAnalyzor.php';


class BlogContentGeneratorWithTwitter extends BlogContentGeneratorAbstract{

	const maxNumOfArticles = 6;
	const maxNumOfArticlesPerQuery = 4;

	private $searchQuery;
	private $blogURL;

	function __construct($article, $tagId, $pageConfig){
	
		parent::__construct($article, $tagId);	

		if(isset($this->keywordId) && !is_nan((int)$this->keywordId)){
			
			global $db; 
	      	if(!isset($db)){
	        	connectDB();
	      	}

	      	$query = "SELECT searchQuery FROM Tag_Keywords WHERE id=".$this->keywordId." LIMIT 1";
			$result = $db->query($query);
			$rs = $result->fetchAll(PDO::FETCH_ASSOC);
			
			if($rs){
				$this->searchQuery = $rs[0]["searchQuery"];
			}else{
				error_log("Search query not found");
				$this->valid = false;
			}

		}else{
			error_log("Search query not found");
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
		return NULL;
	}

	public function generateBlogContent(){

		$tws = TweetAnalyzor::getTweetsByQueries($this->searchQuery);
		$selectedTweets = TweetAnalyzor::selectTweets($tws, $this->searchQuery);

		if(count($selectedTweets)==0)	return NULL;

		$content = "";
		foreach($selectedTweets as $stw){
			$content .= "<div>".$stw."</div>";
		}

$contentBlock = <<<EOT

	<div id="target-article-title">
		<h2 class="entry-title" >{$this->title}</h2>
	</div>
	
	<div class="iframeWrapperOuter" style="display:block">
		<div class="iframeWrapperInner">
		
		</div>
	</div>
	
	<div id="target-article-content">
		{$content}
	</div>
	<div id="home-link">
		<div id="home-link-inner"><a href="{$this->blogURL}"><span id="home-link-text">動画一覧に戻る</span></a></div>
	</div>
	<script>
		window.onload = function(e){
			document.getElementsByClassName("iframeWrapperInner")[0].innerHTML = '<iframe src="{$this->url}?sp" frameborder="0" style="width:100%; height:400px;" />';
		}
	</script>
EOT;
		

		return $contentBlock;
	}

}

?>