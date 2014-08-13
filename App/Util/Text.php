<?php

/**
 *
 * @author nebiros
 */
class App_Util_Text {    
    /**
     *
     * @param string $text
     * @param int $length
     * @param string $tail
     * @return string
     */
    public static function cut($text, $length = 100, $tail = "...") {
        $text = preg_replace("/(\s+){1,}/", " ", trim($text));

        if (mb_strlen($text) > $length) {
            $text = preg_replace("/(\s+){1,}/", " ", trim($text));
            $cutPos = $length - mb_strlen($tail);
            $boundaryPos = mb_strrpos(mb_substr($text, 0, mb_strpos($text, " ", $cutPos)), " ");
            return mb_substr($text, 0, $boundaryPos === false ? $cutPos : $boundaryPos) . $tail;
        }

        return $text;
    }

    /**
     *
     * @param string $string
     * @param bool $lowercase
     * @param string $glue
     * @param bool $removeGlue
     * @return string
     */
    public static function cleanUp($string, $lowercase = true, $glue = "-", $removeGlue = true) {
        if (true === empty($string)) {
            return null;
        }

        $string = trim($string);

        if (true === $lowercase) {
            $string = strtolower($string);
        }

        if (true === $removeGlue) {
            $string = str_replace($glue, " ", $string);
        }

        $string = preg_replace("/[^a-zA-Z0-9_\s-]/", "", $string);
        $string = preg_replace("/[\s]+/", " ", $string);
        $string = preg_replace("/[\s]/", $glue, $string);
        $string = rtrim($string, "-");
        return $string;
    }

    /**
     *
     * @param string $xhtml
     * @return null|string
     */
    public static function minimizeHtml($xhtml) {
        if (false === is_string($xhtml)) {
            return null;
        }

        return preg_replace(array("#[\t\n]#", "#(\s+){1,}#"), array("", " "), $xhtml);
    }

    /**
     *
     * @param string $text
     * @param array $tags
     * @param bolean $invert
     * @return string 
     * @see https://gist.github.com/marcanuy/7651298
     */
    public static function stripTagsContent($text, $tags = "", $invert = false) {
        preg_match_all("/<(.+?)[\s]*\/?[\s]*>/si", trim($tags), $tags);
        $tags = array_unique($tags[1]);

        if (is_array($tags) and count($tags) > 0) {
            if ($invert == false) {
                return preg_replace("@<(?!(?:". implode("|", $tags) .")\b)(\w+)\b.*?>.*?</\1>@si", "", $text);
            } else {
                return preg_replace("@<(". implode("|", $tags) .")\b.*?>.*?</\1>@si", "", $text);
            }
        } else if ($invert == false) {
            return preg_replace("@<(\w+)\b.*?>.*?</\1>@si", "", $text);
        }
        
        return $text;
    }

    /**
     * Extremely simple function to get human filesize.
     * @param int $bytes
     * @param integer $decimals
     * @return string
     * @see http://php.net/manual/en/function.filesize.php#106569
     */
    public static function humanFilesize($bytes, $decimals = 2) {
        $sz = "BKMGTP";
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
}

