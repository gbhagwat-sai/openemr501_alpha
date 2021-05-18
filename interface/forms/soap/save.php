<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require("C_FormSOAP.class.php");
$c = new C_FormSOAP();
echo $c->default_action_process($_POST);
	// Sai custom code start
	//updated by Gangeya for BUG ID 8790 to redirect user back to demographics, 
	//if adding documentation prior to encounter selection.
	
	if($encounter == "000000")
		@formJumpX();
	else
		@formJump();
		
	// Sai custom code end
?>
