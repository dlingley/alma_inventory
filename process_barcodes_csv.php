<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <style type="text/css">
        body {
            font: 12px/14px Arial;
        }

        div.submit-form {
            width: 550px;
            margin: 5px auto;
        }
    </style>
</head>
<body>

<?php
//Ensure Authentication and load API Keys
require("login.php");
require("SortCallNumber.php");
require("almaBarcodeAPI.php");

$problem = false;
$orderProblem = '';
$cnTypeProblem = '';
$nipProblem = '';
$tempProblem = '';
$libraryProblem = '';
$locationProblem = '';
$policyProblem = '';
$typeProblem = '';
$orderProblemCount = 0;

//Only run code below if form submitted
if (isset($_POST['submit'])) {
  //View Post Data Submitted
  //pre($_POST);
    if (isset($_FILES["file"])) {

        //if there was an error uploading the file
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";

        } else {
            //Print file details
            // echo "Upload: " . $_FILES["file"]["name"] . "<br />";
            // echo "Type: " . $_FILES["file"]["type"] . "<br />";
            // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
            // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

            //if file already exists
            if (file_exists("cache/upload/" . $_FILES["file"]["name"])) {
                //echo $_FILES["file"]["name"] . " already exists. ";
            } else {
                //Store file in directory "upload" with the name of "uploaded_file.txt"
                $storagename = "uploaded_file.txt";
                move_uploaded_file($_FILES["file"]["tmp_name"], "cache/upload/" . $storagename);
                //echo "Stored in: " . "cache/" . $_FILES["file"]["name"] . "<br />";
            }
        }
    } else {
        echo '<H1>Barcode.csv file not selected.</H1><BR>';
        echo '<a href=' . 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php' . '> Run New File</a><BR>';
        exit();
    }

    //Check Call # type need to implement other types
    if (isset($_POST['cnType']) && $_POST['cnType'] != 'lc') {
        echo '<H1>Currently only LC callnumber type supported.</H1><BR>';
        echo '<a href=' . 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php' . '> Run New File</a><BR>';
        exit();
    }

    $csv_output_filename = 'ShelfList_' . $_POST['library'] . '_' . $_POST['location'] . '_' . date('Ymd') . '.csv';
    echo '<a href=' . 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php' . '> Run New File</a> | <a href=cache/output/' . $csv_output_filename . '>Download File: ' . $csv_output_filename . '</a><BR>';
    // check if cached barcodeOutput file exists and delete if needed
    if (file_exists("cache/output/" . $csv_output_filename)) {
        unlink("cache/output/" . $csv_output_filename);

        if (isset($_GET['debug'])) {
            print("cache file deleted");
        }
    }

// open the csv file for writing
    $csv_file = fopen('cache/output/' . $csv_output_filename, 'w');

// save the column headers
    fputcsv($csv_file, array('Correct Position', 'Call Number', 'Position Scanned', 'Title', 'Problem'));

    if ($file = fopen("cache/upload/" . $storagename, "r")) {

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
        $sortednk_success = usort($sortednk, "SortLCObject");
        $sortedkey_success = uasort($sortedkey, "SortLCObject");
        // echo '<pre>';
        // print_r($sortednk);
        // echo '</pre>';
        // echo '<pre>';
        // print_r($sortedkey);
        // echo '</pre>';
        echo "<div class='page-header'>";
          echo "  <h1>ShelfList <small>". $_POST['library'] . ' ' . $_POST['location'] . ' ' . date('Ymd') ."</small></h1>";
        echo "</div>";

        echo "<table style='width: auto;' class='table table-hover table-striped table-bordered table-condensed'>";
        echo "<tr>";
        echo "<th>Correct<BR>Order</th>";
        echo "<th>Correct CN Order</th>";
        echo "<th>Where<BR>Scanned</th>";
        echo "<th>Title</th>";
        echo "<th>Problem</th>";
        echo "</tr>";

        $previousCN = 1;
        foreach ($sortednk as $key => $number) {
          $problem = false;
            //new table row for every record
            echo "<tr>";

            //Don't flag order issues if only Other problems are requested
            if ($_POST['onlyother'] == 'false') {
                //Next two if statements take care of undefined offset issue
                if (!isset($sortednk[$key - 1]['scan_loc'])) {
                    $sortednk[$key - 1]['scan_loc'] = null;
                }
                if (!isset($sortednk[$key + 1]['scan_loc'])) {
                    $sortednk[$key + 1]['scan_loc'] = null;
                }
                $prevScan_loc = $sortednk[$key - 1]['scan_loc'] + 1;
                $scan_loc = $sortednk[$key]['scan_loc'] + 1;
                $nextScan_loc = $sortednk[$key + 1]['scan_loc'] + 1;
                $nextdiff = $nextScan_loc - $scan_loc;
                $prevdiff = $scan_loc - $prevScan_loc;

                if ($prevdiff != 1 && $nextdiff != 1) {

                    //Next two if statements take care of undefined offset issue
                    if (!isset($unsortedArray[$sortednk[$key]['scan_loc'] - 1])) {
                        $unsortedArray[$sortednk[$key]['scan_loc'] - 1] = null;
                    }
                    if (!isset($unsortedArray[$sortednk[$key]['scan_loc'] + 1])) {
                        $unsortedArray[$sortednk[$key]['scan_loc'] + 1] = null;
                    }
                    $orderProblem = "**OUT OF ORDER**<BR>Currently Between<BR>" . $unsortedArray[$sortednk[$key]['scan_loc'] - 1]['call_number'] . " & " . $unsortedArray[$sortednk[$key]['scan_loc'] + 1]['call_number'] . "<BR>";
                    $orderProblemCount += 1;
                    $problem = true;


                } else {
                    $orderProblem = '';
                }
            }

            //Don't flag other issues if only order problems are requested
            if ($_POST['onlyorder'] == 'false') {
                $cntype = 0;
                if ($sortednk[$key]['call_number_type'] != $cntype) {
                    $cnTypeProblem = "**WRONG CN TYPE**<BR>";
                    $problem = true;
                } else {
                    $cnTypeProblem = '';
                }

                if ($sortednk[$key]['status'] != 1) {
                    $nipProblem = "**NIP: " . $itemData->process_type . "**<BR>";
                    $problem = true;
                } else {
                    $nipProblem = '';
                }

                if ($sortednk[$key]['in_temp_location'] != 'false') {
                    $tempProblem = "**IN TEMP LOC**<BR>";
                    $problem = true;
                } else {
                    $tempProblem = '';
                }

                if ($sortednk[$key]['requested'] != 'false') {
                    $requestProblem = "**ITEM HAS REQUEST**<BR>";
                    $problem = true;
                } else {
                    $requestProblem = '';
                }

                $location = 'hss2';
                if ($sortednk[$key]['location'] != $location) {
                    $locationProblem = "**WRONG LOCATION: " . $itemData->location . "**<BR>";
                    $problem = true;
                } else {
                    $locationProblem = '';
                }
                $library = 'hsse';
                if ($sortednk[$key]['library'] != $library) {
                    $libraryProblem = "**WRONG LIBRARY: " . $itemData->library . "**<BR>";
                    $problem = true;
                } else {
                    $libraryProblem = '';
                }

                $policy = 'core';
                if ($sortednk[$key]['policy'] != $policy) {
                    if ($sortednk[$key]['policy'] != '') {
                        $policyProblem = "**WRONG POLICY: " . $sortednk[$key]['policy'] . "**<BR>";
                    } else {
                        $policyProblem = "**BLANK I POLICY**";
                    }
                    $problem = true;
                } else {
                    $policyProblem = '';
                }

                $type = 'BOOK';
                if ($sortednk[$key]['physical_material_type'] != $type) {
                    if ($sortednk[$key]['physical_material_type'] != '') {
                        $typeProblem = "**WRONG TYPE: " . $sortednk[$key]['physical_material_type'] . "**<BR>";
                    } else {
                        $typeProblem = "**BLANK I TYPE**";
                    }
                    $problem = true;
                } else {
                    $typeProblem = '';
                }
            }

            $scan_loc = $sortednk[$key]['scan_loc'] + 1;
            $correct_loc = $key + 1;

            //If row has a problem print in Bold and output problems to file
            if ($problem) {
                echo "<td><B>" . $correct_loc . "</td>";
                echo "<td><B>" . $sortednk[$key]['call_number'] . "</td>";
                echo "<td><B>" . $scan_loc . "</td>";
                echo "<td><B>" . substr($sortednk[$key]['title'], 0, 20) . '...' . "</td>";
                echo "<td><B>" . $orderProblem . $cnTypeProblem . $nipProblem . $tempProblem . $libraryProblem . $locationProblem . $policyProblem . $typeProblem . "</td>";
                $problems = $orderProblem . $cnTypeProblem . $nipProblem . $tempProblem . $libraryProblem . $locationProblem . $policyProblem . $typeProblem;
                //remove <BR's> from csv output
                $problems = preg_replace('#(<br */?>\s*)+#i', '', $problems);
                //output to csv
                fputcsv($csv_file, array($correct_loc, $sortednk[$key]['call_number'], $scan_loc, substr($sortednk[$key]['title'], 0, 20), $problems));
                //Otherwise print without bold regularly
            } else {
                //Don't print non-problems if only problems are requested
                if ($_POST['onlyproblems'] == 'false') {
                    echo "<td>" . $correct_loc . "</td>";
                    echo "<td>" . $sortednk[$key]['call_number'] . "</td>";
                    echo "<td>" . $scan_loc . "</td>";
                    echo "<td>" . substr($sortednk[$key]['title'], 0, 20) . '...' . "</td>";
                    echo "<td></td>";
                    //output to csv
                    fputcsv($csv_file, array($correct_loc, $sortednk[$key]['call_number'], $scan_loc, substr($sortednk[$key]['title'], 0, 20), ''));
                }
            }
        }


        echo "</table>";
        echo '<BR>' . $orderProblemCount . ' Order Problems Found';
    }

// Close the file
    fclose($csv_file);
} else {
    echo "No data received.";
}

function pre($data) {
    print '<pre>' . print_r($data, true) . '</pre>';
}

?>

</body>
</html>
