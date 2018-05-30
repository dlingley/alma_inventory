# alma_inventory
A PHP webapp which allows libraries to run a list of scanned barcodes and find which books are out of place

# Things to do to get this working:

Setup api key with the following permissions:
https://www.screencast.com/t/x2RK4R5JaMwh

# key.php:
Add Alma API key setup like  in place of: *YOUR KEY HERE*

If you wish to enable authentication:
# login.php
This example uses phpCAS to authenticate users if you would like to duplicate you would need to install this in your PHP environment separately.  I am keeping the code in here for example purposes.

If you have the login.php page working the way you would like, you can then uncomment the require("login.php"); line in the following 2 files:
# almaLocationsAPI.php
# index.php

For the index.php file you will also want to modify
Change id="itemType" selections to meet your needs (corresponds to material type choices you have enabled in Alma)
Change id="policy" selections to meet your needs (corresponds to item policy types defined in Alma)



Create a cache folder with following subfolders:
https://www.screencast.com/t/PqvTCgLNpEk
