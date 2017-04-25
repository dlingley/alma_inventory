<?php

//Ensure Authentication and load API Keys
require("login.php");
require("SortLC.php");
require("almaBarcodeAPI.php");

// set the Caching Frequency - neverExpire, Daily, Hourly or None (No Caching) (recommended default: Daily)
define("CACHE_FREQUENCY", "neverExpire");

$items = array();
$storagename = "uploaded_file.txt";

if (isset($_POST["cnType"])) {
    if (isset($_FILES["file"])) {

        //if there was an error uploading the file
        if ($_FILES["file"]["error"] > 0) {
            //echo "Return Code: " . $_FILES["file"]["error"] . "<br />";

        } else {
            //Print file details
            // echo "Upload: " . $_FILES["file"]["name"] . "<br />";
            // echo "Type: " . $_FILES["file"]["type"] . "<br />";
            // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
            // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

            //if file already exists
            if (file_exists("cache/" . $_FILES["file"]["name"])) {
                //echo $_FILES["file"]["name"] . " already exists. ";
            } else {
                //Store file in directory "upload" with the name of "uploaded_file.txt"
                $storagename = "uploaded_file.txt";
                move_uploaded_file($_FILES["file"]["tmp_name"], "cache/" . $storagename);
                //echo "Stored in: " . "cache/" . $_FILES["file"]["name"] . "<br />";
            }
        }
    } else {
        //echo "No file selected <br />";
    }
} else {
  //echo "No Call Number Type";
}



if (file_exists("cache/" . $storagename)) {
    $file = fopen("cache/" . $storagename, "r");
    //echo "File opened.<br />";

    $firstline = fgets($file, 4096);
    //Gets the number of fields, in CSV-files the names of the fields are mostly given in the first line
    $num = strlen($firstline) - strlen(str_replace(",", "", $firstline));

    //save the different fields of the firstline in an array called fields
    $fields = array();
    $fields = explode(",", $firstline, ($num + 1));

    $line = array();
    $i = 0;

    //CSV: one line is one record and the cells/fields are seperated by ";"
    //so $dsatz is an two dimensional array saving the records like this: $dsatz[number of record][number of cell]
    while ($line[$i] = fgets($file, 4096)) {

        $dsatz[$i] = array();
        $dsatz[$i] = explode(",", $line[$i], ($num + 1));

        $i++;
    }

    //load callNumber array and sort for printing below
    foreach ($dsatz as $key => $number) {
      foreach ($number as $k => $content) {
        //new table cell for every field of the record
        //retrieve item object data using barcode
        $itemData = retrieveBarcodeInfo($content);

        //store to array for sorting
        $unsorted[$key] = $itemData;
        $unsorted[$key]->scan_loc = $key;
      }
    }
    $unsortedArray = json_decode(json_encode($unsorted), true);

    $sortednk = $unsortedArray;
    $sortedkey = $unsortedArray;
    $sortednk_success = usort ($sortednk, "SortLCObject");
    $sortedkey_success = uasort($sortedkey, "SortLCObject");
    // echo '<pre>';
    // print_r($sortednk);
    // echo '</pre>';
    // echo '<pre>';
    // print_r($sortedkey);
    // echo '</pre>';


    $previousCN = 1;
    foreach ($sortednk as $key => $number) {

                $item_obj = new stdClass();
                $item_obj->call_number = $sortednk[$key]['call_number'];
                $item_obj->scan_loc = $sortednk[$key]['scan_loc'] +1;
                $item_obj->correct_loc = $key +1;
                $item_obj->title_start = substr($sortednk[$key]['title'], 0, 20) . "...";

                $orderDiff = abs($key - $sortednk[$key]['scan_loc']);
                $problem = false;
                if ($orderDiff > 1) {

                  //Next two if statements take care of undefined offset issue
                  if ( ! isset($unsortedArray[$sortednk[$key]['scan_loc']-1])) {
                             $unsortedArray[$sortednk[$key]['scan_loc']-1] = null;
                            }
                  if ( ! isset($unsortedArray[$sortednk[$key]['scan_loc']+1])) {
                             $unsortedArray[$sortednk[$key]['scan_loc']+1] = null;
                            }
                    if (!isset($item_obj->problems->order))
                    $item_obj->problems->order = "**OUT OF ORDER** Currently Between " .$unsortedArray[$sortednk[$key]['scan_loc']-1]['call_number']." & ".$unsortedArray[$sortednk[$key]['scan_loc']+1]['call_number']. "";
                }

                $cntype = 0;
                if ($sortednk[$key]['call_number_type'] != $cntype) {
                    $item_obj->problems->cnType = "**WRONG CN TYPE**";

                }

                if ($sortednk[$key]['status'] != 1) {
                    $item_obj->problems->nip = "**NIP: " . $itemData->process_type . "**";
                }

                if ($sortednk[$key]['in_temp_location'] != 'false') {
                    $item_obj->problems->temp = "**IN TEMP LOC**";

                }

                if ($sortednk[$key]['requested'] != 'false') {
                    $item_obj->problems->request = "**ITEM HAS REQUEST**";

                }

                $location = 'hss2';
                if ($sortednk[$key]['location'] != $location) {
                    $item_obj->problems->location = "**WRONG LOCATION: " . $itemData->location . "**";

                }
                $library = 'hsse';
                if ($sortednk[$key]['library'] != $library) {
                    $item_obj->problems->location = "**WRONG LIBRARY: " . $itemData->library . "**";

                }
                $policy = 'core';
                if ($sortednk[$key]['policy'] != $policy) {
                  if ($sortednk[$key]['policy'] != '')
                  {
                    $item_obj->problems->policy = "**WRONG POLICY: " . $sortednk[$key]['policy']  . "**";
                  }
                  else {
                    $item_obj->problems->policy = "**BLANK I POLICY**";
                  }

                }

                $type = 'BOOK';
                if ($sortednk[$key]['physical_material_type'] != $type) {
                  if ($sortednk[$key]['physical_material_type'] != '')
                  {
                    $item_obj->problems->type = "**WRONG TYPE: " . $sortednk[$key]['physical_material_type'] . "**";
                  }
                  else {
                    $item_obj->problems->type = "**BLANK I TYPE**";
                  }

                }


                $items[$key] = $item_obj;

              }

              // CREATE JSON OUTPUT
              //strip top level unique id from array
              $out = array_values($items);
              print(json_encode($out));

}

?>
