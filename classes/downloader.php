<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 2:23
 */

class Downloader {
    private $url = '';
    private $options = array(
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_FOLLOWLOCATION  => true,
        CURLOPT_CONNECTTIMEOUT  => 10
    );

    public function __construct($url = false, $options = array())
    {
        if (false !== $url) {
            $this->url = $url;
        }

        $defaultOptions = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_CONNECTTIMEOUT  => 10
        );
        $this->options = array_merge_numeric($defaultOptions, $options);
    }

    public function download()
    {
        $options = array_merge_numeric($this->options, array(
            CURLOPT_URL     => $this->url
        ));

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        return curl_exec($ch);
    }

}