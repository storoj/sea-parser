<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 2:23
 */

class Downloader {
    private $url = '';
    private $content = null;
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

        $host = parse_url($url, PHP_URL_HOST);
        $fileName = md5($url);
        $fileName = substr($fileName, 0, 4).'/'
            .substr($fileName, 4, 4).'/'
            .substr($fileName, 8, 4).'/'
            .$fileName.'.html';


        $cacheFileName = $cacheDir.$host.'/'.$fileName;

        return $cacheFileName;
    }

    public function removeCacheFile()
    {
        $cacheFileName = $this->getCacheFileNameForURL($this->url);
        if (file_exists($cacheFileName)) {
            unlink($cacheFileName);
        }
    }

    public function download()
    {
        echo "downloading...";
        $returnTransfer = isset($this->options[CURLOPT_RETURNTRANSFER]) && $this->options[CURLOPT_RETURNTRANSFER];
        if ($returnTransfer && $this->useCache) {
            $cacheFileName = $this->getCacheFileNameForURL($this->url);
            if (file_exists($cacheFileName)) {
                echo " found in cache\n";
                return file_get_contents($cacheFileName);
            }
        }

        $options = array_merge_numeric($this->options, array(
            CURLOPT_URL     => $this->url
        ));

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);

        echo " done\n";

        if ($returnTransfer && $this->useCache) {
            @mkdir(dirname($cacheFileName), 0777, true);
            file_put_contents($cacheFileName, $result);
        }

        $this->content = $result;
        return $result;
    }

}
