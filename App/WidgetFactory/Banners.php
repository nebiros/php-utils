<?php

/**
 *
 * @author nebiros
 */
class App_WidgetFactory_Banners extends App_WidgetFactory_WidgetAbstract {
    public function  __construct($options = null) {
        parent::__construct($options);
    }
    
    public function run() {
        $xhtml = "";
        
        if (empty($this->_config["type"])) {
            return $xhtml;
        }
        
        $xhtml = $this->_process();
        return $xhtml;        
    }
    
    protected function _process() {
        switch (strtolower($this->_config["type"])) {
            case "source":
                Yasc_App::view()->src = $this->_config["src"];
                Yasc_App::view()->render("_widget_banners_source");
                break;
            case "image":
                Yasc_App::view()->link = $this->_config["link"];
                Yasc_App::view()->image = $this->_config["image"];
                Yasc_App::view()->render("_widget_banners_image");
                break;
            case "ad_mob":
                Yasc_App::view()->src = $this->_adMob();
                Yasc_App::view()->render("_widget_banners_source");
                break;
        }
        
        return (string) Yasc_App::view();
    }
    
    /**
     *
     * @return string
     */
    protected function _adMob() {
        if (empty( $this->_config["analytics_id"])) {
            return "";
        }
        
        $admobConfig = array(            
            // Required to request ads. To find your Publisher ID, log in to your AdMob account and click on the "Sites & Apps" tab.
            // "PUBLISHER_ID" => "a14d4c4c1e11b78"
            "PUBLISHER_ID" => $this->_config["publisher_id"],
            // Required to collect Analytics data. To find your Analytics ID, log in to your Analytics account and click on the "Edit" link next to the name of your site.
            "ANALYTICS_ID" => $this->_config["analytics_id"],
            // To request an ad, set to TRUE.
            "AD_REQUEST" => (isset($this->_config["ad_request"])) ? $this->_config["ad_request"] : true,
            // To enable the collection of analytics data, set to TRUE.
            "ANALYTICS_REQUEST" => (isset($this->_config["analytics_request"])) ? $this->_config["analytics_request"] : true,
            // While testing, set to TRUE. When you are ready to make live requests, set to FALSE.
            "TEST_MODE" => (isset($this->_config["test_mode"])) ? $this->_config["test_mode"] : false,
            // Additional optional parameters are available at: http://developer.admob.com/wiki/AdCodeDocumentation
            "OPTIONAL" => (isset($this->_config["optional"])) ? ( array ) $this->_config["optional"] : array()
        );

        // Optional parameters for AdMob Analytics (http://analytics.admob.com)
        
        // Analytics allows you to track site usage based on custom page titles. Enter custom title in this parameter.
        if (isset($this->_config["optional"]["title"])) {
            $admobConfig["OPTIONAL"]["title"] = $this->_config["optional"]["title"];
        }
        
        // To learn more about events, log in to your Analytics account and visit this page: http://analytics.admob.com/reports/events/add
        if (isset($this->_config["optional"]["event"])) {
            $admobConfig["OPTIONAL"]["event"] = $this->_config["optional"]["event"];
        }
        
        /**
         * This code supports the ability for your website to set a cookie on behalf of AdMob
         * To set an AdMob cookie, simply call admob_setcookie() on any page that you call admob_request()
         * The call to admob_setcookie() must occur before any output has been written to the page (http://www.php.net/setcookie)
         * If your mobile site uses multiple subdomains (e.g. "a.example.com" and "b.example.com"), then pass the root domain of your mobile site (e.g. "example.com") as a parameter to admob_setcookie().
         * This will allow the AdMob cookie to be visible across subdomains
         */
        if (isset($this->_config["set_cookie"]) &&  $this->_config["set_cookie"]) {
            $this->_admobSetCookie();
        }

        /**
         * AdMob strongly recommends using cookies as it allows us to better uniquely identify users on your website.
         * This benefits your mobile site by providing:
         * - Improved ad targeting = higher click through rates = more revenue!
         * - More accurate analytics data (http://analytics.admob.com)
         */

        // Send request to AdMob. To make additional ad requests per page, copy and paste this function call elsewhere on your page.
        return $this->_admobRequest($admobConfig);
    }
    
    /**
     *
     * @param string $domain
     * @param string $path
     * @return void 
     */
    protected function _admobSetCookie($domain = null, $path = "/") {
        if (!empty( $_COOKIE["admobuu"] ) ) {
            return;
        }
        
        $value = md5(uniqid(rand(), true)) ;

        if (!empty($domain) && $domain[0] != "." ) {
            $domain = ".{$domain}";
        }
        
        if (setcookie("admobuu", $value, mktime(0, 0, 0, 1, 1, 2038), $path, $domain)) {
            $_COOKIE["admobuu"] = $value; // make it visible to App_WidgetFactory_Banners#_admobSetCookie
        }
    }
    
    /**
     *
     * @staticvar boolean $pixelSent
     * @param array $config
     * @return string 
     */
    protected function _admobRequest(Array $config) {
        static $pixelSent = false;

        $adMode = false;
        if (!empty($config["AD_REQUEST"]) && !empty($config["PUBLISHER_ID"])) {
            $adMode = true;
        }

        $analyticsMode = false;
        if (!empty($config["ANALYTICS_REQUEST"]) && !empty($config["ANALYTICS_ID"]) && !$pixelSent) {
            $analyticsMode = true;
        }

        $protocol = "http";
        if (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) != "off") {
            $protocol = "https";
        }

        $rt = $adMode ? ($analyticsMode ? 2 : 0) : ($analyticsMode ? 1 : -1);
        if ($rt == -1) {
            return "";
        }

        list($usec, $sec) = explode(" ", microtime());
        $params = array("rt=" . $rt,
            "z=" . ($sec + $usec),
            "u=" . urlencode($_SERVER["HTTP_USER_AGENT"]),
            "i=" . urlencode($_SERVER["REMOTE_ADDR"]),
            "p=" . urlencode("{$protocol}://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]),
            "v=" . urlencode("20081105-PHPCURL-acda0040bcdea222"));

        $sid = ((empty($config["SID"])) ? session_id() : $config["SID"]);
        if (!empty($sid)) {
            $params[] = "t=" . md5($sid);
        }
        
        if ($adMode) {
            $params[] = "s=" . $config["PUBLISHER_ID"];
        }
        
        if ($analyticsMode) {
            $params[] = "a=" . $config["ANALYTICS_ID"];
        }
        
        if (!empty($_COOKIE["admobuu"])) {
            $params[] = "o=" . $_COOKIE["admobuu"];
        }
        
        if (!empty($config["TEST_MODE"])) {
            $params[] = "m=test";
        }

        if (!empty($config["OPTIONAL"])) {
            foreach ($config["OPTIONAL"] as $k => $v) {
                $params[] = urlencode($k) . "=" . urlencode($v);
            }
        }

        $ignore = array(
            "HTTP_PRAGMA" => true, 
            "HTTP_CACHE_CONTROL" => true, 
            "HTTP_CONNECTION" => true, 
            "HTTP_USER_AGENT" => true, 
            "HTTP_COOKIE" => true
        );
        
        foreach ($_SERVER as $k => $v) {
            if (substr($k, 0, 4) == "HTTP" && empty($ignore[$k]) && isset($v)) {
                $params[] = urlencode("h[" . $k . "]") . "=" . urlencode($v);
            }
        }

        $post = implode("&", $params);
        $request = curl_init();
        $requestTimeout = 1; // 1 second timeout
        curl_setopt($request, CURLOPT_URL, "http://r.admob.com/ad_source.php");
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_TIMEOUT, $requestTimeout);
        curl_setopt($request, CURLOPT_CONNECTTIMEOUT, $requestTimeout);
        curl_setopt($request, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Connection: Close"));
        curl_setopt($request, CURLOPT_POSTFIELDS, $post);
        list($usecStart, $secStart) = explode(" ", microtime());
        $contents = curl_exec( $request );
        list($usecEnd, $secEnd) = explode(" ", microtime());
        curl_close($request);

        if ($contents === true || $contents === false) {
            $contents = "";
        }

        if (!$pixelSent) {
            $pixelSent = true;
            $contents .= "<div class=\"sections\" style=\"text-align: center;\">"
                . "<img src=\"{$protocol}://p.admob.com/e0?"
                . "rt=" . $rt
                . "&amp;z=" . ($sec + $usec)
                . "&amp;a=" . ($analyticsMode ? $config["ANALYTICS_ID"] : "")
                . "&amp;s=" . ($adMode ? $config["PUBLISHER_ID"] : "")
                . "&amp;o=" . (empty( $_COOKIE["admobuu"] ) ? "" : $_COOKIE["admobuu"])
                . "&amp;lt=" . ($secEnd + $usecEnd - $secStart - $usecStart)
                . "&amp;to=" . $requestTimeout
                . " alt=\"\" width=\"1\" height=\"1\" /></div>";
        }

        return $contents;
    }
}