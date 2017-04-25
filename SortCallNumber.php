<?php
/*********************************************************************
 * SortLC Takes in two LC Call #'s, Normalizes, then sorts them
 * Can use usort or uasort to sort arrays based on the call number
 *********************************************************************/
function SortLC($right, $left)
{
    $right = NormalizeLC($right);
    $left = NormalizeLC($left);
    return (strcmp($right, $left));
} // end SortLC
/*********************************************************************
/*********************************************************************
 * SortLCObject Takes in two Obects contaning LC Call # elements
 * defined as call_number, normalizes, then sorts them
 * Can use usort or uasort to sort arrays based on the call number
 *********************************************************************/
function SortLCObject($right, $left)
{
    $right = NormalizeLC($right["call_number"]);
    $left = NormalizeLC($left["call_number"]);
    return (strcmp($right, $left));
} // end SortLC
/*********************************************************************
 *  NormalizeLC
 *  Normalizes LC for sorting
 *********************************************************************/
function NormalizeLC($lc_call_no_orig)
{
    /*
      User defined setting: set problems to top to sort unparsable
      call numbers to the top of the list; false to sort them to the
      bottom.
    */
    $problems_to_top = "true";
    if ($problems_to_top == "true") {
        $unparsable = " ";
    } else {
        $unparsable = "~";
    }
    //Convert all alpha to uppercase
    $lc_call_no = strtoupper($lc_call_no_orig);
    // define special trimmings that indicate integer
    $integer_markers = array("C.", "BD.", "DISC", "DISK", "NO.", "PT.", "v.", "V.", "VOL.");
    foreach ($integer_markers as $mark) {
        $mark = str_replace(".", "\.", $mark);
        $lc_call_no = preg_replace("/$mark(\d+)/", "$mark$1;", $lc_call_no);
    } // end foreach int marker
    // Remove any inital white space
    $lc_call_no = preg_replace("/\s*/", "", $lc_call_no);

    if (preg_match("/^([A-Z]{1,3})\s*(\d+)\s*\.*(\d*)\s*\.*\s*([A-Z]*)(\d*)\s*([A-Z]*)(\d*)\s*(.*)$/", $lc_call_no, $m)) {
        $initial_letters = $m[1];
        $class_number = $m[2];
        $decimal_number = $m[3];
        $cutter_1_letter = $m[4];
        $cutter_1_number = $m[5];
        $cutter_2_letter = $m[6];
        $cutter_2_number = $m[7];
        $the_trimmings = $m[8];
    } //end if call number match
    else {
        return ($unparsable);
    } // return extreme answer if not a call number
    if ($cutter_2_letter && !($cutter_2_number)) {
        $the_trimmings = $cutter_2_letter . $the_trimmings;
        $cutter_2_letter = '';
    }
    /* TESTING NEW SECTION TO HANDLE VOLUME & PART NUMBERS */
    foreach ($integer_markers as $mark) {
        if (preg_match("/(.*)($mark)(\d+)(.*)/", $the_trimmings, $m)) {
            $trim_start = $m[1];
            $int_mark = $m[2];
            $int_no = $m[3];
            $trim_rest = $m[4];
            $int_no = sprintf("%5s", $int_no);
            $the_trimmings = $trim_start . $int_mark . $int_no . $trim_rest;
        } // end if markers in the trimmings
    } // end foreach integer marker
    /* END NEW SECTION */
    if ($class_number) {
        $class_number = sprintf("%5s", $class_number);
    }
    $decimal_number = sprintf("%-12s", $decimal_number);
    if ($cutter_1_number) {
        $cutter_1_number = " $cutter_1_number";
    }
    if ($cutter_2_letter) {
        $cutter_2_letter = "   $cutter_2_letter";
    }
    if ($cutter_2_number) {
        $cutter_2_number = " $cutter_2_number";
    }
    if ($the_trimmings) {
        $the_trimmings = preg_replace("/(\.)(\d)/", "$1 $2", $the_trimmings);
        $the_trimmings = preg_replace("/(\d)\s*-\s*(\d)/", "$1-$2", $the_trimmings);
        //    $the_trimmings =~ s/(\d+)/sprintf("%5s", $1)/ge;
        $the_trimmings = "   $the_trimmings";
    }
    $normalized = "$initial_letters" . "$class_number"
        . "$decimal_number" . "$cutter_1_letter"
        . "$cutter_1_number" . "$cutter_2_letter"
        . "$cutter_2_number" . "$the_trimmings";

    return ("$normalized");
} // end NormalizeLC

//an adaptation of Koha's Dewey sort routine
//GPL info goes here
		//problem call numbers
		/*
		709.04 M453
	  704.94978 S727
		759.06 E96
		759.1H766
		759.1N
		*/
		//759.06 E96 should display as 759_060000000000000_E96
		//$callNum = '759.06 E96';

		/*********************************************************************
		 * SortDeweyObject  Takes in two Obects contaning Dewey Call # elements
     * defined as call_number, normalizes, then sorts them
     * Can use usort or uasort to sort arrays based on the call number
		 *********************************************************************/
     /*********************************************************************
      * SortDewey Takes in two Dewey Call #'s, Normalizes, then sorts them
      * Can use usort or uasort to sort arrays based on the call number
      *********************************************************************/
     function SortDewey($right, $left)
     {
         $right = normalizeDewey($right);
         $left = normalizeDewey($left);
         return (strcmp($right, $left));
     } // end SortLC
     /*********************************************************************/

		function SortDeweyObject($right, $left)
		{
		    $right = normalizeDewey($right["call_number"]);
		    $left = normalizeDewey($left["call_number"]);
		    return (strcmp($right, $left));
		} // end SortLC

	function normalizeDewey($callNum){
        //Insert ! when lowercase letter comes after number
        $init = preg_replace('/([0-9])(?=[a-z])/','$1!', $callNum);
		//make all characters lowercase... sort works better this way for dewey...
		$init = strtolower($init);
		//get rid of leading whitespace
		$init = preg_replace('/^\s+/', '', $init);
		//get rid of extra whitespace at end of string
		$init = preg_replace('/\s+$/', '', $init);
		//get rid of &nbsp; at end of string
		$init = preg_replace('/\&/', '', $init);
	    //remove any slashes
		$init = preg_replace('/\//', '', $init);
		//remove any backslashes
		$init = stripslashes($init);
		// replace newline characters
		$init = preg_replace('/\n/','', $init);

		//set digit group count
		$digit_group_count = 0;
		//declare first digit group index variable
		$first_digit_group_idx;

		//split string into tokens by . or space
		$tokens = preg_split( '/\.|\s+/', $init);

		//loop through the tokens
		for($i=0;$i<sizeof($tokens);$i++){
			//if the token begins and ends with digits
			if(preg_match("/^\d+$/", $tokens[$i])){
				//increment the number of digit groups
				$digit_group_count++;
				//if it's the first one, store its index in first_digit_group_idx
				if (1 == $digit_group_count) {
                $first_digit_group_idx = $i;
            }
        //if there is a second group of digits, expand it to 15 places, adding 0s
        if (2 == $digit_group_count) {
            if ($i - $first_digit_group_idx == 1) {
                    $tokens[$i] = str_pad($tokens[$i], 15, "0", STR_PAD_RIGHT);
                    //$tokens[$i] =~ tr/ /0/;
                } else {
                $tokens[$first_digit_group_idx] .= '_000000000000000';
              }
            }
			}

		}

		if (1 == $digit_group_count) {
        $tokens[$first_digit_group_idx] .= '_000000000000000';
    }

    $key = implode("_", $tokens);
		return $key;
	}

?>
