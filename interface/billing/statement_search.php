<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once($GLOBALS['OE_SITE_DIR'] . "/statement.inc.php");

//number_format($number, 2, '.', '')
   $site_id = $_SESSION['site_id'];
$statement_path =$web_root."/sites/".$site_id ."/patient_statements/";



function thisLineItem($row) {

  global $statement_path;

  $statement_date = date("m/d/Y h:i:s A", strtotime($row['date']));
  $dob = date("m/d/Y", strtotime($row['dob']));
  $pdf_file = $row['filename'];
  $pdf_path =$statement_path.$pdf_file;
  
  $balance1=sprintf("%01.2f",$row['balance']);
  
  if ($_POST['form_csvexport']) {  
    echo '"' . addslashes($row['account_no' ]) . '",';
    echo '"' . addslashes($statement_date) . '",';
    echo '"' . addslashes($row['patient_name' ]) . '",';
    echo '"' . addslashes($dob) . '",';
    echo '"' . addslashes($row['balance' ]) . '",';
    echo '"' . addslashes($row['pagecount' ]) . '",';
    echo '"' . addslashes($row['isBillable' ]) . '",' . "\n";
  }
  
  else{
?>
  <tr bgcolor="#CEE4FF">
   
    <td class="detail"><a href="<?php echo $pdf_path;?>"  target="_blank" title="View Statement"><?php echo $row['account_no']; ?></a></td>
    <td class="detail"><?php echo $statement_date; ?></td>
    <td class="detail"><?php echo $row['patient_name' ]; ?></td>
    <td class="detail"><?php echo $dob; ?></td>
    <td class="detail"><?php echo $balance1; ?></td>
    <td class="detail"><?php echo $row['pagecount' ]; ?></td>
    <td class="detail"><?php echo $row['isBillable' ]; ?></td>
    
  </tr>
<?php
  } 
} // end of function

if($_POST['form_search'] || $_POST['form_csvexport']){
  
    $form_date      = fixDate($_POST['form_from_date'], "");
    $form_to_date   = fixDate($_POST['form_to_date'], "");
    $form_name      = trim($_POST['form_name']);
    $form_extid      = trim($_POST['form_extid']);
    $insurance_id      = trim($_POST['insurance_id']);
    $isBillable      = trim($_POST['isBillable']);

  }
  else{
  
    $form_date      = fixDate($_GET['form_from_date'], "");
    $form_to_date   = fixDate($_GET['form_to_date'], "");
    $form_name      = trim($_GET['form_name']);
    $form_extid      = trim($_GET['form_extid']);
    $insurance_id      = trim($_GET['insurance_id']);
   $isBillable      = trim($_GET['isBillable']);  
  
}



if ($_POST['form_csvexport']) {
  
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=StatementReport.csv");
    header("Content-Description: File Transfer");
  
    // CSV headers:
    echo '"' . xl('Account No') . '",';
    echo '"' . xl('Statement Date') . '",';
    echo '"' . xl('Patient Name') . '",';
    echo '"' . xl('DOB') . '",';
    echo '"' . xl('Balance') . '",';
    echo '"' . xl('Page Count') . '",';
    echo '"' . xl('Is Billable') . '"' . "\n";
 
        
}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Printed Statement Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Printed Statement','e')?></h2>


<form method='post' action='statement_search.php' name="Form" onsubmit="return validateForm()">

<table border='0' cellpadding='3'>

<tr bgcolor='#ddddff'>  
    <td>
        <label><strong> <?php xl('Patient Name:','e'); ?></strong></label>
      
           <input type='text' name='form_name' id='form_name' size='20' value='<?php echo $form_name; ?>'
            title='<?php xl("Any part of the patient name, or \"last,first\", or \"X-Y\"","e"); ?>'>
        </td>
  
      <td>
        <label><strong><?php xl('External ID:','e'); ?></strong></label>
      
           <input type='text' name='form_extid' id='form_extid' size='10' value='<?php echo $form_extid; ?>'
            title='<?php xl("External ID","e"); ?>'>
      </td>
      <!-- code added for addtional PHP invoices -->
      <td>
      <label><strong> <?php xl('Is Billable:','e'); ?></strong></label>
      <select name="isBillable" id="isBillable" value='<?php echo $_POST['isBillable']; ?>'>
        <option value="0" >Select</option>
        <option value="Yes" <?php if($isBillable=="Yes") echo "selected";  ?>>Yes</option>
        <option value="No" <?php if($isBillable=="No")  echo "selected"; ?>>No</option>

      </select>
      </td>
  </tr>

   <tr bgcolor='#ddddff'>
  
 
        <td>
          <label><strong><?php xl('From:','e')?></strong></label>
      <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_date ?>'
         onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc,'<?php echo date("m/d/Y"); ?>')' title='yyyy-mm-dd'>
         <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
          id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
         title='<?php xl('Click here to choose a date','e'); ?>'>

    </td>
 
      <td>
      <label> &nbsp;<strong>To:</strong></label>
      <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
         onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc,'<?php echo date("m/d/Y"); ?>')' title='yyyy-mm-dd'>
         <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
          id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
         title='<?php xl('Click here to choose a date','e'); ?>'>
    </td>
    <td></td>
  
  
  </tr>

  <tr bgcolor='#ddddff'>
    
    <td colspan="2">
        <label> &nbsp;<strong>Primary Payer:</strong></label>
        <select name="insurance_id" id="insurance_id" value='<?php echo $insurance_id; ?>'>
        
         
     <?php  $dres = sqlStatement("select id,name from insurance_companies where active=1 order by name"); 
   
    
     ?>
      <option value="0">- Select Payer - </option>
     <?php
        while($drow = sqlFetchArray($dres)){
      
           echo "<option value='$drow[id]' >$drow[name]</option>"; 
    
      }
  
     ?>
         </select>
         
         <script type="text/javascript">
  document.getElementById('insurance_id').value = "<?php echo $_POST['insurance_id'];?>";
</script>

         
         </td>
        
        <td></td>
        
        
  </tr>
 <tr><td colspan="4"></td></tr>

<tr align="center">
  <td colspan="4">

   <input type='submit' name='form_search' value="<?php xl('Refresh','e') ?>">
   &nbsp;
   <input type='submit' name='form_csvexport' value="<?php xl('Export to CSV','e') ?>">
   &nbsp;
   <input align="middle" id="button" onClick="resetform()" type="button" value="Reset">
   
  </td>
 </tr>
 
 <tr>
  <td height="1">
  </td>
 </tr>

</table>
</form>


<?php
} // end of else  export

// If generating a report.

if ($_POST['form_search'] || $_POST['form_csvexport']) {
  
    $form_date      = fixDate($_POST['form_from_date'], "");
    $form_to_date   = fixDate($_POST['form_to_date'], "");
    $form_name      = trim($_POST['form_name']);
    $form_extid      = trim($_POST['form_extid']);
    $insurance_id      = trim($_POST['insurance_id']);
    $isBillable      = trim($_POST['isBillable']);
  
  
    $query ="select account_no,date,patient_name,dob,filename,SUM(balance) as balance,pagecount,isBillable from statement_log as sl 
      where 1 = 1 ";

   $search_flag =0;   
  
  if ($form_to_date && $form_to_date) {
    $querystring .="&form_from_date=$form_date&form_to_date=$form_to_date";
          $query .= " AND sl.date >= '$form_date 00:00:00' AND sl.date <= '$form_to_date 23:59:59'";
          $search_flag =1;
  }
  elseif($form_date) {
      
      $querystring .="&form_from_date=$form_date";
     
      $query .= "AND date >= '$form_date' ";
  }
  elseif($form_to_date){
      
    $querystring .="&form_to_date=$form_to_date";
    $query .= " AND date = '$form_to_date'";
    $search_flag =1;
   }
   else{
     
   }
       

  if ($form_name) {
        
      $querystring .="&form_name=$form_name";
     
      // Allow the last name to be followed by a comma and some part of a first name.
      if (preg_match('/^(.*\S)\s*,\s*(.*)/', $form_name, $matches)) {
          $query .= " AND patient_name LIKE '" . $matches[1] . "%' ";
            // Allow a filter like "A-C" on the first character of the last name.
      } 
      else {
          $query .= " AND patient_name LIKE '%$form_name%'";
      }
       $search_flag =1;
  }
  if ($form_extid) {
  
      $querystring .="&form_extid=$form_extid";
      $where .= " AND account_no = '$form_extid'";
       $search_flag =1;
  }
  if ($insurance_id) {
   
      $querystring .="&insurance_id=$insurance_id";
      $query .= " AND insu_id = '$insurance_id'";
       $search_flag =1;
  }
      // code added for addtional PHP invoices
  if ($isBillable) {
      $querystring .="&isBillable=$isBillable";

      if($isBillable=="Yes"){
          $query .= " AND isBillable = 1";
      }
      elseif($isBillable=="No"){
          $query .= " AND isBillable = 0";
      }
      else{

      }
      $search_flag =1;
  }
  

  if($search_flag==0){

   // print('<script>alert("Please select atleast on search criteria.")</script>');
    
  }
  
  $query .= " group by date,account_no  order by date desc";

      
 // echo $query;

  $res = sqlStatement($query);
   $rec_count = mysql_num_rows($res);
   //echo  $rec_count ;
  

  if (! $_POST['form_csvexport']) {
     ?> 
    <table border='0' cellpadding='1' cellspacing='2' width='98%'>
     <tr bgcolor="#ddddff"><td colspan="12">Total Records : <?php echo $rec_count;?></td></tr>
    <tr bgcolor="#6CAEFF" style="font-weight:bold">

        <td class="dehead"><?php xl('Account No','e'  ) ?></td>
        <td class="dehead"><?php xl('Statement date','e'  ) ?></td>
        <td class="dehead"><?php xl('Patient name','e'  ) ?></td>
        <td class="dehead"><?php xl('DOB','e'  ) ?></td>
        <td class="dehead"><?php xl('Balance','e'  ) ?></td>
        <td class="dehead"><?php xl('Page Count','e'  ) ?></td>
        <td class="dehead"><?php xl('Is Billable','e'  ) ?></td>
        
    </tr>
   
   
  <?php
  } 
  while ($row = sqlFetchArray($res)) 
  {
    thisLineItem($row);
  }

} // end report generation

if (! $_POST['form_csvexport']) {
?>

</table>
</form>
</center>
</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

<script type="text/javascript">
function resetform() {//alert("hiii");
//document.getElementById("myform").reset();
document.getElementById("form_extid").value="";
document.getElementById("form_name").value="";
document.getElementById("form_from_date").value="";
document.getElementById("form_to_date").value="";
document.getElementById("insurance_id").value=0;
document.getElementById("isBillable").value="0";

}
</script>

   
 <script type="text/javascript">
    function validateForm()
    {
      
      var a=document.getElementById("form_extid").value;
      var b=document.getElementById("form_name").value;
      var c=document.getElementById("form_from_date").value;
      var d=document.getElementById("form_to_date").value;
      var e=document.getElementById("insurance_id").value;
      var f=document.getElementById("isBillable").value;
    
    if ((a==null || a=="") && (b==null || b=="") && (c==null || c=="") && (d==null || d=="") && (e==0 || e=="" ) && (f==0 || f=="" ))
      {
      alert("Please Fill Atleast One Field");
      return false;
      }
    }
    </script>

</html>
<?php
} // End not csv export
?>
