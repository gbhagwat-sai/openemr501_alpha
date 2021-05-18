<?php
// Copyright (C) 2005-2010 Rod Roark <rod@sunsetsystems.com>
//
// Windows compatibility and statement downloading:
//     2009 Bill Cernansky and Tony McCormick [mi-squared.com]
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is the first of two pages to support posting of EOBs.
// The second is sl_eob_invoice.php.

require_once("../globals.php");
require_once("$srcdir/patient.inc");

require_once("$srcdir/invoice_summary.inc.php");
require_once($GLOBALS['OE_SITE_DIR'] . "/statement.inc.php");
//require_once("$srcdir/parse_era.inc.php");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/formatting.inc.php");
//require_once("$srcdir/classes/class.ezpdf.php");//for the purpose of pdf creation

require_once("$srcdir/mpdf/mpdf.php");
//number_format($number, 2, '.', '')
$site_id = $_SESSION['site_id'];
$paperclaim_path =$web_root."/sites/".$site_id ."/edi/";



function thisLineItem($row) {

  global $paperclaim_path;

  $bill_date = date("m/d/Y h:i:s A", strtotime($row['bill_time']));
 // $dob = date("m/d/Y", strtotime($row['dob']));
  $pdf_file = $row['process_file'];
  $pdf_path =$paperclaim_path.$pdf_file;
  
  
  if ($_POST['form_csvexport']) {  
    echo '"' . addslashes($row['sr_no']) . '",';
    echo '"' . addslashes($bill_date) . '",';
    echo '"' . addslashes($pdf_file) . '",';
    echo '"' . addslashes($row['cms_page_count' ]) . '",';
    echo '"' . addslashes($row['isBillable' ]) . '",' . "\n";
  }
  
  else{
?>
  <tr bgcolor="#CEE4FF">
    
     <td class="detail"><?php echo $row['sr_no']; ?></td>
     <td class="detail"><?php echo $bill_date; ?></td>
      <td class="detail"><a href="<?php echo $pdf_path;?>"  target="_blank" title="View Statement"><?php echo $pdf_file; ?></a></td>
      <td class="detail"><?php echo $row['cms_page_count' ]; ?></td>
      <td class="detail"><?php echo $row['isBillable' ]; ?></td>
    
  </tr>
<?php
  } 
} // end of function

if($_POST['form_search'] || $_POST['form_csvexport']){
  
    $form_date      = fixDate($_POST['form_from_date'], "");
    $form_to_date   = fixDate($_POST['form_to_date'], "");
    $isBillable      = trim($_POST['isBillable']);

  }
  else{
  
    $form_date      = fixDate($_GET['form_from_date'], "");
    $form_to_date   = fixDate($_GET['form_to_date'], "");
    $isBillable      = trim($_GET['isBillable']);  
  
}



if ($_POST['form_csvexport']) {
  
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=PaperClaimReport.csv");
    header("Content-Description: File Transfer");
  
    // CSV headers:
    echo '"' . xl('SR No') . '",';
    echo '"' . xl('Bill Date') . '",';
    echo '"' . xl('File Name') . '",';
    echo '"' . xl('Page Count') . '",';
    echo '"' . xl('Is Billable') . '"' . "\n";
 
        
}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Printed Paper Claim Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Printed Paper Claim','e')?></h2>


<form method='post' action='paperClaimReport.php' name="Form" onsubmit="return validateForm()">

<table border='0' cellpadding='3'>

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
    $isBillable      = trim($_POST['isBillable']);
  
    $query ="SELECT   bill_time,process_file,cms_page_count,isBillable FROM claims as sl
      where 1 = 1 AND process_file like '%pdf%'";

   
  
  if ($form_to_date && $form_to_date) {
          $query .= " AND sl.bill_time >= '$form_date 00:00:00' AND sl.bill_time <= '$form_to_date 23:59:59'";
       
  }
  
  elseif ($form_date) {
      
     $query .= "AND bill_time >= '$form_date' AND bill_time <= '$form_date'";
     
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
     
  }
  
  $query .= " group by process_file order by bill_time desc";

      
  //echo $query;

  $res = sqlStatement($query);
   $rec_count = mysql_num_rows($res);
   //echo  $rec_count ;
  

  if (! $_POST['form_csvexport']) {
     ?> 
    <table border='0' cellpadding='1' cellspacing='2' width='98%'>
     <tr bgcolor="#ddddff"><td colspan="12">Total Records : <?php echo $rec_count;?></td></tr>
    <tr bgcolor="#6CAEFF" style="font-weight:bold">

        <td class="dehead"><?php xl('Sr No','e'  ) ?></td>
        <td class="dehead"><?php xl('Bill Date','e'  ) ?></td>
        <td class="dehead"><?php xl('File Name','e'  ) ?></td>
        <td class="dehead"><?php xl('Page Count','e'  ) ?></td>
        <td class="dehead"><?php xl('Is Billable','e'  ) ?></td>
        
    </tr>
   
   
  <?php
  } 
  $sr_no =1;
  
  while ($row = sqlFetchArray($res)) 
  {
   
    $row['sr_no'] =$sr_no;
      thisLineItem($row);
    $sr_no ++;
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

    document.getElementById("form_from_date").value="";
    document.getElementById("form_to_date").value="";
    document.getElementById("isBillable").value="0";

  }
</script>

   
 <script type="text/javascript">
    function validateForm()
    {
      
     
      var c=document.getElementById("form_from_date").value;
      var d=document.getElementById("form_to_date").value;
      var f=document.getElementById("isBillable").value;
    
      if ( (c==null || c=="") && (d==null || d=="") && (e==0 || e=="" ) && (f==0 || f=="" ))
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
