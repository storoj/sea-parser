<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 1:46
 */

class CParserMorvesti extends CParser {

    public function sourceID()
    {
        return 3;
    }

    private function extractTime($str)
    {
        $time = DateTime::createFromFormat('d.m.Y H:i', $str);

        if ($time) {
            return $time->getTimestamp();
        }
        return false;
    }

    public function extractDataFromHTML($html)
    {
        $document = phpQuery::newDocumentHTML($html);

        $articleElement = $document->find('.textContainer');

        $title = $articleElement->find('h1')->text();

        $timeElement = $articleElement->find('.news-date-time');
        $time = $timeElement->text();

        $newsContainer = $timeElement->parent();
        $timeElement->remove();

        $text = $newsContainer->text();

        $time = $this->extractTime(trim($time));

        $result = array(
            'title'	    => trim($title),
            'date'	    => $time,
            'content'	=> trim($text)
        );

        return $result;
    }

    public function extractDataFromURL($url)
    {
        $result = parent::extractDataFromURL($url);
        if (!$result) {
            return false;
        }

        preg_match('#\d+$#', $url, $match);
        $result['internal_id'] = $match[0];

        return $result;
    }

    public function extractURLListFromHTML($html)
    {
        $document = phpQuery::newDocumentHTML($html);
        $postItems = $document->find('.newsContainer h4 a');

        if ($postItems->length == 0) {
            return false;
        }
        $result = array();
        foreach ($postItems as $postItemLink) {
            $postItemLink = pq($postItemLink);

            $result[] = $this->webSiteBaseURL().$postItemLink->attr('href');
        }

        return $result;
    }

    public function webSiteBaseURL()
    {
        return 'http://morvesti.ru';
    }

    public function getNewsListPageContents($page)
    {
        $url = $this->webSiteBaseURL().'/news/index.php?PAGEN_1='.$page;

        $downloader = Downloader::defaultDownloaderForURL($url);
        $downloader->useCache = false;
        return $downloader->download();

    }
}