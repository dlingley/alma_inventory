<?php

if (!defined('ALMA_SHELFLIST_API_KEY')) define('ALMA_SHELFLIST_API_KEY', '*YOUR KEY HERE*');

// set the Caching Frequency - neverExpire, Daily, Hourly or None (No Caching) (recommended default: Daily)
if (!defined('CACHE_FREQUENCY')) define('CACHE_FREQUENCY', 'Daily');
/*********************************************************************
 * SortLC
 *********************************************************************/
 //retrieve Item Info Using Barcode and return array of data
 function retrieveBarcodeInfo($barcode)
 {

     $xml_barcode_result = false;
     $barcode = urlencode($barcode);
     //Remove encoded data received when processing CSV
     $barcode = str_replace(array("%0D%0A"), '', $barcode);
     // BUILD REST REQUEST URL
     $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=" . $barcode . "&apikey=" . ALMA_SHELFLIST_API_KEY;
     if (isset($_GET['debug']))
         print("URL:" . $barcode . " $url<br>\n");

     if (strcmp(CACHE_FREQUENCY, "None")) {
         // check cache for barcode
         if (file_exists("cache/barcodes/" . $barcode . ".xml")) {
             // check last modified datestamp
             $cache_expired = false;
             switch (CACHE_FREQUENCY) {
                 case 'Hourly':
                     if (filemtime("cache/barcodes/" . $barcode . ".xml") < strtotime(date("Y-m-d H:00:00", strtotime("now")))) $cache_expired = true;
                 case 'Daily':
                     if (filemtime("cache/barcodes/" . $barcode . ".xml") < strtotime(date("Y-m-d 00:00:00", strtotime("now")))) $cache_expired = true;
                 default: if(filemtime("cache/barcodes/". $barcode .".xml") < strtotime(date("Y-m-d 00:00:00",strtotime("now")))) $cache_expired = true;
             }
             //$cache_expired = true;
             if (!$cache_expired) {
                 $xml_barcode_result = simplexml_load_file("cache/barcodes/" . $barcode . ".xml");
                 if (isset($_GET['debug'])) print("loaded data from cache file: cache/barcodes/" . $barcode . ".xml<br>\n");
             }
             else {
               $xml_barcode_result = false;
             }
         }
     }

     // if no cache data available, query the Alma API
     if (!$xml_barcode_result) {
         // use curl to make the API request
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         //Was critical option setting for this, as API redirects response
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
         curl_setopt($ch, CURLOPT_URL, $url);
         $result = curl_exec($ch);

         if (isset($_GET['debug'])) {
             print("xml result from API<br>\n");
             print("<pre>" . htmlspecialchars($result) . "</pre>");
         }

         // save result to cache
         if (strcmp(CACHE_FREQUENCY, "None") && is_writable("cache/barcodes/")) {
             file_put_contents("cache/barcodes/" . $barcode . ".xml", $result);
             if (isset($_GET['debug'])) {
                 print("Barcode File written to cache\n");
             }
         }

         $xml_barcode_result = simplexml_load_string($result);
         curl_close($ch);
     }

     // PARSE RESULTS
     $item_obj = new stdClass();
     $item_obj->title = (string)$xml_barcode_result->bib_data->title;
     $item_obj->item_link = (string)$xml_barcode_result['link']."?apikey=" . ALMA_SHELFLIST_API_KEY;
     $item_obj->mms_id = (string)$xml_barcode_result->bib_data->mms_id;
     $item_obj->bib_link = (string)$xml_barcode_result->bib_data['link']."?apikey=" . ALMA_SHELFLIST_API_KEY;
     $item_obj->holding_id = (string)$xml_barcode_result->holding_data->holding_id;
     $item_obj->holding_link = (string)$xml_barcode_result->holding_data['link']."?apikey=" . ALMA_SHELFLIST_API_KEY;
     $item_obj->item_pid = (string)$xml_barcode_result->item_data->pid;
     $item_obj->item_barcode = (string)$xml_barcode_result->item_data->barcode;
     $item_obj->call_number = (string)$xml_barcode_result->holding_data->call_number. " " . (string)$xml_barcode_result->item_data->enumeration_a. " " . (string)$xml_barcode_result->item_data->chronology_i;
     $item_obj->in_temp_location = (string)$xml_barcode_result->holding_data->in_temp_location;
     $item_obj->call_number_type = (string)$xml_barcode_result->holding_data->call_number_type;
     $item_obj->status = (string)$xml_barcode_result->item_data->base_status;
     $item_obj->status_desc = (string)$xml_barcode_result->item_data->base_status['desc'];
     $item_obj->process_type = (string)$xml_barcode_result->item_data->process_type;
     $item_obj->library = (string)$xml_barcode_result->item_data->library;
     $item_obj->location = (string)$xml_barcode_result->item_data->location;
     $item_obj->physical_material_type = (string)$xml_barcode_result->item_data->physical_material_type;
     $item_obj->item_note3 = (string)$xml_barcode_result->item_data->internal_note_3;
     $item_obj->requested = (string)$xml_barcode_result->item_data->requested;
     $item_obj->policy = (string)$xml_barcode_result->item_data->policy;

     //Add this item to the array of items using the read order as the index value
     return $item_obj;



     //	if(isset($_GET['debug']))
     {
         print("<pre>\n");
         print_r($xml_barcode_result);
         print("</pre>\n");
     }
     $xml_barcode_result = false;


 }
?>
