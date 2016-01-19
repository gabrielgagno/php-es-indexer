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

    public static function addSearchString() {
        $sampArray = array(
            "business.csv",
            "shop.csv",
            "community.csv",
            "tattoo.csv",
            "www.csv"
        );

        $conn = mysqli_connect('localhost', 'root', '', 'nutch');
        if(!$conn) {
            die('Not connected : ' . mysqli_error($conn));
        }
        $conn->autocommit(true);

        foreach($sampArray as $sArray) {
            $file = fopen($sArray, 'r');
            $data = fgetcsv($file);
            while(!feof($file)) {
                $actual = null;
                $data = fgetcsv($file);
                if(substr($data[1], 0, strlen('http://')) === 'http://'){
                    $actual = str_replace('http://', '', $data[1]);
                }
                else if(substr($data[1], 0, strlen('https://')) === 'https://'){
                    $actual = str_replace('https://', '', $data[1]);
                }
                echo $actual;
                $statement = "select id from webpage where baseUrl like \"%".$actual."%\"";
                $results = $conn->query($statement);
                while($row = mysqli_fetch_assoc($results)) {
                    echo "ROW ID: ".$row['id']."\n";
                    echo "DATA: ".$data[0]."\n\n";
                    $update = "update webpage set aq_md_searchstring=\"$data[0]\" where id like \"%".$row['id']."%\"";
                    $conn->query($update);
                    break;
                }
            }
            fclose($file);
        }
    }
}