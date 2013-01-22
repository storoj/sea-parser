<?php

interface IParser
{
	function sourceID();

    function webSiteBaseURL();

    function extractDataFromHTML($html);

    function extractURLListFromHTML($html);

    function getNewsListPageContents($page);
}

abstract class CParser implements IParser
{
    function extractDataFromURL($url)
    {
        $downloader = Downloader::defaultDownloaderForURL($url);
        $html = $downloader->download();
        $data = $this->extractDataFromHTML($html);
        if (!isset($data['content']) || empty($data['content'])) {
            $downloader->removeCacheFile();
            return false;
        }
        return $data;
    }

    /**
     * @param $page - number of page to parse
     * @return bool - false if found data already stored in DB, otherwise true
     */
    function processPage($page)
    {
        $rowCount = DBQuery::withTable('news')
            ->where(array('source_id' => $this->sourceID()))
            ->count();

        $content = $this->getNewsListPageContents($page);
        $urlList = $this->extractURLListFromHTML($content);

        if (!$urlList) {
            return false;
        }

        $urlCount = count($urlList);
        foreach ($urlList as $index => $articleURL) {
            echo "[".$index."/".$urlCount."] ".$articleURL."\n";
            $data = $this->extractDataFromURL($articleURL);
            if (!$data) {
                echo "error\n";
                return false;
            }

            $data['source_id'] = $this->sourceID();
            $data['source_url'] = $articleURL;

            $existance = DBQuery::withTable('news')
                ->getFields(array('id', 'active'))
                ->where(array(
                    'source_id'     => $data['source_id'],
                    'internal_id'   => $data['internal_id']
                ))
                ->fetch();
            if (!!$existance) {
                // found actual info, have to stop parsing
                // mark all non-active rows as active and exit
                if ($existance['active'] == 1) {

                    DBQuery::withTable('news')
                        ->setFields(array(
                            'active' => 1
                        ))
                        ->where(array(
                            'active'    => 0,
                            'source_id' => $this->sourceID()
                        ))
                        ->Update();

                    return false;
                }
                continue;
            }

            if ($rowCount == 0) {
                $data['active'] = 1;
            }
            DBQuery::withTable('news')
                ->setFields($data)
                ->Insert();
        }

        return true;
    }
}
