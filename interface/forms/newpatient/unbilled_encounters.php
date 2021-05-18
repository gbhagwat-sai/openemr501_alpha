<?php
require_once("../../globals.php");
require_once("$srcdir/sql.inc");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Unbilled Encounters','e'); ?></title>

<script language="JavaScript" type="text/javascript">
function toNewEncounter(enc_type,draft_id){
top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?enc=1&autoloaded=1&calenc=&new_enc_type='+enc_type+'&draft_id='+draft_id);
}
</script>
</head>
<body>

<?php
 $pid = $_SESSION['pid'];
 
  
 /* $qry = sqlStatement("select fed.id,fed.date,fed.facility,users.fname,users.lname,fed.draft,fed.review,fed.clarification,fed.final_status from form_encounter_draft as fed, billing_draft as bf, users as users
where fed.id=bf.draft_id and bf.provider_id=users.id and fed.final_status!='save' and fed.pid=$pid group by fed.id ");*/

$qry = sqlStatement("select fed.id,fed.date,fed.facility,fed.draft,fed.review,fed.clarification,fed.final_status,bf.provider_id from form_encounter_draft as fed, billing_draft as bf
where fed.id=bf.draft_id and fed.final_status!='save' and fed.pid=$pid group by fed.id ");

 echo "<table cellspacing=5 border=1>";
 //$result1 = sqlFetchArray($qry);
 //if($result1[0]){
 $count=0;

  while ($result = sqlFetchArray($qry)) { 
  if($result['id'] == "")
  {
  	echo "<tr><td colspan=4>Encounters are not present.</td></tr>";
  }
  else{
  $count=1;
  $id = $result['id'];
  $dos=$result['date'];
  $date = date('Y-m-d', strtotime($dos));
  $facility = $result['facility'];
  //$name= $result['fname']." &nbsp; ".$result['lname'];
   $prov_id = $result['provider_id']; 
  $name = '';
  if($prov_id != 0){  
  $qry1 = sqlStatement("select concat(fname,' ',lname) as name from users where id=$prov_id");
  $result1 = sqlFetchArray($qry1);
  $name = $result1['name'];
  }
  $final_status=ucfirst($result['final_status']);  
  
  
  
 echo "<tr><td><b>Encounter Type</b></td><td><b>Date</b></td><td><b>Rendering Provider</b></td><td><b>Facility</b></td></tr>";
  echo "<tr><td><a href='#' onclick='javascript:toNewEncounter(1,$id)' ><b>$final_status</b></td><td>$date</a></td><td>$name</td><td>$facility</td></a></tr>"; 
  } 
  }
  
  /*}
  else
  {
  	echo "<tr><td colspan=4>No encounters are present</td></tr>";
  }*/
  if($count==0)
  echo "<tr><td cellspacing=2 colspan=4>Encounters are not present.</td></tr>";
  
  echo "</table>";
  
  
  /* ------------------------------------------------------------------
  
     echo "<table><tr><th>Reviewd encounters</th></tr>";
   
  
  $qry = sqlStatement("select fed.id,fed.date,fed.facility,users.fname,users.lname from form_encounter_draft as fed, billing_draft as bf, users as users
where fed.id=bf.draft_id and bf.provider_id=users.id and fed.review=1 and fed.pid=$pid group by fed.id ");
 //if($chk = mysql_fetch_row($qry)){
 echo "<tr><td><b>Date</b></td><td><b>Rendering Provider</b></td><td><b>Facility</b></td></tr>";
  while ($result = sqlFetchArray($qry)) {
  
  $id = $result['id'];
  $dos=$result['date'];
  $date = date('Y-m-d', strtotime($dos));
  $facility = $result['facility'];
  $name= $result['fname']." &nbsp; ".$result['lname'];
 
  echo "<tr><td><a href='#' onclick='javascript:toNewEncounter(1,$id)' >$date</a></td><td>$name</td><td>$facility</td></a></tr>";  
  }
  
  echo "</table>";
  
  //---------------------------------------------------------------------
  
  
    echo "<table><tr><th>Clarified encounters</th></tr>";
   
  
  $qry = sqlStatement("select fed.id,fed.date,fed.facility,users.fname,users.lname from form_encounter_draft as fed, billing_draft as bf, users as users
where fed.id=bf.draft_id and bf.provider_id=users.id and fed.clarification=1 and fed.pid=$pid group by fed.id ");
 //if($chk = mysql_fetch_row($qry)){
 echo "<tr><td><b>Date</b></td><td><b>Rendering Provider</b></td><td><b>Facility</b></td></tr>";
  while ($result = sqlFetchArray($qry)) {
  
  $id = $result['id'];
  $dos=$result['date'];
  $date = date('Y-m-d', strtotime($dos));
  $facility = $result['facility'];
  $name= $result['fname']." &nbsp; ".$result['lname'];
 
  echo "<tr><td><a href='#' onclick='javascript:toNewEncounter(1,$id)' >$date</a></td><td>$name</td><td>$facility</td></a></tr>";  
  }
  
  echo "</table>";
  */

?>

</body>
</html>