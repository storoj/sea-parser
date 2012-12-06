<?php

interface IParser {
	function sourceID();

    function extractDataFromContent($content);

    function extractURLListFromContent($content);
}

abstract class CAParser implements IParser {
    function latestDateFromDB() {
        $sourceID = $this->sourceID();


    }
}