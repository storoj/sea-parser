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
    $baseURL = 'http://portnews.ru/news/'.$id.'/';

    echo "parsing $baseURL\n";

    $document = phpQuery::newDocumentFileHTML($baseURL);
    $articleElement = $document->find('#news>ul>li');

    $titleElement = $articleElement->find('h1');
    $title = $titleElement->text();
    $titleElement->remove();

    $time = $articleElement->find('>p')->text();

    $text = $articleElement->find('.text-container')->text();

    $result = array(
        'title'	=> trim($title),
        'time'	=> trim($time),
        'text'	=> trim($text)
    );

    return $result;
}

include('phpQuery.php');

print_r(parse_news_by_id(150659));