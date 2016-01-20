<?php
/**
 * Created by PhpStorm.
 * User: gabrielgagno
 * Date: 1/20/16
 * Time: 11:20 AM
 */

namespace Parser;
include('MetaParser.php');
use Parser\MetaParser;

echo MetaParser::parseMetaTagsFromHtmlString(addslashes(""));