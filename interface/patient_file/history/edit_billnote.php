<?php
/**
 * Billing notes.
 *
 * Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */



 include_once("../../globals.php");
 include_once("$srcdir/log.inc");
 include_once("$srcdir/acl.inc");

 $feid = $_GET['feid'] + 0; // id from form_encounter table

 $info_msg = "";

if (!acl_check('acct', 'bill', '', 'write')) {
    die(htmlspecialchars(xl('Not authorized'), ENT_NOQUOTES));
}
// Sai custom code start
 // Added By Gangeya BUGID 8284
$user = sqlQuery("select id,username from users where username='".$_SESSION{"authUser"}."'");
$uname = $user['username'];
$user_id=$user['id'];

$todays_date=date('Y-m-d H:i:s');
// Sai custom code end
?>
<html>
<head>
<?php html_header_show();?>
<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>

<!-- Sai custom code start -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<!-- Modified by : Sonali
8710:  	Need delete Functionality for note on past encounter list.  -->
<script language="JavaScript">
function deleteConfirm(){
 
	if (confirm("Are you sure to delete this note permanently ?") == true) {
  		alert("Note successfully deleted.");
		//document.getElementById("myForm").submit();
		document.billnote.submit();
     	return true;
  	}
  	else{
  
  		return false;
  	} 
 
}



// code added for billing note BUG 10228-10229
function closeWin() {
	//alert("close");
    newWindow.close();   // Closes the new window
}
 


</script>
<style>
.flat-table {
  display: block;
  font-family: sans-serif;
  -webkit-font-smoothing: antialiased;
  font-size: 115%;
  overflow: auto;
  width: auto;
}
  
  th {
    background-color: #c9e6f7;
   
    font-weight: bold;
    padding: 20px 30px;
    text-align: center;
  }
  td {
    background-color: rgb(238, 238, 238);
    padding: 20px 30px;
  }
</style>

</head>

<body overflow: auto; padding: 5px">

<?php
if ($_POST['form_submit'] || $_POST['form_cancel']) {
    $fenote = trim($_POST['form_note']);
	$fenote =  htmlspecialchars($fenote, ENT_QUOTES);
    if ($_POST['form_submit']) {
        
  		//sqlStatement("UPDATE form_encounter SET billing_note = ? WHERE id = ?", array($fenote,$feid) );
		if(isset($_POST['action']) && $_POST['action']=='edit'){
		
			
			$row_id=$_POST['note_id'];
			
			
			$sql="update billing_notes set notes='$fenote', last_updated='$todays_date', last_user='$uname' where id=$row_id" ;
			sqlStatement($sql);
			
			// added for log into log table
			newEvent("note-update", $_SESSION["authUser"], $_SESSION["authProvider"], 'success',$sql);
		
		}
		else{
	
			$sql="INSERT into billing_notes(encounter,notes,user_id,username,created_date,last_updated,last_user) 		values($feid,'$fenote',$user_id,'$uname','$todays_date','$todays_date','$uname')" ;
			sqlStatement($sql);
			
			// added for log into log table
			newEvent("note-insert", $_SESSION["authUser"], $_SESSION["authProvider"], 'success',$sql);
		}
  	}
  	else 
  	{
    	$tmp = sqlQuery("SELECT notes FROM billing_notes WHERE encounter = ?", array($feid) );
	$fenote = $tmp['notes'];
        $fenote = $tmp['billing_note'];
    }

  // escape and format note for viewing
    $fenote = htmlspecialchars($fenote, ENT_QUOTES);
    $fenote = str_replace("\r\n", "<br />", $fenote);
    $fenote = str_replace("\n", "<br />", $fenote);
  	if (! $fenote) $fenote = '['. xl('Add') . ']';
  	echo "<script language='JavaScript'>\n";
  	echo " parent.closeNote($feid, '$fenote')\n";
  	echo "</script></body></html>\n";
 	// exit();
}


 /* Added by Sonali: Bug Id :8710  */
 
if(isset($_GET['trigger']) && $_GET['trigger']=='delete'){

	$row_id=$_GET['noteid'];
			
			
	$sql="delete from billing_notes where id=$row_id" ;
	sqlStatement($sql);
	
	// added for log into log table
	newEvent("note-delete", $_SESSION["authUser"], $_SESSION["authProvider"], 'success',$sql);
	
	print "<script language='JavaScript'>alert('Notes deleted')</script>";
    echo "<script language='JavaScript'>\n";
    echo " parent.closeNote($feid, '$fenote')\n";
    echo "</script></body></html>\n";
	//exit();
}


/*$tmp = sqlQuery("SELECT billing_note FROM form_encounter " .
" WHERE id = ?", array($feid) );
$fenote = $tmp['billing_note'];*/

$tmp = sqlStatement("SELECT * FROM billing_notes WHERE encounter =$feid ");
//$tmp1 = sqlFetchArray($tmp);

//$fenote = $tmp1['notes'];
?>
<h3 align="center">Billing Notes</h3>
<hr>
<form method="post" id="billnote" name="billnote" action='edit_billnote.php?feid=<?php echo htmlspecialchars($feid,ENT_QUOTES); ?>'>

	<table border='0' cellpadding="1"  class="text" width = "100%" align="center" class="flat-table">
		<tr>
        	<th>Date</th>
        	<th>User</th>
        	<th>Note</th>
            <?php if(acl_check('admin', 'superbill')){ ?>
        	<th colspan="2">Action</th>
            <?php } ?>
        	
   		</tr>



<!--Edited  by Gangeya : Bug ID 8284-->
<?php 
while($noterow = sqlFetchArray($tmp)){
	$id = $noterow['id'];
	$date =$noterow['created_date'];
	$user =$noterow['username'];
	$notes =$noterow['notes'];
	$html_id= "note".$id;
	
	
	echo "<tr>";
	echo "<td>$date</td>";
	echo "<td>$user</td>";
	echo "<td>$notes</td>";

	if(acl_check('admin', 'superbill')){
	
	echo "<td><a href='edit_billnote.php?trigger=edit&noteid=".htmlspecialchars( $id, ENT_QUOTES)."&feid=".htmlspecialchars( $feid, ENT_QUOTES)."&notes=".htmlspecialchars( $notes, ENT_QUOTES).
	  "' class='css_button_small iframe' title='Edit this note' ><span>". htmlspecialchars( xl('Edit'), ENT_NOQUOTES) ." </span></a>\n";
	  
	 
	  
	
	   
	echo "<td><a href='edit_billnote.php?trigger=delete&noteid=".htmlspecialchars( $id, ENT_QUOTES)."&feid=".htmlspecialchars( $feid, ENT_QUOTES).
	  "' class='css_button_small iframe' title='Delete this note'> <span>". htmlspecialchars( xl('Delete'), ENT_NOQUOTES) ."</span> </a>\n";
	  
	}

}


	
// echo $fenote ? nl2br(htmlspecialchars( $fenote, ENT_NOQUOTES)) : htmlspecialchars( xl('Add','','[',']'), ENT_NOQUOTES);

//echo "<input type='hidden' name='pre_note' id='pre_note' value='$fenote'>";
?>

</table>
<center>
<br>
<?php 
//print_r($_GET);

if(isset($_GET['noteid'])){
	$mynote=$_GET['notes'];
	$noteid=$_GET['noteid'];
	$action =$_GET['trigger'];
	
	
?>
<input type="hidden" name="note_id" value="<?php echo $noteid ;?>"></input>	
<input type="hidden" name="action" value="<?php echo $action ;?>"></input>	
<?php
}
else{

	$mynote='';
}

?>

<textarea rows='8' name='form_note' style='width:90%' id="textarea" required><?php echo $mynote?></textarea>
<p>
<input type='submit' name='form_submit' value='<?php echo htmlspecialchars( xl('Save'), ENT_QUOTES); ?>' "/>

<input type='submit' name='form_cancel' value='<?php echo htmlspecialchars( xl('Cancel'), ENT_QUOTES); ?>' onClick="window.close()"/>
<!-- Added by Sonali: Bug Id :8710  -->
<?php 
if(acl_check('admin', 'superbill')){
?>
<!--<input type='submit' name='form_delete' value='<?php echo htmlspecialchars( xl('Delete'), ENT_QUOTES); ?>' onClick="return deleteConfirm();"/>-->
<?php
}
?>
<!-- Sai custom code end -->
</center>
</form>
</body>
</html>
