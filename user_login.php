<?php

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//echo "pawan==<br>";
$login_body_line = ' background="'.$rootdir.'/pic/aquabg.gif" ';

if(isset($_POST['usersubmit']) || isset($_GET['user'])){

	if(isset($_GET['user'])){
		$username= $_GET['user'];
	}
	else{
		$username= $_POST['authUser'];
	}
	
	// connection parameters
	$dbusername = "root";
	$dbpassword = "";
	$dbhostname = "127.0.0.1"; 
	$db = "openemr501_users";
	

	//connection to the database
	$dbhandle = mysqli_connect($dbhostname, $dbusername, $dbpassword,$db);
	
	//echo "Connected to MySQL<br>";
	
	//$selected = mysql_select_db("openemr_users_uat",$dbhandle)
	$selected = mysqli_select_db($dbhandle,$db);
	
	  
	  //execute the SQL query and return records
	$query = "SELECT id, username,client FROM user_clients where username='$username' and active=1 ORDER BY client";

	$result = mysqli_query($dbhandle,$query) ;

	
	$client_array=array();
	//fetch tha data from the database
	while ($row = mysqli_fetch_array($result)) {
	   //echo "ID:".$row{'id'}." Name:".$row{'username'};
	   
	   $client_array[]=$row['client'];
	}
	
	//close the connection
	mysqli_close($dbhandle);

}


$ignoreAuth=true;
//include_once("../globals.php");


?>
<html>
<head>
<?php //html_header_show(); ?>
<link rel=stylesheet href="../openemr/interface/themes/style_default.css" type="text/css">
<link rel=stylesheet href="../openemr/interface/themes/style_light_blue.css" type="text/css">

<script language='JavaScript' src="../../library/js/jquery-1.4.3.min.js"></script>
<script language='JavaScript'>

function validateForm(){

	var str = document.login_form.authUser.value;


	if( document.login_form.authUser.value == "" ){
		 alert( "Please Enter Username!" );
		 document.login_form.authUser.focus() ;
		 return false;
	}
	
	
}
function validateForm2(){

	var str = document.login_form.authUser.value;


	if( document.login_form.clearPass.value == "" ){
		 alert( "Please Enter Password!" );
		 document.login_form.clearPass.focus() ;
		 return false;
	}
	
	
}



</script>

</head>



<!-- code added for MSTR autologin by pawan----->

<div class="logobar">
<img style="position:absolute;top:0;left:0;"src=" ../openemr/interface/pic/logo.gif" />
<img style="position:absolute;top:35;right:0;"src=" ../openemr/interface/pic/CH_logo.png" /></div>
<div class="body_title" style="margin-top: 0px;
    position: relative;">
<span class="title_bar">payEHR Single Version 2.1.2</span>
</div>
</div>
<title>payEHR 5.0.1</title>
<body background="interface/pic/aquabg.gif" onLoad="javascript:document.login_form.authUser.focus();">
<span class="text"></span>
<center>

<?php
if( (!isset($_POST['usersubmit'])) && (!isset($_GET['user']))){
?>
	<form method="POST" action="user_login.php" target="_top" name="login_form" onSubmit="return(validateForm());">
<?php
}
if(isset($_POST['usersubmit']) && empty($client_array)){ ?>

	<form method="POST" action="user_login.php" target="_top" name="login_form" onSubmit="return(validateForm());">
<?php
}
else{ 
?>
    <form method="POST" action="../openemr501_alpha/interface/login/login.php" target="_top" name="login_form" >
<?php 
}?>


<table width=100% height="90%">
	<tr>
		<td valign=middle width=33%><?php echo $logocode;?></td>
		<td align='center' valign='middle' width=34%>
		<table>
		<?php if(isset($_POST['usersubmit']) && empty($client_array)){ ?>
				<tr><td colspan='2' class='text' style='color:red'>Username Invalid OR You dont have any client access. Please Contact Administrator.</td></tr>
        <?php
			  }
		?>
            
        <?php if (!isset($_POST['usersubmit']) && $_GET['login_fail'] == 1): ?>
        	    <tr><td colspan='2' class='text' style='color:red'>Invalid username or password.</td></tr>
        <?php endif; ?>
        <tr>
      		<td><span class="text"><strong>Username:</strong></span></td>
       		<td>
  				<?php if(isset($_POST['usersubmit']) && !empty($client_array)){  ?> 
        				<input type="text" size="10" id="authUser" name="authUser" value="<?php echo $username ?>" readonly>
        		<?php }
					else{
				?>
            			<input type="text" size="10" id="authUser" name="authUser" value="<?php echo $username ?>" required>	
				<?php } ?>
		
			</td>
        </tr>
        
			<tr>
				<td></td>
				<td></td>
			</tr>
        	<?php if((!isset($_POST['usersubmit'])) && (!isset($_GET['user']))){  ?>
			
            <tr>
            	<td colspan="2" align="center"><input type="submit"  value="Submit" name="usersubmit"  /></td>
            	<td></td>
            </tr>
            <?php
            	}
				if(isset($_POST['usersubmit']) && empty($client_array)){ ?>
				<tr>
            	<td colspan="2" align="center"><input type="submit"  value="Submit" name="usersubmit"  /></td>
            	<td></td>
            </tr>
			<?php
				}
			?>  
           
			<?php if((isset($_POST['usersubmit']) && !empty($client_array)) || (isset($_GET['user']))){  ?> 
             <tr>	
                 <td><span class="text"><strong>Password:</strong></span></td>
                 <td><input type="password" size="10" id="clearPass" name="clearPass" required></td>
             </tr>
                    
             <tr>
                 <td><span class="text"><strong>Select DB</strong></span></td>
                 <td>
                 <select name="clientname" style="width: 104px;">
                    <?php
                        foreach($client_array as $value){
                     ?>
                        	<option value="<?php echo $value?>"><?php echo $value ?></option>
                     <?php   
                        } // end of foreach
                     ?>
                  </select>
   				</td>
  			</tr>
    
        	<?php } // end of if ?>
       

			<tr>
			<td></td>
			<td></td>
			</tr>
			
			<tr>
			<td></td>
			<td></td>
			</tr>

			<tr>
            <td>&nbsp;</td>
            <td>
				<input type="hidden" name="authPass">
				<input type="hidden" name="authNewPass">
			
		<?php if( (isset($_POST['usersubmit']) && !empty($client_array)) || (isset($_GET['user']))){  ?> 
	
				 <input type="submit" onClick="javascript:this.form.authPass.value=SHA1(this.form.clearPass.value);" value='Login' name="loginsubmit">
  
    	<?php } ?>   
			</td></tr>

			<tr><td colspan='2' class='text' style='color:red'>
				<?php
                $ip=$_SERVER['REMOTE_ADDR'];
                
                
                
                ?>
			</td>
            </tr>
		</table>
	</td>

	<td width=33% align="right" valign="top">
	<span style="font-weight:bold; font-size:11px;">Sai payEHR Version V2.1.2</span>
	</center></p>

	</td>
	</table>

</form>

<address>

<a href="../../copyright_notice.html" target="main">Copyright Notice</a><br />
<p>
CPT<sup>&reg;</sup> 2017 American Medical Association. All rights reserved. Fee schedules, relative value units, conversion factors and/or related components are not assigned by AMA, are not part of CPT<sup>&reg;</sup>, and AMA is not recommending their use. AMA does not directly or indirectly practice medicine or dispense medical services. AMA assumes no liability for data contained or not contained herein. CPT<sup>&reg;</sup> is a registered trademark of the American Medical Association
</p>
</address>

</center>
</body>
</html>
