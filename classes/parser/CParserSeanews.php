<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 3:31
 */
// TODO internal id parsing

class CParserSeanews extends CAParser {

    public function sourceID()
    {
        return 2;
    }

    private function extractTime($str)
    {
        $months = array(
            'января', 'февраля', 'марта',
            'апреля', 'мая', 'июня', 'июля',
            'августа', 'сентября', 'октября',
            'ноября', 'декабря'
        );
        $pattern = '#\d+ ([^\d]+) \d+, [0-9:]+#';
        preg_match($pattern, $str, $match);

        $monthName = $match[1];
        $monthIndex = array_search($monthName, $months) + 1;

        $dateStr = str_replace(' '.$monthName.' ', '.'.$monthIndex.'.', $match[0]);

        $time = DateTime::createFromFormat('d.m.Y, H:i', $dateStr);

        if ($time) {
            return $time->getTimestamp();
        }
        return false;
    }

    public function extractDataFromURL($url)
    {
        $result = parent::extractDataFromURL($url);

        preg_match('#\d+$#', $url, $match);
        $result['internal_id'] = $match[0];

        return $result;
    }

    public function extractDataFromHTML($html)
    {
        $document = phpQuery::newDocumentHTML($html);
        $articleElement = $document->find('#forprint .text');

        $title = $articleElement->find('h3')->text();
        $time = $articleElement->find('.timelabel')->text();
        $time = $this->extractTime($time);

        $articleElement->find('h3')->remove();
        $articleElement->find('.timelabel')->remove();

        $text = $articleElement->text();

        $result = array(
            'title'	    => trim($title),
            'date'	    => trim($time),
            'content'	=> trim($text)
        );

        return $result;
    }

    public function extractURLListFromHTML($html)
    {
        $pattern = '#newsID=(\d+)#';
        preg_match_all($pattern, $html, $matches);

        $result = array();
        foreach ($matches[1] as $articleID) {
            $result[] = $this->webSiteBaseURL().'/news/news.asp?newsID='.$articleID;
        }

        return $result;
    }

    public function webSiteBaseURL()
    {
        return 'http://www.seanews.ru';
    }

    public function getNewsListPageContents($page)
    {
        $url = $this->webSiteBaseURL().'/news/Default.asp?kw=&geo=&pg='.$page;

        return Downloader::defaultDownloaderForURL($url)->download();
    }
}