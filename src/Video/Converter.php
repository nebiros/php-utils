<?php

namespace Nebiros\PhpUtils\Video;

use Nebiros\PhpUtils\Db\Mysqli;
use Nebiros\PhpUtils\Video\Downloader;

/**
 * Nebiros\PhpUtils\Video\Converter
 *
 * @author nebiros
 */
class Converter {
    const STATUS_NEW = 1;
    const STATUS_ON_PROCESS = 2;
    const STATUS_DONE = 3;
    const STATUS_ERROR = 4;

    /**
     *
     * @var array
     */
    protected $_defaultOptions = array(
        "dir" => "./videos",
        "host" => "localhost",
        "username" => "root",
        "password" => "somePassword",
        "dbname" => "someDb",
        // ffmpeg commands: http://www.ffmpeg.org/ffmpeg-doc.html
        "ffmpeg" => array(// -b 300k -s 176x144 -vcodec h263 -ac 2 -ab 128k -acodec libfaac
            "b" => "300k",
            "s" => "176x144",
            "vcodec" => "h263",
            "ac" => 2,
            "ab" => "128k",
            "acodec" => "libfaac",
        ),
        "output" => "3gp",
        "videos_table" => "videos"
    );

    /**
     *
     * @var array
     */
    protected $_options = array();
    
    /**
     *
     * @var Nebiros\PhpUtils\Db\Mysqli
     */
    protected $_db = null;

    /**
     *
     * @var string
     * @example ffmpeg -i <INPUT_FILE> <COMMANDS> <OUTPUT_FILE>
     */
    protected $_ffmpegCommand = "ffmpeg -y -i %s %s %s";

    /**
     *
     * @var array
     */
    protected $_allowedFormats = array(
        "video/x-flv",
        "video/mp4"
    );

    /**
     *
     * @param array $options
     */
    public function  __construct(Array $options = null) {
        $this->setDefaultOptions();
        
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Set option.
     *
     * @param mixed $key
     * @param mixed $value
     * @return Nebiros\PhpUtils\Video\Converter
     */
    public function setOption($key, $value = null) {
        $this->_options[$key] = $value;
        return $this;
    }

    /**
     * Get option.
     *
     * @param mixed $key
     * @param null|mixed $default
     * @return mixed
     */
    public function getOption($key, $default = null) {
        if (true === isset($this->_options[$key])) {
            return $this->_options[$key];
        }

        return $default;
    }

    /**
     * Default options.
     *
     * @return Nebiros\PhpUtils\Video\Converter
     */
    public function setDefaultOptions() {
        $this->_options = $this->_defaultOptions;
        return $this;
    }

    /**
     * Reset to default options.
     *
     * @return Nebiros\PhpUtils\Video\Converter
     */
    public function clearOptions() {
        $this->_options = $this->_defaultOptions;
        return $this;
    }

    /**
     * Set options.
     *
     * @param array $options
     * @return Nebiros\PhpUtils\Video\Converter
     */
    public function setOptions(Array $options) {
        if (true === isset($options["ffmpeg"])) {
            $this->_options["ffmpeg"] = $options["ffmpeg"];
        }

        unset($options["ffmpeg"]);
        $this->_options = array_merge($this->_options, $options);
        
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getOptions() {
        return $this->_options;
    }

    /**
     * Inicia el proceso de convertir videos de la cola de videos.
     *
     * @return void
     */
    public function run() {
        try {
            $this->_initDb();
            $this->_process();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return void
     */
    protected function _initDb() {
        try {
            $this->_db = new Mysqli(array(
                "host" => $this->getOption("host"),
                "username" => $this->getOption("username"),
                "password" => $this->getOption("password"),
                "dbname" => $this->getOption("dbname")
            ));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return void
     */
    protected function _process() {
        $result = $this->_db->connect()->query("SELECT " . $this->getOption("videos_table") . ".*
            FROM " . $this->getOption("videos_table") . "
            WHERE " . $this->getOption("videos_table") . ".status = " . self::STATUS_NEW . "
            OR " . $this->getOption("videos_table") . ".status = " . self::STATUS_ERROR);
        $videos = $this->_db->fetchAssoc($result);

        foreach ($videos as $id => $video) {
            $this->_db->update($this->getOption("videos_table"), array("status" => self::STATUS_ON_PROCESS), "id = '{$id}'");

            // TODO: create a prefix? (nebiros)
            $filename = Downloader::getTmp() . "/video_{$id}";

            if (false === is_file($filename)) {
                $downloader = new Downloader($video);
                $filename = $downloader->download();
                
                if (false === $filename) {
                    $this->_db->update($this->getOption("videos_table"), array("status" => self::STATUS_ERROR), "id = '{$id}'");
                    @unlink($filename);
                    continue;
                }
            }

            $mime = mime_content_type($filename);

            if (false === in_array($mime, $this->_allowedFormats)) {
                $this->_db->update($this->getOption("videos_table"), array("status" => self::STATUS_ERROR), "id = '{$id}'");
                @unlink($filename);
                continue;
            }

            if (false === ($out = $this->convert($filename))) {
                $this->_db->update($this->getOption("videos_table"), array("status" => self::STATUS_ERROR), "id = '{$id}'");
                continue;
            }

            $this->_db->update($this->getOption("videos_table"), array("status" => self::STATUS_DONE), "id = '{$id}'");
            $this->_db->update($this->getOption("videos_table"),
                array("video" => $out),
                "id = '{$id}'"
           );
        }
    }

    /**
     *
     * @param string $filename
     * @return string|false
     */
    public function convert($filename) {
        if (false === is_dir($this->getOption("dir"))) {
            mkdir($this->getOption("dir"));
        }

        $out = $this->getOption("dir") . "/" . basename($filename) . "." . $this->getOption("output");

        if (true === is_file($out)) {
            @unlink($out);
        }

        $ffmpegOptions = array();

        foreach ($this->getOption("ffmpeg") as $key => $value) {
            $ffmpegOptions[] = "-" . $key . " " . $value;
        }

        $command = sprintf(
            $this->_ffmpegCommand,
            $filename,
            implode(" ", $ffmpegOptions),
            $out
       );

        $lastLine = system($command, $output);

        if (0 != $output) {
            $this->_db->update($this->getOption("videos_table"), array("status" => self::STATUS_ERROR), "id = '{$id}'");
            error_log(__METHOD__ . " - ffmpeg error: ({$lastLine}), command: ({$command})", 0);
            return false;
        }

        return $out;
    }
}
