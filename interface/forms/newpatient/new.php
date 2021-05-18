<?php
/**
 * Encounter form new script.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */




include_once("../../globals.php");
include_once("$srcdir/acl.inc");
include_once("$srcdir/lists.inc");
// Sai custom code start
require_once("$srcdir/patient.inc");

$spid='';



if($_GET['set_pid'])
$spid = $_GET['set_pid'];
else if($_SESSION['pid'])
$spid = $_SESSION['pid'];


if($spid=='')
{
?>
 <script>
 alert("You must first select or add a patient.");
top.window.parent.left_nav.loadFrame2('new','RTop','main/finder/dynamic_finder.php?from_enc=1');
</script> 
<?php
//require_once("../../main/finder/dynamic_finder.php");
}
else{
// Check permission to create encounters.
//if($_GET['set_pid']){
if($_GET['set_pid'])
$pid=$_GET['set_pid'];
else if($_SESSION['pid'])
$pid=$_SESSION['pid'];

$_GET['set_pid'] = $pid;
$_SESSION['pid'] = $pid;
// Sai custom code end
$tmp = getPatientData($pid, "squad");
if (($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) ||
  !acl_check_form('newpatient', '', array('write', 'addonly'))) {
    echo "<body>\n<html>\n";
    echo "<p>(" . xlt('New encounters not authorized') . ")</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

$viewmode = false;
// Sai custom code start
if($_GET['set_pid']){
$set_pid=$_GET['set_pid'];
echo "<input type='hidden' name='set_pid' id='set_pid' value='$set_pid'>";
}
//}
//else
//{?>
<!--<script>
top.window.parent.left_nav.loadFrame2('nen1','RBot','../../main/finder/dynamic_finder.php?from_enc=1');
</script>-->
<?php
//}
require_once("common.php");
}
// Sai custom code end
?>
