<?php
include_once("../../globals.php");

// Page Created by Gangeya for BUG ID 8790 : on Dr. Ghandour's flow change request.

// allow a custom 'Assign' form
// this form is created to assign added documentations (SOAP, ROS, ROS Checks, Vitals)
// prior selecting any encounter by just selecting a patient .
// this functionality will ease user to add documentation & later assign it to desired encounters

$deleteform = $incdir . "/forms/" . $_GET["formname"]."/assign.php";
if (file_exists($deleteform)) {
    include_once($deleteform);
    exit;
}


// when the Cancel button is pressed, where do we go?
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

if ($_POST['confirm']) {
    // set the encounter id of the assigned encounter to the indicated form
    $sql = "update forms set encounter= $encounter where id=".$_POST['id'];
    if ($_POST['id'] != "*" && $_POST['id'] != '') sqlInsert($sql);
    // log the event   
    
    // redirect back to the encounter
    $address = "{$GLOBALS['rootdir']}/patient_file/encounter/$returnurl";
    echo "\n<script language='Javascript'>top.restoreSession();window.location='$address';</script>\n";
    exit;
}
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

</head>

<body class="body_top">

<span class="title">Assign Encounter Form</span>

<form method="post" action="<?php echo $rootdir;?>/patient_file/encounter/assign_form.php" name="my_form" id="my_form">
<?php
// output each GET variable as a hidden form input
foreach ($_GET as $key => $value) {
    echo '<input type="hidden" id="'.$key.'" name="'.$key.'" value="'.$value.'"/>'."\n";
}
?>
<input type="hidden" id="confirm" name="confirm" value="1"/>
<p>
You are about to assign form '<?php echo $_GET['formname']; ?>' to <?php xl('This Encounter','e'); ?>.
</p>
<input type="button" id="confirmbtn" name="confirmbtn" value="Yes, Assign this form">
<input type="button" id="cancel" name="cancel" value="Cancel">
</form>

</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#confirmbtn").click(function() { return ConfirmAssign(); });
    $("#cancel").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>'; });
});

function ConfirmAssign() {
    if (confirm("This action cannot be undone. Are you sure you wish to assign this form?")) {
        top.restoreSession();
        $("#my_form").submit();
        return true;
    }
    return false;
}

</script>

</html>
