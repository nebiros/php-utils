<?php

/**
 *
 * @author nebiros
 */
class App_WidgetFactory_RemoteUrl extends App_WidgetFactory_WidgetAbstract {
    /**
     *
     * @param array $options 
     */
    public function  __construct(Array $options = array()) {
        parent::__construct($options);
    }
    
    /**
     *
     * @return string
     */
    public function run() {
        if (empty($this->_options["link"])) {
            return "";
        }
        
        return $this->_build();
    }
    
    /**
     *
     * @return string 
     */
    protected function _build() {        
        if (true === (bool) $this->_config["frame"]) {
            $xhtml = "<div class='remote_url_frame'>
                <iframe src='" . urldecode($this->_options["link"]) . "' 
                    scrolling='auto' 
                    frameborder='0'
                    border='0'
                    style='border: none; 
                        overflow: hidden; 
                        display: block;
                        width: " .((!strpos($this->_config["width"], "%")) ? "{$this->_config["width"]}px" : $this->_config["width"]) . "; 
                        height: " .((!strpos($this->_config["height"], "%")) ? "{$this->_config["height"]}px" : $this->_config["height"]) . ";'
                    allowTransparency='true'></iframe></div>";
            return $xhtml;
        }
        
        if (!($xhtml = $this->_cache->load("remote_url_" . $this->_options["id"]))) {
            if(!($xhtml = file_get_contents(urldecode($this->_options["link"])))) {
                return "";
            }
            
            $xhtml = base64_encode($xhtml);
            $this->_cache->save("remote_url_" . $this->_options["id"], $xhtml, $this->_config["refresh_frequency"] * 60);
        }
        
        $xhtml = "<div class='remote_url_cache'>
            " . base64_decode($xhtml) . "
            </div>";
        
        return $xhtml;
    }
}