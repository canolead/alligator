<?php
require_once dirname(__FILE__).'/AlligatorAPI.php';
date_default_timezone_set('Asia/Tokyo');

if(isset($argv[1])){
	$t = $argv[1];
}else{
	echo "Invalid tag id\n";
	exit;
}

AlligatorAPI::generateArticleJSONFiles((int)$t);

?>