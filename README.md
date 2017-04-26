# alma_inventory
A PHP webapp which allows libraries to run a list of scanned barcodes and find which books are out of place

# Things to do to get this working:

Setup api key with the following permissions:
https://www.screencast.com/t/x2RK4R5JaMwh

# almaBarcodeAPI.php:
Add Alma API key in place of: *YOUR KEY HERE*

# almaLocationsAPI.php
Add Alma API key in place of: *YOUR KEY HERE*

# index.php
Add Alma BIB API key in place of: *YOUR KEY HERE*
Change id="itemType" selections to meet your needs
Change id="policy" selections to meet your needs

# login.php
Add Alma BIB API key in place of: *YOUR KEY HERE*
This uses phpCAS to authenticate users

Create cache folder with following subfolders:
https://www.screencast.com/t/PqvTCgLNpEk
