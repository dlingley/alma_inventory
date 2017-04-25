<?php

require('CAS.php');

// set your Alma Booking API Key
define("ALMA_SHELFLIST_API_KEY","*YOUR KEY HERE*");

$user = phpCAS::getUser();
/*echo $user;
var_dump(phpCAS::getAttributes());
*/


$result = false;
// Can limit access to the application to only these succesfully authenticated users
if ($user = "dlingley" || "subrama" || "bmeaghe2")
{
  $result=true;
}


if($result != true){
	header("Location: noaccess.php");
	exit;
}
