<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");



$userID = $_SESSION['authUserID'];
if($_POST['form_refresh'] && !empty($_POST['encounters'])){

	$encounter_ids=$_POST['encounters'];
	$encounter_status= $_POST['encounter_status'];
	
	//$new_string=preg_replace( "/\n/", ",", $encounter_ids );
	//$new_string2=rtrim($new_string,',');
	
	//echo $new_string2;
	
	$query= "update form_encounter SET claim_status_id=$encounter_status,modified_date=now(),modified_by=$userID where encounter IN($encounter_ids)";
	
	//echo $query;
	$result=sqlStatement($query);
	if($result){
		echo "<span style='color:blue'>";
		echo "Claims status changed successfully.";
		echo "<span>";
	}
	else{
		echo "Wrong input..query failed..claim status not changed.";
	} 


}

?>

<script>
function validateForm() {
    var str = document.getElementById("encounters").value;
                
    var res = str.match(/^\d+(?:,\d+)*$/);
                if(res == null){
                                alert('Please Enter Valid Encounters.');
                                return false;

                }
				
	var status = document.getElementById("encounter_status").value;
	if(status == 0){
		alert("Please select status");
		return false;
	}
	
	var sel = document.getElementById("encounter_status");
	var text= sel.options[sel.selectedIndex].text;
	
	
    if (confirm("Are you sure to change status to " +text) == true) {
       return true;
    } else {
       return false;
    }
	
	
	
            
        
}
</script>
<style type="text/css">
	.text-area{
		  background-color:#FFFFFF;
		  border:1px solid black;
		  width:100%;
		  height:100px;
		  overflow:auto;
		  text-align:left;
}

	ul{

		color:red;
		font-size: 11 px;
		
	}
</style>
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

<ul>
	<li>Instructions</li>	
	<ul>
		<li>Please enter comma separated encounter ids in encounters box.</li>
		<li>Remove comma from last encouter</li>
		<li>Do not enter any charectors (A-Z).</li>
	</ul>
</ul>


<br>
<form method='post' action='status_update.php' onsubmit="return validateForm()">


<table border='0' cellpadding='3'>
	<tr>
  		<td>
  			<label>Encounters</label>
  		</td>
  
  		<td>
  			  <textarea id='encounters' name="encounters" class="text-area"></textarea>
         </td>
	</tr>
 
	<tr>
 		<td><label>Status</td>
 		<td>
 		<select name="encounter_status" id="encounter_status"  ?>			 > 
		<?php

		$qsql = sqlStatement("SELECT id, status,iphone_status FROM claim_status");
  		echo "<option value='0'>SELECT</options>";
		while ($statusrow = sqlFetchArray($qsql)) {
 			$claim_status = $statusrow['status'];
 			$claim_status_id = $statusrow['id'];
 
 			echo "<option value='$claim_status_id'>$claim_status</option>";
	 	} 
?>
</select>
		</td>
 	</tr>

	<tr>
		<td> 
				
				<input type='submit'  name='form_refresh' value="<?php xl('Update Status','e') ?>"></td>
    	<td></td>

	</tr>

 	<tr>
  		<td height="1"></td>
        <td></td>
 	</tr>

</table>

</form>


</body>
</html>

