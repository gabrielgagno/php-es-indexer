<?php
/**
 * Created by PhpStorm.
 * User: gabrielgagno
 * Date: 1/19/16
 * Time: 6:07 PM
 */

namespace Parser;
include('MetaParser.php');
use Parser\MetaParser;

$rds_settings = array(
    "host"     => "",
    "db"       => "",
    "user"     => "",
    "password" => ""
);
$es_settings = array(
    "host"  => "",
    "index" => ""
);

# save rds settings
$rds_file = fopen('rds_location', 'r');
echo "UPDATING RDS CREDENTIALS\n";
if(!$rds_file) {
    die('ERROR: RDS CONFIG FILE NOT FOUND\n');
}

while(!feof($rds_file)) {
    $row = fgets($rds_file);
    $tok = strtok($row, "=\n");
    $rds_settings[$tok] = strtok("=\n");
}
fclose($rds_file);
echo "SUCCESSFULLY CONFIGURED RDS SETTINGS\n";

#save es settings
echo "UPDATING ES CREDENTIALS\n";
$es_file = fopen('es_location', 'r');
if(!$es_file) {
    die('ERROR: ES CONFIG FILE NOT FOUND\n');
}

while(!feof($es_file)) {
    $row = fgets($es_file);
    $tok = strtok($row, "=\n");
    $es_settings[$tok] = strtok("=\n");
}
echo "SUCCESSFULLY CONFIGURED ES SETTINGS\n";

echo "CONNECTING TO MYSQL DATABASE...\n";

$conn = mysqli_connect(
    $rds_settings['host'],
    $rds_settings['user'],
    $rds_settings['password'],
    $rds_settings['db']);
if(!$conn) {
    die('Connection failed : ' . mysqli_error($conn));
}

echo "SUCCESSFULLY CONNECTED TO MYSQL DATABASE\n";

$conn->autocommit("true");

echo "AUTOCOMMIT SET TO TRUE\n";

$query = "select id, title, baseUrl, text, content from webpage where status = 2";

$results = $conn->query($query);

# initialize curl payload
$payload = "";
$rowNum = 0;
# for all results of query
while($row = mysqli_fetch_assoc($results)) {
    echo ++$rowNum."\n";
    $metas = MetaParser::parseMetaTagsFromHtmlString($row['content'], ['description', 'keywords']);
    $payloadHalf = json_encode(array(
        "id" => utf8_encode($row['id']),
        "title" => utf8_encode($row['title']),
        "url" => utf8_encode($row['baseUrl']),
        "content" => utf8_encode($row['text']),
        "desc"  => empty($metas['description'])?null:utf8_encode($metas['description']),
        "keywords"  => empty($metas['keywords'])?null:utf8_encode($metas['keywords']),
        "subdomain" => MetaParser::getSubdomain($row['baseUrl'])
    ), JSON_UNESCAPED_SLASHES);
    $payload = $payload."{\"create\":{}}\n".$payloadHalf."\n";
}
#bulk index using curl
$curlUrl = "http://".$es_settings['host'].":".$es_settings['port']."/".$es_settings['index']."/webpage/_bulk";
echo "CURL URL: ".$curlUrl;
$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_URL => $curlUrl,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_POSTFIELDS => $payload
));

echo "\nindexing job: starting...\n";
#execute bulk index
$response = curl_exec($ch);
echo $response;
echo "indexing job: done.\n";

curl_close($ch);

# close the connection
$conn->close();

echo "DONE\n";