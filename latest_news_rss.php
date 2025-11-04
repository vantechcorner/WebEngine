<?php
// Minimal RSS feed for Game Launcher / external readers
// Outputs items from WebEngine News (OpenMU compatible)

define('access', 'index');

try {
	// Minimal bootstrap (avoid full Handler/template)
	define('__ROOT_DIR__', str_replace('\\','/',dirname(__FILE__)).'/');
	define('__PATH_INCLUDES__', __ROOT_DIR__.'includes/');
	define('__PATH_CLASSES__', __PATH_INCLUDES__.'classes/');
	define('__PATH_FUNCTIONS__', __PATH_INCLUDES__.'functions/');
	define('__PATH_CONFIGS__', __PATH_INCLUDES__.'config/');
	define('__PATH_MODULE_CONFIGS__', __PATH_CONFIGS__.'modules/');
	define('__PATH_CACHE__', __PATH_INCLUDES__.'cache/');

	if(!@include_once(__PATH_INCLUDES__ . 'functions.php')) throw new Exception('Could not load functions.');
	if(!@include_once(__PATH_CLASSES__ . 'class.database.php')) throw new Exception('Could not load class (database).');
	if(!@include_once(__PATH_CLASSES__ . 'class.connection.php')) throw new Exception('Could not load class (connection).');
	if(!@include_once(__PATH_CLASSES__ . 'class.validator.php')) throw new Exception('Could not load class (validator).');
	if(!@include_once(__PATH_CLASSES__ . 'class.news.php')) throw new Exception('Could not load class (news).');

	$limit = 10;
	if(isset($_GET['limit']) && is_numeric($_GET['limit'])) {
		$val = (int)$_GET['limit'];
		if($val > 0 && $val <= 50) $limit = $val;
	}

	$host = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$baseUrl = $scheme.'://'.$host;

	$News = new News();
	$list = $News->retrieveNews();

	// Helper: short text
	$shorten = function($text, $limit = 250) {
		if(!is_string($text) || $text === '') return '';
		$plain = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8'))));
		if(mb_strlen($plain, 'UTF-8') <= $limit) return $plain;
		return rtrim(mb_substr($plain, 0, $limit, 'UTF-8')).'…';
	};

	header('Content-Type: application/rss+xml; charset=utf-8');
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	echo "<rss version=\"2.0\">\n";
	echo "\t<channel>\n";
	echo "\t\t<title>MU Online - News</title>\n";
	echo "\t\t<link>".$baseUrl."/</link>\n";
	echo "\t\t<description>Latest news</description>\n";
	echo "\t\t<language>en</language>\n";
	echo "\t\t<lastBuildDate>".date(DATE_RSS)."</lastBuildDate>\n";

	$printed = 0;
	if(is_array($list) && count($list) > 0) {
		foreach($list as $row) {
			$newsId = isset($row['news_id']) ? (int)$row['news_id'] : 0;
			$title = isset($row['news_title']) ? $row['news_title'] : '';
			$content = isset($row['news_content']) ? $row['news_content'] : '';
			$category = (isset($row['category']) && $row['category'] !== null) ? $row['category'] : '';
			$dateTs = isset($row['news_date']) ? (int)$row['news_date'] : 0;
			$pubDate = $dateTs > 0 ? date(DATE_RSS, $dateTs) : date(DATE_RSS);
			$link = $newsId > 0 ? ($baseUrl.'/news/'.$newsId.'/') : ($baseUrl.'/news/');

			echo "\t\t<item>\n";
			echo "\t\t\t<title><![CDATA[".$title."]]></title>\n";
			echo "\t\t\t<link>".$link."</link>\n";
			echo "\t\t\t<guid isPermaLink=\"false\">news-".$newsId."</guid>\n";
			echo "\t\t\t<pubDate>".$pubDate."</pubDate>\n";
			if($category !== '') {
				echo "\t\t\t<category><![CDATA[".$category."]]></category>\n";
			}
			echo "\t\t\t<description><![CDATA[".$shorten($content, 500)."]]></description>\n";
			echo "\t\t</item>\n";
			$printed++;
			if($printed >= $limit) break;
		}
	}

	echo "\t</channel>\n";
	echo "</rss>";

} catch(Exception $ex) {
	header('Content-Type: text/plain; charset=utf-8');
	echo 'Error: ' . $ex->getMessage();
}


