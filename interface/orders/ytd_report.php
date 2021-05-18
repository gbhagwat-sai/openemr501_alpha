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

function addProviderLineItem($povdrr)
{
if ($_POST['form_csvexport']) {
   echo '"' . addslashes($povdrr['Provider']) . '",';
//   echo '"' . addslashes($row['Collection']) . '"' . "\n";
  }
  else {
?>
 <tr style="font-family:Verdana, Arial, Helvetica, sans-serif ; font-size:10px" bgcolor="#C1EEFF">
  <td class="detail"><?php echo $povdrr['Provider']; ?></td>
<?php
}
}
function addcollectionLineItem($collr)
{
if ($_POST['form_csvexport']) {
   echo '"' . addslashes($collr['Collection']) . '",';
//   echo '"' . addslashes($row['Collection']) . '"' . "\n";
  }
  else {
?>
  <td class="detail"><?php echo $collr['Collection']; ?></td>
<?php
}
}
function addcollectionSumLineItem($collectionSum)
{
if ($_POST['form_csvexport']) {
   echo '"' . addslashes($collectionSum) . '",';
//   echo '"' . addslashes($row['Collection']) . '"' . "\n";
  }
  else {
?>
  <td class="detail"><?php echo $collectionSum; ?></td>
<?php
}
}
function thisLineItem($row) {

  if ($_POST['form_csvexport']) {
   echo '"' . addslashes($row['Provider']) . '",';
   echo '"' . addslashes($row['Collection']) . '"' . "\n";
  }
  else {
?>
 <tr style="font-family:Verdana, Arial, Helvetica, sans-serif ; font-size:10px" bgcolor="#C1EEFF">
  <td class="detail"><?php echo $row['Provider']; ?></td>
  <td class="detail"><?php echo $row['Collection']; ?></td>

<?php
  } // End not csv export
}
function addBreakLineItem()
{
if ($_POST['form_csvexport']) {
   echo "\n";
  }
  else {
?>
</tr>
<?php
}
}
function endLineItem()
{?>
</tr>
<?php
}


function addthisLineItem($row) {

  if ($_POST['form_csvexport']) {
   echo '"' . addslashes($row['Collection']) . '",';
  }
  else {
?>
   <td class="detail"><?php echo $row['Collection']; ?></td>
<?php
  } // End not csv export
}


if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_facility  = $_POST['form_provider'];

if ($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=YTD_Collection_Report.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
// echo '"' . xl('Provider') . '",';
// echo '"' . xl('Collection') . '"' . "\n";
}
else { // not export
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('YTD Collection Report','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('YTD Collection Report','e')?></h2>

<form method='post' action='ytd_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
 <?php /*?><?php dropdown_provider(strip_escape_custom($form_provider), 'form_provider', false); ?><?php */?>
 
   &nbsp;
 		DOS / Posted Date
   <select name="Mode"> 
  <option  value="DOS" <?php if(isset($_POST['Mode']) && $_POST['Mode'] == "DOS" ){ echo "selected"; }?>>DOS</option>
  <option value="PostedDt" <?php if(isset($_POST['Mode']) && $_POST['Mode'] == "PostedDt" ){ echo "selected"; }?>>Posted Date</option>
	</select>
   Date / Month
   <select name="D/M"> 
  <option  value="Date" <?php if(isset($_POST['D/M']) && $_POST['D/M'] == "Date" ){ echo "selected"; }?>>Date</option>
  <option value="Month" <?php if(isset($_POST['D/M']) && $_POST['D/M'] == "Month" ){ echo "selected"; }?>>Month</option>
	</select>
   <?xl('From:','e')?> From
   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc,'<?php echo date("m/d/Y"); ?>')' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;To:
   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc,'<?php echo date("m/d/Y"); ?>')' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
    </td></tr>
    <tr><td align="center">
       &nbsp;
   <input type='submit' name='form_refresh' value="<?php xl('Refresh','e') ?>">
   &nbsp;
   <input type='submit' name='form_csvexport' value="<?php xl('Export to CSV','e') ?>">
   &nbsp;
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' />
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>
 <tr bgcolor="#5AD1E7" style="font-family:Verdana, Arial, Helvetica, sans-serif ; font-size:12px">
 
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
if ($_POST['D/M'] == "Date")
{
if ($_POST['Mode']=="PostedDt")
{
$from_date = $form_from_date;
$to_date   = $form_to_date;

  if ($_POST['form_csvexport']) {
  echo '"' . xl('Provider') . '",';
 echo '"' . xl('Collection') . '"' . "\n";
  }
  else {

?>
 <td class="dehead"><?php xl('Provider','e'  ) ?></td>
 <td class="dehead"><?php xl('Collection','e'  ) ?></td>
 </tr>
<?php
}
$query = "select distinct(provider_id) as pro_id from form_encounter";
$res = sqlStatement($query);
while ($row = sqlFetchArray($res))
{
//print_r ($row['pro_id']);
$t= $row['pro_id'];
$povdr = "select Concat(lname,' ',fname,' ',mname) as Provider from users ".
"where id = '$t'";
$povdrs = sqlStatement($povdr);
$povdrr=sqlFetchArray($povdrs);
//echo $povdrr['Provider'];
addProviderLineItem($povdrr);

$collq ="select sum(aa.pay_amount) as Collection from ar_activity aa, form_encounter fe where ".
"aa.encounter=fe.encounter and fe.provider_id = '$t' AND aa.post_time >= '$from_date' AND aa.post_time <= "."'$to_date' "; 
$colls = sqlStatement($collq);
$collr=sqlFetchArray($colls);
//echo $collr['Collection'];
  if ($_POST['form_csvexport']) {
   echo '"' . addslashes($collr['Collection']) . '"' . "\n";
  }
  else {
?>
  <td class="detail"><?php echo $collr['Collection']; ?></td>
 </tr>
<?php
}
}
}
}


if ($_POST['D/M'] == "Date")
{
if ($_POST['Mode']=="DOS")
{
$from_date = $form_from_date;
$to_date   = $form_to_date;
  if ($_POST['form_csvexport']) {
  echo '"' . xl('Provider') . '",';
 echo '"' . xl('Collection') . '"' . "\n";
  }
  else {
?>
 <td class="dehead"><?php xl('Provider','e'  ) ?></td>
 <td class="dehead"><?php xl('Collection','e'  ) ?></td>
 </tr>
<?php
}
$query = "select distinct(provider_id) as pro_id from form_encounter";
$res = sqlStatement($query);
while ($row = sqlFetchArray($res))
{
//print_r ($row['pro_id']);
$t= $row['pro_id'];
$povdr = "select Concat(lname,' ',fname,' ',mname) as Provider from users ".
"where id = '$t'";
$povdrs = sqlStatement($povdr);
$povdrr=sqlFetchArray($povdrs);
//echo $povdrr['Provider'];
addProviderLineItem($povdrr);

$collq ="select sum(aa.pay_amount) as Collection from ar_activity aa, form_encounter fe where ".
"aa.encounter=fe.encounter and fe.provider_id = '$t' AND fe.date >= '$from_date' AND fe.date <= "."'$to_date' "; 
$colls = sqlStatement($collq);
$collr=sqlFetchArray($colls);
//echo $collr['Collection'];
  if ($_POST['form_csvexport']) {
   echo '"' . addslashes($collr['Collection']) . '"' . "\n";
  }
  else {
?>
  <td class="detail"><?php echo $collr['Collection']; ?></td>
 </tr>
<?php
}
}

//
//$query = 	
//  "select Concat(u.lname,' ',u.fname,' ',u.mname) as Provider,".
//" sum(aa.pay_amount) as Collection , month(fe.date) as month ".
//"from ar_activity aa, users u, form_encounter fe ".
//"where aa.encounter=fe.encounter and fe.provider_id = u.id ".
//"and fe.date >= '$from_date' AND fe.date <= '$to_date' ";

// TBD: What if preliminary and final reports for the same order?
  
//if ($form_provider) 
// {
//    $query .= " AND u.id = '$form_provider' ";
//
//  }
//$query .= " group by Provider,month(fe.date)";
//
//  $res = sqlStatement($query);
// 
// $row = sqlFetchArray($res);
// $temp = $row['Provider'];
// thisLineItem($row);
// 
// while ($row = sqlFetchArray($res))
// {
//if($temp==$row['Provider'])
//   {
//    addthisLineItem($row);
//   }
 // if($temp!=$row['Provider'])
//  {
//  endLineItem();
//  $temp=$row['Provider'];
// thisLineItem($row);
//  }
//   thisLineItem($row);
//  }

}
}
 else 
 {
 if ($_POST['Mode']=="DOS")
{
  $time1  = strtotime($form_from_date); 
   $time2  = strtotime($form_to_date); 
   $my     = date('n-Y', $time2); 
   $mesi = array(Jan,Feb,Mar,Apr,May,Jun,July,Aug,Sept,Oct,Nov,Dec);
   
   //$months = array(date('F', $time1)); 
   $monthsHeader = array(); 
   $f      = ''; 

   while($time1 < $time2) { 
      if(date('n-Y', $time1) != $f) { 
         $f = date('n-Y', $time1); 
         if(date('n-Y', $time1) != $my && ($time1 < $time2)) {
             $str_mese=$mesi[(date('n', $time1)-1)];
            $monthsHeader[] = $str_mese." ".date('Y', $time1); 
         }
      } 
      $time1 = strtotime((date('Y-n-d', $time1).' +15days')); 
   } 

   $str_mese=$mesi[(date('n', $time2)-1)];
   $monthsHeader[] = $str_mese." ".date('Y', $time2);  
 if ($_POST['form_csvexport']) {
  echo '"' . xl('Provider') . '",';
  }
  else {
?>
 <td class="dehead"><?php xl('Provider','e'  ) ?></td>
   <?php
   }
foreach ($monthsHeader as $key=>$value) {
if ($_POST['form_csvexport']) {
  echo '"' . xl($value) . '",';
  }
  else {

 ?> 
 <td class="dehead"><?php xl($value,'e') ?></td>
 <?php
 }
} 

 if ($_POST['form_csvexport']) {
 echo '"' . xl('Total') . '"' . "\n";
  }
  else {

?> 
 <td class="dehead"><?php xl('Total','e') ?></td>
 </tr>
 <?php
}
   $time1  = strtotime($form_from_date);
   $time2  = strtotime($form_to_date);
   $my     = date('mY', $time2);

   $months = array(date('n', $time1));
   $f      = '';

   while($time1 < $time2) {
      $time1 = strtotime((date('Y-m-d', $time1).' +15days'));
      if(date('n', $time1) != $f) {
         $f = date('n', $time1);
         if(date('mY', $time1) != $my && ($time1 < $time2))
            $months[] = date('n', $time1);
      }
   }

$months[] = date('n', $time2);
array_shift($months);
//print_r ($months);
//die;
//$cnt  =0;
//foreach ($months as $key=>$value) {
//echo $value;
//echo substr($monthsHeader[$cnt], -4);
//$cnt++;
//} 
$query = "select distinct(provider_id) as pro_id from form_encounter";
$res = sqlStatement($query);
while ($row = sqlFetchArray($res))
{
//print_r ($row['pro_id']);
$t= $row['pro_id'];

$povdr = "select Concat(lname,' ',fname,' ',mname) as Provider from users ".
"where id = '$t'";
$povdrs = sqlStatement($povdr);
$povdrr=sqlFetchArray($povdrs);
//echo $povdrr['Provider'];
addProviderLineItem($povdrr);
$cnt=0;
$collectionSum=0;
foreach ($months as $key=>$value) {
//echo $value;
$yr= substr($monthsHeader[$cnt], -4);

$cnt++;
$collq ="select sum(aa.pay_amount) as Collection from ar_activity aa, form_encounter fe where ".
"aa.encounter=fe.encounter and fe.provider_id = '$t' and month(fe.date) = '$value' AND ".
"year(fe.date) = '$yr'"; 
$colls = sqlStatement($collq);
$collr=sqlFetchArray($colls);
//echo $collr['Collection'];
$collectionSum += $collr['Collection'];
addcollectionLineItem($collr);
} 
addcollectionSumLineItem($collectionSum);
addBreakLineItem();
}


//$from_date = $form_from_date;
//$to_date   = $form_to_date;
//
//$query = 	
//  "select Concat(u.lname,' ',u.fname,' ',u.mname) as Provider,".
//" sum(aa.pay_amount) as Collection , month(fe.date) as month ".
//"from ar_activity aa, users u, form_encounter fe ".
//"where aa.encounter=fe.encounter and fe.provider_id = u.id ".
//"and fe.date >= '$from_date' AND fe.date <= '$to_date' ";

// TBD: What if preliminary and final reports for the same order?
  
//if ($form_provider) 
// {
//    $query .= " AND u.id = '$form_provider' ";
//
//  }
  //$query .= " group by Provider,month(fe.date)";
//
//  $res = sqlStatement($query);
// 
// $row = sqlFetchArray($res);
// $temp = $row['Provider'];
// thisLineItem($row);
// 
  // while ($row = sqlFetchArray($res))
  // {
   //if($temp==$row['Provider'])
//   {
//    addthisLineItem($row);
//   }
 // if($temp!=$row['Provider'])
//  {
//  endLineItem();
//  $temp=$row['Provider'];
// thisLineItem($row);
//  }
 //   thisLineItem($row);
//  }
} // end report generation
if ($_POST['Mode']=="PostedDt")
{
  $time1  = strtotime($form_from_date); 
   $time2  = strtotime($form_to_date); 
   $my     = date('n-Y', $time2); 
   $mesi = array(Jan,Feb,Mar,Apr,May,Jun,July,Aug,Sept,Oct,Nov,Dec);
   
   //$months = array(date('F', $time1)); 
   $monthsHeader = array(); 
   $f      = ''; 

   while($time1 < $time2) { 
      if(date('n-Y', $time1) != $f) { 
         $f = date('n-Y', $time1); 
         if(date('n-Y', $time1) != $my && ($time1 < $time2)) {
             $str_mese=$mesi[(date('n', $time1)-1)];
            $monthsHeader[] = $str_mese." ".date('Y', $time1); 
         }
      } 
      $time1 = strtotime((date('Y-n-d', $time1).' +15days')); 
   } 

   $str_mese=$mesi[(date('n', $time2)-1)];
   $monthsHeader[] = $str_mese." ".date('Y', $time2);  
if ($_POST['form_csvexport']) {
  echo '"' . xl('Provider') . '",';
  }
  else {
?>

 <td class="dehead"><?php xl('Provider','e'  ) ?></td>
  <?php
  }
foreach ($monthsHeader as $key=>$value) {
if ($_POST['form_csvexport']) {
  echo '"' . xl($value) . '",';
  }
  else {

 ?> 
 <td class="dehead"><?php xl($value,'e') ?></td>
 <?php
   }
} 
if ($_POST['form_csvexport']) {
 echo '"' . xl('Total') . '"' . "\n";
  }
  else {
?> 
 <td class="dehead"><?php xl('Total','e') ?></td>
 </tr>
 <?php
}
   $time1  = strtotime($form_from_date);
   $time2  = strtotime($form_to_date);
   $my     = date('mY', $time2);

   $months = array(date('n', $time1));
   $f      = '';

   while($time1 < $time2) {
      $time1 = strtotime((date('Y-m-d', $time1).' +15days'));
      if(date('n', $time1) != $f) {
         $f = date('n', $time1);
         if(date('mY', $time1) != $my && ($time1 < $time2))
            $months[] = date('n', $time1);
      }
   }

$months[] = date('n', $time2);
array_shift($months);
//print_r ($months);
//die;
//$cnt  =0;
//foreach ($months as $key=>$value) {
//echo $value;
//echo substr($monthsHeader[$cnt], -4);
//$cnt++;
//} 
$query = "select distinct(provider_id) as pro_id from form_encounter";
$res = sqlStatement($query);
while ($row = sqlFetchArray($res))
{
//print_r ($row['pro_id']);
$t= $row['pro_id'];

$povdr = "select Concat(lname,' ',fname,' ',mname) as Provider from users ".
"where id = '$t'";
$povdrs = sqlStatement($povdr);
$povdrr=sqlFetchArray($povdrs);
//echo $povdrr['Provider'];
addProviderLineItem($povdrr);
$cnt=0;
$collectionSum=0;
foreach ($months as $key=>$value) {
//echo $value;
$yr= substr($monthsHeader[$cnt], -4);

$cnt++;
$collq ="select sum(aa.pay_amount) as Collection from ar_activity aa, form_encounter fe where ".
"aa.encounter=fe.encounter and fe.provider_id = '$t' and month(aa.post_time) = '$value' AND ".
"year(aa.post_time) = '$yr'"; 
$colls = sqlStatement($collq);
$collr=sqlFetchArray($colls);
//echo $collr['Collection'];
$collectionSum += $collr['Collection'];
addcollectionLineItem($collr);
} 
addcollectionSumLineItem($collectionSum);
addBreakLineItem();
}

} // end report generation

}

}
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

</html>
<?php
} // End not csv export
?>
