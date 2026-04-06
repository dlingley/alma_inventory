<?php

require("key.php");

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
                     break;
                 case 'Daily':
                     if (filemtime("cache/barcodes/" . $barcode . ".xml") < strtotime(date("Y-m-d 00:00:00", strtotime("now")))) $cache_expired = true;
                     break;
                 default:
                     if(filemtime("cache/barcodes/". $barcode .".xml") < strtotime(date("Y-m-d 00:00:00",strtotime("now")))) $cache_expired = true;
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
         // use curl to make the API request, retrying up to 3 times on transient failures
         $max_attempts = 3;
         $result = false;
         for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
             $ch = curl_init();
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             //Was critical option setting for this, as API redirects response
             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
             curl_setopt($ch, CURLOPT_URL, $url);
             curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
             curl_setopt($ch, CURLOPT_TIMEOUT, 15);
             $result = curl_exec($ch);
             $curl_error = curl_errno($ch);
             curl_close($ch);

             if ($result !== false && $curl_error === 0) {
                 break; // success
             }

             if (isset($_GET['debug'])) {
                 print("cURL attempt $attempt failed (errno $curl_error); " . ($attempt < $max_attempts ? "retrying...<br>\n" : "giving up.<br>\n"));
             }

             if ($attempt < $max_attempts) {
                 sleep($attempt); // brief back-off: 1s, then 2s
             }
         }

         if (isset($_GET['debug'])) {
             print("xml result from API<br>\n");
             print("<pre>" . htmlspecialchars($result) . "</pre>");
         }

         // save result to cache
         if ($result !== false && strcmp(CACHE_FREQUENCY, "None") && is_writable("cache/barcodes/")) {
             file_put_contents("cache/barcodes/" . $barcode . ".xml", $result);
             if (isset($_GET['debug'])) {
                 print("Barcode File written to cache\n");
             }
         }

         $xml_barcode_result = $result !== false ? simplexml_load_string($result) : false;
     }

     // PARSE RESULTS
     //Add this item to the array of items using the read order as the index value
     return parseXmlToItemObj($xml_barcode_result);
 }

 // Parse a SimpleXML item response into a stdClass item object
 function parseXmlToItemObj($xml_barcode_result)
 {
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
     return $item_obj;
 }

 /**
  * Fetch multiple barcodes in parallel using curl_multi.
  *
  * $barcodes_by_row is an associative array keyed by the original scan-order row
  * number (integer) mapped to the raw barcode string.  Results are returned keyed
  * by the same row numbers so that the caller's scan_loc assignments are never
  * affected by API response order.
  *
  * Barcodes already present in the cache are resolved without a network request.
  * Any barcode whose parallel request fails is retried once via the single-request
  * retrieveBarcodeInfo() function (which has its own retry loop).
  */
 function retrieveBarcodesInBatch($barcodes_by_row)
 {
     $results  = [];
     $to_fetch = []; // row => ['url' => ..., 'barcode_enc' => ...]

     // --- Pass 1: satisfy as many rows as possible from cache ---
     foreach ($barcodes_by_row as $row => $barcode) {
         $barcode_enc = urlencode($barcode);
         $barcode_enc = str_replace(array("%0D%0A"), '', $barcode_enc);

         $xml_barcode_result = false;
         if (strcmp(CACHE_FREQUENCY, "None")) {
             if (file_exists("cache/barcodes/" . $barcode_enc . ".xml")) {
                 $cache_expired = false;
                 switch (CACHE_FREQUENCY) {
                     case 'Hourly':
                         if (filemtime("cache/barcodes/" . $barcode_enc . ".xml") < strtotime(date("Y-m-d H:00:00", strtotime("now")))) $cache_expired = true;
                         break;
                     case 'Daily':
                         if (filemtime("cache/barcodes/" . $barcode_enc . ".xml") < strtotime(date("Y-m-d 00:00:00", strtotime("now")))) $cache_expired = true;
                         break;
                     default:
                         if (filemtime("cache/barcodes/" . $barcode_enc . ".xml") < strtotime(date("Y-m-d 00:00:00", strtotime("now")))) $cache_expired = true;
                 }
                 if (!$cache_expired) {
                     $xml_barcode_result = simplexml_load_file("cache/barcodes/" . $barcode_enc . ".xml");
                 }
             }
         }

         if ($xml_barcode_result) {
             // Cache hit — store result immediately, keyed by original row number
             $results[$row] = parseXmlToItemObj($xml_barcode_result);
         } else {
             $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/items?item_barcode=" . $barcode_enc . "&apikey=" . ALMA_SHELFLIST_API_KEY;
             $to_fetch[$row] = ['url' => $url, 'barcode_enc' => $barcode_enc];
         }
     }

     if (empty($to_fetch)) {
         return $results;
     }

     // --- Pass 2: fetch uncached barcodes in parallel ---
     // Results are stored back using each handle's original row key, not response
     // arrival order, so scan_loc assignments in the caller remain correct.
     $mh      = curl_multi_init();
     $handles = []; // row => curl handle

     foreach ($to_fetch as $row => $info) {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
         curl_setopt($ch, CURLOPT_URL, $info['url']);
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
         curl_setopt($ch, CURLOPT_TIMEOUT, 15);
         curl_multi_add_handle($mh, $ch);
         $handles[$row] = $ch;
     }

     $running = null;
     do {
         curl_multi_exec($mh, $running);
         curl_multi_select($mh);
     } while ($running > 0);

     $failed_rows = []; // rows whose parallel request failed; will be retried

     foreach ($handles as $row => $ch) {
         $result     = curl_multi_getcontent($ch);
         $curl_error = curl_errno($ch);
         curl_multi_remove_handle($mh, $ch);
         curl_close($ch);

         $barcode_enc = $to_fetch[$row]['barcode_enc'];

         if ($result !== false && $curl_error === 0) {
             if (strcmp(CACHE_FREQUENCY, "None") && is_writable("cache/barcodes/")) {
                 file_put_contents("cache/barcodes/" . $barcode_enc . ".xml", $result);
             }
             $xml = simplexml_load_string($result);
             // Store keyed by original row number — preserves scan order
             $results[$row] = $xml ? parseXmlToItemObj($xml) : new stdClass();
         } else {
             $failed_rows[] = $row;
         }
     }

     curl_multi_close($mh);

     // --- Pass 3: retry any failed rows individually (uses retry loop in retrieveBarcodeInfo) ---
     foreach ($failed_rows as $row) {
         $raw_barcode = $barcodes_by_row[$row];
         $results[$row] = retrieveBarcodeInfo($raw_barcode);
     }

     return $results;
 }
?>
