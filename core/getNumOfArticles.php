<?php
require_once dirname(__FILE__).'/util.php';
date_default_timezone_set('Asia/Tokyo');

global $db;
if(!isset($db)){
connectDB();
}
/* ファイルポインタをオープン */
$file = fopen("list.txt", "r");
$datetime = date('Y-m-d H:i:s', strtotime("-1day"));

/* ファイルを1行ずつ出力 */
if($file){
	$query = "SELECT COUNT(id) as num FROM Article WHERE pubDate > '".$datetime."' AND journalId>=68 AND (";
	$isFirst = true;
	while ($line = fgets($file)) {
		$str = trim($line);
		if(strlen($str)==0)	continue;

		if($isFirst){
			$isFirst = false;
		}else{
			$query .= " OR ";	
		}
		$query .= " title LIKE '%".$str."%' ";
	
	}
	$query .= ")";

    $result = $db->query($query);
	$rs = $result->fetchAll(PDO::FETCH_ASSOC);
    if(isset($rs[0])){
        echo("num of articles:".$rs[0]['num']."\n");
    }
	

}
 
fclose($file);

?>