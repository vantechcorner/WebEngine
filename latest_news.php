<?php
// Minimal Latest News feed for Game Launcher
// Outputs only: Category (if present), Title, Date

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

	$limit = 5;
	if(isset($_GET['limit']) && is_numeric($_GET['limit'])) {
		$val = (int)$_GET['limit'];
		if($val > 0 && $val <= 20) $limit = $val;
	}

	// Dimensions (px) with sane bounds
	$w = 520; $h = 350;
	if(isset($_GET['w']) && is_numeric($_GET['w'])) {
		$wVal = (int)$_GET['w'];
		if($wVal >= 240 && $wVal <= 1200) $w = $wVal;
	}
	if(isset($_GET['h']) && is_numeric($_GET['h'])) {
		$hVal = (int)$_GET['h'];
		if($hVal >= 160 && $hVal <= 1200) $h = $hVal;
	}

	$News = new News();
	$list = $News->retrieveNews();

	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Official MU Online</title><base target="_blank"></head>';
	echo '<body oncontextmenu="return false" ondragstart="return false" onselectstart="return false">';
	echo '<style type="text/css">html,body,form,ul,li,h4{margin:0;padding:0;border:0}body,div,li,h4{font:11px/14px Arial,Verdana,sans-serif;color:#fff}html,body{width:'.$w.'px;height:'.$h.'px;background-color:#0f1320;overflow:hidden}ul{list-style-type:none}a,a:link,a:visited{color:#fff;text-decoration:none}a:hover{text-decoration:underline}#notice_a{position:relative;width:100%;height:100%;background:url(img/bg_list.gif) left 29px no-repeat;background-color:#171c29;border-radius:8px;box-sizing:border-box}#notice_a h4{position:relative;width:100%;display:block;height:29px;background:url(img/tab_newsevent.gif) left top no-repeat;margin:0;padding:5px 0 0 194px;box-sizing:border-box}#notice_a h4 strong{display:block !important;position:absolute;left:12px;top:6px;font-weight:700;color:#f0c54a;letter-spacing:0.5px;text-transform:uppercase}#notice_a ul{position:absolute;top:29px;left:0;right:0;bottom:0;height:auto;width:100%;list-style-type:none;margin:0;padding:0 15px 0 15px;overflow-y:auto;box-sizing:border-box}#notice_a ul li{width:100%;height:36px;margin:0;padding:0 0 0 9px;background:url(img/bu_list.gif) left 21px no-repeat;vertical-align:top;box-sizing:border-box}#notice_a ul li strong{display:block;font-weight:lighter;font-size:9px;color:#a0a3b6;line-height:100%;padding-top:3px}#notice_a ul li span{display:block;font-size:11px;color:#e3e4ea;line-height:120%;padding-top:3px;max-width:100%;text-overflow:ellipsis;white-space:nowrap;overflow:hidden;cursor:pointer}</style>';
	$homeHref = '/';
	echo '<div id="notice_a">';
	echo '<h4 title="Notice &amp; Events"><strong>Notice &amp; Events</strong></h4>';
	echo '<ul>';
	if(is_array($list) && count($list) > 0) {
		$printed = 0;
		foreach($list as $row) {
			$category = (isset($row['category']) && $row['category'] !== null) ? $row['category'] : '';
			$title = isset($row['news_title']) ? $row['news_title'] : '';
			$dateTs = isset($row['news_date']) ? (int)$row['news_date'] : 0;
			$dateStr = $dateTs > 0 ? date('Y-m-d', $dateTs) : '';
			$head = [];
			if($category !== '') $head[] = '['.htmlspecialchars($category, ENT_QUOTES, 'UTF-8').']';
			if($dateStr !== '') $head[] = htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8');
			echo '<li>';
			echo '<strong>'.implode(' ~ ', $head).'</strong>';
			$newsId = isset($row['news_id']) ? (int)$row['news_id'] : 0;
			$newsUrl = $newsId > 0 ? '/news/'.$newsId.'/' : '/news/';
			echo '<a href="'.$newsUrl.'" target="_blank"><span>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</span></a>';
			echo '</li>';
			$printed++;
			if($printed >= $limit) break;
		}
	}
	echo '</ul>';
	echo '</div>';
	echo '</body></html>';

} catch(Exception $ex) {
	header('Content-Type: text/plain; charset=utf-8');
	echo 'Error: ' . $ex->getMessage();
}


