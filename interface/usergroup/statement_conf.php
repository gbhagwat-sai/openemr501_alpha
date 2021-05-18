<?php
require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");

$todays_date = date('Y-m-d H:i:s');


	$query = "SELECT * FROM statement_config where id=1";
	$fres = sqlStatement($query);
	$frow = sqlFetchArray($fres);
	if(empty($frow)){
		$mode = "new";
	}
	else{
	
		
		$payment_mode1 = array();
		$card_type1 = array();
		$mode ="update";
		$record_id = $frow['id'];
		
		$payment_mode_array1 = $frow['payment_mode'];
		
		if(!empty($payment_mode_array1))	
			$payment_mode1 = explode(",",$payment_mode_array1);
		
		$card_type_array1 	= $frow['card_type'];
		
		if(!empty($card_type_array1))
			$card_type1 = 	explode(",",$card_type_array1);
			
		//echo "pawan";
		//print_r($payment_mode1);
		
		$gl_message1 = $frow['gl_message1'];	
		$gl_message2= $frow['gl_message2']	;
		$day_bet_stmt= $frow['day_bet_stmt'];	
		$min_bal_stmt= $frow['min_bal_stmt'];	
		$practice_name= $frow['practice_name']	;
		$practice_address= $frow['practice_address']	;
		$practice_city= $frow['practice_city']	;
		$practice_state= $frow['practice_state'];	
		$practice_zip= $frow['practice_zip']	;
		$practice_phone= $frow['practice_phone'];	
		$practice_email= $frow['practice_email'];	
		$practice_web= $frow['practice_web']	;
		$billing_name= $frow['billing_name'];	
		$billing_address= $frow['billing_address']	;
		$billing_city= $frow['billing_city']	;
		$billing_state= $frow['billing_state']	;
		$billing_zip= $frow['billing_zip']	;
		$billing_phone= $frow['billing_phone']	;
		$billing_email= $frow['billing_email']	;
		$admin_name= $frow['admin_name']	;
		$admin_address= $frow['admin_address']	;
		$admin_city= $frow['admin_city']	;
		$admin_state= $frow['admin_state']	;
		$admin_zip= $frow['admin_zip']	;
		$admin_phone= $frow['admin_phone']	;
		$admin_email= $frow['admin_email']	;
		$admin_note= $frow['admin_note']	;
		
	}



if(isset($_POST['submit']) && $_POST['submit'] == "Save" ){



	$payment_mode_array = $_POST['payment_mode'];
	
	if(!empty($payment_mode_array))	
		$payment_mode = implode(",",$payment_mode_array);
	
	$card_type_array 	= $_POST['card_type'];
	
	if(!empty($card_type_array))
		$card_type = 	implode(",",$card_type_array);
	
	$gl_message1 = $_POST['gl_message1'];	
	$gl_message2= $_POST['gl_message2']	;
	$day_bet_stmt= $_POST['day_bet_stmt'];	
	$min_bal_stmt= $_POST['min_bal_stmt'];	
	$practice_name= $_POST['practice_name']	;
	$practice_address= $_POST['practice_address']	;
	$practice_city= $_POST['practice_city']	;
	$practice_state= $_POST['practice_state'];	
	$practice_zip= $_POST['practice_zip']	;
	$practice_phone= $_POST['practice_phone'];	
	$practice_email= $_POST['practice_email'];	
	$practice_web= $_POST['practice_web']	;
	$billing_name= $_POST['billing_name'];	
	$billing_address= $_POST['billing_address']	;
	$billing_city= $_POST['billing_city']	;
	$billing_state= $_POST['billing_state']	;
	$billing_zip= $_POST['billing_zip']	;
	$billing_phone= $_POST['billing_phone']	;
	$billing_email= $_POST['billing_email']	;
	$admin_name= $_POST['admin_name']	;
	$admin_address= $_POST['admin_address']	;
	$admin_city= $_POST['admin_city']	;
	$admin_state= $_POST['admin_state']	;
	$admin_zip= $_POST['admin_zip']	;
	$admin_phone= $_POST['admin_phone']	;
	$admin_email= $_POST['admin_email']	;
	$admin_note= $_POST['admin_note']	;
	$created_user= $_SESSION['authUser'];
	$created_date= $todays_date;
	$last_updated_user= $_SESSION['authUser'];
	$last_updated_date= $todays_date;


	$pagemode = $_POST['mode'];
	
	if($pagemode == "new"){
		$insert_query =" Insert into statement_config(
								payment_mode	,
								card_type	,
								gl_message1	,
								gl_message2	,
								day_bet_stmt	,
								min_bal_stmt	,
								practice_name	,
								practice_address	,
								practice_city	,
								practice_state	,
								practice_zip	,
								practice_phone	,
								practice_email	,
								practice_web	,
								billing_name	,
								billing_address	,
								billing_city	,
								billing_state	,
								billing_zip	,
								billing_phone	,
								billing_email	,
								admin_name	,
								admin_address	,
								admin_city	,
								admin_state	,
								admin_zip	,
								admin_phone	,
								admin_email	,
								admin_note ,
								created_user	,
								created_date	,
								last_updated_user	,
								last_updated_date	
								)
								values(
									'$payment_mode'	,
									'$card_type',
									'".mysql_real_escape_string($gl_message1)."',
									'".mysql_real_escape_string($gl_message2)."',
									'$day_bet_stmt'	,
									'$min_bal_stmt'	,
									'$practice_name',
									'".mysql_real_escape_string($practice_address)."',
									'$practice_city'	,
									'$practice_state'	,
									'$practice_zip'	,
									'$practice_phone'	,
									'$practice_email'	,
									'$practice_web'	,
									'$billing_name' 	,
									'".mysql_real_escape_string($billing_address)."',
									'$billing_city'	,
									'$billing_state'	,
									'$billing_zip'	,
									'$billing_phone'	,
									'$billing_email'	,
									'$admin_name'	,
									'".mysql_real_escape_string($admin_address)."',
									'$admin_city'	,
									'$admin_state'	,
									'$admin_zip'	,
									'$admin_phone'	,
									'$admin_email'	,
									'".mysql_real_escape_string($admin_note)."',
									'$created_user'	,
									'$created_date'	,
									'$last_updated_user',
									'$last_updated_date'
									)";
	
			//echo $insert_query;
	
			$result_insert=sqlStatement($insert_query);			
								
			echo '<script language="javascript">';
			echo 'alert("Inserted Successfuly")';
			echo '</script>';					
		
		}
		else{
			
		
					sqlStatement("update statement_config set
				payment_mode='" . trim($payment_mode) . "',
				card_type='" . trim($card_type) . "',
				gl_message1='" . mysql_real_escape_string($gl_message1) . "',
				gl_message2='" . mysql_real_escape_string($gl_message2) . "',
				day_bet_stmt='" . trim($day_bet_stmt) . "',
				min_bal_stmt='" . trim($min_bal_stmt) . "',
				practice_name='" . trim($practice_name) . "',
				practice_address='" . mysql_real_escape_string($practice_address) . "',
				practice_city='" . trim($practice_city) . "',
				practice_state='" . trim($practice_state) . "',
				practice_zip='" . trim($practice_zip) . "',
				practice_phone='" . trim($practice_phone) . "',
				practice_email='" . trim($practice_email) . "',
				practice_web='" . trim($practice_web) . "',
				billing_name='" . trim($billing_name) . "',
				billing_address='" . mysql_real_escape_string($billing_address) . "',
				billing_city='" . trim($billing_city) . "',
				billing_state='" . trim($billing_state) . "',
				billing_zip='" . trim($billing_zip) . "' ,
				billing_phone='" . trim($billing_phone) . "' ,
				billing_email='" . trim($billing_email) . "' ,
				admin_name='" . trim($admin_name) . "',
				admin_address='" . mysql_real_escape_string($admin_address) . "',
				admin_city='" . trim($admin_city) . "',
				admin_state='" . trim($admin_state) . "',
				admin_zip='" . trim($admin_zip) . "',
				admin_phone='" . trim($admin_phone) . "',
				admin_email='" . trim($admin_email) . "',
				admin_note='" . mysql_real_escape_string($admin_note) . "',
				last_updated_user='" . trim($last_updated_user) . "',
				last_updated_date='" . trim($last_updated_date) . "' 
			where id='" . trim($record_id) . "'" );
			
				echo '<script language="javascript">';
				echo 'alert("Updated Successfuly")';
				echo '</script>';
			
				header("Refresh:0");
		}


}




?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<style type="text/css">
body
{
	text-align:center;
	    background-color: #f0f9ff;
}
.form-style-2{
    max-width: 700px;
    padding: 20px 12px 10px 20px;
    font: 13px Arial, Helvetica, sans-serif;
	//margin:0px auto;
	text-align:left;
	background:#e2f3fa;
	    margin-top: -20px;
}
.form-style-2-heading{
    border: 1px dashed #b2c9d1;
    color: #157fa9;
    font-size: 15px;
    font-style: italic;
    font-weight: bold;
    margin-bottom: 20px;
    padding: 3px;
}
.form-style-2 label{
    display: block;
    margin: 0px 0px 15px 0px;
}
.form-style-2 label > span{
    width: 200px;
    font-weight: bold;
    float: left;
    padding-top: 8px;
    padding-right: 5px;
}
.form-style-2 span.required{
    color:red;
}
.form-style-2 .tel-number-field{
    width: 40px;
    text-align: center;
}
.form-style-2 input.input-field{
    width: 48%;
   
}

.form-style-2 input.input-field,
.form-style-2 .tel-number-field,
.form-style-2 .textarea-field,
 .form-style-2 .select-field{
    box-sizing: border-box;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    border: 1px solid #C2C2C2;
    box-shadow: 1px 1px 4px #EBEBEB;
    -moz-box-shadow: 1px 1px 4px #EBEBEB;
    -webkit-box-shadow: 1px 1px 4px #EBEBEB;
    border-radius: 3px;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    padding: 7px;
    outline: none;
}
.form-style-2 .input-field:focus,
.form-style-2 .tel-number-field:focus,
.form-style-2 .textarea-field:focus,  
.form-style-2 .select-field:focus{
    border: 1px solid #0C0;
}
.form-style-2 .textarea-field{
    height:100px;
    width: 55%;
}
.form-style-2 input[type=submit],
.form-style-2 input[type=button]{
    border: none;
    padding: 8px 15px 8px 15px;
    background: #FF8500;
    color: #fff;
    box-shadow: 1px 1px 4px #DADADA;
    -moz-box-shadow: 1px 1px 4px #DADADA;
    -webkit-box-shadow: 1px 1px 4px #DADADA;
    border-radius: 3px;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
}
.form-style-2 input[type=submit]:hover,
.form-style-2 input[type=button]:hover{
    background: #EA7B00;
    color: #fff;
}
body {
  margin: 0;
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 13px;
  line-height: 18px;
  color: #333333;
  background-color: #ffffff;
  
  /* changes for better demo */
  margin: 20px 0 0;
  text-align: center;
}

.badge {
  padding: 1px 9px 2px;
  font-size: 12.025px;
  font-weight: bold;
  white-space: nowrap;
  color: #ffffff;
  background-color: #999999;
  -webkit-border-radius: 9px;
  -moz-border-radius: 9px;
  border-radius: 9px;
}
.badge:hover {
  color: #ffffff;
  text-decoration: none;
  cursor: pointer;
}
.badge-error {
  background-color: #b94a48;
}
.badge-error:hover {
  background-color: #953b39;
}

</style>


<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Form</title>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>

<script>
$(document).ready(function () {
	
   /*$("#Card").change(function () {
	
		alert("hiii");
	});*/
	 	
	
			  
});
	
function mycheck(){
	
	
		if (document.getElementById("Card").checked == false){
			
				document.getElementById("master").checked = false;
				document.getElementById("visa").checked = false;
				document.getElementById("amex").checked = false;
				document.getElementById("disc").checked = false;
				document.getElementById("other").checked = false;
				
				document.getElementById("show_me").style.display = "none";
			
		} 
		else
		{
			document.getElementById("show_me").style.display = "block";
		}
			
	}  	   

</script>

</head>

<body>

<div class="form-style-2">


<!----------------------------------------Start----------------------------------------------------------------------->


<form action="statement_conf.php" method="POST" >

<div class="form-style-2-heading"> Payment Mode</div>

<?php /*if(!empty($payment_mode)){

			
			$payment_mode = explode(",",$payment_mode);

	  } */


?>


        <input name="payment_mode[]" type="checkbox" value="Card" id="Card" <?php if (in_array('Card',$payment_mode1)) echo "checked='checked'"; ?> onclick="mycheck();" />Card 
        <input name="payment_mode[]" type="checkbox" value="Cash"  <?php if (in_array('Cash',$payment_mode1)) echo "checked='checked'"; ?>>Cash 
        <input name="payment_mode[]" type="checkbox" value="Cheque" <?php if (in_array('Cheque',$payment_mode1)) echo "checked='checked'"; ?> />Cheque 
       
        <br /></br>
        
   
        
       <div id="show_me">
            <input name="card_type[]"  id="master"  type="checkbox" value="Master" <?php if (in_array('Master',$card_type1)) echo "checked='checked'"; ?> />Master
            <input name="card_type[]"  id="visa" type="checkbox" value="Visa" <?php if (in_array('Visa',$card_type1)) echo "checked='checked'"; ?>/>Visa
            <input name="card_type[]"  id="disc" type="checkbox" value="Discover"  <?php if (in_array('Discover',$card_type1)) echo "checked='checked'"; ?>/>Discover
            <input name="card_type[]"  id="amex" type="checkbox" value="American Express"  <?php if (in_array('American Express',$card_type1)) echo "checked='checked'"; ?> />American Express 
            <input name="card_type[]"  id="other" type="checkbox" value="other"  <?php if (in_array('other',$card_type1)) echo "checked='checked'"; ?>/>Other
    
</div>

<br />
<br />

<label for="field5"><span>Global Message 1 <br />(Default Text on Statement)</span><textarea name="gl_message1" class="textarea-field" required ><?php echo $gl_message1 ?></textarea></label>
<label for="field5"><span>Global Message 2</span><textarea name="gl_message2" maxlength="120" class="textarea-field" placeholder="Maxlength of charectors is 120"><?php echo $gl_message2 ?></textarea></label>
<label for="field1"><span>Days Between Statement</span><!--<input type="text" class="input-field" name="day_bet_stmt" value="<?php echo $day_bet_stmt ?>" />-->
<select name="day_bet_stmt">
	<option value="30"  <?php if($day_bet_stmt == "30" ) echo "selected='selected'" ;?>>30</option>
    <option value="45" <?php if($day_bet_stmt == "45" ) echo "selected='selected'" ;?>>45</option>
    <!--<option value="90"  <?php if($day_bet_stmt == "90" ) echo "selected='selected'" ;?>>90</option>
    <option value="120"  <?php if($day_bet_stmt == "120" ) echo "selected='selected'" ;?>>120</option>-->
</select>
</label>
<label for="field1"><span>Minimum Balance Statement</span><input type="text" class="input-field" name="min_bal_stmt" value="<?php echo $min_bal_stmt ?>" required /></label>


<!------------------------------------------------Practice Information----------------------------------------------->

<div class="form-style-2-heading"> Practice Information</div>
<label for="field1"><span>Name</span><input type="text" class="input-field" name="practice_name" value="<?php echo $practice_name?>" required/></label>

<label for="field1"><span>Address</span><input type="text" class="input-field" name="practice_address" value="<?php echo $practice_address ?>" required/></label>

<label for="field1"><span>City</span><input type="text" class="input-field" name="practice_city" value="<?php echo $practice_city ?>" required/></label>

<label for="field1"><span>State</span><input type="text" class="input-field" name="practice_state" value="<?php echo $practice_state ?>" required/></label>

<label for="field1"><span>Zip</span><input type="text" class="input-field" name="practice_zip" value="<?php echo $practice_zip ?>" required/></label>

<label for="field1"><span>Phone Number</span><input type="text" class="input-field" name="practice_phone" value="<?php echo $practice_phone ?>" required/></label>

<label for="field1"><span>Email</span><input type="email" class="input-field" name="practice_email" value="<?php echo $practice_email ?>" placeholder="Enter a valid email address"/></label>

<label for="field1"><span>Website</span><input type="url" class="input-field" name="practice_web" value="<?php echo $practice_web ?>" /></label>

<!------------------------------------------------Billing Information----------------------------------------------->

<div class="form-style-2-heading">Billing Information</div>

<label for="field1"><span>Name</span><input type="text" class="input-field" name="billing_name" value="<?php echo $billing_name  ?>" required/></label>

<label for="field1"><span>Address</span><input type="text" class="input-field" name="billing_address" value="<?php echo $billing_address ?>" required/></label>

<label for="field1"><span>City</span><input type="text" class="input-field" name="billing_city" value="<?php echo $billing_city ?>" required/></label>

<label for="field1"><span>State</span><input type="text" class="input-field" name="billing_state" value="<?php echo $billing_state ?>" required/></label>

<label for="field1"><span>Zip</span><input type="text" class="input-field" name="billing_zip" value="<?php echo $billing_zip ?>" required/></label>

<label for="field1"><span>Phone Number</span><input type="text" class="input-field" name="billing_phone" value="<?php echo $billing_phone ?>" required/></label>

<label for="field1"><span>Email</span><input type="email" class="input-field" name="billing_email" value="<?php echo $billing_email ?>" placeholder="Enter a valid email address"/></label>


<!------------------------------------------------Administrator Information----------------------------------------------->


<div class="form-style-2-heading">Administrator</div>

<label for="field1"><span>Name</span><input type="text" class="input-field" name="admin_name" value="<?php echo $admin_name ?>" /></label>

<label for="field1"><span>Address</span><input type="text" class="input-field" name="admin_address" value="<?php echo $admin_address ?>" /></label>

<label for="field1"><span>City</span><input type="text" class="input-field" name="admin_city" value="<?php echo $admin_city ?>" /></label>

<label for="field1"><span>State</span><input type="text" class="input-field" name="admin_state" value="<?php echo $admin_state ?>" /></label>

<label for="field1"><span>Zip</span><input type="text" class="input-field" name="admin_zip" value="<?php echo $admin_zip ?>" /></label>

<label for="field1"><span>Phone Number</span><input type="text" class="input-field" name="admin_phone" value="<?php echo $admin_phone ?>" /></label>

<label for="field1"><span>Email</span><input type="email" class="input-field" name="admin_email" value="<?php echo $admin_email ?>" placeholder="Enter a valid email address"/></label>

<label for="field5"><span>Notes</span><textarea name="admin_note" class="textarea-field"><?php echo $admin_note ?></textarea></label>
<input type="hidden" name="mode" value="<?php echo $mode?>" />

<label><span>&nbsp;</span><input type="submit" value="Save" name="submit" /></label>
</form>

</div>

</body>
</html>
