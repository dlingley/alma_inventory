<?php
require("key.php");
//Uncomment below if you wish to enable authentication
//require("login.php");

@$lib_id=$_GET['lib_id'];
$ch = curl_init();
$url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/conf/libraries/'. $lib_id .'/locations';
$templateParamNames = array('$lib_id');
$templateParamValues = array(urlencode('hsse'));
$url = str_replace($templateParamNames, $templateParamValues, $url);
$queryParams = '?' . urlencode('lang') . '=' . urlencode('en') . '&' . urlencode('apikey') . '=' . ALMA_SHELFLIST_API_KEY;
curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$response = curl_exec($ch);
curl_close($ch);

$xml_result = simplexml_load_string($response);
// PARSE RESULTS
$locations = [];
foreach($xml_result->location as $location)
{
	$location_obj = new stdClass();
	$location_obj->code = (string) $location->code;
	$location_obj->name = (string) $location->name;
	//Add this loation to the array of locations using the unique location code as the index value
  $locations[trim($location->code)] = $location_obj;
}
//strip top level unique id from array
$out = array_values($locations);
$main = array('locationData'=>$out);
echo json_encode($main);
?>
