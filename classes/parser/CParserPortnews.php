<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 20.01.13
 * Time: 3:13
 */

class CParserPortnews extends CParser {

    public function sourceID()
    {
        return 4;
    }

    private function extractTime($str)
    {
        $months = array(
            'января', 'февраля', 'марта',
            'апреля', 'мая', 'июня', 'июля',
            'августа', 'сентября', 'октября',
            'ноября', 'декабря'
        );
        $pattern = '#\d+ ([^\d]+) \d+ [0-9:]+#';
        preg_match($pattern, $str, $match);

        $monthName = $match[1];
        $monthIndex = array_search($monthName, $months) + 1;

        $dateStr = str_replace(' '.$monthName.' ', '.'.$monthIndex.'.', $match[0]);

        $time = DateTime::createFromFormat('d.m.Y H:i', $dateStr);

        if ($time) {
            return $time->getTimestamp();
        }
        return false;
    }

    public function extractDataFromURL($url)
    {
        $result = parent::extractDataFromURL($url);

        preg_match('#(\d+)/$#', $url, $match);
        $result['internal_id'] = $match[1];

        return $result;
    }

    public function extractDataFromHTML($html)
    {
        $document = phpQuery::newDocumentHTML($html);
        $articleElement = $document->find('.text-container');

        $titleElement = $articleElement->find('h1');
        $title = $titleElement->text();

        $time = $document->find('.pageWrapper>h1')->next()->text();
        $time = $this->extractTime(trim($time));

        $text = $articleElement->find('p')->text();

        $result = array(
            'title'     => trim($title),
            'date'      => trim($time),
            'content'	=> trim($text)
        );

        return $result;
    }

    public function extractURLListFromHTML($html)
    {
        $pattern = '#/news/(\d+)/#';
        preg_match_all($pattern, $html, $matches);

        $result = array();
        foreach ($matches[1] as $articleID) {
            // will use print version to decrease downloads
            $result[] = $this->webSiteBaseURL().'/news/print/'.$articleID.'/';
        }

        return $result;
    }

    public function webSiteBaseURL()
    {
        return 'http://portnews.ru';
    }

    public function getNewsListPageContents($page)
    {
        $dateOffset = ($page == 1) ? 'now' : (1-$page).' day';
        $date = new DateTime($dateOffset);
        $dateFormat = $date->format('Y-m-d');

        $url = $this->webSiteBaseURL().'/news/'.$dateFormat.'/s1/';

        $downloader = Downloader::defaultDownloaderForURL($url);
        $downloader->useCache = false;
        return $downloader->download();
    }
}