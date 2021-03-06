<?php
/**
 * The address book entry editor.
 * Available from Administration->Addr Book in the concurrent layout.
 *
 * Copyright (C) 2006-2010, 2016 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Improved slightly by tony@mi-squared.com 2011, added organization to view
 * and search
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://open-emr.org
 */

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/options.inc.php");
 use OpenEMR\Core\Header;

$popup = empty($_GET['popup']) ? 0 : 1;
$rtn_selection = 0;
if ($popup) {
    $rtn_selection = $_GET['popup'] == 2 ? 1 : 0;
}

 $form_fname = trim($_POST['form_fname']);
 $form_lname = trim($_POST['form_lname']);
 $form_specialty = trim($_POST['form_specialty']);
 $form_organization = trim($_POST['form_organization']);
 $form_abook_type = trim($_REQUEST['form_abook_type']);
 $form_external = $_POST['form_external'] ? 1 : 0;

$sqlBindArray = array();
$query = "SELECT u.*, lo.option_id AS ab_name, lo.option_value as ab_option FROM users AS u " .
  "LEFT JOIN list_options AS lo ON " .
  "list_id = 'abook_type' AND option_id = u.abook_type AND activity = 1 " .
  "WHERE u.active = 1 AND ( u.authorized = 1 OR u.username = '' ) ";
if ($form_organization) {
    $query .= "AND u.organization LIKE ? ";
    array_push($sqlBindArray, $form_organization."%");
}

if ($form_lname) {
    $query .= "AND u.lname LIKE ? ";
    array_push($sqlBindArray, $form_lname."%");
}

if ($form_fname) {
    $query .= "AND u.fname LIKE ? ";
    array_push($sqlBindArray, $form_fname."%");
}

if ($form_specialty) {
    $query .= "AND u.specialty LIKE ? ";
    array_push($sqlBindArray, "%".$form_specialty."%");
}

if ($form_abook_type) {
    $query .= "AND u.abook_type LIKE ? ";
    array_push($sqlBindArray, $form_abook_type);
}

if ($form_external) {
    $query .= "AND u.username = '' ";
}

if ($form_lname) {
    $query .= "ORDER BY u.lname, u.fname, u.mname";
} else if ($form_organization) {
    $query .= "ORDER BY u.organization";
} else {
    $query .= "ORDER BY u.organization, u.lname, u.fname";
}

$query .= " LIMIT 500";
echo $query;
$res = sqlStatement($query, $sqlBindArray);
?>

<!DOCTYPE html>
<html>

<head>

<?php Header::setupHeader(['common']); ?>

<title><?php echo xlt('Address Book'); ?></title>

<!-- style tag moved into proper CSS file -->

</head>

<body class="body_top">

<div class="container-fluid">
    <div class="nav navbar-fixed-top body_title">
        <div class="col-md-12">
            <h3><?php echo xlt('Address Book'); ?></h3>

        <form class='navbar-form' method='post' action='addrbook_list.php'
              onsubmit='return top.restoreSession()'>

            <div class="text-center">
                <div class="form-group">
                    <label><?php echo xlt('Organization') ?>:</label>
                    <input type='text' name='form_organization' size='10'
                           value='<?php echo attr($_POST['form_organization']); ?>'
                           class='inputtext' title='<?php echo xla("All or part of the organization") ?>'/>&nbsp;
                    <label><?php echo xlt('First Name') ?>:</label>
                    <input type='text' name='form_fname' size='10' value='<?php echo attr($_POST['form_fname']); ?>'
                           class='inputtext' title='<?php echo xla("All or part of the first name") ?>'/>&nbsp;
                    <label><?php echo xlt('Last Name') ?>:</label>
                    <input type='text' name='form_lname' size='10' value='<?php echo attr($_POST['form_lname']); ?>'
                           class='inputtext' title='<?php echo xla("All or part of the last name") ?>'/>&nbsp;
                    <label><?php echo xlt('Specialty') ?>:</label>
                    <input type='text' name='form_specialty' size='10' value='<?php echo attr($_POST['form_specialty']); ?>'
                           class='inputtext' title='<?php echo xla("Any part of the desired specialty") ?>'/>&nbsp;
                    <?php
                    echo xlt('Type') . ": ";
                    // Generates a select list named form_abook_type:
                    echo generate_select_list("form_abook_type", "abook_type", $_REQUEST['form_abook_type'], '', 'All');
                    ?>
                    <input type='checkbox' name='form_external' value='1'<?php if ($form_external) {
                        echo ' checked ';} ?>
                           title='<?php echo xla("Omit internal users?") ?>'/>
                    <?php echo xlt('External Only') ?>
                    <input type='button' class='btn btn-primary' value='<?php echo xla("Add New"); ?>'
                           onclick='doedclick_add(document.forms[0].form_abook_type.value)'/>&nbsp;&nbsp;
                    <input type='submit' title='<?php echo xla("Use % alone in a field to just sort on that column") ?>'
                           class='btn btn-primary' name='form_search' value='<?php echo xla("Search") ?>'/>
                </div>
            </div>
        </form>
    </div>
    </div>
<div style="margin-top: 110px;" class="table-responsive">
<table class="table table-condensed table-bordered table-striped table-hover">
 <thead>
  <th title='<?php echo xla('Click to view or edit'); ?>'><?php echo xlt('Organization'); ?></th>
  <th><?php echo xlt('Name'); ?></th>
  <th><?php echo xlt('Local'); ?></th><!-- empty for external -->
  <th><?php echo xlt('Type'); ?></th>
  <th><?php echo xlt('Specialty'); ?></th>
  <th><?php echo xlt('Phone(W)'); ?></th>
  <th><?php echo xlt('Mobile'); ?></th>
  <th><?php echo xlt('Fax'); ?></th>
  <th><?php echo xlt('Email'); ?></th>
  <th><?php echo xlt('Street'); ?></th>
  <th><?php echo xlt('City'); ?></th>
  <th><?php echo xlt('State'); ?></th>
  <th><?php echo xlt('Postal'); ?></th>
 </thead>
<?php
 $encount = 0;
while ($row = sqlFetchArray($res)) {
    ++$encount;
    $username = $row['username'];
    if (! $row['active']) {
        $username = '--';
    }

    $displayName = $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']; // Person Name
    if ($row['suffix'] >'') {
        $displayName .=", ".$row['suffix'];
    }

    if (acl_check('admin', 'practice') || (empty($username) && empty($row['ab_name']))) {
       // Allow edit, since have access or (no item type and not a local user)
        $trTitle = xl('Edit'). ' ' . $displayName;
        echo " <tr class='address_names detail' style='cursor:pointer' " .
        "onclick='doedclick_edit(" . $row['id'] . ")' title='".attr($trTitle)."'>\n";
    } else {
       // Do not allow edit, since no access and (item is a type or is a local user)
        $trTitle = $displayName . " (" . xl("Not Allowed to Edit") . ")";
        echo " <tr class='address_names detail' title='".attr($trTitle)."'>\n";
    }

    echo "  <td>" . text($row['organization']) . "</td>\n";
    echo "  <td>" . text($displayName) . "</td>\n";
    echo "  <td>" . ($username ? '*' : '') . "</td>\n";
    echo "  <td>" . generate_display_field(array('data_type'=>'1','list_id'=>'abook_type'), $row['ab_name']) . "</td>\n";
    echo "  <td>" . text($row['specialty']) . "</td>\n";
    echo "  <td>" . text($row['phonew1'])   . "</td>\n";
    echo "  <td>" . text($row['phonecell']) . "</td>\n";
    echo "  <td>" . text($row['fax'])       . "</td>\n";
    echo "  <td>" . text($row['email'])     . "</td>\n";
    echo "  <td>" . text($row['street'])    . "</td>\n";
    echo "  <td>" . text($row['city'])      . "</td>\n";
    echo "  <td>" . text($row['state'])     . "</td>\n";
    echo "  <td>" . text($row['zip'])       . "</td>\n";
    echo " </tr>\n";
}
?>
</table>
</div>

<?php if ($popup) { ?>
<script type="text/javascript" src="../../library/topdialog.js"></script>
<?php } ?>
<script type="text/javascript" src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>

<script language="JavaScript">

<?php if ($popup) {
    require($GLOBALS['srcdir'] . "/restoreSession.php");
} ?>

// Callback from popups to refresh this display.
function refreshme() {
 // location.reload();
 document.forms[0].submit();
}

// Process click to pop up the add window.
function doedclick_add(type) {
 top.restoreSession();
 dlgopen('addrbook_edit.php?type=' + type, '_blank', 650, (screen.availHeight * 75/100));
}

// Process click to pop up the edit window.
function doedclick_edit(userid) {
    let rtn_selection = <?php echo $rtn_selection ?>;
 if(rtn_selection) {
    dlgclose('contactCallBack', userid);
 }
 top.restoreSession();
 dlgopen('addrbook_edit.php?userid=' + userid, '_blank', 650, (screen.availHeight * 75/100));
}

// Removed .ready and fancy box (no longer used here) - 10/23/17 sjp

</script>
</div>
</body>
</html>
