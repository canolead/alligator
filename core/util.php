<?php
require_once dirname(__FILE__).'/config.php';

function connectDB(){
	global $db;
	try {
		$db = new PDO('mysql:unix_socket='.DB_SOCKET.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
		$db->exec('set names utf8');

	} catch (PDOException $e) {
	    print "PDO Error: " . $e->getMessage() . "<br/>";
	    die();
	}

}


?>