<?php
//this is the setup used for Purdue the CAS.php is not included in this repo and would
//need to be installed separately.  If you wish to enable Authentication then uncomment
//the require statements in index.php and almaLocationsAPI.php
require('CAS.php');


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
