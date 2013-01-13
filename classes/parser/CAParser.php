<?php

interface IParser
{
	function sourceID();

    function webSiteBaseURL();

    function extractDataFromHTML($html);

    function extractURLListFromHTML($html);

    function getNewsListPageContents($page);
}

abstract class CAParser implements IParser
{

    function latestDateFromDB()
    {
        $sourceID = $this->sourceID();

        $maxDate = DBQuery::withTable('news')
            ->getFields('MAX(date)', true)
            ->where(array('source_id' => $sourceID))
            ->fetch();

        return $maxDate['MAX(date)'];
    }

    function earliestDateFromDB()
    {
        $sourceID = $this->sourceID();

        $minDate = DBQuery::withTable('news')
            ->getFields('MIN(date)', true)
            ->where(array('source_id' => $sourceID))
            ->fetch();

        return $minDate['MIN(date)'];
    }

    function extractDataFromURL($url)
    {
        $html = Downloader::defaultDownloaderForURL($url)->download();
        $data = $this->extractDataFromHTML($html);
        if (!isset($data['content']) || empty($data['content'])) {
            return false;
        }
        return $data;
    }

    function processPage($page)
    {
        $latestDate = $this->latestDateFromDB();
        $earliestDate = $this->earliestDateFromDB();

        $content = $this->getNewsListPageContents($page);
        $urlList = $this->extractURLListFromHTML($content);
        $urlCount = count($urlList);
        foreach ($urlList as $index => $articleURL) {
//            echo $articleURL."\n";
            echo "[".$index."/".$urlCount."] ".$articleURL."\n";
            $data = $this->extractDataFromURL($articleURL);
            if (!$data) {
                echo "error\n";
                continue;
            }

            $data['source_id'] = $this->sourceID();
            $data['source_url'] = $articleURL;

            $existance = DBQuery::withTable('news')
                ->getFields(array('id'))
                ->where(array(
                    'source_id'     => $data['source_id'],
                    'internal_id'   => $data['internal_id']
                ))
                ->fetch();
            if (!!$existance) {
                echo " exists\n";
                continue;
            }
//            if ($data['date'] >= $earliestDate && $data['date'] <= $latestDate) {
//                echo "found actual info\n";
//                break;
//            }

            DBQuery::withTable('news')
                ->setFields($data)
                ->Insert();
        }
    }
}