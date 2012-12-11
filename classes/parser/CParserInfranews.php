<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 1:46
 */

class CParserInfranews extends CAParser {

    public function sourceID()
    {
        return 1;
    }

    private function extractTime($str)
    {
        $pattern = '#[0-9:.]+#';
        preg_match_all($pattern, $str, $matches);

        $timeStr = $matches[0][1].' '.$matches[0][0];
        $time = DateTime::createFromFormat('H:i d.m.y', $timeStr);
        if ($time) {
            return $time->getTimestamp();
        }
        return false;
    }

    public function extractDataFromHTML($html)
    {
        $document = phpQuery::newDocumentHTML($html);
        $articleElement = $document->find('#main-content');

        $title = $articleElement->find('.title h1')->text();

        $time = $articleElement->find('.details')->text();
        $time = $this->extractTime(trim($time));

        $text = $articleElement->find('.single-review-content')->text();

        $result = array(
            'title'	    => trim($title),
            'date'	    => $time,
            'content'	=> trim($text)
        );

        return $result;
    }

    public function extractURLListFromHTML($html)
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

    public function webSiteBaseURL()
    {
        return 'http://infranews.ru';
    }

    public function getNewsListPageContents($page)
    {
        $url = $this->webSiteBaseURL().'/page/'.$page.'/';

        return Downloader::defaultDownloaderForURL($url)->download();
    }
}