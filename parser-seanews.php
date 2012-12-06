<?php
error_reporting(E_ALL);
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 05.12.12
 * Time: 22:46
 * To change this template use File | Settings | File Templates.
 */

function parse_news_by_id($id) {
    $baseURL = 'http://www.seanews.ru/news/news.asp?newsID='.$id;

    echo "parsing $baseURL\n";

    $document = phpQuery::newDocumentFileHTML($baseURL);
    $articleElement = $document->find('#forprint .text');

    $title = $articleElement->find('h3')->text();
    $time = $articleElement->find('.timelabel')->text();

    $articleElement->find('h3')->remove();
    $articleElement->find('.timelabel')->remove();

    $text = $articleElement->text();

    $result = array(
        'title'	=> trim($title),
        'time'	=> trim($time),
        'text'	=> trim($text)
    );

    return $result;
}

function parse_news_id_list()
{
    $url = 'http://www.seanews.ru/news/';
    $documentContents = file_get_contents($url);

    $pattern = '#newsID=(\d+)#';
    preg_match_all($pattern, $documentContents, $matches);
    return $matches[1];
}

include('phpQuery.php');

print_r(parse_news_by_id(1016779));