<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 1:46
 */

class CParserMorvesti extends CAParser {

    public function sourceID()
    {
        return 3;
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

        $pathComponents = explode('/', parse_url($url, PHP_URL_PATH));
        $lastComponent = end($pathComponents);
        if (empty($lastComponent)) {
            array_pop($pathComponents);
            $lastComponent = end($pathComponents);
        }
        preg_match('#^\d+#', $lastComponent, $match);
        $result['internal_id'] = $match[0];

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
        return 'http://morvesti.ru';
    }

    public function getNewsListPageContents($page)
    {
        $url = $this->webSiteBaseURL().'/page/'.$page.'/';

        return Downloader::defaultDownloaderForURL($url)->download();
    }
}