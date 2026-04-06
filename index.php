<!DOCTYPE html>
<?php
//require("login.php");
require("key.php");

if(!isset($_SESSION))
    {
        session_start();
    }
    $_SESSION['progress']=0;
    session_write_close();
  ?>
<html lang="en">
  <head>
    <!--
  	First, include the main jQuery and jQuery UI javascripts (not included with reformed; you may use Google's CDN links as below:)
  -->
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js"></script>

  <!--
  	Next, include links to the form's CSS, taking care to ensure the correct paths dependent upon where you have uploaded the files
  	contained within the reformed.zip and the reformed-form-(YOUR-THEME-HERE).zip files.

  	Be sure to edit the line:
  	<link rel="stylesheet" href="css/reformed-form-YOUR-THEME/jquery-ui-1.8.7.custom.css" type="text/css" />
  	replacing "YOUR-THEME" with the name of your theme (in this case, it's ui-lightness).
  -->
  <!-- necessary reformed CSS -->
  <!--[if IE]>
      <link rel="stylesheet" type="text/css" href="reformed/css/ie_fieldset_fix.css" />
  <![endif]-->
  <link rel="stylesheet" href="reformed/css/uniform.aristo.css" type="text/css" />
  <link rel="stylesheet" href="reformed/css/ui.reformed.css" type="text/css" />
  <link rel="stylesheet" href="reformed/css/jquery-ui-1.8.7.custom.css" type="text/css" />
  <!-- end necessary reformed CSS -->

  <!--
  	Finally, include the necessary javascript to enable the validation rules and style the form.

  	Be sure to edit the line:
  	$('#YOURFORMID').reformed().validate();
  	and replace YOURFORMID with the actual id attribute's value of your form (e.g., "demo" below).
  -->
  <!-- necessary reformed js -->
  <script src="reformed/js/jquery.uniform.min.js" type="text/javascript"></script>
  <script src="reformed/js/jquery.validate.min.js" type="text/javascript"></script>
  <script src="reformed/js/jquery.ui.reformed.min.js" type="text/javascript"></script>

  <script type="text/javascript">
  $(function(){ //on doc ready
      //set validation options
      //(this creates range messages from max/min values)
      $.validator.autoCreateRanges = true;
      $.validator.setDefaults({
          highlight: function(input) {
              $(input).addClass("ui-state-highlight");
          },
          unhighlight: function(input) {
              $(input).removeClass("ui-state-highlight");
          },
          errorClass: 'error_msg',
          wrapper : 'dd',
          errorPlacement : function(error, element) {
              error.addClass('ui-state-error');
              error.prepend('<span class="ui-icon ui-icon-alert"></span>');
              error.appendTo(element.closest('dl.ui-helper-clearfix').effect('highlight', {}, 2000));
          }
      });

      //call reformed on your form
      $('#ShelfLister').reformed().validate();
  });
//Code to show loader image
$(document).ready(function() {
//hide on start
 $('#loading').hide();

 $('#ShelfLister').submit(function() {
    startProgress(pg);
     $("#loading").show();
     return true;
 });

});

  </script>
  <!-- end necessary reformed js -->
    <!-- start lookup Ajax js -->
  <script type="text/javascript">
  function AjaxFunction()
  {
  var httpxml;
  try
    {
    // Firefox, Opera 8.0+, Safari
    httpxml=new XMLHttpRequest();
    }
  catch (e)
    {
    // Internet Explorer
  		  try
     			 		{
     				 httpxml=new ActiveXObject("Msxml2.XMLHTTP");
      				}
    			catch (e)
      				{
      			try
        		{
        		httpxml=new ActiveXObject("Microsoft.XMLHTTP");
       		 }
      			catch (e)
        		{
        		alert("Your browser does not support AJAX!");
        		return false;
        		}
      		}
    }
  function stateck()
      {
      if(httpxml.readyState==4)
        {
  //alert(httpxml.responseText);
  var myarray = JSON.parse(httpxml.responseText);
  // Remove the options from 2nd dropdown list
  for(j=document.ShelfLister.location.options.length-1;j>=0;j--)
  {
  document.ShelfLister.location.remove(j);
  }


  for (i=0;i<myarray.locationData.length;i++)
  {
  var optn = document.createElement("OPTION");
  optn.text = myarray.locationData[i].name;
  optn.value = myarray.locationData[i].code;
  document.ShelfLister.location.options.add(optn);

  }
        }
      } // end of function stateck
  var url="almaLocationsAPI.php";
  var cat_id=document.getElementById('library').value;
  url=url+"?lib_id="+cat_id;
  url=url+"&sid="+Math.random();
  httpxml.onreadystatechange=stateck;
  //alert(url);
  httpxml.open("GET",url,true);
  httpxml.send(null);
    }
    <!-- end location lookup Ajax js -->
</script>
<!-- Start progress Ajax js -->
<script type="text/javascript">
    var progress = 0;
    var job = "";

    function startProgress(barName){
                console.log("PG Process Started");
                progressLoop(barName);
            }

            function progressLoop(barName){
                console.log("Progress Called");
                $.ajax({
                    url: "getProgress.php",
                    cache: false,
                    dataType: "JSON",
                    success: function(data){
                        console.log(data);
                        obj = JSON.parse(data);
                        console.log(obj.job);
                        console.log(obj.percentage);
                        var pBar = document.getElementById('pg');
                        //console.log("pSUCCESS: " . obj.percentage);
                        pBar.value = obj.percentage;
                        $('.progress-value').html(obj.job + ': ' + obj.percentage + '%');
                        if (obj.percentage < 100 || obj.job != "complete" ){
                            setTimeout(progressLoop(barName), (1000*2));
                        }
                    },
                    error: function(xhr,status,err){
                        console.log("pERROR: " + err);
                        //alert("PROGRESS ERROR");
                    }
                });
            }

</script>
<!-- End progress Ajax js -->
  <!-- The following style code is NOT necessary; just some styling to center the form on the page and set the default font size -->
  <style type="text/css">
  	body { font: 12px/14px Arial;}
  	div.reformed-form { width: 550px; margin: 5px auto;}

  #loading {
  width: 100%;
  height: 100%;
  top: 0px;
  left: 0px;
  position: fixed;
  display: block;
  opacity: 0.9;
  background-color: #fff;
  z-index: 99;
  text-align: center;
}

#loading-image {
  position: absolute;
  top: 25%;
  left: 25%;
  z-index: 100;
}

import url(http://fonts.googleapis.com/css?family=Expletus+Sans);

/* Basic resets */

* {
	margin:0; padding:0;
	box-sizing: border-box;
}

body {
margin: 50px auto 0;
max-width: 800px;

font-family: "Expletus Sans", sans-serif;
}

li {

	width: 50%;
	float: left;
	list-style-type: none;

	padding-right: 5.3333333%;
}

li:nth-child(even) { margin-bottom: 5em;}

h2 {
	margin: 0 0 1.5em;
	border-bottom: 1px solid #ccc;

	padding: 0 0 .25em;
}

/* Styling an indeterminate progress bar */

progress:not(value) {
	/* Add your styles here. As part of this walkthrough we will focus only on determinate progress bars. */
}

/* Styling the determinate progress element */

progress[value] {
	/* Get rid of the default appearance */
	appearance: none;

	/* This unfortunately leaves a trail of border behind in Firefox and Opera. We can remove that by setting the border to none. */
	border: none;

	/* Add dimensions */
	width: 50%; height: 20px;

	/* Although firefox doesn't provide any additional pseudo class to style the progress element container, any style applied here works on the container. */
	  background-color: whiteSmoke;
	  border-radius: 3px;
	  box-shadow: 0 2px 3px rgba(0,0,0,.5) inset;

	/* Of all IE, only IE10 supports progress element that too partially. It only allows to change the background-color of the progress value using the 'color' attribute. */
	color: royalblue;

  position: fixed;
  top: 40%;
  left: 25%;
	margin: 0 0 1.5em;
}

/*
Webkit browsers provide two pseudo classes that can be use to style HTML5 progress element.
-webkit-progress-bar -> To style the progress element container
-webkit-progress-value -> To style the progress element value.
*/

progress[value]::-webkit-progress-bar {
	background-color: whiteSmoke;
	border-radius: 3px;
	box-shadow: 0 2px 3px rgba(0,0,0,.5) inset;
}

progress[value]::-webkit-progress-value {
	position: relative;

	background-size: 35px 20px, 100% 100%, 100% 100%;
	border-radius:3px;

	/* Let's animate this */
	animation: animate-stripes 5s linear infinite;
}

@keyframes animate-stripes { 100% { background-position: -100px 0; } }

/* Let's spice up things little bit by using pseudo elements. */

progress[value]::-webkit-progress-value:after {
	/* Only webkit/blink browsers understand pseudo elements on pseudo classes. A rare phenomenon! */
	content: '';
	position: absolute;

	width:5px; height:5px;
	top:7px; right:7px;

	background-color: white;
	border-radius: 100%;
}

/* Firefox provides a single pseudo class to style the progress element value and not for container. -moz-progress-bar */

progress[value]::-moz-progress-bar {
	/* Gradient background with Stripes */
	background-image:
	-moz-linear-gradient( 135deg,
													 transparent,
													 transparent 33%,
													 rgba(0,0,0,.1) 33%,
													 rgba(0,0,0,.1) 66%,
													 transparent 66%),
    -moz-linear-gradient( top,
														rgba(255, 255, 255, .25),
														rgba(0,0,0,.2)),
     -moz-linear-gradient( left, #09c, #f44);

	background-size: 35px 20px, 100% 100%, 100% 100%;
	border-radius:3px;

	/* Firefox doesn't support CSS3 keyframe animations on progress element. Hence, we did not include animate-stripes in this code block */
}

/* Fallback technique styles */
.progress-bar {
  position: fixed;
  top: 25%;
  left: 25%;
	background-color: whiteSmoke;
	border-radius: 3px;
	box-shadow: 0 2px 3px rgba(0,0,0,.5) inset;

	/* Dimensions should be similar to the parent progress element. */
	width: 75%; height:20px;
}

.progress-bar span {
	background-color: royalblue;
	border-radius: 3px;

	display: block;
	text-indent: -9999px;
}

p[data-value] {

  position: relative;
}

/* The percentage will automatically fall in place as soon as we make the width fluid. Now making widths fluid. */

p[data-value]:after {
	content: attr(data-value) '%';
	position: absolute; right:0;
}

.html5::-webkit-progress-value,
.python::-webkit-progress-value  {
	/* Gradient background with Stripes */
	background-image:
	-webkit-linear-gradient( 135deg,
													 transparent,
													 transparent 33%,
													 rgba(0,0,0,.1) 33%,
													 rgba(0,0,0,.1) 66%,
													 transparent 66%),
    -webkit-linear-gradient( top,
														rgba(255, 255, 255, .25),
														rgba(0,0,0,.2)),
     -webkit-linear-gradient( left, #09c, #f44);
}

/* Similarly, for Mozillaa. Unfortunately combining the styles for different browsers will break every other browser. Hence, we need a separate block. */

.html5::-moz-progress-bar,
.php::-moz-progress-bar {
	/* Gradient background with Stripes */
	background-image:
	-moz-linear-gradient( 135deg,
													 transparent,
													 transparent 33%,
													 rgba(0,0,0,.1) 33%,
													 rgba(0,0,0,.1) 66%,
													 transparent 66%),
    -moz-linear-gradient( top,
														rgba(255, 255, 255, .25),
														rgba(0,0,0,.2)),
     -moz-linear-gradient( left, #09c, #f44);
}
.progress-value {
    padding: 0px 5px;
    line-height: 20px;
    margin-left: 5px;
    color: black;
    height: 18px;
    position: fixed;
    top: 38%;
    left: 40%;
}
  </style>


  </head>
  <body>

<div id="loading">

<progress id="pg" max="100" value="0" class="html5"/></progress>

  <span class="progress-value">0%</span>
  <!--<img id="loading-image" src="images/ajax_loader_blue_512.gif" alt="Loading..." />-->
</div>
    <div class="reformed-form">
      <h1>Inventory Report <small>Fill in form and submit</small></h1>
    	<form method="post" name="ShelfLister" id="ShelfLister" action="<?php echo 'https://' . $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']) . '/process_barcodes.php'; ?>" enctype="multipart/form-data">
    		<dl>
    			<dt>
    				<label for="flie">Barcode XLSX FIle:</label>
    			</dt>
    			<dd><input type="file" id="flie" class="required" name="file" accept=".xlsx" /></dd>
    		</dl>
    		<dl>
    			<dt>
    				<label for="cnType">Call Number<BR> Type</label>
    			</dt>
    			<dd>
    				<ul>
    					<li><input type="radio" class="required" id="cnType" name="cnType" value="lc" checked="checked" />
    						<label>LC</label>
    					</li>
    					<li><input type="radio" class="required" id="cnType" name="cnType" value="dewey" />
    						<label>Dewey</label>
    					</li>
    					<li><input type="radio" class="required" id="cnType" name="cnType" value="other" />
    						<label>Other</label>
    					</li>
    				</ul>
    						</dd>
    		</dl>
    		<dl>
    			<dt>
    				<label for="library">Library</label>
    			</dt>
    			<dd>
    				<select size="1" name="library" id="library" class="required"  onchange=AjaxFunction();>
              <?php
$ch = curl_init();
$url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/conf/libraries';
$queryParams = '?' . urlencode('lang') . '=' . urlencode('en') . '&' . urlencode('apikey') . '=' . ALMA_SHELFLIST_API_KEY;
curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$response = curl_exec($ch);
curl_close($ch);

$xml_result = simplexml_load_string($response);
// PARSE RESULTS
	foreach($xml_result->library as $library)
	{
echo "<option value=$library->code>$library->name</option>";
}
?>
    				</select>
    			</dd>
    		</dl>
    		<dl>
    			<dt>
    				<label for="location">Scan Location</label>
    			</dt>
    			<dd>
    				<select size="1" name="location" id="location" class="required">
    				</select>
    			</dd>
    		</dl>
    		<dl>
    			<dt>
    				<label for="itemType">Primary Item<BR> Type for Scanned Location</label>
    			</dt>
    			<dd>
    				<select size="1" name="itemType" id="itemType" class="required">
    					<option value="BOOK">Book</option>
    					<option value="PERIODICAL">Periodical</option>
              <option value="DVD">DVD</option>
    					<option value="THESIS">Thesis</option>
    				</select>
    			</dd>
    		</dl>
        <dl>
    			<dt>
    				<label for="itemType">Primary Policy<BR> Type for Scanned Location</label>
    			</dt>
    			<dd>
    				<select size="1" name="policy" id="policy" class="required">
    					<option value="core">Core</option>
    					<option value="reserve">Reserve</option>
              <option value="cont lit">Contemporary Lit</option>
    					<option value="media">Media</option>
    				</select>
    			</dd>
    		</dl>
        <dl>
    			<dt>
    				<label for="cnType">Only Report<BR>CN Order Problems?</label>
    			</dt>
    			<dd>
    				<ul>
    					<li><input type="radio" class="required" id="onlyOrder" name="onlyorder" value="false" checked="checked" />
    						<label>No</label>
    					</li>
    					<li><input type="radio" class="required" id="onlyOrder" name="onlyorder" value="true" />
    						<label>Yes</label>
    					</li>
    				</ul>
    						</dd>
    		</dl>
        <dl>
    			<dt>
    				<label for="cnType">Only Report<BR>Problems Other Than CN?</label>
    			</dt>
    			<dd>
    				<ul>
    					<li><input type="radio" class="required" id="onlyOrder" name="onlyother" value="false" checked="checked" />
    						<label>No</label>
    					</li>
    					<li><input type="radio" class="required" id="onlyOrder" name="onlyother" value="true" />
    						<label>Yes</label>
    					</li>
    				</ul>
    						</dd>
    		</dl>
        <dl>
    			<dt>
    				<label for="cnType">Report Only<BR> Problems?</label>
    			</dt>
    			<dd>
    				<ul>
    					<li><input type="radio" class="required" id="onlyProblems" name="onlyproblems" value="false" checked="checked" />
    						<label>No</label>
    					</li>
    					<li><input type="radio" class="required" id="onlyProblems" name="onlyproblems" value="true" />
    						<label>Yes</label>
    					</li>
    				</ul>
    						</dd>
    		</dl>
			<dl>
    			<dt>
    				<label for="cnType">Clear Cache?</label>
    			</dt>
    			<dd>
    				<ul>
    					<li><input type="radio" class="required" id="clearCache" name="clearCache" value="false" checked="checked" />
    						<label>No</label>
    					</li>
    					<li><input type="radio" class="required" id="clearCache" name="clearCache" value="true" />
    						<label>Yes</label>
    					</li>
    				</ul>
    						</dd>
    		</dl>
    		<div id="submit_buttons">
    			<input type="submit" name="submit"/>
    		</div>
    		</form>
    </div>
  </body>
</html>
