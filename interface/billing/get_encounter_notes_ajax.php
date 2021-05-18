<?php
require_once("../globals.php");

if($_POST['action'] == 'get_billing_notes'){

	$id_encounter = $_POST['id'];
	
	//echo "id===".$id_encounter;
	
	$res = sqlStatement("select id,username,created_date,notes from billing_notes where encounter = $id_encounter");
	
	
    	
	while($row = sqlFetchArray($res)){
	
		$user = $row['username'];
		$date = $row['created_date'];
		$notes= $row['notes']; 
		echo "[$user]:[$date]: $notes  ";
	
	
	}
	
	
	 
}

?>