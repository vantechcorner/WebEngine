<?php
/**
 * WebEngine CMS
 * https://webenginecms.org/
 * 
 * @version 1.2.6
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2025 Lautaro Angelico, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

try {
	
	// Module status
	if(!mconfig('active')) throw new Exception(lang('error_47',true));
	
    // News object
	$News = new News();
	$cachedNews = loadCache('news.cache');
	if(!is_array($cachedNews)) throw new Exception(lang('error_61'));
	
	// Set news language
	if(config('language_switch_active',true)) {
		if(isset($_SESSION['language_display'])) {
			$News->setLanguage($_SESSION['language_display']);
		}
	}
	
    // Optional category filter
    $requestedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

    // Single news
	$requestedNewsId = isset($_GET['subpage']) ? $_GET['subpage'] : '';
	$showSingleNews = false;
	if(check_value($requestedNewsId) && $News->newsIdExists($requestedNewsId)) {
		$showSingleNews = true;
		$newsID = $requestedNewsId;
	}
	
    // News list
	$i = 0;
	foreach($cachedNews as $newsArticle) {
        if($requestedCategory !== '' && isset($newsArticle['category'])) {
            if(strcasecmp($newsArticle['category'], $requestedCategory) !== 0) continue;
        }
		if($showSingleNews) if($newsArticle['news_id'] != $newsID) continue;
		$News->setId($newsArticle['news_id']);
		
		if($i > mconfig('news_list_limit')) continue;
		
		$news_id = $newsArticle['news_id'];
		$news_title = base64_decode($newsArticle['news_title']);
		$news_author = $newsArticle['news_author'];
		$news_date = $newsArticle['news_date'];
		$news_url = __BASE_URL__.'news/'.$news_id.'/';
		
		// translated news title
		if(config('language_switch_active',true)) {
			if(isset($_SESSION['language_display']) && isset($newsArticle['translations']) && is_array($newsArticle['translations']) && array_key_exists($_SESSION['language_display'], $newsArticle['translations'])) {
				$news_title = base64_decode($newsArticle['translations'][$_SESSION['language_display']]);
			}
		}
		
		// Build content: single = full, list = 500-word excerpt with Read more
		if($showSingleNews) {
			$loadNewsCache = $News->LoadCachedNews();
		} else {
			$fullContent = $News->LoadCachedNews();
			$noTags = strip_tags($fullContent);
			$decoded = html_entity_decode($noTags, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			$plain = trim(preg_replace('/\s+/u', ' ', $decoded));
			$words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY);
			if(count($words) > 250) {
				$excerpt = implode(' ', array_slice($words, 0, 250)) . '...';
			} else {
				$excerpt = $plain;
			}
			$loadNewsCache = '<p>'.$excerpt.'</p>';
			$loadNewsCache .= '<div><a href="'.$news_url.'" class="btn btn-xs btn-default news-readmore">' . lang('news_txt_3') . '</a></div>';
		}
		
			echo '<div class="panel panel-news">';
				echo '<div class="panel-heading">';
					$catBadge = '';
					if(isset($newsArticle['category']) && $newsArticle['category']) {
						$catUrl = __BASE_URL__.'news/?category='.urlencode($newsArticle['category']);
						$catBadge = '<span class="label label-default" style="margin-right:8px;"><a href="'.$catUrl.'" style="color:inherit; text-decoration:none;">'.htmlspecialchars($newsArticle['category']).'</a></span> ';
					}
					echo '<h3 class="panel-title">'.$catBadge.'<a href="'.$news_url.'">'.$news_title.'</a></h3>';
				echo '</div>';
			if(mconfig('news_expanded') > $i) {
				echo '<div class="panel-body">';
					echo $loadNewsCache;
				echo '</div>';
				echo '<div class="panel-footer">';
					echo '<div class="col-xs-6 nopadding">';
					echo '</div>';
					echo '<div class="col-xs-6 nopadding text-right">';
						echo date("l, F jS Y",$news_date);
					echo '</div>';
				echo '</div>';
			}
		echo '</div>';
		
		$i++;
	}

} catch(Exception $ex) {
	message('warning', $ex->getMessage());
}