<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";

 include_once("$srcdir/pid.inc");
 $set_pid = $_GET["set_pid"] ? $_GET["set_pid"] : $_GET["pid"];
 if ($set_pid && $set_pid != $_SESSION["pid"]) {
  setpid($set_pid);
 }

function thisLineItem($row) 
{
    
?>
 <tr bgcolor="#CEE4FF">
  <td class="detail"><?php echo oeFormatShortDate($row['date'  ]); ?></td>
  <td class="detail"><?php echo $row['user' ]; ?></td>
  <td class="detail"><?php echo $row['Comments' ]; ?></td>
 </tr>
<?php
}
?>


<html>
<head>
<?php html_header_show();?>
<title><?php xl('Encounter Log','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h3><?php xl('Encounter Log','e')?></h3>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>
 <tr bgcolor="#6CAEFF" style="font-weight:bold">
	  <td class="dehead"><?php xl('Date','e'  ) ?></td>
	  <td class="dehead"><?php xl('User','e'  ) ?></td>
	  <td class="dehead"><?php xl('Comments','e'  ) ?></td>
 </tr>
<?php


  
	$query = "select Date(date) as date,user,case when comments like 'INSERT%' ".
			"then 'Encounter Created' when comments like 'UPDATE%' ".
			"then 'Encounter Updated' end as Comments ".
			"from log where patient_id = '$pid' and comments like '%form_encounter%' ".
			"and comments like '%$encounter%'";

	$res = sqlStatement($query);
	while ($row = sqlFetchArray($res)) 
	{
		thisLineItem($row);
	}

?>

</table>
</center>
</body>
</html>

