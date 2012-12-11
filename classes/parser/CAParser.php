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

        return $maxDate;
    }

    function extractDataFromURL($url)
    {
        $html = Downloader::defaultDownloaderForURL($url)->download();
        return $this->extractDataFromHTML($html);
    }

    function processPage($page)
    {
        $content = $this->getNewsListPageContents($page);
        $urlList = $this->extractURLListFromHTML($content);
        foreach ($urlList as $articleURL) {
            echo $articleURL."\n";
            $data = $this->extractDataFromURL($articleURL);

            $data['source_id'] = $this->sourceID();
            $data['source_url'] = $articleURL;

            DBQuery::withTable('parser')
                ->setFields($data)
                ->Insert();

//            print_r($data);
        }
    }
}