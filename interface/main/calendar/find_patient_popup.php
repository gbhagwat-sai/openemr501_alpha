<?php
/* Copyright (C) 2005-2007 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

/*
 *
 * This popup is called when adding/editing a calendar event
 *
 */

include_once('../../globals.php');
include_once("$srcdir/patient.inc");

use OpenEMR\Core\Header;

$info_msg = "";

 // If we are searching, search.
 //
if ($_REQUEST['searchby'] && $_REQUEST['searchparm']) {
    $searchby = $_REQUEST['searchby'];
    $searchparm = trim($_REQUEST['searchparm']);

    if ($searchby == "Last") {
        $result = getPatientLnames("$searchparm", "*");
    } elseif ($searchby == "Phone") {                  //(CHEMED) Search by phone number
        $result = getPatientPhone("$searchparm", "*");
    } elseif ($searchby == "ID") {
        $result = getPatientId("$searchparm", "*");
    } elseif ($searchby == "DOB") {
        $result = getPatientDOB(DateToYYYYMMDD($searchparm), "*");
    } elseif ($searchby == "SSN") {
        $result = getPatientSSN("$searchparm", "*");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['common', 'datetime-picker', 'opener']); ?>
<title><?php echo htmlspecialchars(xl('Patient Finder'), ENT_NOQUOTES); ?></title>

<style>
form {
    padding: 0px;
    margin: 0px;
}
#searchCriteria {
    text-align: center;
    width: 100%;
    /*font-size: 0.8em;*/
    background-color: #ddddff;
    font-weight: bold;
    padding: 3px;
}
#searchResultsHeader {
    width: 100%;
    /*background-color: #fff;*/
    border-collapse: collapse;
}
#searchResultsHeader th {
    /*font-size: 0.7em;*/
}
#searchResults {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
    overflow: auto;
}

#searchResults tr {
    cursor: hand;
    cursor: pointer;
}
#searchResults td {
    /*font-size: 0.7em;*/
    border-bottom: 1px solid #eee;
}
.oneResult { }
.billing { color: red; font-weight: bold; }

/* for search results or 'searching' notification */
#searchstatus {
    font-size: 0.8em;
    font-weight: bold;
    padding: 1px 1px 10px 1px;
    font-style: italic;
    color: black;
    text-align: center;
}
.noResults { background-color: #ccc; }
.tooManyResults { background-color: #fc0; }
.howManyResults { background-color: #9f6; }
#searchspinner {
    display: inline;
    visibility: hidden;
}

/* highlight for the mouse-over */
.highlight {
    background-color: #336699;
    color: white;
}
</style>

<!-- ViSolve: Verify the noresult parameter -->
<?php
if (isset($_GET["res"])) {
    echo '
<script language="Javascript">
			// Pass the variable to parent hidden type and submit
			opener.document.theform.resname.value = "noresult";
			opener.document.theform.submit();
			// Close the window
			window.self.close();
</script>';
}
?>
<!-- ViSolve: Verify the noresult parameter -->

<script language="JavaScript">
//	phone added by sangram for bug 8996
 function selpid(pid, lname, fname, dob,phone) { // Sai custom code 
  if (opener.closed || ! opener.setpatient)
   alert("<?php echo htmlspecialchars(xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
  else
//	phone added by sangram for bug 8996
   opener.setpatient(pid, lname, fname, dob,phone);// Sai custom code 
  dlgclose();
  return false;
 }

</script>

</head>

<body class="body_top">
<div class="container-responsive">
<div id="searchCriteria">
<form class="form-inline" method='post' name='theform' id="theform" action='find_patient_popup.php?<?php if (isset($_GET['pflag'])) {
    echo "pflag=0";
} ?>'>
    <?php echo htmlspecialchars(xl('Search by:'), ENT_NOQUOTES); ?>
   <select name='searchby' class="input-sm">
    <option value="Last"><?php echo htmlspecialchars(xl('Name'), ENT_NOQUOTES); ?></option>
    <!-- (CHEMED) Search by phone number -->
    <option value="Phone"<?php if ($searchby == 'Phone') {
        echo ' selected';
} ?>><?php echo htmlspecialchars(xl('Phone'), ENT_NOQUOTES); ?></option>
    <option value="ID"<?php if ($searchby == 'ID') {
        echo ' selected';
} ?>><?php echo htmlspecialchars(xl('ID'), ENT_NOQUOTES); ?></option>
    <option value="SSN"<?php if ($searchby == 'SSN') {
        echo ' selected';
} ?>><?php echo htmlspecialchars(xl('SSN'), ENT_NOQUOTES); ?></option>
    <option value="DOB"<?php if ($searchby == 'DOB') {
        echo ' selected';
} ?>><?php echo htmlspecialchars(xl('DOB'), ENT_NOQUOTES); ?></option>
   </select>
    <?php echo htmlspecialchars(xl('for:'), ENT_NOQUOTES); ?>
   <input type='text' class="input-sm" id='searchparm' name='searchparm' size='12' value='<?php echo htmlspecialchars($_REQUEST['searchparm'], ENT_QUOTES); ?>'
    title='<?php echo htmlspecialchars(xl('If name, any part of lastname or lastname,firstname'), ENT_QUOTES); ?>'>
   &nbsp;
   <input type='submit' id="submitbtn" value='<?php echo htmlspecialchars(xl('Search'), ENT_QUOTES); ?>'>
   <div id="searchspinner"><img src="<?php echo $GLOBALS['webroot'] ?>/interface/pic/ajax-loader.gif"></div>
</form>
</div>

<?php if (! isset($_REQUEST['searchparm'])) : ?>
<div id="searchstatus"><?php echo htmlspecialchars(xl('Enter your search criteria above'), ENT_NOQUOTES); ?></div>
<?php elseif (count($result) == 0) : ?>
<div id="searchstatus" class="noResults"><?php echo htmlspecialchars(xl('No records found. Please expand your search criteria.'), ENT_NOQUOTES); ?>
<br>
<!--VicarePlus :: If pflag is set the new patient create link will not be displayed -->
<a class="noresult" href='find_patient_popup.php?res=noresult' 
    <?php
    if (isset($_GET['pflag']) || (!acl_check('patients', 'demo', '', array('write','addonly')))) {
        ?> style="display:none;" 
        <?php
    }
    ?>  >
    <?php echo htmlspecialchars(xl('Click Here to add a new patient.'), ENT_NOQUOTES); ?></a>
</div>
<?php elseif (count($result)>=100) : ?>
<div id="searchstatus" class="tooManyResults"><?php echo htmlspecialchars(xl('More than 100 records found. Please narrow your search criteria.'), ENT_NOQUOTES); ?></div>
<?php elseif (count($result)<100) : ?>
<div id="searchstatus" class="howManyResults"><?php echo htmlspecialchars(count($result), ENT_NOQUOTES); ?> <?php echo htmlspecialchars(xl('records found.'), ENT_NOQUOTES); ?></div>
<?php endif; ?>

<?php if (isset($result)) : ?>

<table class="table table-condensed">
<thead id="searchResultsHeader" class="head">
 <tr>
  <th class="srName"><?php echo htmlspecialchars(xl('Name'), ENT_NOQUOTES); ?></th>
  <th class="srPhone"><?php echo htmlspecialchars(xl('Phone'), ENT_NOQUOTES); ?></th> <!-- (CHEMED) Search by phone number -->
  <th class="srSS"><?php echo htmlspecialchars(xl('SS'), ENT_NOQUOTES); ?></th>
  <th class="srDOB"><?php echo htmlspecialchars(xl('DOB'), ENT_NOQUOTES); ?></th>
  <th class="srID"><?php echo htmlspecialchars(xl('ID'), ENT_NOQUOTES); ?></th>
  <th class="srDischarge"><?php echo htmlspecialchars( xl('Discharged'), ENT_NOQUOTES); ?></th> <!-- Sai custom code -->
 </tr>
</thead>
<tbody id="searchResults">
<?php
foreach ($result as $iter) {
    $iterpid   = $iter['pid'];
    $iterlname = $iter['lname'];
    $iterfname = $iter['fname'];
    $itermname = $iter['mname'];
    $iterdob   = $iter['DOB'];

    // Sai custom code start 
	$iterphone = $iter['phone_home'];
	if($iter['isDischarged']){
		$iterdischarged = $iter['isDischarged'];
	}
	else{
			$iterdischarged = 'NO';	
	}
	//  phone_home added by sangram for bug 8996<?php 
 // Sai custom code end 
    // If billing note exists, then it gets special coloring and an extra line of output
    // in the 'name' column.
    $trClass = "oneresult";
    if (!empty($iter['billing_note'])) {
        $trClass .= " billing";
    }
    // Sai custom code start 
    if($iterdischarged == 'YES'){
		echo " <tr style='cursor:not-allowed;background-color:#FF3737' class='".$trClass2."' id='" .
			htmlspecialchars( $iterpid."~".$iterlname."~".$iterfname."~".$iterdob."~".$iterphone."~".$iterdischarged, ENT_QUOTES) . "'>";
    }
    else{
		echo " <tr class='".$trClass."' id='" .
			htmlspecialchars( $iterpid."~".$iterlname."~".$iterfname."~".$iterdob."~".$iterphone."~".$iterdischarged, ENT_QUOTES) . "'>";
    }
	// Sai custom code end 
    echo "  <td class='srName'>" . htmlspecialchars($iterlname.", ".$iterfname." ".$itermname, ENT_NOQUOTES);
    if (!empty($iter['billing_note'])) {
        echo "<br>" . htmlspecialchars($iter['billing_note'], ENT_NOQUOTES);
    }

    echo "</td>\n";
    echo "  <td class='srPhone'>" . htmlspecialchars($iter['phone_home'], ENT_NOQUOTES) . "</td>\n"; //(CHEMED) Search by phone number
    echo "  <td class='srSS'>" . htmlspecialchars($iter['ss'], ENT_NOQUOTES) . "</td>\n";
    echo "  <td class='srDOB'>" . htmlspecialchars($iter['DOB'], ENT_NOQUOTES) . "</td>\n";
    echo "  <td class='srID'>" . htmlspecialchars($iter['pubpid'], ENT_NOQUOTES) . "</td>\n";
    echo "  <td class='srDischarge'>" . htmlspecialchars( $iterdischarged, ENT_NOQUOTES) . "</td>\n"; // Sai custom code 
    echo " </tr>";
}
?>
</tbody>
</table>

<?php endif; ?>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(function(){
    $("#searchparm").focus();
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").click(function() { SelectPatient(this); });
    //ViSolve
    $(".noresult").click(function () { SubmitForm(this);});

    //$(".event").dblclick(function() { EditEvent(this); });
    $("#theform").submit(function() { SubmitForm(this); });

    $('[name="searchby"').on('change', function () {
        if($(this).val() === 'DOB'){
            $('#searchparm').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        } else {
            $('#searchparm').datetimepicker("destroy");
        }
    });

});

// show the 'searching...' status and submit the form
var SubmitForm = function(eObj) {
    $("#submitbtn").css("disabled", "true");
    $("#searchspinner").css("visibility", "visible");
    return true;
}


// another way to select a patient from the list of results
// parts[] ==>  0=PID, 1=LName, 2=FName, 3=DOB
var SelectPatient = function (eObj) {
    objID = eObj.id;
    var parts = objID.split("~");
    return selpid(parts[0], parts[1], parts[2], parts[3],parts[4]); <!-- // Sai custom code -->
	//part 4 added by sangram for bug 8996
}

</script>

</div>
</body>
</html>
