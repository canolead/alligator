<?php
require_once dirname(__FILE__).'/twitteroauth/autoload.php'; 
require_once dirname(__FILE__).'/twitteroauth/src/TwitterOAuth.php'; 
use Abraham\TwitterOAuth\TwitterOAuth;

define('CONSUMER_KEY', 'wgxJ67RRQPgWfgNAlroHs3bKs');
define('CONSUMER_SECRET', 'J0zo0Mm9wQPbQBisEGglmAmmm2hRAALAO5DF8OUjVKwtOkbHz6');
define('ACCESS_TOKEN', '3118867754-HzPs92JjmUEvsstRVP8A2roSrcBwYlAyDxK36UL');
define('ACCESS_TOKEN_SECRET', 'a30S2kFg8XIo4NhciUJ6lAivQi3nSSmAnVD4qzx3Uo7Lv');

Class TwitterAPI{

	private $toa;

	function __construct(){
	  $this->toa = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
	}

	function search(array $query)
	{
	  return $this->toa->get('search/tweets', $query);
	}
	 
}

?>