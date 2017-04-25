<!DOCTYPE html>
<?php require("login.php");?>
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

  </script>
  <!-- end necessary reformed js -->

  <!-- The following style code is NOT necessary; just some styling to center the form on the page and set the default font size -->
  <style type="text/css">
  	body { font: 12px/14px Arial;}
  	div.reformed-form { width: 550px; margin: 5px auto;}
  </style>


  </head>
  <body>
    <div class="reformed-form">
      <h1>Inventory Report <small>Fill in form and submit</small></h1>
    	<form method="post" name="ShelfLister" id="ShelfLister" action="<?php echo 'https://' . $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']) . '/process_barcodes_csv.php'; ?>" enctype="multipart/form-data">
    		<dl>
    			<dt>
    				<label for="flie">Barcode CSV FIle:</label>
    			</dt>
    			<dd><input type="file" id="flie" class="required" name="file" accept=".csv" /></dd>
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
    				<select size="1" name="library" id="library" class="required">
    					<option value="hsse">HSSE</option>
    					<option value="ugrl">Undergrad</option>
    				</select>
    			</dd>
    		</dl>
    		<dl>
    			<dt>
    				<label for="location">Location</label>
    			</dt>
    			<dd>
    				<select size="1" name="location" id="location" class="required">
    					<option value="hss2">Hsse 2nd floor</option>
    					<option value="hss3">Hsse 3rd floor</option>
    				</select>
    			</dd>
    		</dl>
    		<dl>
    			<dt>
    				<label for="itemType">Primary Item<BR> Type</label>
    			</dt>
    			<dd>
    				<select size="1" name="itemType" id="itemType" class="required">
    					<option value="book">Book</option>
    					<option value="periodical">Periodical</option>
    				</select>
    			</dd>
    		</dl>
        <dl>
          <dt>
            <label for="itemType">Primary Policy<BR> Type</label>
          </dt>
          <dd>
            <select size="1" name="policy" id="policy" class="required">
              <option value="core">Core</option>
              <option value="reserve">Reserve</option>
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
    		<div id="submit_buttons">
    			<input type="submit" name="submit"/>
    		</div>
    		</form>
    </div>
  </body>
</html>
