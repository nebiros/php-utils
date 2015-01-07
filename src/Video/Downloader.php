<?php

namespace Nebiros\PhpUtils\Video;

/**
 * Nebiros\PhpUtils\Video\Downloader
 *
 * @author nebiros
 */
class Downloader {
    const TOKEN_YT = "youtube.com";
    const TOKEN_VIMEO = "vimeo.com";

    const SOURCE_YT = 1;
    const SOURCE_VIMEO = 2;
    const SOURCE_OTHER = 3;
    
    const DOWNLOAD_VIMEO_URL = "http://www.vimeo.com/moogaloop/play/clip:%d/%s/%d/?q=sd";

    const DATA_VIDEO_YT_URL = "http://www.youtube.com/get_video_info?video_id=%s";
    const DATA_VIDEO_VIMEO_URL = "http://www.vimeo.com/moogaloop/load/clip:%d";

    const VIMEO_VIDEO_ID_PARAM_POS = 1;

    const YT_VIDEO_PARAM_POS = 1;
    const YT_VIDEO_PARAM_VALUE = "v";
    const YT_VIDEO_PARAM_EMBED = "embed";
    const YT_VIDEO_ID_PARAM_POS = 2;
    
    const YT_QUALITY_5_TOKEN = "youtube.com,5";
    const YT_QUALITY_18_TOKEN = "youtube.com,18";

    /**
     *
     * @var null|array
     */
    protected $_video = null;
    
    /**
     *
     * @var string
     */
    protected static $_tmp = null;

    /**
     *
     * @param array $video 
     */
    public function  __construct(Array $video) {
        $this->_video = $video;
        // we going to download the file to the system temporal folder.
        self::setTmp();
    }

    /**
     *
     * @param string $tmp
     */
    public static function setTmp($tmp = null) {
        if (null === $tmp) {
            $tmp = sys_get_temp_dir();
        }
        
        self::$_tmp = $tmp;
    }

    /**
     *
     * @return string
     */
    public static function getTmp() {
        if (null === self::$_tmp) {
            self::setTmp();
        }

        return self::$_tmp;
    }

    /**
     *
     * @return string|false
     */
    public function download() {
        if (null === $this->_video) {
            throw new \Exception("Video data is empty, please provide some video data to start the downloading process");
        }

        if (strstr($this->_video["url"], self::TOKEN_YT)) {
            $url = $this->_processSource(self::SOURCE_YT);

            if (null === $url) {
                error_log(__METHOD__ . " - The download url for this video '{$this->_video["url"]}' can't be found", 0);
                return false;
            }

            $filename = $this->_request($url, "video_" . $this->_video["id"]);
        } else if (strstr($this->_video["url"], self::TOKEN_VIMEO)) {
            $url = $this->_processSource(self::SOURCE_VIMEO);

            if (null === $url) {
                error_log(__METHOD__ . " - The download url for this video '{$this->_video["url"]}' can't be found", 0);
                return false;
            }

            $filename = $this->_request($url, "video_" . $this->_video["id"]);
        } else {
            $url = $this->_processSource(self::SOURCE_OTHER);

            if (null === $url) {
                error_log(__METHOD__ . " - The download url for this video '{$this->_video["url"]}' can't be found", 0);
                return false;
            }

            $filename = $this->_request($url, "video_" . $this->_video["id"]);
        }

        return realpath(self::getTmp() . "/" . $filename);
    }

    /**
     *
     * @param int $source
     * @return string|null
     */
    protected function _processSource($source = self::SOURCE_OTHER) {
        $url = null;

        try {
            switch ($source) {
                case self::SOURCE_YT:
                    $url = parse_url(trim($this->_video["url"]));
                    $path = explode("/", $url["path"]);
                    $value = $path[self::YT_VIDEO_PARAM_POS];

                    // if the url is youtube.com/v/<ID>.
                    if (self::YT_VIDEO_PARAM_VALUE == $value) {
                        $this->_video["identifier"] = $path[self::YT_VIDEO_ID_PARAM_POS];
                    // url is like youtube.com/embed/<ID>.
                    } else if (self::YT_VIDEO_PARAM_EMBED == $value) {
                        $this->_video["identifier"] = $path[self::YT_VIDEO_ID_PARAM_POS];
                    } else {
                        parse_str($url["query"], $params);
                        $this->_video["identifier"] = $params["v"];
                    }
                    
                    $data = file_get_contents(sprintf(self::DATA_VIDEO_YT_URL, $this->_video["identifier"]));
                    $data = urldecode($data);
                    $data = explode("|", $data);

                    // check youtybe errors.
                    // TODO: dunno if create another constant :\ (nebiros).
                    // TODO: log? mayyyybeeeeeeeeee an email (nebiros).
                    parse_str($data[0], $error);

                    if ("fail" == strtolower($error["status"])) {
                        // log error to the PHP's system logger.
                        error_log(__METHOD__ . " - YouTube error: ({$error["errorcode"]}) {$error["reason"]}", 0);
                        return; // end process.
                    }
                    
                    $url = $this->_ytFinder($data);
                    break;
                case self::SOURCE_VIMEO:
                    try {
                        $path = explode("/", parse_url(trim($this->_video["url"]), PHP_URL_PATH));
                        $this->_video["identifier"] = $path[self::VIMEO_VIDEO_ID_PARAM_POS];
                        $xml = new SimpleXMLElement(file_get_contents(sprintf(self::DATA_VIDEO_VIMEO_URL, $this->_video["identifier"])));
                        $url = sprintf(self::DOWNLOAD_VIMEO_URL, $this->_video["identifier"], $xml->request_signature, $xml->request_signature_expires);
                    } catch (\Exception $e) {
                        error_log(__METHOD__ . " - Vimeo error: ({$e->getMessage()})", 0);
                    }
                    break;                
                case self::SOURCE_OTHER:
                    // TODO: nein! nein! nein! (nebiros).
                    $url = $this->_video["url"];
                    break;
            }
        } catch (\Exception $e) {
            throw $e;
        }
        
        return $url;
    }

    /**
     *
     * @param string $url
     * @param string $filename
     * @return string
     */
    protected function _request($url, $filename = null) {
        if (null === $filename) {
            $filename = uniqid();
        }

        $handle = fopen(self::getTmp() . "/" . $filename, "wb");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FILE, $handle);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE);
        curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE);

        if (false === curl_exec($ch)) {
            throw new \Exception("CURL error! (" . curl_error($ch) . ")" );
        }

        curl_close($ch);
        fclose($handle);

        return $filename;
    }

    /**
     *
     * @param array $data
     * @return string|null
     */
    protected function _ytFinder(Array $data) {
        $url = null; $yt5Key = null; $yt18Key = null;

        foreach ($data as $key => $value) {
            if (strpos($value, self::YT_QUALITY_5_TOKEN)) {
                $yt5Key = $key + 1;
            } else if (strpos($value, self::YT_QUALITY_18_TOKEN)) {
                $yt18Key = $key + 1;
            }

            if (null !== $yt5Key && null !== $yt18Key) {
                break;
            }
        }

        $yt5 = file_get_contents($data[$yt5Key], null, null, 0, 10);

        if (false === strpos("200", $http_response_header[0])) {
            error_log(__METHOD__ . " - error: '{$data[$yt5Key]}' ({$http_response_header[0]})", 0);
        }

        if (true === empty($yt5)) {
            $url = $data[$yt18Key];
        } else {
            $url = $data[$yt5Key];
        }

        return $url;
    }
}
