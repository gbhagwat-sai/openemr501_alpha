<?php

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

 require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("history.inc.php");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/pnotes.inc");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    tabbify();
});
</script>

<style type="text/css">
</style>

</head>
<body class="body_top">

<?php
 $thisauth = acl_check('patients', 'med');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) {
  echo "<p>(".htmlspecialchars(xl('History not authorized'),ENT_NOQUOTES).")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

 $result = getHistoryData($pid);
	
$patient_id = $result['pid'];	
 
 
 if (!is_array($result)) {
  newHistoryData($pid);
  $result = getHistoryData($pid);
 }
?>


<div>
    <span class="title"><?php echo htmlspecialchars(xl('Patient Statement'),ENT_NOQUOTES); ?></span>
</div>
<div style='float:left;margin-right:10px'>
<?php echo htmlspecialchars(xl('for'),ENT_NOQUOTES);?>&nbsp;<span class="title"><a href="../summary/demographics.php" onClick="top.restoreSession()"><?php echo htmlspecialchars(getPatientName($pid),ENT_NOQUOTES) ?></a></span>
</div>
<div>
    
    <a href="../summary/demographics.php" <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?> class="css_button" onClick="top.restoreSession()">
        <span><?php echo htmlspecialchars(xl('Back To Patient'),ENT_NOQUOTES);?></span>
    </a>
</div>
<br/>


<div style='float:none; margin-top: 10px; margin-right:20px; '>
    <table style="font-style:inherit; background:#FFFFFF;font-size: 14px;" width="60%">
    <tr  style="text-align:left"><th>DATE</th><th>BALANCE</th><th>FILE</th><th>USER</th></tr>
    
    <?php
	
	$pres = sqlQuery("SELECT lname, fname,pubpid " .
 	"FROM patient_data WHERE pid = $pid" );
	$patientname = $pres['lname'] . ", " . $pres['fname'];

	$external_id = $pres['pubpid'];
	
	$site_id = $_SESSION['site_id'];

	$statement_path =$web_root."/sites/".$site_id ."/patient_statements/";
	
	
	$N=0;
	$log_result = getStatementLog($external_id, $N, 0);
	
	
	
	foreach ($log_result as $iter) {
		 //formatting start
		$date1 = strtotime($iter['date']);
		$date2 = date("m/d/Y h:i:s A", $date1);
		$balance1=sprintf("%01.2f",$iter['balance']);
	
		$filename = $iter['filename'];
		$statement_file =  substr($filename, 0, -3);
		$pdf_path =$statement_path.$filename;
	?>
    <tr style="text-align:left ; border:#000000;">
        <td><?php echo $date2;?></td> <td><?php echo $balance1;?></td> <td><a href="<?php echo $pdf_path;?>" target="_blank"><?php echo $statement_file;?></a></td> <td><?php echo $iter['user'];?></td> 
     </tr>
   
      <?php
      }
      ?> 
    
     </table>
</div>

</body>
</html>
