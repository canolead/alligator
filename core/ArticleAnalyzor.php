<?php
	require_once dirname(__FILE__).'/util.php';
	require_once dirname(__FILE__).'/Image.php';
	require_once dirname(__FILE__).'/libs/simplehtmldom/simple_html_dom.php';

	class ArticleAnalyzor{

		public static function getTagFromTitle($title, $journalCategory){

			global $db;
			if(!isset($db)){
				connectDB();
			}

		    $query = "SELECT * FROM Tag_Keywords WHERE journalCategory='".$journalCategory."'";		
		    $result = $db->query($query);

		    $tagIds = array();
			$articleIndices = array();
			$keywordIds = array();
			$priorities = array();
		    while ($rs = $result->fetch(PDO::FETCH_ASSOC)) {

		    	$tid = $rs['tid'];
		    	$aidx = $rs['articleIndex'];
		    	$kid = $rs['id'];
		    	$priority = $kid = $rs['priority'];
		    	$keywordSet = json_decode(stripslashes($rs['keywords']));

	    		if(is_array($keywordSet)){	//more than a word
		    		$kwValid = true;
		    		foreach($keywordSet as $kw){
		    			if(strpos($title, $kw) === false) {
		    				$kwValid = false;
		    			}
		    		}
		    		if($kwValid){
		    			$tagIds[] = $tid;
		    			$articleIndices[$tid] = array("index"=>$aidx);
		    			if(isset($kid))	$keywordIds[$tid] = (int)$kid;
		    			$priorities[$tid] = (int)$priority;
		    		}
	    		}else{ 	//a single word

    				if(strpos($title, $keywordSet) !== false) {
	    				$tagIds[] = $tid;
	    				$articleIndices[$tid] = array("index"=>$aidx);
	    				if(isset($kid))	$keywordIds[$tid] = (int)$kid;
	    				$priorities[$tid] = (int)$priority;
	    			}	
	    		}
		    		
		    }

			return array("tagIds"=>array_unique($tagIds), "articleIndices"=>$articleIndices, "keywordIds"=>$keywordIds, "priorities"=>$priorities);
		}


		public static function extractArticleHeader($filename, $focusedClass, $focusedItemClass=NULL, $contentKeywordArray=NULL, $contentNGwordArray=NULL){
			
		
			$html = file_get_html($filename);
			if(!$html){
				error_log("Error while opening the html file");
				return NULL;
			}

			$container = $html->find("[class*='".$focusedClass."']",0);

			if(!isset($container)){
				return NULL;
			}

			if(strpos($container, "転載禁止")){
				return NULL;
			}

			if($container->first_child() == NULL){
				return NULL;
			}

			if(isset($focusedItemClass)){
				$headerArray = extractTextByClass($container, $focusedItemClass, $contentKeywordArray,  $contentNGwordArray);
				return $headerArray;
			}else{
				$headerArray = extractTextRecursively($container, $contentKeywordArray, $contentNGwordArray);
				return array("headerStr"=>$headerArray["string"]);
			}

		}		
		
		public static function extractArticleImages($filename, $focusedClass){
			
			$imageSet = array();
			$html = file_get_html($filename);
			if(!$html){
				error_log("Error while opening the html file");
				return NULL;
			}

			$container = $html->find("[class*='".$focusedClass."']",0);
				
			if(isset($container)){
				preg_match_all("/<img([^\>]*)>/", $container, $imgs);
			
			    if(isset($imgs[0])){

			    	foreach($imgs[0] as $imgVal){
			      		preg_match("/(http|https):\/\/[\S]+\.(jpg|jpeg|png|gif)/", $imgVal, $imgPathTemp);
			    
				      	if(isset($imgPathTemp[0]) && isset($imgPathTemp[2])){

				      		$imageSet[] = array("path"=>$imgPathTemp[0], "extension"=>$imgPathTemp[2]);

				      	}
				  	}
				  	
			    }	    
			}

			return $imageSet;
		}		


	}

	function extractTextByClass($container, $className, $contentKeywordArray=NULL, $contentNGwordArray=NULL){

		$imgAdded = array();
		$resStr = ""; 

		switch ($className) {
			case 't_h':
				$q = ".t_b, .t_h, #t_b, #t_h";	
				$qb = "t_b";
				break;
			case 'dt':
				$q = "dd, dt";	
				$qb = "dd";
				break;
			case 'p':
				$q = "p";
				break;
			default:
				$q = ".".$className;	
				break;
		}
		
		$divArray = $container->find($q);

		$hasBody = false;
		if($className=="t_h"){
			foreach ($divArray as $div) {
				$attrClassString = $div->getAttribute("class");
				if(strpos($attrClassString, $qb)!==false){
					$hasBody = true;
					break; 
				}	

				//hack for drama impression
				$attrIdString = $div->getAttribute("id");
				if(strpos($attrIdString, $qb)!==false){
					$hasBody = true;
					break; 
				}	

			}
		}else if($className=="dt"){
			foreach ($divArray as $div) {
				if($div->tag == $qb){
					$hasBody = true;
					break; 
				} 
			}
		}


		$headerText = "";
		$articleHeaderStr = "";
		$currentDivIndex = 0; 
		$firstRow = true;
		$numOfResponses = 0;
		foreach ($divArray as $div) {

			$isHeader = false;
			if($className=="t_h"){
				$attrClassString = $div->getAttribute("class");
				if(strpos($attrClassString, "t_h")!==false){
					$isHeader = true;
				}

				//hack for drama impression
				$attrIdString = $div->getAttribute("id");
				if(strpos($attrIdString, "t_h")!==false){
					$isHeader = true;
				}

			}else if($className=="dt"){
				if($div->tag == "dt"){
					$isHeader = true;
				} 
			}

			if($isHeader && ($currentDivIndex >= count($divArray)-2 || $currentDivIndex >= 40)){
				break;
			}
			$currentDivIndex++;	


			$textsToRemove = array();
			$anchors = $div->find("a");
			foreach($anchors as $anc){
				$textsToRemove[] = $anc->plaintext;
			}

			$textsToInsertBr = array();
			$paragraphs = $div->find("p,div,dd,dt");
			foreach($paragraphs as $par){
				$textsToInsertBr[] = $par->plaintext;
			}

			$imgStr = "";
			$numOfImages = 0;	
			preg_match_all("/<img([^\>]*)>/", $div->innertext, $imgs);
		    if(isset($imgs[0]) && is_array($imgs[0])){
				
				foreach ($imgs[0] as $imgElem) {
					if($numOfImages >= 5) break;

		      		preg_match("/(http|https):\/\/[\S]+\.(jpg|jpeg|png)/", $imgElem, $imgPathTemp);
			      	if(isset($imgPathTemp[0])){	
			      		preg_match("/profile|google|twitter|amazon/",$imgPathTemp[0],$m);
			      		if(isset($m[0]))	continue;	
			      	
			      		$imgStr .= '<figure><img src="'.$imgPathTemp[0].'" /></figure>';
			      		$imgAdded[] = $imgPathTemp[0];
			      		$numOfImages++;
			      		
			      	}
		      	}
			}
		
			$str = $div->plaintext;
			foreach($textsToRemove as $txt){
				$str = str_replace($txt, '', $str);
			}

			foreach($textsToInsertBr as $txt){
				$str = str_replace($txt, $txt.'<br/>', $str);
			}
			$str = preg_replace('/\n|\r/', '<br/>', $str);
			$str = preg_replace('/<br\/>(\s)*<br\/>(\s)*(<br\/>(\s)*)*/', '<br/><br/>', $str);

			$isValid = true;
			
			if(!$firstRow && !$isHeader){

				foreach ($contentNGwordArray as $ngw) {
					preg_match("/".preg_quote($ngw)."/", $str, $match);
					if(count($match)>0){
						$isValid = false;
						break;
					}
				}
				if(!$isValid){
					$headerText = "";
					continue;
				}
			}	

			if(strlen($imgStr)==0){
				$spaceOnlyStr = "/^[\\s|\\x{3000}|(<br(\\s)*(\/)*>)]+$/u";
				preg_match($spaceOnlyStr, $str, $match);
				if(count($match)>0){
					$headerText = "";
					continue;
				}
			}


			if($isHeader){

				if($hasBody){
					$headerText = '<div class="d_h"><font class="small">'.$str.'</font></div>';
				}else{
					$resStr .= $imgStr.'<div>'.$str.'</div>';
					$numOfResponses++;
				}

			}else{
				if($hasBody){
					if(strlen($str)>=300){
						$resStr .= $headerText.$imgStr.'<div class="d_b"><font class="middle">'.$str.'</font></div>';
					}else{
						if($firstRow){
							$resStr .= $headerText.$imgStr.'<div class="d_b"><font class="red">'.$str.'</font></div>';
						}else{
							$resStr .= $headerText.$imgStr.'<div class="d_b">'.$str.'</div>';
						}
					}
					$firstRow = false;
				}else{
					if(strlen($str)>=300){
						$resStr .= $imgStr.'<div><font class="middle">'.$str.'</font></div>';
					}else{
						$resStr .= $imgStr.'<div>'.$str.'</div>';
					}
				}
				$numOfResponses++;
			}

			if($numOfResponses >= 8 && strlen($articleHeaderStr)==0){
				$articleHeaderStr = $resStr;
			}
		}

		if(strlen($articleHeaderStr)==0){
			$articleHeaderStr = $resStr;
		}
/*
		$imgStr = "";
		$imgArray = array();
		preg_match_all("/<img([^\>]*)>/", $container->innertext, $imgs);
	    if(isset($imgs[0])){

	    	foreach($imgs[0] as $imgVal){

	      		preg_match("/(http|https):\/\/[\S]+\.(jpg|jpeg|png)/", $imgVal, $imgPathTemp);
	    
		      	if(isset($imgPathTemp[0])){	
		      		preg_match("/profile|google|twitter|amazon/",$imgPathTemp[0],$m);
		      		if(isset($m[0]))	continue;	
		      	
		      		$imgMatched = false;
		      		foreach ($imgAdded as $imgSrc) {
		      			if($imgSrc == $imgPathTemp[0]){
		      				$imgMatched = true;
		      				break;
		      			}
		      		}
		      		if($imgMatched)	continue;

		      		$imgArray[] = '<img src="'.$imgPathTemp[0].'" />';
		      	}
		  	}
		  	if(count($imgArray)>2 ){
			  	$imgStr .= array_shift($imgArray);
			  	$rand_keys = array_rand($imgArray, 2);
			  	$imgStr .= $imgArray[$rand_keys[0]];
			  	$imgStr .= $imgArray[$rand_keys[1]];
		  	}else if(count($imgArray)==2){
		  		$imgStr .= $imgArray[0].$imgArray[1];
		  	}else if(count($imgArray)==1){
		  		$imgStr .= $imgArray[0];
		  	}
	    }
	    var_dump($resStr.$imgStr);
	    */

	    return array("headerStr"=>$articleHeaderStr, "fullTextStr"=>$resStr);
	}

	function extractTextRecursively($e, $contentKeywordArray=NULL, $contentNGwordArray=NULL){
		$strThres = 1500;

		$hasChild = false;
		foreach ($e->children as $child) {
			if($child->tag == "div" || $child->tag == "blockquote" || $child->tag == "ul" ||  $child->tag == "dd" ||  $child->tag == "dt"){
				$hasChild = true;
			}
		}

		if(!$hasChild){
/*
			$imgStr = "";

			$numOfImages = 0;
			$imageSet = array();

			if(end($contentNGwordArray)!="/<img([^\>]*)>/"){
				preg_match_all("/<img([^\>]*)>/", $e->innertext, $imgs);
			    if(isset($imgs[0])){

			    	foreach($imgs[0] as $imgVal){

			      		preg_match("/(http|https):\/\/[\S]+\.(jpg|jpeg|png)/", $imgVal, $imgPathTemp);
			    
				      	if(isset($imgPathTemp[0])){	
				      		preg_match("/profile|google|twitter|amazon/",$imgPathTemp[0],$m);
				      		if(isset($m[0]))	continue;	
				      	
				      		$imgStr .= '<figure><img src="'.$imgPathTemp[0].'" /></figure>';
				      		$numOfImages++;
				      	}
				      	if ($numOfImages >= 1) break;
				  	}
				  	
			    }	
			}
		    

			$text = preg_replace('/\n|\r|\r\n|[0-9]+\:[0-9]+\:[0-9]+|[0-9]+\/[0-9]+\/[0-9]+|<img([^\>]*)>|<?\/p([^\>]*)>/', '', $e->plaintext);
			$text = htmlspecialchars(preg_replace('/[\s|\x{3000}]+/u',' ',$text));
*/
/*
			if(strlen($text)>300 || strlen($text)==0){
				return array("string"=>$imgStr, "done"=>false, "numOfImages"=>$numOfImages);
			}
*/


			$numOfImages = 0;
			$textsToRemove = array();
			$anchors = $e->find("a");
			foreach($anchors as $anc){
				$textsToRemove[] = $anc->plaintext;
			}

			$textsToInsertBr = array();
			$paragraphs = $e->find("p");
			foreach($paragraphs as $par){
				$textsToInsertBr[] = $par->plaintext;
			}

			$imgStr = "";
			if($numOfImages < 3){		
				preg_match_all("/<img([^\>]*)>/", $e->innertext, $imgs);
			    if(isset($imgs[0]) && is_array($imgs[0])){
					
					foreach ($imgs[0] as $imgElem) {
						if($numOfImages >= 3) break;

			      		preg_match("/(http|https):\/\/[\S]+\.(jpg|jpeg|png)/", $imgElem, $imgPathTemp);
				      	if(isset($imgPathTemp[0])){	
				      		preg_match("/profile|google|twitter|amazon|line/",$imgPathTemp[0],$m);
				      		if(isset($m[0]))	continue;	
				      	
				      		$imgStr .= '<figure><img src="'.$imgPathTemp[0].'" /></figure>';
				      		$imgAdded[] = $imgPathTemp[0];
				      		$numOfImages++;
				      		
				      	}
			      	}
				}
			}

			$str = $e->plaintext;
			foreach($textsToRemove as $txt){
				$str = str_replace($txt, '', $str);
			}

			foreach($textsToInsertBr as $txt){
				$str = str_replace($txt, $txt.'<br/>', $str);
			}
			$str = preg_replace('/\n|\r/', '<br/>', $str);
			$str = preg_replace('/<br\/>(\s)*<br\/>(\s)*(<br\/>(\s)*)*/', '<br/><br/>', $str);


			if(isset($contentNGwordArray) && is_array($contentNGwordArray)){
				foreach ($contentNGwordArray as $ngw) {
					preg_match($ngw, $str, $match);
					if(count($match)>0){
					
						return array("string"=>$imgStr, "done"=>false, "numOfImages"=>$numOfImages);
					}
				}	
			}


			if(isset($contentKeywordArray)){

				$hasKeyword = false;
				foreach ($contentKeywordArray as $kw) {
					if(strpos($str, $kw)!==false){
						$hasKeyword = true;
						break;
					}	
				}
				
				if($hasKeyword || strlen($imgStr)>0){
					if(strlen($str) > $strThres+200*$numOfImages){
						return array("string"=>substr($imgStr.$str, $strThres+200*$numOfImages), "done"=>true, "numOfImages"=>$numOfImages);
					}
					return array("string"=>$imgStr.$str, "done"=>false, "numOfImages"=>$numOfImages);
				}else{
					return array("string"=>$imgStr, "done"=>false, "numOfImages"=>$numOfImages);
				}
			
			}else{

				if(strlen($str) > $strThres+200*$numOfImages){
					return array("string"=>substr($imgStr.$str, $strThres+200*$numOfImages), "done"=>true, "numOfImages"=>$numOfImages);
				}
				return array("string"=>$imgStr.$str, "done"=>false, "numOfImages"=>$numOfImages);
			}

		}else{

			$totNumOfImages = 0;
			$retstr = "";
			foreach ($e->children as $child) {
				/*
				if($child->tag == "br"){
					$str .= "<br/>";
					continue;
				}
				*/

				if($child->tag == "table" || $child->tag == "br" || $child->tag == "a") 	continue;

				$value = extractTextRecursively($child, $contentKeywordArray, $contentNGwordArray);

				$rawString = preg_replace('/\n|\r|\r\n/', '', $value["string"]);
				if($totNumOfImages < 3){
				
					$totNumOfImages += $value["numOfImages"];
				
					if($totNumOfImages>=3){
						if(isset($contentNGwordArray)){
							$contentNGwordArray[] = '/<img([^\>]*)>/';
						}else{
							$contentNGwordArray = array('/<img([^\>]*)>/');
						}
					}
				}

				if($value["done"]){	
					$value["string"] = $retstr.$value["string"];
					return $value;
				}


				$newstr = $retstr;
				if(strlen($rawString)!=0){
				
					if(strpos($rawString,"<div>")===false){
						$newstr .= "<div>".$rawString."</div>";
					}else{
						$newstr .= $rawString;
					}
					
					if(strlen($newstr) > $strThres+$totNumOfImages*200){
						return array("string"=>$retstr, "done"=>true, "numOfImages"=>$totNumOfImages); 
					}
					$retstr = $newstr;
				}


			}
			return array("string"=>$newstr, "done"=>false, "numOfImages"=>$totNumOfImages); 	
		}
		
	}
		
?>