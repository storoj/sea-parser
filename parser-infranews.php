<?php
error_reporting(E_ALL);
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 05.12.12
 * Time: 22:46
 * To change this template use File | Settings | File Templates.
 */

function parse_news_page($url) {
    $baseURL = $url;

    echo "parsing $baseURL\n";

    $document = phpQuery::newDocumentFileHTML($baseURL);
    $articleElement = $document->find('#main-content');

    $title = $articleElement->find('.title h1')->text();
    $time = $articleElement->find('.details')->text();

    $text = $articleElement->find('.single-review-content')->text();

    $result = array(
        'title'	=> trim($title),
        'time'	=> trim($time),
        'text'	=> trim($text)
    );

    return $result;
}

function parse_news_id_list()
{
    $url = 'http://infranews.ru/';
    $document = phpQuery::newDocumentFileHTML($url);
    $postItems = $document->find('.post-item .description .index-title');

    $result = array();
    foreach ($postItems as $postItemLink) {
        $postItemLink = pq($postItemLink);

        $result[] = trim($postItemLink->attr('href'));
    }

    return $result;
}

include('phpQuery.php');

$newsLinks = parse_news_id_list();
echo implode(', ', $newsLinks);
print_r(parse_news_page($newsLinks[0]));