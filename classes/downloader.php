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
    public $useCache = true;

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

    public static function defaultDownloaderForURL($url)
    {
        return new Downloader($url);
    }

    public function getCacheFileNameForURL($url)
    {
        $cacheDir = PATH_ROOT.'cache/';

        $urlParts = parse_url($url);
        $host = $urlParts['host'];
        $path = preg_replace('#/$#', '', $urlParts['path']);

        return $cacheDir.$host.$path.'.html';
    }

    public function download()
    {
        $returnTransfer = isset($this->options[CURLOPT_RETURNTRANSFER]) && $this->options[CURLOPT_RETURNTRANSFER];
        if ($returnTransfer) {
            $cacheFileName = $this->getCacheFileNameForURL($this->url);
            if (file_exists($cacheFileName)) {
                return file_get_contents($cacheFileName);
            }
        }

        $options = array_merge_numeric($this->options, array(
            CURLOPT_URL     => $this->url
        ));

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);

        if ($returnTransfer) {
            @mkdir(dirname($cacheFileName), 0777, true);
            file_put_contents($cacheFileName, $result);
        }

        return $result;
    }

}