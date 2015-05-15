<?php

class Article{
	private $id;
	private $url;
	private $journalId;
	private $journalName;
	private $journalCategory;
	private $isNew;
	private $processed;
	private $title;
	private $description;
	private $imagePath;
	private $imageExtension;
	private $contentImages;
	private $contentClassName;	
	private $itemClassName;
	private $contentHeader;
	private $pubDate;
	private $tags;
	private $metadata;
	private $keywordIds;
	private $originalUrl;
	private $originalTitle;

	function __construct() {
		$this->tags = array();
		$this->metadata = array();
		$this->keywordIds = array();
		$this->contentImages = array();
	}

	public function setId($id){
		$this->id = $id;
	} 
	public function getId(){
		return $this->id;
	} 	
	public function setUrl($url){
		$this->url = $url;
	} 
	public function getUrl(){
		return $this->url;
	} 	
	public function setJournalId($journalId){
		$this->journalId = $journalId;
	} 
	public function getJournalId(){
		return $this->journalId;
	}
	public function setJournalName($journalName){
		$this->journalName = $journalName;
	} 
	public function getJournalName(){
		return $this->journalName;
	}
	public function setJournalCategory($journalCategory){
		$this->journalCategory = $journalCategory;
	} 
	public function getJournalCategory(){
		return $this->journalCategory;
	}
	public function setProcessed($processed){
		$this->processed = $processed;
	} 
	public function getProcessed(){
		return $this->processed;
	} 	
	public function setIsNew($isNew){
		$this->isNew = $isNew;
	} 
	public function getIsNew(){
		return $this->isNew;
	}
	public function setTitle($title){
		$this->title = $title;
	} 
	public function getTitle(){
		return $this->title;
	} 	 
	public function setDescription($desc){
		$this->description = $desc;
	} 
	public function getDescription(){
		return $this->description;
	} 	 	
	public function setImagePath($imagePath){
		$this->imagePath = $imagePath;
	} 
	public function getImagePath(){
		return $this->imagePath;
	} 
	public function setImageExtension($imageExtension){
		$this->imageExtension = $imageExtension;
	} 
	public function getImageExtension(){
		return $this->imageExtension;
	} 	
	public function setContentImages($contentImages){
		$this->contentImages = $contentImages;
	} 
	public function getContentImages(){
		return $this->contentImages;
	} 	
	public function setcontentClassName($contentClassName){
		$this->contentClassName = $contentClassName;
	} 
	public function getContentClassName(){
		return $this->contentClassName;
	} 	
	public function setItemClassName($itemClassName){
		$this->itemClassName = $itemClassName;
	} 
	public function getItemClassName(){
		return $this->itemClassName;
	} 	
	public function setContentHeader($contentHeader){
		$this->contentHeader = $contentHeader;
	} 
	public function getContentHeader(){
		return $this->contentHeader;
	} 	
	public function setPubDate($pubDate){
		$this->pubDate = $pubDate;
	} 
	public function getMetadata(){
		return $this->metadata;
	} 	
	public function setMetadata($metadata){
		$this->metadata = $metadata;
	} 
	public function getKeywordIds(){
		return $this->keywordIds;
	} 	
	public function setKeywordIds($keywordIds){
		$this->keywordIds = $keywordIds;
	} 
	public function getPubDate(){
		return $this->pubDate;
	}
	public function addTag($tag){
		$this->tags[] = $tag;
	} 
	public function getTags(){
		return $this->tags;
	} 	 	
	public function getOriginalTitle(){
		return $this->originalTitle;
	} 	
	public function setOriginalTitle($originalTitle){
		$this->originalTitle = $originalTitle;
	} 
	public function getOriginalUrl(){
		return $this->originalUrl;
	} 	
	public function setOriginalUrl($originalUrl){
		$this->originalUrl = $originalUrl;
	} 
}

?>