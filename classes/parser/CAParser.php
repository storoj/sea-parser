<?php

interface IParser {
	function sourceID();

    function extractDataFromHTML($html);

    function extractURLListFromHTML($html);
}

abstract class CAParser implements IParser {

    function latestDateFromDB() {
        $sourceID = $this->sourceID();

        $maxDate = DBQuery::withTable('news')
            ->getFields('MAX(date)', true)
            ->where(array('source_id' => $sourceID))
            ->fetch();

        return $maxDate;
    }

}