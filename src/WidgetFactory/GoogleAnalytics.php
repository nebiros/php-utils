<?php

namespace Nebiros\PhpUtils\WidgetFactory;

/**
 *
 * @author nebiros
 */
class GoogleAnalytics extends Nebiros\PhpUtils\WidgetFactory\WidgetAbstract {
    const GA_PIXEL = "/ga.php";
    
    public function  __construct(Array $options) {
        parent::__construct($options);
    }
    
    public function run() {
        $xhtml = "";
        
        if (empty($this->_config["account"])) {
            return $xhtml;
        }
        
        $xhtml = $this->_process();
        return $xhtml;        
    }
    
    protected function _process() {
        $url = "";
        $url .= self::GA_PIXEL . "?";
        $url .= "utmac={$this->_config["account"]}";
        $url .= "&utmn=" . rand(0, 0x7fffffff);

        $referer = $_SERVER["HTTP_REFERER"];
        $query = $_SERVER["QUERY_STRING"];
        $path = $_SERVER["REQUEST_URI"];

        if (empty($referer)) {
            $referer = "-";
        }
        
        $url .= "&utmr=" . urlencode($referer);

        if (!empty($path)) {
          $url .= "&utmp=" . urlencode($path);
        }

        $url .= "&guid=ON";
        $url = str_replace("&", "&amp;", $url);
        return "<img src=\"" . Yasc_App::viewHelper("url")->url(array("uri" => $url)) . "\" />";
    }
}