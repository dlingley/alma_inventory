<?php
require('CAS.php');

echo $user . " ";

// logout if desired
if (isset($_REQUEST['logout'])) {
phpCAS::logout(['url' =>  'https://apps.lib.purdue.edu/alma/inventory/index.php']);
}
?>

<!DOCTYPE html>
<html>
<head>
</head>
<body>
<a href="?logout=">Logout</a><BR>
You do not have access to this application.  Email dlingley @ purdue if you feel this is an error.
</body>
</html>
