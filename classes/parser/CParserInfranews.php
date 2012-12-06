<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 1:46
 */

class CParserInfranews extends CAParser {

    function sourceID()
    {
        return 1;
    }

    function extractDataFromHTML($html)
    {
        $document = phpQuery::newDocumentHTML($html);
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

    function extractURLListFromHTML($html)
    {
        $document = phpQuery::newDocumentHTML($html);
        $postItems = $document->find('.post-item .description .index-title');

        $result = array();
        foreach ($postItems as $postItemLink) {
            $postItemLink = pq($postItemLink);

            $result[] = trim($postItemLink->attr('href'));
        }

        return $result;
    }
}