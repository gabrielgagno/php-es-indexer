<?php
/**
 * @MetaParser.php
 * @author Gabriel John P. Gagno
 * @company Stratpoint Technoologies, Inc.
 * Date: 11/27/15
 * Time: 1:45 PM
 */

namespace Parser;

use DOMDocument;
use DOMXPath;

/**
 * Class MetaParser
 * Class used to parse meta tags from html strings or files
 * @package Parser
 */
class MetaParser
{
    private static $tagName = 'meta';

    /**
     * wrapper for parsing using html-formatted strings
     * @param $string
     * @param null $names
     * @return array
     */
    public static function parseMetaTagsFromHtmlString($string, $names = NULL)
    {
        return MetaParser::parse($string, $names);
    }

    /**
     * private function that generalizes parsing
     * @param $string
     * @param null $names
     * @return array
     */
    private static function parse($string, $names = NULL)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($string);
        $metas = $dom->getElementsByTagName('meta');
        $metaArray = array();
        if($names==NULL) {
            foreach($metas as $meta) {
                $nameKey = $meta->getAttribute('name');
                $contentValue = $meta->getAttribute('content');
                $metaArray["$nameKey"] = $contentValue;
            }
        }
        else {
            foreach($names as $name) {
                foreach($metas as $meta) {
                    if($name===$meta->getAttribute('name')){
                        $nameKey = $meta->getAttribute('name');
                        $contentValue = $meta->getAttribute('content');
                        $metaArray["$nameKey"] = $contentValue;
                    }
                }
            }
        }
        return $metaArray;
    }

    public static function getSubdomain($url) {
        $parseArray = explode(".", parse_url($url)['host']);
        return $parseArray[0].".".$parseArray[1];
    }
}