<?php
require_once("../../globals.php");
require_once("$srcdir/sql.inc");
require_once("../../../custom/code_types.inc.php");


$code_type = $_REQUEST['code_type'];

echo "<table><tr><th>Code Type</th><th>Code</th><th>Description</th></tr>";

  $sql = "select ct_key,code,code_text FROM codes,code_types where code_type=ct_id and ct_key='$code_type' order by code asc";
 
    $results = sqlStatement($sql);
   
   // $row= sqlFetchArray($results);
    // print_r($results);
    while ($row = sqlFetchArray($results)) {
      $code         = $row['code'];

      //$code_text    = $row['code_text_short'];
	   $code_text    = $row['code_text'];
      $code_type    = $row['ct_key'];     
	  echo "<tr><td>$code_type</td><td>$code</td><td>$code_text</td></tr>";
    }
echo "</table>";	
?>