<?php

/**
 * This script Assign acl 'Emergency login'.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/auth.inc");

use OpenEMR\Core\Header;

if (!acl_check('admin', 'users')) {
    die(xlt('Access denied'));
}

/*************database connection added for BUG ID 10911 single version code ************************/
	   // connection parameters
    $dbusername = "root";
    $dbpassword = "";
    $dbhostname = "127.0.0.1"; 
    $db = "openemr501_users";
    
	// connection parameters
		
	// connection parameters
	/*$dbusername = "testdbadmin";
	$dbpassword = "P@s$53B!lh#3";
	$dbhostname = "50.97.54.99"; */
	
	
		
		$client = $_SESSION['site_id'];
		$client_name = strtoupper($client);
		
		
		//connection to the database
		$dbhandle = mysqli_connect($dbhostname, $dbusername, $dbpassword,$db)
		  or die("Unable to connect to MySQL");
		//echo "Connected to MySQL<br>";
		
		
		$selected = mysqli_select_db($dbhandle,$db)
		  or die("Could not select database");
		  
		 // $selected = mysql_select_db("openemr_users_uat",$dbhandle)
	//or die("Could not select examples");
/************************************************************************************************************/

$alertmsg = '';
$bg_msg = '';
$set_active_msg=0;
$show_message=0;

/* Sending a mail to the admin when the breakglass user is activated only if $GLOBALS['Emergency_Login_email'] is set to 1 */
$bg_count=count($_POST['access_group']);
$mail_id = explode(".", $SMTP_HOST);
for ($i=0; $i<$bg_count; $i++) {
    if (($_POST['access_group'][$i] == "Emergency Login") && ($_POST['active'] == 'on') && ($_POST['pre_active'] == 0)) {
        if (($_POST['get_admin_id'] == 1) && ($_POST['admin_id'] != "")) {
            $res = sqlStatement("select username from users where id= ? ", array($_POST["id"]));
            $row = sqlFetchArray($res);
            $uname=$row['username'];
            $mail = new MyMailer();
            $mail->From = $GLOBALS["practice_return_email_path"];
            $mail->FromName = "Administrator OpenEMR";
            $text_body  = "Hello Security Admin,\n\n The Emergency Login user ".$uname.
                                              " was activated at ".date('l jS \of F Y h:i:s A')." \n\nThanks,\nAdmin OpenEMR.";
            $mail->Body = $text_body;
            $mail->Subject = "Emergency Login User Activated";
            $mail->AddAddress($_POST['admin_id']);
            $mail->Send();
        }
    }
}
/* To refresh and save variables in mail frame */
if (isset($_POST["privatemode"]) && $_POST["privatemode"] =="user_admin") {
    if ($_POST["mode"] == "update") {
        if (isset($_POST["username"])) {
            // $tqvar = addslashes(trim($_POST["username"]));
            $tqvar = trim(formData('username', 'P'));
            $user_data = sqlFetchArray(sqlStatement("select * from users where id= ? ", array($_POST["id"])));
            sqlStatement("update users set username='$tqvar' where id= ? ", array($_POST["id"]));
            sqlStatement("update `groups` set user='$tqvar' where user= ?", array($user_data["username"]));
            //echo "query was: " ."update `groups` set user='$tqvar' where user='". $user_data["username"]  ."'" ;
        }
        if ($_POST["taxid"]) {
            $tqvar = formData('taxid', 'P');
            sqlStatement("update users set federaltaxid='$tqvar' where id= ? ", array($_POST["id"]));
        }
        // Sai custom code start
      /*
      * Add code by kiran to update user email feild.
      * 
      */
      if ($_GET["email"]) {
        $tqvar = formData('email','G');
        sqlStatement("update users set email='$tqvar' where id=? ", array($_POST["id"]));
      }
   // Sai custom code end


        if ($_POST["state_license_number"]) {
            $tqvar = formData('state_license_number', 'P');
            sqlStatement("update users set state_license_number='$tqvar' where id= ? ", array($_POST["id"]));
        }

        if ($_POST["drugid"]) {
            $tqvar = formData('drugid', 'P');
            sqlStatement("update users set federaldrugid='$tqvar' where id= ? ", array($_POST["id"]));
        }

        if ($_POST["upin"]) {
            $tqvar = formData('upin', 'P');
            sqlStatement("update users set upin='$tqvar' where id= ? ", array($_POST["id"]));
        }

        if ($_POST["npi"]) {
            $tqvar = formData('npi', 'P');
            sqlStatement("update users set npi='$tqvar' where id= ? ", array($_POST["id"]));
        }

        if ($_POST["taxonomy"]) {
            $tqvar = formData('taxonomy', 'P');
            sqlStatement("update users set taxonomy = '$tqvar' where id= ? ", array($_POST["id"]));
        }

        if ($_POST["lname"]) {
            $tqvar = formData('lname', 'P');
            sqlStatement("update users set lname='$tqvar' where id= ? ", array($_POST["id"]));
        }

        if ($_POST["job"]) {
            $tqvar = formData('job', 'P');
            sqlStatement("update users set specialty='$tqvar' where id= ? ", array($_POST["id"]));
        }

        if ($_POST["mname"]) {
              $tqvar = formData('mname', 'P');
              sqlStatement("update users set mname='$tqvar' where id= ? ", array($_POST["id"]));
        }
      // Sai custom code start
        if ($_POST["facility_id"]) {
              $tqvar = formData('facility_id', 'P');
              sqlStatement("update users set facility_id = '$tqvar' where id = ? ", array($_POST["id"]));
              //(CHEMED) Update facility name when changing the id
            if($tqvar!=0){// code change for select option pawan
                  sqlStatement("UPDATE users, facility SET users.facility = facility.name WHERE facility.id = '$tqvar' AND users.id = ? ",array($_POST["id"]));
            }
            else{ 
            sqlStatement("UPDATE users, facility SET users.facility = '' where users.id = ? ",array($_POST["id"]));
            }
              //END (CHEMED)
        }
       /*
      * Add code by kiran to update last modify and last modify date.
      * 
      */
      $active_date = date('Y-m-d H:i:s');
      sqlStatement("update users set modified_by='".$_SESSION['authUserID']."', modified_date = '".$active_date."' where id= ? ",array($_POST["id"]));
      // end edited by kiran
  // Sai custom code start  
        if ($GLOBALS['restrict_user_facility'] && $_POST["schedule_facility"]) {
            sqlStatement("delete from users_facility
            where tablename='users'
            and table_id= ?
            and facility_id not in (" . implode(",", $_POST['schedule_facility']) . ")", array($_POST["id"]));
            foreach ($_POST["schedule_facility"] as $tqvar) {
                sqlStatement("replace into users_facility set
                facility_id = '$tqvar',
                tablename='users',
                table_id =?", array($_POST["id"]));
            }
        }

        if ($_POST["fname"]) {
              $tqvar = formData('fname', 'P');
              sqlStatement("update users set fname='$tqvar' where id= ? ", array($_POST["id"]));
        }
	//added by Gangeya to update any user as Servicing provider JIRA ID PAYEHR-292
		$servar  = $_POST["servicingprovider"] ? 1 : 0;
		sqlStatement("update users set isServicingProvider = $servar where id={$_POST["id"]}");

        if (isset($_POST['default_warehouse'])) {
            sqlStatement("UPDATE users SET default_warehouse = '" .
            formData('default_warehouse', 'P') .
            "' WHERE id = '" . formData('id', 'P') . "'");
        }

        if (isset($_POST['irnpool'])) {
            sqlStatement("UPDATE users SET irnpool = '" .
            formData('irnpool', 'P') .
            "' WHERE id = '" . formData('id', 'P') . "'");
        }

        if ($_POST["adminPass"] && $_POST["clearPass"]) {
              require_once("$srcdir/authentication/password_change.php");
              $clearAdminPass=$_POST['adminPass'];
              $clearUserPass=$_POST['clearPass'];
              $password_err_msg="";
              $success=update_password($_SESSION['authId'], $_POST['id'], $clearAdminPass, $clearUserPass, $password_err_msg);
            if (!$success) {
                error_log($password_err_msg);
                $alertmsg.=$password_err_msg;
            }
        }

        $tqvar  = $_POST["authorized"] ? 1 : 0;
        $actvar = $_POST["active"]     ? 1 : 0;
        $calvar = $_POST["calendar"]   ? 1 : 0;
   // Sai custom code start
    //Condition added by Gangeya 
    //BUG ID 10758 : active/inactive user list
     
     
    // code added by pawan 21-03-2017
    if(empty($_GET['form_term_date'])){
      $termDate = '0000-00-00 00:00:00';
    }
    else{
      $termDate =$_GET['form_term_date'];
      
    }
   

        sqlStatement("UPDATE users SET authorized = $tqvar, active = $actvar, " .
        " term_date = '".$termDate."',calendar = $calvar, see_auth = ? WHERE " .
        "id = ? ", array($_POST['see_auth'], $_POST["id"]));
 // Sai custom code end 
      //Display message when Emergency Login user was activated
        $bg_count=count($_POST['access_group']);
        for ($i=0; $i<$bg_count; $i++) {
            if (($_POST['access_group'][$i] == "Emergency Login") && ($_POST['pre_active'] == 0) && ($actvar == 1)) {
                $show_message = 1;
            }
        }

        if (($_POST['access_group'])) {
            for ($i=0; $i<$bg_count; $i++) {
                if (($_POST['access_group'][$i] == "Emergency Login") && ($_POST['user_type']) == "" && ($_POST['check_acl'] == 1) && ($_POST['active']) != "") {
                    $set_active_msg=1;
                }
            }
        }

        if ($_POST["comments"]) {
            $tqvar = formData('comments', 'P');
            sqlStatement("update users set info = '$tqvar' where id = ? ", array($_POST["id"]));
        }

        $erxrole = formData('erxrole', 'P');
        sqlStatement("update users set newcrop_user_role = '$erxrole' where id = ? ", array($_POST["id"]));

        if ($_POST["physician_type"]) {
            $physician_type = formData('physician_type');
            sqlStatement("update users set physician_type = '$physician_type' where id = ? ", array($_POST["id"]));
        }

        if ($_POST["main_menu_role"]) {
              $mainMenuRole = filter_input(INPUT_POST, 'main_menu_role');
              sqlStatement("update `users` set `main_menu_role` = ? where `id` = ? ", array($mainMenuRole, $_POST["id"]));
        }

        if ($_POST["patient_menu_role"]) {
            $patientMenuRole = filter_input(INPUT_POST, 'patient_menu_role');
            sqlStatement("update `users` set `patient_menu_role` = ? where `id` = ? ", array($patientMenuRole, $_POST["id"]));
        }

        if ($_POST["erxprid"]) {
            $erxprid = formData('erxprid', 'P');
            sqlStatement("update users set weno_prov_id = '$erxprid' where id = ? ", array($_POST["id"]));
        }

        // Set the access control group of user
        $user_data = sqlFetchArray(sqlStatement("select username from users where id= ?", array($_POST["id"])));
        set_user_aro(
            $_POST['access_group'],
            $user_data["username"],
            formData('fname', 'P'),
            formData('mname', 'P'),
            formData('lname', 'P')
        );
    }
}
/* To refresh and save variables in mail frame  - Arb*/
if (isset($_POST["mode"])) {
    if ($_POST["mode"] == "new_user") {
        if ($_POST["authorized"] != "1") {
            $_POST["authorized"] = 0;
        }

        // $_POST["info"] = addslashes($_POST["info"]);

        $calvar = $_POST["calendar"] ? 1 : 0;

        $res = sqlStatement("select distinct username from users where username != ''");
        $doit = true;
        while ($row = sqlFetchArray($res)) {
            if ($doit == true && $row['username'] == trim(formData('rumple'))) {
                $doit = false;
            }
        }
 // Sai custom code start
    // check duplicate email by kiran malve - 29-01-2017
    $emailcheck = "select username from users where email = '".formData('email')."'";
    $duplicateres = sqlStatement($emailcheck);

   // $duplicateEmail = mysql_fetch_array($duplicateres);
    $duplicateEmail = sqlFetchArray($duplicateres);
    //print_r($duplicateEmail);
    if(isset($duplicateEmail['username']) && !empty($duplicateEmail['username']))
    {
      $alertmsg .=  "Can not enter duplicate email address.";
      $doit = false;
      //print "coming";exit;
    }
    //end check duplicate email by kiran malve - 29-01-2017
 // Sai custom code end
        if ($doit == true) {
            require_once("$srcdir/authentication/password_change.php");

          //if password expiration option is enabled,  calculate the expiration date of the password
            if ($GLOBALS['password_expiration_days'] != 0) {
                $exp_days = $GLOBALS['password_expiration_days'];
                $exp_date = date('Y-m-d', strtotime("+$exp_days days"));
            }
       // Sai custom code start
    else{
      $exp_date='0000-00-00 00:00:00';
      
    }
    // echo $exp_date;
  //  die;
    //added by Gangeya BUG ID 10758 : active date at the time of adding a user.
    $active_date = date('Y-m-d H:i:s');
    
    //Query updated by Gangeya BUG ID 10758

            $insertUserSQL=
            "insert into users set " .
            "username = '"         . trim(formData('rumple')) .
            "', password = '"      . 'NoLongerUsed'                  .
            "', fname = '"         . trim(formData('fname')) .
            "', mname = '"         . trim(formData('mname')) .
            "', lname = '"         . trim(formData('lname')) .
          /*
          * Add code by kiran to update user email feild.
          * 
          */
        "', email = '"         . trim(formData('email')) .
        "', isServicingProvider = '"         . trim(formData('servicingprovider')) .
            "', federaltaxid = '"  . trim(formData('federaltaxid')) .
            "', state_license_number = '"  . trim(formData('state_license_number')) .
            "', newcrop_user_role = '"  . trim(formData('erxrole')) .
            "', physician_type = '"  . trim(formData('physician_type')) .
            "', main_menu_role = '"  . trim(formData('main_menu_role')) .
            "', patient_menu_role = '"  . trim(formData('patient_menu_role')) .
            "', weno_prov_id = '"  . trim(formData('erxprid')) .
            "', authorized = '"    . trim(formData('authorized')) .
            "', info = '"          . trim(formData('info')) .
            "', federaldrugid = '" . trim(formData('federaldrugid')) .
            "', upin = '"          . trim(formData('upin')) .
            "', npi  = '"          . trim(formData('npi')).
            "', taxonomy = '"      . trim(formData('taxonomy')) .
            "', facility_id = '"   . trim(formData('facility_id')) .
            "', specialty = '"     . trim(formData('specialty')) .
            "', see_auth = '"      . trim(formData('see_auth')) .
        "', cal_ui = '"        . trim(formData('cal_ui'       )) .
            "', default_warehouse = '" . trim(formData('default_warehouse')) .
            "', irnpool = '"       . trim(formData('irnpool')) .
            "', calendar = '"      . $calvar                         .
            "', pwd_expiration_date = '" . trim("$exp_date") .
    "', active_date = '" . trim("$active_date") .
     "', created_date = '" . trim("$active_date") .          
    "', created_by = '" . trim($_SESSION['authUserID']) .                      
            "'";

     // $prov_id = idSqlStatement($insertUserSQL);
      
      	/*******code added for BUG ID 10911 Single version code start**************************/
		
		
		  $client_user = trim(formData('rumple'));
		  //execute the SQL query and return records
		  $dbquery ="INSERT INTO user_clients (username,client,active) values ('$client_user','$client_name',1)";
		  //echo $dbquery; 
		 // $result = mysql_query($dbquery,$dbhandle);
          $result = mysqli_query($dbhandle,$dbquery) ;
		
		
		/****************************end******************************************************/

     
 // Sai custom code end 

            $clearAdminPass=$_POST['adminPass'];
            $clearUserPass=$_POST['stiltskin'];
            $password_err_msg="";
            $prov_id="";
            $success = update_password(
                $_SESSION['authId'],
                0,
                $clearAdminPass,
                $clearUserPass,
                $password_err_msg,
                true,
                $insertUserSQL,
                trim(formData('rumple')),
                $prov_id
            );
            error_log($password_err_msg);
            $alertmsg .=$password_err_msg;
            if ($success) {
                  //set the facility name from the selected facility_id
                  sqlStatement("UPDATE users, facility SET users.facility = facility.name WHERE facility.id = '" . trim(formData('facility_id')) . "' AND users.username = '" . trim(formData('rumple')) . "'");

                  sqlStatement("insert into `groups` set name = '" . trim(formData('groupname')) .
                    "', user = '" . trim(formData('rumple')) . "'");

                if (trim(formData('rumple'))) {
                              // Set the access control group of user
                              set_user_aro(
                                  $_POST['access_group'],
                                  trim(formData('rumple')),
                                  trim(formData('fname')),
                                  trim(formData('mname')),
                                  trim(formData('lname'))
                              );
                }
            }
        } else {
            $alertmsg .= xl('User', '', '', ' ') . trim(formData('rumple')) . xl('already exists.', '', ' ');
        }

        if ($_POST['access_group']) {
            $bg_count=count($_POST['access_group']);
            for ($i=0; $i<$bg_count; $i++) {
                if ($_POST['access_group'][$i] == "Emergency Login") {
                      $set_active_msg=1;
                }
            }
        }
    } else if ($_POST["mode"] == "new_group") {
        $res = sqlStatement("select distinct name, user from `groups`");
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result[$iter] = $row;
        }

        $doit = 1;
        foreach ($result as $iter) {
            if ($doit == 1 && $iter{"name"} == trim(formData('groupname')) && $iter{"user"} == trim(formData('rumple'))) {
                $doit--;
            }
        }

        if ($doit == 1) {
            sqlStatement("insert into `groups` set name = '" . trim(formData('groupname')) .
            "', user = '" . trim(formData('rumple')) . "'");
        } else {
            $alertmsg .= "User " . trim(formData('rumple')) .
            " is already a member of group " . trim(formData('groupname')) . ". ";
        }
    }
}
if (isset($_GET["mode"])) {
  /*******************************************************************
  // This is the code to delete a user.  Note that the link which invokes
  // this is commented out.  Somebody must have figured it was too dangerous.
  //
  if ($_GET["mode"] == "delete") {
    $res = sqlStatement("select distinct username, id from users where id = '" .
      $_GET["id"] . "'");
    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
      $result[$iter] = $row;

    // TBD: Before deleting the user, we should check all tables that
    // reference users to make sure this user is not referenced!

    foreach($result as $iter) {
      sqlStatement("delete from `groups` where user = '" . $iter{"username"} . "'");
    }
    sqlStatement("delete from users where id = '" . $_GET["id"] . "'");
  }
  *******************************************************************/

    if ($_GET["mode"] == "delete_group") {
        $res = sqlStatement("select distinct user from `groups` where id = ?", array($_GET["id"]));
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result[$iter] = $row;
        }

        foreach ($result as $iter) {
            $un = $iter{"user"};
        }

        $res = sqlStatement("select name, user from `groups` where user = '$un' " .
        "and id != ?", array($_GET["id"]));

        // Remove the user only if they are also in some other group.  I.e. every
        // user must be a member of at least one group.
        if (sqlFetchArray($res) != false) {
              sqlStatement("delete from `groups` where id = ?", array($_GET["id"]));
        } else {
              $alertmsg .= "You must add this user to some other group before " .
                "removing them from this group. ";
        }
    }
}
// added for form submit's from usergroup_admin_add and user_admin.php
// sjp 12/29/17
if (isset($_REQUEST["mode"])) {
    exit(trim($alertmsg));
}

$form_inactive = empty($_REQUEST['form_inactive']) ? false : true;

?>
<html>
<head>
<title><?php echo xlt('User / Groups');?></title>

<?php Header::setupHeader(['common','jquery-ui']); ?>

<script type="text/javascript">

$(document).ready(function(){

    tabbify();

    $(".medium_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 660, 450, '', '', {
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

});

function authorized_clicked() {
 var f = document.forms[0];
 f.calendar.disabled = !f.authorized.checked;
 f.calendar.checked  =  f.authorized.checked;
}

</script>
 <!-- Sai custom code start -->
<script language="javascript">

document.onkeydown = function(evt)
{ 
  evt = evt || window.event;
    if (evt.keyCode == 78 && evt.altKey) {
        top.window.parent.left_nav.loadFrame2('new0','RTop','new/new.php');
    }
  else if (evt.keyCode == 110 && evt.altKey) 
  {
        top.window.parent.left_nav.loadFrame2('new0','RTop','new/new.php');
    }
  else if (evt.keyCode == 85 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','usergroup/usergroup_admin.php');
  }
  else if (evt.keyCode == 117 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','usergroup/usergroup_admin.php');
  }
  else if (evt.keyCode == 73 && evt.altKey)
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','../controller.php?practice_settings&insurance_company&action=edit');
  }
  else if (evt.keyCode ==  105 && evt.altKey)
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','../controller.php?practice_settings&insurance_company&action=edit');
  }
  else if (evt.keyCode == 74 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?autoloaded=1&calenc=');
  }
  else if (evt.keyCode == 106 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('nen1','RTop','forms/newpatient/new.php?autoloaded=1&calenc=');
  } 
  else if (evt.keyCode == 75 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('adm0','RTop','usergroup/facilities.php');
  } 
  else if (evt.keyCode == 107 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('adm0','RTop','usergroup/facilities.php');
  }
  else if (evt.keyCode == 76 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('ens1','RTop','patient_file/history/encounters.php');
  }
  else if (evt.keyCode == 108 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('ens1','RTop','patient_file/history/encounters.php');
  }
  else if (evt.keyCode == 77 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('cal0','RTop','main/main_info.php');
  }
  else if (evt.keyCode == 109 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('cal0','RTop','main/main_info.php');
  }
  else if (evt.keyCode == 79 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','billing/search_payments.php');
  }
  else if (evt.keyCode == 111 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','billing/search_payments.php');
  }
  else if (evt.keyCode == 80 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','billing/new_payment.php');
  }
  else if (evt.keyCode == 112 && evt.altKey) 
  {     
    top.window.parent.left_nav.loadFrame2('new0','RTop','billing/new_payment.php');
  }
  else if (evt.keyCode == 81 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('dem1','RTop','patient_file/summary/demographics.php');
  }
  else if (evt.keyCode == 113 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('dem1','RTop','patient_file/summary/demographics.php');
  }
  else if (evt.keyCode == 82 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('bil0','RTop','billing/billing_report.php');
  }
  else if (evt.keyCode == 114 && evt.altKey) 
  { 
    top.window.parent.left_nav.loadFrame2('bil0','RTop','billing/billing_report.php');
  }
  if (evt.keyCode == 88 && evt.altKey) 
  {
    deleteme();
  } 
  if (evt.keyCode == 120 && evt.altKey) 
  {
    deleteme();
  }
  else 
  {
    return true; // it's not a key we recognize, move on...
  }
  return false;
}

</script>
 <!-- Sai custom code end -->
</head>
<body class="body_top">

<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class="page-title">
                <h2><?php echo xlt('User / Groups');?></h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="btn-group">
                <a href="usergroup_admin_add.php" class="medium_modal btn btn-default btn-add"><?php echo xlt('Add User'); ?></a>
                <a href="facility_user.php" class="btn btn-default btn-show"><?php echo xlt('View Facility Specific User Information'); ?></a>
            </div>
            <form name='userlist' method='post' style="display: inline;" class="form-inline" class="pull-right" action='usergroup_admin.php' onsubmit='return top.restoreSession()'>
                <div class="checkbox">
                    <label for="form_inactive">
                        <input type='checkbox' class="form-control" id="form_inactive" name='form_inactive' value='1' onclick='submit()' <?php echo ($form_inactive) ? 'checked ' : ''; ?> >
                        <?php echo xlt('Include inactive users'); ?>
                    </label>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <?php
            if ($set_active_msg == 1) {
                echo "<div class='alert alert-danger'>".xlt('Emergency Login ACL is chosen. The user is still in active state, please de-activate the user and activate the same when required during emergency situations. Visit Administration->Users for activation or de-activation.')."</div><br>";
            }

            if ($show_message == 1) {
                echo "<div class='alert alert-danger'>".xlt('The following Emergency Login User is activated:')." "."<b>".text($_GET['fname'])."</b>"."</div><br>";
                echo "<div class='alert alert-danger'>".xlt('Emergency Login activation email will be circulated only if following settings in the interface/globals.php file are configured:')." \$GLOBALS['Emergency_Login_email'], \$GLOBALS['Emergency_Login_email_id']</div>";
            }

            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th><?php echo xlt('Username'); ?></th>
                        <th><?php echo xlt('Real Name'); ?></th>
                        <th><?php echo xlt('Additional Info'); ?></th>
                        <th><?php echo xlt('Authorized'); ?>?</th>
                        <th></th>
                    </tr>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM users WHERE username != '' ";
                        if (!$form_inactive) {
                            $query .= "AND active = '1' ";
                        }

                        $query .= "ORDER BY username";
                        $res = sqlStatement($query);
                        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                            $result4[$iter] = $row;
                        }

                        foreach ($result4 as $iter) {
                            if ($iter{"authorized"}) {
                                $iter{"authorized"} = xl('yes');
                            } else {
                                $iter{"authorized"} = "";
                            }

                            print "<tr>
                                <td>
                                <b><a href='user_admin.php?id=" . attr($iter{"id"}) .
                                "' class='medium_modal' onclick='top.restoreSession()'>" . text($iter{"username"}) . "</a></b>" ."&nbsp;</td>
                                <td>" . text($iter{"fname"}) . ' ' . text($iter{"lname"}) ."&nbsp;</td>
                                <td>" . text($iter{"info"}) . "&nbsp;</td>
                                <td align='left'><span>" .text($iter{"authorized"}) . "&nbsp;</td>";
                            print "<td><!--<a href='usergroup_admin.php?mode=delete&id=" . attr($iter{"id"}) .
                                "' class='link_submit'>[Delete]</a>--></td>";
                            print "</tr>\n";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            if (empty($GLOBALS['disable_non_default_groups'])) {
                $res = sqlStatement("select * from `groups` order by name");
                for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                    $result5[$iter] = $row;
                }

                foreach ($result5 as $iter) {
                    $grouplist{$iter{"name"}} .= $iter{"user"} .
                        "(<a class='link_submit' href='usergroup_admin.php?mode=delete_group&id=" .
                        attr($iter{"id"}) . "' onclick='top.restoreSession()'>" . xlt('Remove') . "</a>), ";
                }

                foreach ($grouplist as $groupname => $list) {
                    print "<span class='bold'>" . text($groupname) . "</span><br>\n<span>" .
                        text(substr($list, 0, strlen($list)-2)) . "</span><br>\n";
                }
            }
            ?>
        </div>
    </div>
</div>
<script language="JavaScript">
<?php
if ($alertmsg = trim($alertmsg)) {
    echo "alert('$alertmsg');\n";
}
?>
</script>
</body>
</html>
