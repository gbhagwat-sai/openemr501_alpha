<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.


require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/amc.php");
?>
<html>

<head>

<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>



<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script language="javascript">
//Gangeya : To add Shortcut functionality
document.onkeydown = function(evt) 
{
    masterkeypress(evt);
	evt = evt || window.event; // because of Internet Explorer quirks...
	k = evt.which || evt.charCode || evt.keyCode; // because of browser differences...
	if (k == 88 && evt.altKey && !evt.ctrlKey && !evt.shiftKey) 
	{
		deleteme();
	}
//	if (k == 69 && evt.altKey && evt.ctrlKey && !evt.shiftKey) 
//	{
//		alert("Hiii..!!!");
//	}
}

</script>
<script language="JavaScript">

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?encounterid=<?php echo $encounter; ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleter.php window on a successful delete.
 function imdeleted(EncounterId) {
<?php if ($GLOBALS['concurrent_layout']) { ?>
  top.window.parent.left_nav.removeOptionSelected(EncounterId);
  top.window.parent.left_nav.clearEncounter();
<?php } else { ?>
  top.restoreSession();
  top.Title.location.href = '../patient_file/encounter/encounter_title.php';
  top.Main.location.href  = '../patient_file/encounter/patient_encounter.php?mode=new';
<?php } ?>
 }

function newPopup(url) 
{
    popupWindow = window.open(url,'popUpWindow', 'height=700, width=800, left=10, top=10, resizable=yes, scrollbars=no, toolbar=no, menubar=no, location=no, directories=no, status=yes')
}

</script>

<style type="text/css">
    div.tab {
        min-height: 50px;
        padding:8px;
    }

    div.form_header_controls {
        float:left;margin-bottom:2px;
    }

    div.form_header {
        float:left;
        margin-left:6px;
    }
</style>

</head>
<?php
$hide=1;
require_once("$incdir/patient_file/encounter/new_form.php");
?>
<body class="body_top">

<div id="encounter_forms">

<?php
$dateres = getEncounterDateByEncounter($encounter);
$encounter_date = date("Y-m-d",strtotime($dateres["date"]));
?>

<div style='float:left'>

<?php

if (is_numeric($pid)) 
{
	echo '<strong>';
    $result = getPatientData($pid, "fname,lname,squad");
    echo htmlspecialchars( xl('Upload Progress Notes for ') . $result['fname'] . " " . $result['lname'] );
	echo '</strong>';
}
$uss = $_SESSION['authUser'];

$userQuery = "Select id, fname,mname,lname from users where username = '$uss'";
$userData = sqlQuery($userQuery);
$userID = $userData['id'];

?> 
</div>
<script type="text/javascript" src="http://code.jquery.com/jquery-2.0.0.js"></script>
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 


<script type="text/javascript">

$(document).ready(
	function()
	{
		$('#btnUpload').attr('disabled',true);
		//$('input:submit').attr('disabled',true);
		$('input:file').change(
		function()
		{
			if ($(this).val())
			{
				$('input:submit').removeAttr('disabled'); 
			}
			else 
			{
				$('input:submit').attr('disabled',true);
			}
	});
});

function un_check()
{
	for (var i = 0; i < document.Upload_PN.elements.length; i++) 
	{
		var e = document.Upload_PN.elements[i];
		if ((e.name != 'allbox') && (e.type == 'checkbox'))
		{
			e.checked = document.Upload_PN.allbox.checked;
		}
	}
}

function rejectPN()
{
	var retVal = prompt("Reason for rejection (max 120 char)");
	
	if(retVal != null){
		if(retVal.length <= '120')
		{
			document.getElementById("reasonLabel").value = retVal;
		}
		else
		{
			alert("Please enter less than 120 characters !");
			return false;
		}
	}
	else 
		return false;
}

</script>

<form name='new_patient' method='post' action="upload_pn.php">&nbsp;&nbsp;
<input  name="Upload PN" type="submit" value="Back to Encounter">
</form>
<br><br>

<?php 
/////////////////////////////////////////////////////////////////

?>

<?php
if(isset($_FILES['files']))
{
	$errors= array();
	foreach($_FILES['files']['tmp_name'] as $key => $tmp_name )
	{
		$file_name = $_FILES['files']['name'][$key];
		$file_size =$_FILES['files']['size'][$key];
		$file_tmp =$_FILES['files']['tmp_name'][$key];
		$file_type=$_FILES['files']['type'][$key];	
		$user = $rowUser['0'];
		$date = date('Y-m-d H:i:s');
		$desired_dir="uploads/" . $pid . "/" . $encounter . "/";
		
		if(!file_exists($desired_dir))
		{
			mkdir($desired_dir,0755, true);		// Create directory if it does not exist
		}
		
		if($file_size > 2097152)
		{
			$errors[]='File size must be less than 2 MB';
		}				
	
		if(empty($errors)==true)
		{
			if(file_exists($desired_dir.$file_name)==false)
			{
				$location = $desired_dir.$file_name;
				
				$query="INSERT INTO progress_notes (id, pid, encounter, date_uploaded, url, file_size, file_type, user_id,status,file_name) VALUES ('', '$pid', '$encounter', '$date', '$location', '$file_size', '$file_type', '$userID','1','$file_name')";
				move_uploaded_file($file_tmp,$location);
				sqlStatement($query);
				
				//$query_update_FE = "update form_encounter set pn_status = '1' where encounter = '$encounter'";
				//sqlStatement($query_update_FE);
			}
			else
			{	
				//rename the file if another one exist
				$location=$desired_dir.$file_name;					
				$path_parts = pathinfo($location);
				$f_name = $path_parts['filename'];
				$f_ext = $path_parts['extension'];
				
				$location1 = $desired_dir.$f_name.date('m-d-Y').".".$f_ext;
				$new_file =  $f_name.date('m-d-Y').".".$f_ext;
				
				$query="INSERT INTO progress_notes (id, pid, encounter, date_uploaded, url, file_size, file_type, user_id,status,file_name) VALUES ('', '$pid', '$encounter', '$date', '$location1', '$file_size', '$file_type', '$userID','1','$new_file')";
				rename($file_tmp,$location1) ;		
				sqlStatement($query);		
				
				//$query_update_FE = "update form_encounter set pn_status = '1' where encounter = '$encounter'";
				//sqlStatement($query_update_FE);
			}	
			
		}
		else
		{
				print_r($errors);
		}
	}
	echo "<script> alert('Document/s uploaded successfully !'); </script>";		
}


?>


<form action="upload_pn.php" enctype="multipart/form-data" method="POST">
	&nbsp;&nbsp;
    <label>Select file/s to upload : </label>
	<input type="file" name="files[]" multiple/>
	<input id="btnUpload" type="submit" name="Upload" value="Upload"/>
</form>
    
<?php 


$sql = "SELECT id, pid, encounter, url, file_size, file_type, file_name, DATE_FORMAT(date_uploaded,'%m/%d/%Y %h:%m:%s') as date_uploaded, activity,  status FROM progress_notes WHERE pid = '$pid' AND encounter = '$encounter' AND is_deleted = '0' and activity = '1'";

/*$sql = "SELECT id, pid, encounter, url, file_size, file_type, file_name, DATE_FORMAT(date_uploaded,'%m/%d/%Y %h:%m:%s') as date_uploaded, activity FROM progress_notes WHERE pid = '$pid' AND encounter = '$encounter' AND is_deleted = '0' and activity = '1'";*/
$data = sqlStatement($sql);



$count = sqlNumRows($data);

?>


<?php
if($count == 0)
{
	echo '<table>';
	echo '<tr>';
	echo '<td> No uploaded documents found </td>';
	echo '</tr>';
	echo '</table>';
}
else
{
 ?>
<table width="" border="0" cellspacing="1" cellpadding="10">
<tr>
<td>

<form name="Upload_PN" method="post" action="upload_pn.php">

<table width="" border="0" cellpadding="3" cellspacing="2">

    <tr>
    <td align="center" bgcolor="#6CAEFF" >
    	<input type="checkbox" id="allbox" name="allbox" title="Select or Deselct ALL" onClick="un_check();" readonly checked /></td>    
    <td align="center" bgcolor="#6CAEFF"><strong>File Name</strong></td>
    <td align="center" bgcolor="#6CAEFF"><strong>Date Uploaded</strong></td>
    <td align="center" bgcolor="#6CAEFF"><strong>Status</strong></td>
    <td align="center" bgcolor="#6CAEFF"><strong>Link</strong></td>
    </tr>
    
<?php 

	
	$tr = '<tr bgcolor="#CEE4FF">';

	while($row=sqlFetchArray($data))
    {
    	echo "$tr";?>
		<td bgcolor="#CEE4FF" style="padding:5; "><input name='checkbox[]' type='checkbox' id='checkbox[]' readonly checked value="<?php echo $row['id']; ?>" ></td>
<?php
		$url = "uploads/" . $pid . "/" . $encounter . "/".$row['file_name'];
	
		echo "<td style='padding:5'><center />".$row['file_name']."</td>";
		echo "<td style='padding:5'><center />".$row['date_uploaded']."</td>";
		echo "<td style='padding:5'><center />".$row['status']."</td>";
		echo "<td  style='padding:6'><center />";
		echo '<a href="'.$url.'" target="_blank" >';
		echo 'Click to View';
		//echo '<img src='.$row['file_name'].' alt='.$row['file_name'].' style="display:none" onLoad="window.print();" style="width:100%;height:100%;">';
		echo '</a>';
		echo "</td>"; 	
		echo "</tr>";
	
		$rows[] = $url.$row['id'];		
    }
}
?>
<tr>
<td colspan="5" align="center">&nbsp;</td>
</tr>
</table>

<table>
    <tr>
      <td colspan="5">
      <center>
   
  <?php 
  if (acl_check('admin', 'super') && $count != 0)
  {
  ?>   
		<input name="delete" type="submit" id="delete" value="Reject" onClick="return rejectPN()" />&nbsp;
        <input type="hidden" id="reasonLabel" name="reasonLabel" />
  <?php 
  }
  else 
  {
  ?>
		<input name="delete" type="submit" id="delete" value="Reject" disabled/>&nbsp;
  <?php 
  }
  ?>
      <input name="verify" type="submit" id="verify" value="Verify" />&nbsp;      
      <input name="trasmit" type="submit" id="trasmit" value="Transmit" />
	</center>
      </td>
    </tr>
</table>

</form>


</td>
</tr>


<tr>
<td style="padding-bottom:5px">
<!--<form name="Upload_PN" method="post" action="upload_pn.php">
    <br/>
  <?php 
  if ($count == 0)
  {
  ?>  
    <input name="request" type="submit" id="request" value="Request" />&nbsp;
  <?php
  } else {
  ?>
	<input name="request" type="submit" id="request" value="Request" disabled />&nbsp;
  <?php
  }
  ?>
  
</form>
-->
</td>
</tr>

</table>

<center>
</center>

<?php

// Module to send document request to user.

/*if($request)
{
	$sql = "UPDATE form_encounter SET pn_status = '2' WHERE encounter = '$encounter'";
	$data = sqlStatement($sql);		
	
	// if successful redirect to delete_multiple.php		
	if($data)
	{
		echo "<meta http-equiv=\"refresh\" content=\"0;URL=forms.php\">";
		echo "<script> alert('Document Requested!'); </script>";	
	}
}*/

// Module to set status and modified date after rejecting document.

if(isset($_POST['delete']) && $_POST['delete'] == 'Reject' )
{
	
	$date = date('Y-m-d H:i:s');
	$user = $rowUser['0'];
	$reason = $_POST['reasonLabel'];
	
	for($i=0;$i < count($_POST["checkbox"]); $i++)
	{
		
		$del_id = $_POST["checkbox"][$i];
		$sql = "UPDATE progress_notes SET activity = '0', date_rejected = '$date', Status = '3', rejected_by = '$userID' WHERE id='$del_id'";

		$data1 =  sqlStatement($sql);
		//update form_encounter
	//	$query_update_FE = "update form_encounter set pn_status = '3', pn_reason = '$reason' where encounter = '$encounter'";
		//$data2 = sqlStatement($query_update_FE);
	}
	// if successful redirect to delete_multiple.php
	if($data1 && $data2)
	{
		echo "<meta http-equiv=\"refresh\" content=\"0;URL=upload_pn.php\">";
	}
}

// Module to set status and modified date after verification.
if(isset($_POST['verify']) && $_POST['verify'] == 'Verify' )
{

	$date = date('Y-m-d H:i:s');
	$user = $rowUser['0'];
	
	for($i=0;$i < count($_POST["checkbox"]); $i++)
	{
		$verify_id = $_POST["checkbox"][$i];
		$sql = "UPDATE progress_notes SET status = '4', date_verified = '$date', verified_by = '$userID' WHERE id='$verify_id'";
		$data1 = sqlStatement($sql);	
		//update form_encounter
		//$query_update_FE = "update form_encounter set pn_status = '4' where encounter = '$encounter'";
		//$data2 = sqlStatement($query_update_FE);	
	}	
	// if successful redirect to delete_multiple.php		
	if($data1 && $data2)
	{
		echo "<meta http-equiv=\"refresh\" content=\"0;URL=upload_pn.php\">";
	}
}

// Module to set status and modified date after Transmission.
if(isset($_POST['trasmit']) && $_POST['trasmit'] == 'Transmit' )
{
	$date = date('Y-m-d H:i:s');
	$user = $rowUser['0'];
	
	for($i=0;$i < count($_POST["checkbox"]); $i++)
	{
		$transmit_id = $_POST["checkbox"][$i];
		$sql = "UPDATE progress_notes SET status = '5', date_transmitted ='$date', transmitted_by = '$userID' WHERE id='$transmit_id'";
		$data1 = sqlStatement($sql);	
		//update form_encounter
		//$query_update_FE = "update form_encounter set pn_status = '5' where encounter = '$encounter'";
		//$data2 = sqlStatement($query_update_FE);	
	}	
	// if successful redirect to delete_multiple.php		
	if($data1 && $data2)
	{
		echo "<meta http-equiv=\"refresh\" content=\"0;URL=upload_pn.php\">";
	}
}

?>
 

</div> 
</body>
</html>
