<?php

	require_once dirname(__FILE__).'/util.php';

	date_default_timezone_set('Asia/Tokyo');
	const sortByDate = -1;
	const sortByIndex = 0;
	const sortByDateAndIndex = 1;
	const sortByIndexAndDate = 2;

	function getArticlesByTagId($tagId, $condition, $sortType){
		global $db;
		if(!$db){
			connectDB();
		} 	

		if(!isset($tagId) || !is_numeric($tagId)){
			return array();
		}

		$whereClause = "";
		$limitClause = "";
		if(isset($condition)){
	     
	      if(isset($condition['date']) && is_string($condition['date'])){
	        $whereClause .= " AND Article.pubDate > '".$condition['date']."' "; 
	      }
	      if(isset($condition['withThumbnailOnly']) && is_bool($condition['withThumbnailOnly'])){
	        $whereClause .= " AND ((Article.imagePath IS NOT NULL) OR (Article.contentImages IS NOT NULL))" ; 
	      }

	      if(isset($condition['journalId'])){
	        $whereClause .= " AND Journal.id=".$condition['journalId']; 
	      }

	      if(isset($condition['numOfArticles']) && is_string($condition['numOfArticles'])){
	        $limitClause .= " LIMIT ".$condition['numOfArticles']; 
	      }

    	} 
    	
		$query = "SELECT Article.id, Article.title, Article.url, Article.pubDate, Article.imagePath, Article.contentImages, Journal.name, Journal.thumbnail, Article_Tag.metadata FROM Article 
					INNER JOIN Article_Tag ON Article.id = Article_Tag.aid
					INNER JOIN Journal ON Article.journalId = Journal.id
					WHERE Article_Tag.tid=".$tagId.$whereClause." ORDER BY pubDate DESC".$limitClause;

		$result = $db->query($query);

		return restructData($result->fetchAll(PDO::FETCH_ASSOC), $sortType);

	}

	function restructData($data, $type){
		$restructedData = array();
		switch ($type) {

			case sortByIndex:
				
				$metadataArray = array();
				foreach($data as $d){
					$metadata = json_decode($d['metadata']);
					if(isset($metadata->index)){
						$metadataArray[] = $metadata->index;
					}else{
						$metadataArray[] = "";
					}
				}
				asort($metadataArray);

				foreach($metadataArray as $key => $value){					
					
					$restructedData[] = array('title' => $data[$key]['title'], 'url' => $data[$key]['url'], 'pubDate' => date('Y-m-d',strtotime($data[$key]['pubDate'])), 'pubTime' => $data[$key]['pubDate'] , 'name' => $data[$key]['name'], 'headerFile'=>generateContentHeaderPath($data[$key]['id']), 'journalThumbnail' => $data[$key]['thumbnail'], 'articleThumbnail' => generateFullImagePath($data[$key]['imagePath'], $data[$key]['contentImages']), 'index' => $value);
				}
				break;

			case sortByDateAndIndex:

				$metadataArray = array();
				$metadataArrayTemp = array();
				$startKeyArray = array();
				$previousPubDate=""; 
				$currentRow=0;
	
				foreach($data as $d){

					$pubDate = date('Y-m-d',strtotime($d['pubDate']));
					if($pubDate != $previousPubDate){
						$startKeyArray[$pubDate] = $currentRow; 
						if(isset($previousPubDate) && count($metadataArrayTemp)>0){
							$metadataArray[$previousPubDate] = unserialize(serialize($metadataArrayTemp));
						}
						$metadataArrayTemp = array();

					}

					$metadata = json_decode($d['metadata']);
					if(isset($metadata->index)){
						$metadataArrayTemp[] = $metadata->index;
					}else{
						$metadataArrayTemp[] = "";
					}
					$previousPubDate = $pubDate;
					$currentRow++;
				}

				//For the last date
				if(count($metadataArrayTemp)>0){
					$metadataArray[$previousPubDate] = unserialize(serialize($metadataArrayTemp));
				}

				foreach ($metadataArray as $date => $MDAOneDay) {
					asort($MDAOneDay);
					foreach($MDAOneDay as $key => $value){	
						$restructedData[] = array('title' => $data[$key+$startKeyArray[$date]]['title'], 'url' => $data[$key+$startKeyArray[$date]]['url'], 'pubDate' => $date, 'pubTime' => $data[$key+$startKeyArray[$date]]['pubDate'] , 'name' => $data[$key+$startKeyArray[$date]]['name'], 'journalThumbnail' => $data[$key+$startKeyArray[$date]]['thumbnail'], 'headerFile'=>generateContentHeaderPath($data[$key+$startKeyArray[$date]]['id']), 'articleThumbnail' => generateFullImagePath($data[$key+$startKeyArray[$date]]['imagePath'], $data[$key+$startKeyArray[$date]]['contentImages']), 'index' => $value);
					}
				}
				break;

			case sortByIndexAndDate:
				
				$restructedData = array();

				$i=0;
				$metadataArray = array();
				$matadataToIndex = array();
				foreach($data as $d){

					$metadata = json_decode($d['metadata']);
					if(isset($metadata->index)){
						$metadataArray[] = $metadata->index;
					}else{
						$metadataArray[] = "";
					}

					if(isset($matadataToIndex[$metadata->index])){
						$matadataToIndex[$metadata->index][] = $i;
					}else{
						$matadataToIndex[$metadata->index] = array($i);
					}
					$i++;

				}
				$metadataArrayUnique = array_unique($metadataArray);
				asort($metadataArrayUnique);
				foreach($metadataArrayUnique as $md){
					foreach ($matadataToIndex[$md] as $key) {
						$restructedData[] = array('title' => $data[$key]['title'], 'url' => $data[$key]['url'], 'pubDate' => date('Y-m-d',strtotime($data[$key]['pubDate'])), 'pubTime' => $data[$key]['pubDate'] , 'name' => $data[$key]['name'], 'headerFile'=>generateContentHeaderPath($data[$key]['id']), 'journalThumbnail' => $data[$key]['thumbnail'], 'articleThumbnail' => generateFullImagePath($data[$key]['imagePath'], $data[$key]['contentImages']), 'index' => $md);
					}
				}

				break;
			default:
		
				foreach($data as $value){					
					
					$restructedData[] = array('title' => $value['title'], 'url' => $value['url'], 'pubDate' => date('Y-m-d',strtotime($value['pubDate'])), 'pubTime' => $value['pubDate'] , 'name' => $value['name'], 'headerFile'=>generateContentHeaderPath($value['id']), 'journalThumbnail' => $value['thumbnail'], 'articleThumbnail' => generateFullImagePath($value['imagePath'], $value['contentImages']), 'index' => NULL);
				}
				break;

		}
		return 	$restructedData;

	} 

	function generateTitle($data, $separator, $appendedStr){
		$numOfElem = count($data);
		$randIndex = rand(0, $numOfElem==0 ? 0 : $numOfElem-1);
		$title = $data[$randIndex]['title'];
		$temp = explode($separator, $title);

		return $temp[0].$separator.$appendedStr;
	}

	function generateContentHeaderPath($id){
		$filename =  CONTENT_DIRECTORY_PATH."/header_".$id.".html";
		if (file_exists($filename)) {
			return CONTENT_URL."/header_".$id.".html";
		}else{
			return NULL;
		}

	}

	function generateFullImagePath($imagePath, $contentImages){

		if (!isset($imagePath) || strlen($imagePath)==0) {
			if (!isset($contentImages) || strlen($contentImages)==0) {
				return NULL;
			}else{
				$imageArray = json_decode($contentImages);
				return array( "normal"=> IMAGE_URL."/content_image_".$imageArray[0].".jpg", "wide" => IMAGE_URL."/content_image_".$imageArray[0]."_w.jpg");		
			}
			
		}else{
			return array( "normal"=> IMAGE_URL."/thumbnail_".$imagePath.".jpg", "wide" => IMAGE_URL."/thumbnail_".$imagePath."_w.jpg");
		}
	}

	function getArticlesForRSS($tagId, $numOfArticles=3){

		global $db;
		if(!$db){
			connectDB();
		} 	

		$query = "SELECT journalId FROM TagJournalRSS WHERE tagId=".$tagId;
		$result = $db->query($query);
		$rs = $result->fetchAll(PDO::FETCH_ASSOC);

		$articlesArray = array();
		foreach ($rs as $value) {
			$articles = getArticlesByTagId($tagId, array('withThumbnailOnly'=>true, 'numOfArticles'=>$numOfArticles, 'journalId'=>$value['journalId']), NULL);
			$articlesArray[$value['journalId']] = $articles;
		}

		return $articlesArray;

	}


?>