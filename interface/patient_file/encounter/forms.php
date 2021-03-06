<?php
/**
 * forms.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/encounter.inc");
require_once("$srcdir/group.inc");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/amc.php");
require_once $GLOBALS['srcdir'].'/ESign/Api.php';
require_once("$srcdir/../controllers/C_Document.class.php");

use ESign\Api;
use OpenEMR\Core\Header;

$reviewMode = false;
if (!empty($_REQUEST['review_id'])) {
    $reviewMode = true;
    $encounter=sanitizeNumber($_REQUEST['review_id']);
}

$is_group = ($attendant_type == 'gid') ? true : false;
if ($attendant_type == 'gid') {
    $groupId = $therapy_group;
}
$attendant_id = $attendant_type == 'pid' ? $pid : $therapy_group;
if ($is_group && !acl_check("groups", "glog", false, array('view','write'))) {
    echo xlt("access not allowed");
    exit();
}

?>
<html>
<!-- sai custom code start -->
<?php 
if($_POST['Reopen_encounter'])
{

reopen_encounter($_POST['Encounter']);

print '<script type="text/javascript">';
print 'alert("Claim '.$_POST['Encounter'].'  has been re-opened.")';
print '</script>';   


}
?>
<?php if($_POST['Update_Status_Btn'])
{
	/**** code added for validation for DX linking BUG ID 10348********/
	
	// modified by Sonali : 10412: IP/DP issue
	$ip_dp_query= sqlStatement("select IP_DP from patient_data where pid=$pid");
	$ip_dp_row=sqlFetchArray($ip_dp_query);
	$ip_dp = $ip_dp_row['IP_DP'];
	if($_POST['Encounter_Status']=="Unbilled Demo Pending"){
		if($ip_dp!="IP|DP" && $ip_dp!="DP"){
			$dpflag=1;
				print '<script type="text/javascript">';
				print 'alert("Can\'t update status. Please check DP on demographics page.")';
				print '</script>'; 					
			}
			else{
		
				if(update_encounter($_POST['Encounter_Status'],$_POST['Encounter']))
				{
				print '<script type="text/javascript">';
				print 'alert("Status of encounter '.$_POST['Encounter'].' : '.$_POST['Encounter_Status'].'")';
				print '</script>';   
				}
				else
				{
				print '<script type="text/javascript">';
				print 'alert("failed")';
				print '</script>'; 
				}
			
			} // -----
	}
	else if($_POST['Encounter_Status']=="Unbilled Insurance Pending"){
		if($ip_dp!="IP|DP" && $ip_dp!="IP"){
			$ipflag=1;
				print '<script type="text/javascript">';
				print 'alert("Can\'t update status. Please check IP on demographics page.")';
				print '</script>'; 	
			}
			else{
		
				if(update_encounter($_POST['Encounter_Status'],$_POST['Encounter']))
				{
				print '<script type="text/javascript">';
				print 'alert("Status of encounter '.$_POST['Encounter'].' : '.$_POST['Encounter_Status'].'")';
				print '</script>';   
				}
				else
				{
				print '<script type="text/javascript">';
				print 'alert("failed")';
				print '</script>'; 
				}
			
			} // -----
	}
	else {		
	$encounter_id=$_POST['Encounter'];
	//Update By Gangeya BUG ID 10639 : added activity condition in below query.
	$dx_query =sqlStatement("select * from billing where encounter=$encounter_id AND code_type='CPT4' AND activity = 1");
	//$dx_row=sqlFetchArray($dx_query);
	//print_r($dx_row);
	while($dx_row=sqlFetchArray($dx_query)){
		$justify = $dx_row['justify'];
		if(empty($justify))
			$dxflag=1;	
	}	
	if($dxflag!=1){

		if(update_encounter($_POST['Encounter_Status'],$_POST['Encounter']))
		{
		print '<script type="text/javascript">';
		print 'alert("Status of encounter '.$_POST['Encounter'].' : '.$_POST['Encounter_Status'].'")';
		print '</script>';   
		}
		else
		{
		print '<script type="text/javascript">';
		print 'alert("failed")';
		print '</script>'; 
		}
	}
	else{
		print '<script type="text/javascript">';
		print 'alert("DX Linking Missing. Cant update status")';
		print '</script>'; 
	
	 }	
	}
}
?>
<!-- sai custom code end -->
<head>

<?php require $GLOBALS['srcdir'] . '/js/xl/dygraphs.js.php'; ?>

<?php Header::setupHeader(['common','esign','dygraphs']); ?>

<?php
$esignApi = new Api();
?>

<?php // if the track_anything form exists, then include the styling and js functions for graphing
if (file_exists(dirname(__FILE__) . "/../../forms/track_anything/style.css")) { ?>
 <script type="text/javascript" src="<?php echo $GLOBALS['web_root']?>/interface/forms/track_anything/report.js"></script>
 <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']?>/interface/forms/track_anything/style.css" type="text/css">
<?php } ?>

<?php
// If the user requested attachment of any orphaned procedure orders, do it.
if (!empty($_GET['attachid'])) {
    $attachid = explode(',', $_GET['attachid']);
    foreach ($attachid as $aid) {
        $aid = intval($aid);
        if (!$aid) {
            continue;
        }
        $tmp = sqlQuery(
            "SELECT COUNT(*) AS count FROM procedure_order WHERE " .
            "procedure_order_id = ? AND patient_id = ? AND encounter_id = 0 AND activity = 1",
            array($aid, $pid)
        );
        if (!empty($tmp['count'])) {
              sqlStatement(
                  "UPDATE procedure_order SET encounter_id = ? WHERE " .
                  "procedure_order_id = ? AND patient_id = ? AND encounter_id = 0 AND activity = 1",
                  array($encounter, $aid, $pid)
              );
              addForm($encounter, "Procedure Order", $aid, "procedure_order", $pid, $userauthorized);
        }
    }
}
?>

<script type="text/javascript">
//Gangeya : To add Shortcut functionality
document.onkeydown = function(evt) 
{
    masterkeypress(evt);
	evt = evt || window.event; // because of Internet Explorer quirks...
	k = evt.which || evt.charCode || evt.keyCode; // because of browser differences...
	if (k == 88 && evt.altKey && !evt.ctrlKey && !evt.shiftKey) 
	{
		deleteme();
	}
//	if (k == 69 && evt.altKey && evt.ctrlKey && !evt.shiftKey) 
//	{
//		alert("Hiii..!!!");
//	}
}

// sai custom code end -->
$.noConflict();
jQuery(document).ready( function($) {
    var formConfig = <?php echo $esignApi->formConfigToJson(); ?>;
    $(".esign-button-form").esign(
        formConfig,
        {
            afterFormSuccess : function( response ) {
                if ( response.locked ) {
                    var editButtonId = "form-edit-button-"+response.formDir+"-"+response.formId;
                    $("#"+editButtonId).replaceWith( response.editButtonHtml );
                }

                var logId = "esign-signature-log-"+response.formDir+"-"+response.formId;
                $.post( formConfig.logViewAction, response, function( html ) {
                    $("#"+logId).replaceWith( html );
                });
            }
        }
    );

    var encounterConfig = <?php echo $esignApi->encounterConfigToJson(); ?>;
    $(".esign-button-encounter").esign(
        encounterConfig,
        {
            afterFormSuccess : function( response ) {
                // If the response indicates a locked encounter, replace all
                // form edit buttons with a "disabled" button, and "disable" left
                // nav visit form links
                if ( response.locked ) {
                    // Lock the form edit buttons
                    $(".form-edit-button").replaceWith( response.editButtonHtml );
                    // Disable the new-form capabilities in left nav
                    top.window.parent.left_nav.syncRadios();
                    // Disable the new-form capabilities in top nav of the encounter
                    $(".encounter-form-category-li").remove();
                }

                var logId = "esign-signature-log-encounter-"+response.encounterId;
                $.post( encounterConfig.logViewAction, response, function( html ) {
                    $("#"+logId).replaceWith( html );
                });
            }
        }
    );

    $("#prov_edu_res").click(function() {
        if ( $('#prov_edu_res').prop('checked') ) {
            var mode = "add";
        }
        else {
            var mode = "remove";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "patient_edu_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
            }
        );
    });

    $("#provide_sum_pat_flag").click(function() {
        if ( $('#provide_sum_pat_flag').prop('checked') ) {
            var mode = "add";
        }
        else {
            var mode = "remove";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "provide_sum_pat_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
            }
        );
    });

    $("#trans_trand_care").click(function() {
        if ( $('#trans_trand_care').prop('checked') ) {
            var mode = "add";
            // Enable the reconciliation checkbox
            $("#med_reconc_perf").removeAttr("disabled");
        $("#soc_provided").removeAttr("disabled");
        }
        else {
            var mode = "remove";
            //Disable the reconciliation checkbox (also uncheck it if applicable)
            $("#med_reconc_perf").attr("disabled", true);
            $("#med_reconc_perf").prop("checked",false);
        $("#soc_provided").attr("disabled",true);
        $("#soc_provided").prop("checked",false);
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "med_reconc_amc",
              complete: false,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
            }
        );
    });

    $("#med_reconc_perf").click(function() {
        if ( $('#med_reconc_perf').prop('checked') ) {
            var mode = "complete";
        }
        else {
            var mode = "uncomplete";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
            { amc_id: "med_reconc_amc",
              complete: true,
              mode: mode,
              patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
              object_category: "form_encounter",
              object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
            }
        );
    });
    $("#soc_provided").click(function(){
        if($('#soc_provided').prop('checked')){
                var mode = "soc_provided";
        }
        else{
                var mode = "no_soc_provided";
        }
        top.restoreSession();
        $.post( "../../../library/ajax/amc_misc_data.php",
                { amc_id: "med_reconc_amc",
                complete: true,
                mode: mode,
                patient_id: <?php echo htmlspecialchars($pid, ENT_NOQUOTES); ?>,
                object_category: "form_encounter",
                object_id: <?php echo htmlspecialchars($encounter, ENT_NOQUOTES); ?>
                }
        );
    });

     $(".deleteme").click(function(evt) { deleteme(); evt.stopPropogation(); });

<?php
  // If the user was not just asked about orphaned orders, build javascript for that.
if (!isset($_GET['attachid'])) {
    $ares = sqlStatement(
        "SELECT procedure_order_id, date_ordered " .
        "FROM procedure_order WHERE " .
        "patient_id = ? AND encounter_id = 0 AND activity = 1 " .
        "ORDER BY procedure_order_id",
        array($pid)
    );
    echo "  // Ask about attaching orphaned orders to this encounter.\n";
    echo "  var attachid = '';\n";
    while ($arow = sqlFetchArray($ares)) {
        $orderid   = $arow['procedure_order_id'];
        $orderdate = $arow['date_ordered'];
        echo "  if (confirm('" . xls('There is a lab order') . " $orderid " .
        xls('dated') . " $orderdate " .
        xls('for this patient not yet assigned to any encounter.') . " " .
        xls('Assign it to this one?') . "')) attachid += '$orderid,';\n";
    }
    echo "  if (attachid) location.href = 'forms.php?attachid=' + attachid;\n";
}
?>

    <?php if ($reviewMode) { ?>
        $("body table:first").hide();
        $(".encounter-summary-column").hide();
        $(".css_button").hide();
        $(".css_button_small").hide();
        $(".encounter-summary-column:first").show();
        $(".title:first").text("<?php echo xls("Review"); ?> " + $(".title:first").text() + " ( <?php echo addslashes($encounter); ?> )");
    <?php } ?>
});

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?encounterid=<?php echo $encounter; ?>', '_blank', 500, 200, '', '', {
      buttons: [
          {text: '<?php echo xla('Done'); ?>', close: true, style: 'primary btn-sm'}
      ],
      allowResize: false,
      allowDrag: true,
  });
  return false;
 }

 // Called by the deleter.php window on a successful delete.
function imdeleted(EncounterId) {
    top.window.parent.left_nav.removeOptionSelected(EncounterId);
    top.window.parent.left_nav.clearEncounter();
    if (top.tab_mode) {
        top.encounterList();
    } else {
        top.window.parent.left_nav.loadFrame('ens1', window.parent.name, 'patient_file/history/encounters.php');
    }
}

// Called to open the data entry form a specified encounter form instance.
function openEncounterForm(formdir, formname, formid) { 
  var url = '<?php echo "$rootdir/patient_file/encounter/view_form.php?formname=" ?>' +
    formdir + '&id=' + formid;


  if (formdir == 'newpatient' || !parent.twAddFrameTab) {
    top.restoreSession();
    location.href = url;
  }
  else {
    parent.twAddFrameTab('enctabs', formname, url);
  }
  return false;
}

// Called when an encounter form may changed something that requires a refresh here.
function refreshVisitDisplay() {
  location.href = '<?php echo $rootdir; ?>/patient_file/encounter/forms.php';
}

</script>

<script language="javascript">
function expandcollapse(atr) {
  for (var i = 1; i < 15; ++i) {
    var mydivid="divid_" + i; var myspanid = "spanid_" + i;
    var ele = document.getElementById(mydivid);
    var text = document.getElementById(myspanid);
    if (!ele) continue;
    if (atr == "expand") {
      ele.style.display = "block"; text.innerHTML = "<?php xl('Collapse', 'e'); ?>";
    }
    else {
      ele.style.display = "none" ; text.innerHTML = "<?php xl('Expand', 'e'); ?>";
    }
  }
}

function divtoggle(spanid, divid) {
    var ele = document.getElementById(divid);
    var text = document.getElementById(spanid);
    if(ele.style.display == "block") {
        ele.style.display = "none";
        text.innerHTML = "<?php xl('Expand', 'e'); ?>";
    }
    else {
        ele.style.display = "block";
        text.innerHTML = "<?php xl('Collapse', 'e'); ?>";
    }
}
</script>

<style type="text/css">
    div.tab {
        min-height: 50px;
        padding:8px;
    }

    div.form_header {
        float:left;
        min-width:300pt;
    }

    div.form_header_controls {
        float:left;
        margin-bottom:2px;
        margin-left:6px;
    }

    div.formname {
        float:left;
        min-width:120pt;
        font-weight:bold;
        padding:0px;
        margin:0px;
    }

    .encounter-summary-container {
        float:left;
        width:100%;
    }

    .encounter-summary-column {
        width: 33.3%;
        float:left;
        display:inline;
        margin-top:10px;
    }
</style>

<!-- *************** -->
<!-- Form menu start -->
<script language="JavaScript">

function openNewForm(sel, label) {
  top.restoreSession();
  var FormNameValueArray = sel.split('formname=');
  if (FormNameValueArray[1] == 'newpatient') {
    // TBD: Make this work when it's not the first frame.
    parent.frames[0].location.href = sel;
  }
  else {
    parent.twAddFrameTab('enctabs', label, sel);
  }
}

function toggleFrame1(fnum) {
  top.frames['left_nav'].document.forms[0].cb_top.checked=false;
  top.window.parent.left_nav.toggleFrame(fnum);
}
</script>
<style type="text/css">
#sddm
{   margin: 0;
    padding: 0;
    z-index: 30;
}

</style>
<script type="text/javascript" language="javascript">

var timeout = 500;
var closetimer  = 0;
var ddmenuitem  = 0;
var oldddmenuitem = 0;
var flag = 0;

// open hidden layer
function mopen(id)
{
    // cancel close timer
    //mcancelclosetime();

    flag=10;

    // close old layer
    //if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
    //if(ddmenuitem) ddmenuitem.style.display = 'none';

    // get new layer and show it
        oldddmenuitem = ddmenuitem;
    ddmenuitem = document.getElementById(id);
        if((ddmenuitem.style.visibility == '')||(ddmenuitem.style.visibility == 'hidden')){
            if(oldddmenuitem) oldddmenuitem.style.visibility = 'hidden';
            if(oldddmenuitem) oldddmenuitem.style.display = 'none';
            ddmenuitem.style.visibility = 'visible';
            ddmenuitem.style.display = 'block';
        }else{
            ddmenuitem.style.visibility = 'hidden';
            ddmenuitem.style.display = 'none';
        }
}
// close showed layer
function mclose()
{
    if(flag==10)
     {
      flag=11;
      return;
     }
    if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
    if(ddmenuitem) ddmenuitem.style.display = 'none';
}

// close layer when click-out
document.onclick = mclose;
//=================================================
function findPosX(id)
  {
    obj=document.getElementById(id);
    var curleft = 0;
    if(obj.offsetParent)
        while(1)
        {
          curleft += obj.offsetLeft;
          if(!obj.offsetParent)
            break;
          obj = obj.offsetParent;
        }
    else if(obj.x)
        curleft += obj.x;
   PropertyWidth=document.getElementById(id).offsetWidth;
   if(PropertyWidth>curleft)
    {
     document.getElementById(id).style.left=0;
    }
  }

  function findPosY(obj)
  {
    var curtop = 0;
    if(obj.offsetParent)
        while(1)
        {
          curtop += obj.offsetTop;
          if(!obj.offsetParent)
            break;
          obj = obj.offsetParent;
        }
    else if(obj.y)
        curtop += obj.y;
    return curtop;
  }
</script>

</head>
<body class="bgcolor2">
<dl>
<?php //DYNAMIC FORM RETREIVAL
include_once("$srcdir/registry.inc");

function myGetRegistered($state = "1", $limit = "unlimited", $offset = "0")
{
    global $attendant_type;
    $sql = "SELECT category, nickname, name, state, directory, id, sql_run, " .
    "unpackaged, date, aco_spec FROM registry WHERE ";
  // select different forms for groups
    if ($attendant_type == 'pid') {
        $sql .= "patient_encounter = 1 AND ";
    } else {
        $sql .= "therapy_group_encounter = 1 AND ";
    }
    $sql .=  "state LIKE \"$state\" ORDER BY category, priority, name";
    if ($limit != "unlimited") {
        $sql .= " limit $limit, $offset";
    }
    $res = sqlStatement($sql);
    if ($res) {
        for ($iter=0; $row=sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
        }
    } else {
        return false;
    }
    return $all;
}

$reg = myGetRegistered();
$old_category = '';

  $DivId=1;

// To see if the encounter is locked. If it is, no new forms can be created
$encounterLocked = false;
if ($esignApi->lockEncounters() &&
isset($GLOBALS['encounter']) &&
!empty($GLOBALS['encounter']) ) {
    $esign = $esignApi->createEncounterESign($GLOBALS['encounter']);
    if ($esign->isLocked()) {
        $encounterLocked = true;
    }
}

if (!empty($reg)) {
    $StringEcho= '<ul id="sddm">';
    if ($encounterLocked === false) {
        foreach ($reg as $entry) {
          // Check permission to create forms of this type.
            $tmp = explode('|', $entry['aco_spec']);
            if (!empty($tmp[1])) {
                if (!acl_check($tmp[0], $tmp[1], '', 'write') && !acl_check($tmp[0], $tmp[1], '', 'addonly')) {
                    continue;
                }
            }
            $new_category = trim($entry['category']);
            $new_nickname = trim($entry['nickname']);
            if ($new_category == '') {
                $new_category = htmlspecialchars(xl('Miscellaneous'), ENT_QUOTES);
            } else {
                $new_category = htmlspecialchars(xl($new_category), ENT_QUOTES);
            }
            if ($new_nickname != '') {
                $nickname = $new_nickname;
            } else {
                $nickname = trim($entry['name']);
            }
            if ($old_category != $new_category) {
                $new_category_ = $new_category;
                $new_category_ = str_replace(' ', '_', $new_category_);
                if ($old_category != '') {
                    $StringEcho .= "</table></div></li>";
                }
                $StringEcho .= "<li class=\"encounter-form-category-li\"><a href='JavaScript:void(0);' onClick=\"mopen('$DivId');\" >$new_category</a><div id='$DivId' ><table border='0' cellspacing='0' cellpadding='0'>";
                $old_category = $new_category;
                $DivId++;
            }
            $StringEcho .= "<tr><td style='border-top: 1px solid #000000;padding:0px;'><a onclick=\"openNewForm('" .
                $rootdir . "/patient_file/encounter/load_form.php?formname=" . urlencode($entry['directory']) .
                "', '" . addslashes(xl_form_title($nickname)) . "')\" href='JavaScript:void(0);'>" .
                text(xl_form_title($nickname)) . "</a></td></tr>";
        }
    }
    $StringEcho.= '</table></div></li>';
}

if ($StringEcho) {
    $StringEcho2= '<div style="clear:both"></div>';
} else {
    $StringEcho2="";
}

// This shows Layout Based Form names just like the above.
//
if ($encounterLocked === false) {
    $lres = sqlStatement("SELECT grp_form_id AS option_id, grp_title AS title, grp_aco_spec " .
    "FROM layout_group_properties WHERE " .
    "grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_activity = 1 " .
    "ORDER BY grp_seq, grp_title");

    if (sqlNumRows($lres)) {
        if (!$StringEcho) {
            $StringEcho= '<ul id="sddm">';
        }
        $StringEcho.= "<li class=\"encounter-form-category-li\"><a href='JavaScript:void(0);' onClick=\"mopen('lbf');\" >" .
        xl('Layout Based') . "</a><div id='lbf' ><table border='0' cellspacing='0' cellpadding='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id']; // should start with LBF
            $title = $lrow['title'];
            // Check ACO attribute, if any, of this LBF.
            if (!empty($lrow['grp_aco_spec'])) {
                $tmp = explode('|', $lrow['grp_aco_spec']);
                if (!acl_check($tmp[0], $tmp[1], '', 'write') && !acl_check($tmp[0], $tmp[1], '', 'addonly')) {
                    continue;
                }
            }
            $StringEcho .= "<tr><td style='border-top: 1px solid #000000;padding:0px;'><a onclick=\"openNewForm('" .
                $rootdir . "/patient_file/encounter/load_form.php?formname=" . urlencode($option_id) .
                "', '" . addslashes(xl_form_title($title)) . "')\" href='JavaScript:void(0);'>" .
                text(xl_form_title($title)) . "</a></td></tr>";
        }
    }
}
?>
<!-- DISPLAYING HOOKS STARTS HERE -->
<?php
    $module_query = sqlStatement("SELECT msh.*,ms.menu_name,ms.path,m.mod_ui_name,m.type FROM modules_hooks_settings AS msh LEFT OUTER JOIN modules_settings AS ms ON
                                    obj_name=enabled_hooks AND ms.mod_id=msh.mod_id LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id
                                    WHERE fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='encounter' ORDER BY mod_id");
    $DivId = 'mod_installer';
    if (sqlNumRows($module_query)) {
        $jid = 0;
        $modid = '';
        while ($modulerow = sqlFetchArray($module_query)) {
            $DivId = 'mod_'.$modulerow['mod_id'];
            $new_category = $modulerow['mod_ui_name'];
            $modulePath = "";
            $added      = "";
            if ($modulerow['type'] == 0) {
                $modulePath = $GLOBALS['customModDir'];
                $added      = "";
            } else {
                $added      = "index";
                $modulePath = $GLOBALS['zendModDir'];
            }
            $relative_link = "../../modules/".$modulePath."/".$modulerow['path'];
            $nickname = $modulerow['menu_name'] ? $modulerow['menu_name'] : 'Noname';
            if ($jid==0 || ($modid!=$modulerow['mod_id'])) {
                if ($modid!='') {
                    $StringEcho.= '</table></div></li>';
                }
                $StringEcho.= "<li><a href='JavaScript:void(0);' onClick=\"mopen('$DivId');\" >$new_category</a><div id='$DivId' ><table border='0' cellspacing='0' cellpadding='0'>";
            }
            $jid++;
            $modid = $modulerow['mod_id'];
            $StringEcho.= "<tr><td style='border-top: 1px solid #000000;padding:0px;'><a onclick=" .
                "\"openNewForm('$relative_link', '" . addslashes(xl_form_title($nickname)) . "')\" " .
                "href='JavaScript:void(0);'>" . xl_form_title($nickname) . "</a></td></tr>";
        }
    }
    ?>
<!-- DISPLAYING HOOKS ENDS HERE -->
<?php
if ($StringEcho) {
    $StringEcho.= "</table></div></li></ul>".$StringEcho2;
}
?>
<table cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td valign="top"><?php echo $StringEcho; ?></td>
  </tr>
</table>
</dl>
<!-- Form menu stop -->
<!-- *************** -->

<div id="encounter_forms">

<?php
$dateres = getEncounterDateByEncounter($encounter);
$encounter_date = date("Y-m-d", strtotime($dateres["date"]));
$providerIDres = getProviderIdOfEncounter($encounter);
$providerNameRes = getProviderName($providerIDres);
?>
<div class='encounter-summary-container'>
<div class='encounter-summary-column'>
<div>
<span class="title"><?php echo xl("Claim #") . " <font color='red'> ".$encounter."</font> " ."      (" .oeFormatShortDate($encounter_date) . ")   "; ?> </span><!-- sai custom code -->
<?php
$pass_sens_squad = true;

//fetch acl for category of given encounter
$pc_catid = fetchCategoryIdByEncounter($encounter);
$postCalendarCategoryACO = fetchPostCalendarCategoryACO($pc_catid);
if ($postCalendarCategoryACO) {
    $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
    $authPostCalendarCategory = acl_check($postCalendarCategoryACO[0], $postCalendarCategoryACO[1]);
    $authPostCalendarCategoryWrite = acl_check($postCalendarCategoryACO[0], $postCalendarCategoryACO[1], '', 'write');
} else { // if no aco is set for category
    $authPostCalendarCategory = true;
    $authPostCalendarCategoryWrite = true;
}

if ($attendant_type == 'pid' && is_numeric($pid)) {
    echo '<span class="title">' . text(oeFormatShortDate($encounter_date)) . " " . xlt("Encounter") . '</span>';

    // Check for no access to the patient's squad.
    // sai custom code start 
	//Updated By Gangeya for BUG ID 11240
    $result = getPatientData($pid, "fname,lname,stop_stmt,squad");
	$stop_stmt = $result['stop_stmt'];
	
	$stop_stmtArray  = explode("|",$stop_stmt);
	$stop_stmt = $stop_stmtArray['0'];
// sai custom code end		
    echo htmlspecialchars(xl('for', '', ' ', ' ') . $result['fname'] . " " . $result['lname']);
    if ($result['squad'] && ! acl_check('squads', $result['squad'])) {
        $pass_sens_squad = false;
    }

    // Check for no access to the encounter's sensitivity level.
    $result = sqlQuery("SELECT sensitivity FROM form_encounter WHERE " .
                        "pid = '$pid' AND encounter = '$encounter' LIMIT 1");
    if (($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) || !$authPostCalendarCategory) {
        $pass_sens_squad = false;
    }
    // for therapy group
} else {
    echo '<span class="title">' . text(oeFormatShortDate($encounter_date)) . " " . xlt("Group Encounter") . '</span>';
    // Check for no access to the patient's squad.
    $result = getGroup($groupId);
    echo htmlspecialchars(xl('for ', '', ' ', ' ') . $result['group_name']);
    if ($result['squad'] && ! acl_check('squads', $result['squad'])) {
        $pass_sens_squad = false;
    }
    // Check for no access to the encounter's sensitivity level.
    $result = sqlQuery("SELECT sensitivity FROM form_groups_encounter WHERE " .
        "group_id = ? AND encounter = ? LIMIT 1", array($groupId, $encounter));
    if (($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) || !$authPostCalendarCategory) {
        $pass_sens_squad = false;
    }
}
?>
</div>
<div style='margin-top:8px;'>
<?php
// ESign for entire encounter
$esign = $esignApi->createEncounterESign($encounter);
if ($esign->isButtonViewable()) {
    echo $esign->buttonHtml();
}
?>
<?php if (acl_check('admin', 'superbill')) { ?>
    <a href='#' class='css_button' onclick='return deleteme()'><span><?php echo xl('Delete') ?></span></a>
<?php } ?>
&nbsp;&nbsp;&nbsp;<a href="#" onClick='expandcollapse("expand");' style="font-size:80%;"><?php xl('Expand All', 'e'); ?></a>
&nbsp;&nbsp;&nbsp;<a  style="font-size:80%;" href="#" onClick='expandcollapse("collapse");'><?php xl('Collapse All', 'e'); ?></a>
</div>
</div>

<div class='encounter-summary-column'>
<?php if ($esign->isLogViewable()) {
    $esign->renderLog();
} ?>
</div>

<div class='encounter-summary-column'>
<?php if ($GLOBALS['enable_amc_prompting']) { ?>
    <div style='float:right;margin-right:25px;border-style:solid;border-width:1px;'>
        <div style='float:left;margin:5px 5px 5px 5px;'>
            <table>
            <tr>
            <td>
            <?php // Display the education resource checkbox (AMC prompting)
                $itemAMC = amcCollect("patient_edu_amc", $pid, 'form_encounter', $encounter);
            ?>
            <?php if (!(empty($itemAMC))) { ?>
                <input type="checkbox" id="prov_edu_res" checked>
            <?php } else { ?>
                <input type="checkbox" id="prov_edu_res">
            <?php } ?>
            </td>
            <td>
            <span class="text"><?php echo xl('Provided Education Resource(s)?') ?></span>
            </td>
            </tr>
            <tr>
            <td>
            <?php // Display the Provided Clinical Summary checkbox (AMC prompting)
                $itemAMC = amcCollect("provide_sum_pat_amc", $pid, 'form_encounter', $encounter);
            ?>
            <?php if (!(empty($itemAMC))) { ?>
                <input type="checkbox" id="provide_sum_pat_flag" checked>
            <?php } else { ?>
                <input type="checkbox" id="provide_sum_pat_flag">
            <?php } ?>
            </td>
            <td>
            <span class="text"><?php echo xl('Provided Clinical Summary?') ?></span>
            </td>
            </tr>
            <?php // Display the medication reconciliation checkboxes (AMC prompting)
                $itemAMC = amcCollect("med_reconc_amc", $pid, 'form_encounter', $encounter);
            ?>
            <?php if (!(empty($itemAMC))) { ?>
                <tr>
                <td>
                <input type="checkbox" id="trans_trand_care" checked>
                </td>
                <td>
                <span class="text"><?php echo xl('Transition/Transfer of Care?') ?></span>
                </td>
                </tr>
                </table>
                <table style="margin-left:2em;">
                <tr>
                <td>
                <?php if (!(empty($itemAMC['date_completed']))) { ?>
                    <input type="checkbox" id="med_reconc_perf" checked>
                <?php } else { ?>
                    <input type="checkbox" id="med_reconc_perf">
                <?php } ?>
                </td>
                <td>
                <span class="text"><?php echo xl('Medication Reconciliation Performed?') ?></span>
                </td>
                </tr>
        <tr>
                <td>
                <?php if (!(empty($itemAMC['soc_provided']))) { ?>
                    <input type="checkbox" id="soc_provided" checked>
                <?php } else { ?>
                    <input type="checkbox" id="soc_provided">
                <?php } ?>
                </td>
                <td>
                <span class="text"><?php echo xl('Summary Of Care Provided?') ?></span>
                </td>
                </tr>
                </table>
            <?php } else { ?>
                <tr>
                <td>
                <input type="checkbox" id="trans_trand_care">
                </td>
                <td>
                <span class="text"><?php echo xl('Transition/Transfer of Care?') ?></span>
                </td>
                </tr>
                </table>
                <table style="margin-left:2em;">
                <tr>
                <td>
                <input type="checkbox" id="med_reconc_perf" DISABLED>
                </td>
                <td>
                <span class="text"><?php echo xl('Medication Reconciliation Performed?') ?></span>
                </td>
                </tr>
                <tr>
                <td>
                <input type="checkbox" id="soc_provided" DISABLED>
                </td>
                <td>
                <span class="text"><?php echo xl('Summary of Care Provided?') ?></span>
                </td>
                </tr>
                </table>
            <?php } ?>
        </div>
    </div>
<?php } ?>
</div>

</div>

<!-- Get the documents tagged to this encounter and display the links and notes as the tooltip -->
<?php
if ($attendant_type == 'pid') {
    $docs_list = getDocumentsByEncounter($pid, $_SESSION['encounter']);
} else {
    // already doesn't exist document for therapy groups
    $docs_list = array();
}
if (!empty($docs_list) && count($docs_list) > 0) {
?>
<div class='enc_docs'>
<span class="bold"><?php echo xlt("Document(s)"); ?>:</span>
<?php
$doc = new C_Document();
foreach ($docs_list as $doc_iter) {
    $doc_url = $doc->_tpl_vars[CURRENT_ACTION]. "&view&patient_id=".attr($pid)."&document_id=" . attr($doc_iter[id]) . "&";
    // Get notes for this document.
    $queryString = "SELECT GROUP_CONCAT(note ORDER BY date DESC SEPARATOR '|') AS docNotes, GROUP_CONCAT(date ORDER BY date DESC SEPARATOR '|') AS docDates
			FROM notes WHERE foreign_id = ? GROUP BY foreign_id";
    $noteData = sqlQuery($queryString, array($doc_iter[id]));
    $note = '';
    if ($noteData) {
        $notes = array();
        $notes = explode("|", $noteData['docNotes']);
        $dates = explode("|", $noteData['docDates']);
        for ($i = 0; $i < count($notes); $i++) {
            $note .= oeFormatShortDate(date('Y-m-d', strtotime($dates[$i]))) . " : " . $notes[$i] . "\n";
        }
    }
?>
<br>
<a href="<?php echo $doc_url;?>" style="font-size:small;" onsubmit="return top.restoreSession()"><?php echo text(oeFormatShortDate($doc_iter[docdate])) . ": " . text(basename($doc_iter[url]));?></a>
<?php if ($note != '') {?>
            <a href="javascript:void(0);" title="<?php echo attr($note);?>"><img src="../../../images/info.png"/></a>
    <?php }?>
<?php } ?>
</div>
<?php } ?>
<br/>
<?php
// sai custom code start
 $St=CheckEncounterStatus($encounter);
 //Updated By Gangeya for BUG ID 11240
  if($stop_stmt == 'self_pay'){
 	$qsql = sqlStatement("SELECT id, status,iphone_status FROM claim_status where id in (1, 10, 11, 12, 15, 16, 17)");
 }
 else{
	 $qsql = sqlStatement("SELECT id, status,iphone_status FROM claim_status");
 }
 
//$qsql = sqlStatement("SELECT id, status,iphone_status FROM claim_status");
  $selected1='';
 ?>

<form name='new_patient' method='post' action="forms.php">

<select name='Encounter_Status' id="Encounter_Status" > 

<?php

  
  while ($statusrow = sqlFetchArray($qsql)) {
 $claim_status = $statusrow['status'];
 $claim_status_id = $statusrow['id'];
 
 
  if($St['Status'] == $claim_status)
 $selected1 = "selected";
 else  $selected1="";

 
 if($statusrow['iphone_status']== "true" || $statusrow['id'] == 20)
 $disabled1 = "disabled";
 else
 $disabled1 = "";
 
 echo "<option value='$claim_status' $selected1 $disabled1>$claim_status</option>";
	 } 
?>

 
</select> 
<input  name="Update_Status_Btn" type="submit" value="Update Status">
<input  name="Reopen_encounter" type="submit" value="Re-Open">
<input type="hidden" name="Encounter" value="<?php echo $encounter; ?>">
<input type="hidden" name="pid" value="<?php echo $pid; ?>">
<br><br>&nbsp;
<a href="encounter_log.php" class='iframe medium_modal' onclick='top.restoreSession()' style="font-weight:bold">
<?php echo htmlspecialchars(xl('Log'),ENT_NOQUOTES); ?></a>
<span>|</span>
<a href="forms_doc.php" class='iframe medium_modal' onclick='top.restoreSession()' style="font-weight:bold">
<?php echo htmlspecialchars(xl('Documentation'),ENT_NOQUOTES); ?></a>
<span>|</span>

<?php 
// code added for progress note  view 

$progressNoteResult = sqlStatement("select fe.billEHREncounterID,fe.encounter,pn.URL from progressnotes pn 
JOIN form_encounter fe 
on pn.encounterID=fe.billEHREncounterID
where fe.encounter=$encounter  and pn.isActive=1 order by pn.progressnoteID desc limit 1");


$progressNoteRow = sqlFetchArray($progressNoteResult);

//print_r($progressNoteRow);

    if(isset($progressNoteRow) && !empty($progressNoteRow)){
        $progressNoteURL = $billehrUploadPath.$progressNoteRow['URL'];
	 
?>
	 
<a href="<?php echo $progressNoteURL ?>" style="font-weight:bold" >
<?php echo htmlspecialchars(xl('billEHR Progress Note'),ENT_NOQUOTES); ?></a>
<span>|</span>

<?php
    }
    else{
        $progressNoteURL = '';
    }
?>

<?php

    //pacEHR Prgoress Note View code by Gangeya
    $sql = "select pmsencounterID, URL from pacehrprogressnotes where pmsencounterID = $encounter and isActive= 1 order by progressnoteID desc limit 1";

    $pacehrProgressNoteResult = sqlStatement($sql);

    $pacehrprogressNoteRow = sqlFetchArray($pacehrProgressNoteResult);
    //print_r($pacehrprogressNoteRow);

    if(isset($pacehrprogressNoteRow) && !empty($pacehrprogressNoteRow)){
        $pacehrprogressNoteURL = $pacehrUploadPath.$pacehrprogressNoteRow['URL'];
?>
<a href = "<?php echo $pacehrprogressNoteURL ?>" style = "font-weight:bold" >
<?php echo htmlspecialchars(xl('pacEHR Progress Note'),ENT_NOQUOTES); ?></a>
<span>|</span>

<?php
    }
    else{
        $pacehrprogressNoteURL = '';
    }

?>

<a href="upload_pn.php" onclick='top.restoreSession()' style="font-weight:bold" >
<?php echo htmlspecialchars(xl('payEHR Progress Note'),ENT_NOQUOTES); ?></a>



</form>
<?php
// sai custom code end

if ($pass_sens_squad &&
    ($result = getFormByEncounter(
        $attendant_id,
        $encounter,
        "id, date, form_id, form_name, formdir, user, deleted",
        "",
        "FIND_IN_SET(formdir,'newpatient') DESC, form_name, date DESC"
    ))) {
    echo "<table width='100%' id='partable'>";
    $divnos = 1;
    foreach ($result as $iter) {
       // print_r($iter);
        $formdir = $iter['formdir'];

        // skip forms whose 'deleted' flag is set to 1
        if ($iter['deleted'] == 1) {
            continue;
        }

        $aco_spec = false;

        if (substr($formdir, 0, 3) == 'LBF') {
            // Skip LBF forms that we are not authorized to see.
            $lrow = sqlQuery(
                "SELECT grp_aco_spec " .
                "FROM layout_group_properties WHERE " .
                "grp_form_id = ? AND grp_group_id = '' AND grp_activity = 1",
                array($formdir)
            );
            if (!empty($lrow)) {
                if (!empty($lrow['grp_aco_spec'])) {
                    $aco_spec = explode('|', $lrow['grp_aco_spec']);
                    if (!acl_check($aco_spec[0], $aco_spec[1])) {
                        continue;
                    }
                }
            }
        } else {
          // Skip non-LBF forms that we are not authorized to see.
            $tmp = getRegistryEntryByDirectory($formdir, 'aco_spec');
            if (!empty($tmp['aco_spec'])) {
                $aco_spec = explode('|', $tmp['aco_spec']);
                if (!acl_check($aco_spec[0], $aco_spec[1])) {
                    continue;
                }
            }
        }

        // $form_info = getFormInfoById($iter['id']);
        if (strtolower(substr($iter['form_name'], 0, 5)) == 'camos') {
            //CAMOS generates links from report.php and these links should
            //be clickable without causing view.php to come up unexpectedly.
            //I feel that the JQuery code in this file leading to a click
            //on the report.php content to bring up view.php steps on a
            //form's autonomy to generate it's own html content in it's report
            //but until any other form has a problem with this, I will just
            //make an exception here for CAMOS and allow it to carry out this
            //functionality for all other forms.  --Mark
            echo '<tr title="' . xl('Edit form') . '" '.
                  'id="'.$formdir.'~'.$iter['form_id'].'">';
        } else {
            echo '<tr id="' . $formdir . '~' . $iter['form_id'] . '" class="text onerow">';
        }

        $acl_groups = acl_check("groups", "glog", false, 'write') ? true : false;
        $user = getNameFromUsername($iter['user']);

        $form_name = ($formdir == 'newpatient') ? xl('Visit Summary') : xl_form_title($iter['form_name']);


        // Create the ESign instance for this form
        $esign = $esignApi->createFormESign($iter['id'], $formdir, $encounter);

        // echo "<tr>"; // Removed as bug fix.

        echo "<td style='border-bottom:1px solid'>";

        // Figure out the correct author (encounter authors are the '$providerNameRes', while other
        // form authors are the '$user['fname'] . "  " . $user['lname']').
        if ($formdir == 'newpatient') {
            $form_author = $providerNameRes;
        } else {
            $form_author = $user['fname'] . "  " . $user['lname'];
        }
        echo "<div class='form_header'>";
        echo "<a href='#' onclick='divtoggle(\"spanid_$divnos\",\"divid_$divnos\");' class='small' id='aid_$divnos'>" .
          "<div class='formname'>" . text($form_name) . "</div> " .
          xlt('by') . " " . text($form_author) . " " .
          "(<span id=spanid_$divnos class=\"indicator\">" . ($divnos == 1 ? xlt('Collapse') : xlt('Expand')) . "</span>)</a>";
        echo "</div>";

        // a link to edit the form
        echo "<div class='form_header_controls'>";

        // If the form is locked, it is no longer editable
        if ($esign->isLocked()) {
                 echo "<a href=# class='css_button_small form-edit-button-locked' id='form-edit-button-" . attr($formdir) . "-" . attr($iter['id']) . "'><span>" . xlt('Locked') . "</span></a>";
        } else {
            if ((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write') and $is_group == 0 and $authPostCalendarCategoryWrite)
            or (((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write')) and $is_group and acl_check("groups", "glog", false, 'write')) and $authPostCalendarCategoryWrite)) {
                echo "<a class='css_button_small form-edit-button' " .
                    "id='form-edit-button-" . attr($formdir) . "-" . attr($iter['id']) . "' " .
                    "href='#' " .
                    "title='" . xla('Edit this form') . "' " .
                    "onclick=\"return openEncounterForm('" . attr($formdir) . "', '" .
                    attr($form_name) . "', '" . attr($iter['form_id']) . "')\">";
                echo "<span>" . xlt('Edit') . "</span></a>";
                //echo $form_name."--".$iter['form_id'];
            }
        }

        if (($esign->isButtonViewable() and $is_group == 0 and $authPostCalendarCategoryWrite) or ($esign->isButtonViewable() and $is_group and acl_check("groups", "glog", false, 'write') and $authPostCalendarCategoryWrite)) {
            if (!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write')) {
                echo $esign->buttonHtml();
            }
        }

        if (substr($formdir, 0, 3) == 'LBF') {
          // A link for a nice printout of the LBF
            echo "<a target='_blank' " .
            "href='$rootdir/forms/LBF/printable.php?"   .
            "formname="   . urlencode($formdir)         .
            "&formid="    . urlencode($iter['form_id']) .
            "&visitid="   . urlencode($encounter)       .
            "&patientid=" . urlencode($pid)             .
            "' class='css_button_small' title='" . xl('Print this form') .
            "' onclick='top.restoreSession()'><span>" . xlt('Print') . "</span></a>";
        }

        if (acl_check('admin', 'superbill')) {
            if ($formdir != 'newpatient' && $formdir != 'newGroupEncounter') {
                // a link to delete the form from the encounter
                echo "<a href='$rootdir/patient_file/encounter/delete_form.php?" .
                    "formname=" . $formdir .
                    "&id=" . $iter['id'] .
                    "&encounter=". $encounter.
                    "&pid=".$pid.
                    "' class='css_button_small' title='" . xl('Delete this form') . "' onclick='top.restoreSession()'><span>" . xl('Delete') . "</span></a>";
            } else {
                ?><a href='javascript:;' class='css_button_small' style='color:gray'><span><?php xl('Delete', 'e'); ?></span></a><?php
            }
        }
        echo "</div>\n"; // Added as bug fix.

        echo "</td>\n";
        echo "</tr>";
        echo "<tr>";
        echo "<td valign='top' class='formrow'><div class='tab' id='divid_$divnos' ";
        echo "style='display:" . ($divnos == 1 ? 'block' : 'none') . "'>";

        // Use the form's report.php for display.  Forms with names starting with LBF
        // are list-based forms sharing a single collection of code.
        //
        if (substr($formdir, 0, 3) == 'LBF') {
            include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

            call_user_func("lbf_report", $attendant_id, $encounter, 2, $iter['form_id'], $formdir, true);
        } else {
            include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
            call_user_func($formdir . "_report", $attendant_id, $encounter, 2, $iter['form_id']);
        }

        if ($esign->isLogViewable()) {
            $esign->renderLog();
        }

        echo "</div></td></tr>";
        $divnos=$divnos+1;
    }
    echo "</table>";
}
if (!$pass_sens_squad) {
    echo xlt("Not authorized to view this encounter");
}
?>

</div> <!-- end large encounter_forms DIV -->
</body>
</html>
