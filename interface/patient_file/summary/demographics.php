<?php
/**
 *
 * Patient summary screen.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("../history/history.inc.php");
require_once("$srcdir/edi.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once("$srcdir/clinical_rules.php");
require_once("$srcdir/options.js.php");
require_once("$srcdir/group.inc");
require_once(dirname(__FILE__)."/../../../library/appointments.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\Reminder\BirthdayReminder;

if (isset($_GET['set_pid'])) {
    include_once("$srcdir/pid.inc");
    setpid($_GET['set_pid']);
}

  $active_reminders = false;
  $all_allergy_alerts = false;
if ($GLOBALS['enable_cdr']) {
    //CDR Engine stuff
    if ($GLOBALS['enable_allergy_check'] && $GLOBALS['enable_alert_log']) {
      //Check for new allergies conflicts and throw popup if any exist(note need alert logging to support this)
        $new_allergy_alerts = allergy_conflict($pid, 'new', $_SESSION['authUser']);
        if (!empty($new_allergy_alerts)) {
            $pop_warning = '<script type="text/javascript">alert(\'' . xls('WARNING - FOLLOWING ACTIVE MEDICATIONS ARE ALLERGIES') . ':\n';
            foreach ($new_allergy_alerts as $new_allergy_alert) {
                $pop_warning .= addslashes($new_allergy_alert) . '\n';
            }

            $pop_warning .= '\')</script>';
            echo $pop_warning;
        }
    }

    if ((!isset($_SESSION['alert_notify_pid']) || ($_SESSION['alert_notify_pid'] != $pid)) && isset($_GET['set_pid']) && $GLOBALS['enable_cdr_crp']) {
      // showing a new patient, so check for active reminders and allergy conflicts, which use in active reminder popup
        $active_reminders = active_alert_summary($pid, "reminders-due", '', 'default', $_SESSION['authUser'], true);
        if ($GLOBALS['enable_allergy_check']) {
            $all_allergy_alerts = allergy_conflict($pid, 'all', $_SESSION['authUser'], true);
        }
    }
}

function print_as_money($money)
{
    preg_match("/(\d*)\.?(\d*)/", $money, $moneymatches);
    $tmp = wordwrap(strrev($moneymatches[1]), 3, ",", 1);
    $ccheck = strrev($tmp);
    if ($ccheck[0] == ",") {
        $tmp = substr($ccheck, 1, strlen($ccheck)-1);
    }

    if ($moneymatches[2] != "") {
        return "$ " . strrev($tmp) . "." . $moneymatches[2];
    } else {
        return "$ " . strrev($tmp);
    }
}

// get an array from Photos category
function pic_array($pid, $picture_directory)
{
    $pics = array();
    $sql_query = "select documents.id from documents join categories_to_documents " .
                 "on documents.id = categories_to_documents.document_id " .
                 "join categories on categories.id = categories_to_documents.category_id " .
                 "where categories.name like ? and documents.foreign_id = ?";
    if ($query = sqlStatement($sql_query, array($picture_directory,$pid))) {
        while ($results = sqlFetchArray($query)) {
            array_push($pics, $results['id']);
        }
    }

    return ($pics);
}
// Get the document ID of the first document in a specific catg.
function get_document_by_catg($pid, $doc_catg)
{

    $result = array();

    if ($pid and $doc_catg) {
        $result = sqlQuery("SELECT d.id, d.date, d.url FROM " .
        "documents AS d, categories_to_documents AS cd, categories AS c " .
        "WHERE d.foreign_id = ? " .
        "AND cd.document_id = d.id " .
        "AND c.id = cd.category_id " .
        "AND c.name LIKE ? " .
        "ORDER BY d.date DESC LIMIT 1", array($pid, $doc_catg));
    }

    return($result['id']);
}

// Display image in 'widget style'
function image_widget($doc_id, $doc_catg)
{
        global $pid, $web_root;
        $docobj = new Document($doc_id);
        $image_file = $docobj->get_url_file();
        $image_width = $GLOBALS['generate_doc_thumb'] == 1 ? '' : 'width=100';
        $extension = substr($image_file, strrpos($image_file, "."));
        $viewable_types = array('.png','.jpg','.jpeg','.png','.bmp','.PNG','.JPG','.JPEG','.PNG','.BMP');
    if (in_array($extension, $viewable_types)) { // extention matches list
        $to_url = "<td> <a href = $web_root" .
        "/controller.php?document&retrieve&patient_id=$pid&document_id=$doc_id&as_file=false&original_file=true&disable_exit=false&show_original=true" .
        "/tmp$extension" .  // Force image type URL for fancybo
        " onclick=top.restoreSession(); class='image_modal'>" .
        " <img src = $web_root" .
        "/controller.php?document&retrieve&patient_id=$pid&document_id=$doc_id&as_file=false" .
        " $image_width alt='$doc_catg:$image_file'>  </a> </td> <td valign='center'>".
        htmlspecialchars($doc_catg) . '<br />&nbsp;' . htmlspecialchars($image_file) .
        "</td>";
    } else {
        $to_url = "<td> <a href='" . $web_root . "/controller.php?document&retrieve" .
            "&patient_id=$pid&document_id=$doc_id'" .
            " onclick='top.restoreSession()' class='css_button_small'>" .
            "<span>" .
            htmlspecialchars(xl("View"), ENT_QUOTES)."</a> &nbsp;" .
            htmlspecialchars("$doc_catg - $image_file", ENT_QUOTES) .
            "</span> </td>";
    }

        echo "<table><tr>";
        echo $to_url;
        echo "</tr></table>";
}

// Determine if the Vitals form is in use for this site.
$tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE " .
  "directory = 'vitals' AND state = 1");
$vitals_is_registered = $tmp['count'];

// Get patient/employer/insurance information.
//
$result  = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD,Alert_note");
$result2 = getEmployerData($pid);
$result3 = getInsuranceData($pid, "primary", "copay, provider, DATE_FORMAT(`date`,'%Y-%m-%d') as effdate");
$insco_name = "";
if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
    $insco_name = getInsuranceProvider($result3['provider']);
}
?>
<html>

<head>

    <?php Header::setupHeader(['common']); ?>

<script type="text/javascript" language="JavaScript">
// Sai custom code start 
function myAjaxBilling() {

$('#loadingmessage').show();  // show the loading message.
      $.ajax({
           type: "POST",
           url: 'demo_billing.php',
           data:{action:'demo_billing'},
           success:function(html) {
            // alert(html);
			 // var e = document.getElementById("billing_ps_expand_table");
			  
      			//e.style.display = 'block';
			 document.getElementById("billing_ps_expand").innerHTML = html;
     
			  $('#loadingmessage').hide();
           }

      });
 }


function myAjaxNotes() {
      $.ajax({
           type: "POST",
           url: 'pnotes_fragment.php',
           data:{action:'demo_notes'},
           success:function(html) {
             //alert(html);
			  var e = document.getElementById("pnotes_ps_expand_table");
      			e.style.display = 'block';
			 document.getElementById("pnotes_ps_expand").innerHTML = html;
           }

      });
 }
 
 function myAjaxPatReminder() {
      $.ajax({
           type: "POST",
           url: 'patient_reminders_fragment.php',
           data:{action:'demo_patreminder'},
           success:function(html) {
             //alert(html);
			  var e = document.getElementById("patient_reminders_ps_expand_table");
      			e.style.display = 'block';
			 document.getElementById("patient_reminders_ps_expand").innerHTML = html;
           }

      });
 }
 
 function myAjaxDisc() {
      $.ajax({
           type: "POST",
           url: 'disc_fragment.php',
           data:{action:'demo_disc'},
           success:function(html) {
             //alert(html);
			  var e = document.getElementById("disclosures_ps_expand_table");
      			e.style.display = 'block';
			 document.getElementById("disclosures_ps_expand").innerHTML = html;
           }

      });
 }
 
 function myAjaxVitals() {
      $.ajax({
           type: "POST",
           url: 'vitals_fragment.php',
           data:{action:'demo_vitals'},
           success:function(html) {
             //alert(html);
			  var e = document.getElementById("vitals_ps_expand_table");
      			e.style.display = 'block';
			 document.getElementById("vitals_ps_expand").innerHTML = html;
           }

      });
 }
 
  function myAjaxCliReminder() {
      $.ajax({
           type: "POST",
           url: 'clinical_reminders_fragment.php',
           data:{action:'demo_vitals'},
           success:function(html) {
           // alert(html);
			  var e = document.getElementById("clinical_reminders_ps_expand_table");
      			e.style.display = 'block';
			document.getElementById("clinical_reminders_ps_expand").innerHTML = html;
           }

      });
 }
 
 function myAjaxAppointments() {
      $.ajax({
           type: "POST",
           url: 'demo_billing.php',
           data:{action:'demo_appointments'},
           success:function(html) {
             //alert(html);
			 var e = document.getElementById("appointments_ps_expand_table");
			  
      			e.style.display = 'block';
			 document.getElementById("appointments_ps_expand_table").innerHTML = html;
           }

      });
 }
 
 function myAjaxOthers() {
      $.ajax({
           type: "POST",
           url: 'demo_billing.php',
           data:{action:'demo_others'},
           success:function(html) {
            // alert(html);
			 // var e = document.getElementById("other_ps_expand_table");
			  
      			//e.style.display = 'block';
			 document.getElementById("other_ps_expand").innerHTML = html;
           }

      });
 }
 
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
	if (k == 69 && evt.altKey && evt.ctrlKey && !evt.shiftKey) 
	{
		 top.restoreSession();
	}
}
// Sai custom code end 
 var mypcc = '<?php echo htmlspecialchars($GLOBALS['phone_country_code'], ENT_QUOTES); ?>';

 function oldEvt(apptdate, eventid) {
   let title = '<?php echo xla('Appointments'); ?>';
   dlgopen('../../main/calendar/add_edit_event.php?date=' + apptdate + '&eid=' + eventid, '_blank', 725, 500, '', title);
 }

 function advdirconfigure() {
   dlgopen('advancedirectives.php', '_blank', 400, 500);
  }

 function refreshme() {
  top.restoreSession();
  location.reload();
 }

 // Process click on Delete link.
 function deleteme() { // @todo don't think this is used any longer!!
  dlgopen('../deleter.php?patient=<?php echo htmlspecialchars($pid, ENT_QUOTES); ?>', '_blank', 500, 450, '', '',{
      allowResize: false,
      allowDrag: false,
      dialogId: 'patdel',
      type: 'iframe'
  });
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
    <?php if ($GLOBALS['new_tabs_layout']) { ?>
   top.clearPatient();
    <?php } else { ?>
   parent.left_nav.clearPatient();
    <?php } ?>
 }

 function newEvt() {
     let title = '<?php echo xla('Appointments'); ?>';
     let url = '../../main/calendar/add_edit_event.php?patientid=<?php echo htmlspecialchars($pid, ENT_QUOTES); ?>';
     dlgopen(url, '_blank', 725, 500, '', title);
     return false;
 }

function sendimage(pid, what) {
 // alert('Not yet implemented.'); return false;
 dlgopen('../upload_dialog.php?patientid=' + pid + '&file=' + what,
  '_blank', 500, 400);
 return false;
}

</script>

<script type="text/javascript">

function toggleIndicator(target,div) {

    $mode = $(target).find(".indicator").text();
    if ( $mode == "<?php echo htmlspecialchars(xl('collapse'), ENT_QUOTES); ?>" ) {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('expand'), ENT_QUOTES); ?>" );
        $("#"+div).hide();
    $.post( "../../../library/ajax/user_settings.php", { target: div, mode: 0 });
    } else {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('collapse'), ENT_QUOTES); ?>" );
        $("#"+div).show();
    $.post( "../../../library/ajax/user_settings.php", { target: div, mode: 1 });
    }
}

// Sai custom code start 
/*Modified by Sonali Bug 10547: Performance Tuning*/
function toggleTabs(div) {

     var e = document.getElementById(div);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
}
// Sai custom code end 

// edit prescriptions dialog.
// called from stats.php.
//
function editScripts(url) {
    var AddScript = function () {
        var iam = top.tab_mode ? top.frames.editScripts : window[0];
        iam.location.href = "<?php echo $GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?php echo attr($pid);?>"
    };
    var ListScripts = function () {
        var iam = top.tab_mode ? top.frames.editScripts : window[0];
        iam.location.href = "<?php echo $GLOBALS['webroot']?>/controller.php?prescription&list&id=<?php echo attr($pid); ?>"
    };

    let title = '<?php echo xla('Prescriptions'); ?>';
    let w = 810;
    <?php if ($GLOBALS['weno_rx_enable']) {
        echo 'w = 910;'; }?>


    dlgopen(url, 'editScripts', w, 300, '', '', {
        buttons: [
            {text: '<?php echo xla('Add'); ?>', close: false, style: 'primary  btn-sm', click: AddScript},
            {text: '<?php echo xla('List'); ?>', close: false, style: 'primary  btn-sm', click: ListScripts},
            {text: '<?php echo xla('Done'); ?>', close: true, style: 'default btn-sm'}
        ],
        onClosed: 'refreshme',
        allowResize: true,
        allowDrag: true,
        dialogId: 'editscripts',
        type: 'iframe'
    });
}

function doPublish() {
    let title = '<?php echo xla('Publish Patient to FHIR Server'); ?>';
    let url = top.webroot_url + '/phpfhir/providerPublishUI.php?patient_id=<?php echo attr($pid); ?>';

    dlgopen(url, 'publish', 'modal-mlg', 750, '', '', {
        buttons: [
            {text: '<?php echo xla('Done'); ?>', close: true, style: 'default btn-sm'}
        ],
        allowResize: true,
        allowDrag: true,
        dialogId: '',
        type: 'iframe'
    });
}

$(document).ready(function(){
  var msg_updation='';
    <?php
    if ($GLOBALS['erx_enable']) {
        //$soap_status=sqlQuery("select soap_import_status from patient_data where pid=?",array($pid));
        $soap_status=sqlStatement("select soap_import_status,pid from patient_data where pid=? and soap_import_status in ('1','3')", array($pid));
        while ($row_soapstatus=sqlFetchArray($soap_status)) {
            //if($soap_status['soap_import_status']=='1' || $soap_status['soap_import_status']=='3'){ ?>
            top.restoreSession();
            $.ajax({
                type: "POST",
                url: "../../soap_functions/soap_patientfullmedication.php",
                dataType: "html",
                data: {
                    patient:<?php echo $row_soapstatus['pid']; ?>,
                },
                async: false,
                success: function(thedata){
                    //alert(thedata);
                    msg_updation+=thedata;
                },
                error:function(){
                    alert('ajax error');
                }
            });
            <?php
            //}
            //elseif($soap_status['soap_import_status']=='3'){ ?>
            top.restoreSession();
            $.ajax({
                type: "POST",
                url: "../../soap_functions/soap_allergy.php",
                dataType: "html",
                data: {
                    patient:<?php echo $row_soapstatus['pid']; ?>,
                },
                async: false,
                success: function(thedata){
                    //alert(thedata);
                    msg_updation+=thedata;
                },
                error:function(){
                    alert('ajax error');
                }
            });
            <?php
            if ($GLOBALS['erx_import_status_message']) { ?>
            if(msg_updation)
              alert(msg_updation);
            <?php
            }

            //}
        }
    }
    ?>
    // load divs
    $("#stats_div").load("stats.php", { 'embeddedScreen' : true }, function() {});
    // Sai custom code start 
   // $("#pnotes_ps_expand").load("pnotes_fragment.php");
  //  $("#disclosures_ps_expand").load("disc_fragment.php");

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) { ?>
      top.restoreSession();
     /* $("#clinical_reminders_ps_expand").load("clinical_reminders_fragment.php", { 'embeddedScreen' : true }, function() {
          // (note need to place javascript code here also to get the dynamic link to work)
          $(".medium_modal").on('click', function(e) {
              e.preventDefault();e.stopPropagation();
              dlgopen('', '', 800, 200, '', '', {
                  buttons: [
                      {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
                  ],
                  onClosed: 'refreshme',
                  allowResize: false,
                  allowDrag: true,
                  dialogId: 'demreminder',
                  type: 'iframe',
                  url: $(this).attr('href')
              });
          });
      });*/
    <?php } // end crw?>

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) { ?>
      top.restoreSession();
     // $("#patient_reminders_ps_expand").load("patient_reminders_fragment.php");
    <?php } // end prw?>

<?php if ($vitals_is_registered && acl_check('patients', 'med')) { ?>
    // Initialize the Vitals form if it is registered and user is authorized.
  //  $("#vitals_ps_expand").load("vitals_fragment.php");
<?php } ?>
// Sai custom code end 
    // Initialize track_anything
    $("#track_anything_ps_expand").load("track_anything_fragment.php");


    // Initialize labdata
    $("#labdata_ps_expand").load("labdata_fragment.php");
<?php
// Initialize for each applicable LBF form.
$gfres = sqlStatement("SELECT grp_form_id FROM layout_group_properties WHERE " .
  "grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_repeats > 0 AND grp_activity = 1 " .
  "ORDER BY grp_seq, grp_title");
while ($gfrow = sqlFetchArray($gfres)) {
?>
    $("#<?php echo attr($gfrow['grp_form_id']); ?>_ps_expand").load("lbf_fragment.php?formname=<?php echo attr($gfrow['grp_form_id']); ?>");
<?php
}
?>
    tabbify();

// modal for dialog boxes
    $(".large_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 1000, 600, '', '', {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".rx_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        var AddAmendment = function () {
            var iam = top.tab_mode ? top.frames.editAmendments : window[0];
            iam.location.href = "<?php echo $GLOBALS['webroot']?>/interface/patient_file/summary/add_edit_amendments.php"
        };
        var ListAmendments = function () {
            var iam = top.tab_mode ? top.frames.editAmendments : window[0];
            iam.location.href = "<?php echo $GLOBALS['webroot']?>/interface/patient_file/summary/list_amendments.php"
        };
        var title = '<?php echo xla('Amendments'); ?>';
        dlgopen('', 'editAmendments', 800, 300, '', title, {
            buttons: [
                {text: '<?php echo xla('Add'); ?>', close: false, style: 'primary  btn-sm', click: AddAmendment},
                {text: '<?php echo xla('List'); ?>', close: false, style: 'primary  btn-sm', click: ListAmendments},
                {text: '<?php echo xla('Done'); ?>', close: true, style: 'default btn-sm'}
            ],
            onClosed: 'refreshme',
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

// modal for image viewer
    $(".image_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 400, 300, '', '<?php echo xla('Patient Images'); ?>', {
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".deleter").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 600, 360, '', '', {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            //onClosed: 'imdeleted',
            allowResize: false,
            allowDrag: false,
            dialogId: 'patdel',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".iframe1").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 350, 300, '', '', {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });
// for patient portal
  $(".small_modal").on('click', function(e) {
      e.preventDefault();e.stopPropagation();
      dlgopen('', '', 380, 200, '', '', {
          buttons: [
              {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
          ],
          allowResize: true,
          allowDrag: true,
          dialogId: '',
          type: 'iframe',
          url: $(this).attr('href')
      });
  });

  function openReminderPopup() {
      top.restoreSession()
      dlgopen('', 'reminders', 500, 250, '', '', {
          buttons: [
              {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
          ],
          allowResize: true,
          allowDrag: true,
          dialogId: '',
          type: 'iframe',
          url: $("#reminder_popup_link").attr('href')
      });
  }


<?php if ($GLOBALS['patient_birthday_alert']) {
    // To display the birthday alert:
    //  1. The patient is not deceased
    //  2. The birthday is today (or in the past depending on global selection)
    //  3. The notification has not been turned off (or shown depending on global selection) for this year
    $birthdayAlert = new BirthdayReminder($pid, $_SESSION['authId']);
    if ($birthdayAlert->isDisplayBirthdayAlert()) {
    ?>
    // show the active reminder modal
    dlgopen('', 'bdayreminder', 300, 170, '', false, {
        allowResize: false,
        allowDrag: true,
        dialogId: '',
        type: 'iframe',
        url: $("#birthday_popup").attr('href')
    });

    <?php } elseif ($active_reminders || $all_allergy_alerts) { ?>
    openReminderPopup();
    <?php }?>
<?php } elseif ($active_reminders || $all_allergy_alerts) { ?>
    openReminderPopup();
<?php }?>

});

// JavaScript stuff to do when a new patient is set.
//
function setMyPatient() {
 // Avoid race conditions with loading of the left_nav or Title frame.
 if (!parent.allFramesLoaded()) {
  setTimeout("setMyPatient()", 500);
  return;
 }
<?php if (isset($_GET['set_pid'])) { ?>
 parent.left_nav.setPatient(<?php echo "'" . addslashes($result['fname']) . " " . addslashes($result['lname']) . "'," . addslashes($pid) . ",'" . addslashes($result['pubpid']) . "','', ' " . xls('DOB') . ": " . addslashes(oeFormatShortDate($result['DOB_YMD'])) . " " . xls('Age') . ": " . addslashes(getPatientAgeDisplay($result['DOB_YMD'])) . "','" .addslashes($result['Alert_note']) ."'"; ?>);	 // Sai custom code -->
 var EncounterDateArray = new Array;
 var CalendarCategoryArray = new Array;
 var EncounterIdArray = new Array;
 var Count = 0;
<?php
  //Encounter details are stored to javacript as array.
  $result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
    " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));
if (sqlNumRows($result4)>0) {
    while ($rowresult4 = sqlFetchArray($result4)) {
?>
 EncounterIdArray[Count] = '<?php echo addslashes($rowresult4['encounter']); ?>';
 EncounterDateArray[Count] = '<?php echo addslashes(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>';
 CalendarCategoryArray[Count] = '<?php echo addslashes(xl_appt_category($rowresult4['pc_catname'])); ?>';
 Count++;
<?php
    }
}
?>
 parent.left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);
<?php } // end setting new pid ?>
 parent.left_nav.syncRadios();
<?php if ((isset($_GET['set_pid']) ) && (isset($_GET['set_encounterid'])) && ( intval($_GET['set_encounterid']) > 0 )) {
    $encounter = intval($_GET['set_encounterid']);
    $_SESSION['encounter'] = $encounter;
    $query_result = sqlQuery("SELECT `date` FROM `form_encounter` WHERE `encounter` = ?", array($encounter)); ?>
 encurl = 'encounter/encounter_top.php?set_encounter=' + <?php echo attr($encounter);?> + '&pid=' + <?php echo attr($pid);?>;
    <?php if ($GLOBALS['new_tabs_layout']) { ?>
  parent.left_nav.setEncounter('<?php echo attr(oeFormatShortDate(date("Y-m-d", strtotime($query_result['date'])))); ?>', '<?php echo attr($encounter); ?>', 'enc');
    top.restoreSession();
  parent.left_nav.loadFrame('enc2', 'enc', 'patient_file/' + encurl);
    <?php } else { ?>
  var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
  parent.left_nav.setEncounter('<?php echo attr(oeFormatShortDate(date("Y-m-d", strtotime($query_result['date'])))); ?>', '<?php echo attr($encounter); ?>', othername);
    top.restoreSession();
  parent.frames[othername].location.href = '../' + encurl;
    <?php } ?>
<?php } // end setting new encounter id (only if new pid is also set) ?>
}

$(window).on('load', function() {
 setMyPatient();
});

</script>

<style type="css/text">

#pnotes_ps_expand {
  height:auto;
  width:100%;
}

<?php
// This is for layout font size override.
$grparr = array();
getLayoutProperties('DEM', $grparr, 'grp_size');
if (!empty($grparr['']['grp_size'])) {
    $FONTSIZE = $grparr['']['grp_size'];
?>
/* Override font sizes in the theme. */
#DEM .groupname {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#DEM .label {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#DEM .data {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
#DEM .data td {
  font-size: <?php echo attr($FONTSIZE); ?>pt;
}
<?php } ?>

</style>

</head>

<body class="body_top patient-demographics">

<a href='../reminder/active_reminder_popup.php' id='reminder_popup_link' style='display: none;' onclick='top.restoreSession()'></a>

<a href='../birthday_alert/birthday_pop.php?pid=<?php echo attr($pid); ?>&user_id=<?php echo attr($_SESSION['authId']); ?>' id='birthday_popup' style='display: none;' onclick='top.restoreSession()'></a>
<?php
$thisauth = acl_check('patients', 'demo');
if ($thisauth) {
    if ($result['squad'] && ! acl_check('squads', $result['squad'])) {
        $thisauth = 0;
    }
}

if (!$thisauth) {
    echo "<p>(" . htmlspecialchars(xl('Demographics not authorized'), ENT_NOQUOTES) . ")</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

if ($thisauth) : ?>

<table class="table_header">
    <tr>
        <td>
            <span class='title'>
                <?php echo htmlspecialchars(getPatientName($pid), ENT_NOQUOTES); ?>
            </span>
        </td>
        <?php if (acl_check('admin', 'superbill') && $GLOBALS['allow_pat_delete']) : ?>
        <td style='padding-left:1em;' class="delete">
            <a class='css_button deleter'
               href='../deleter.php?patient=<?php echo htmlspecialchars($pid, ENT_QUOTES);?>'
               onclick='return top.restoreSession()'>
                <span><?php echo htmlspecialchars(xl('Delete'), ENT_NOQUOTES);?></span>
            </a>
        </td>
        <?php endif; // Allow PT delete
if ($GLOBALS['erx_enable']) : ?>
        <td style="padding-left:1em;" class="erx">
            <a class="css_button" href="../../eRx.php?page=medentry" onclick="top.restoreSession()">
                <span><?php echo htmlspecialchars(xl('NewCrop MedEntry'), ENT_NOQUOTES);?></span>
            </a>
        </td>
        <td style="padding-left:1em;">
            <a class="css_button iframe1"
               href="../../soap_functions/soap_accountStatusDetails.php"
               onclick="top.restoreSession()">
                <span><?php echo htmlspecialchars(xl('NewCrop Account Status'), ENT_NOQUOTES);?></span>
            </a>
        </td>
        <td id='accountstatus'></td>
<?php endif; // eRX Enabled
        //Patient Portal
        $portalUserSetting = true; //flag to see if patient has authorized access to portal
if (($GLOBALS['portal_onsite_enable'] && $GLOBALS['portal_onsite_address']) ||
            ($GLOBALS['portal_onsite_two_enable'] && $GLOBALS['portal_onsite_two_address']) ) :
        $portalStatus = sqlQuery("SELECT allow_patient_portal FROM patient_data WHERE pid=?", array($pid));
        if ($portalStatus['allow_patient_portal']=='YES') :
            $portalLogin = sqlQuery("SELECT pid FROM `patient_access_onsite` WHERE `pid`=?", array($pid));?>
                <td style='padding-left:1em;'>
                    <a class='css_button small_modal'
                           href='create_portallogin.php?portalsite=on&patient=<?php echo htmlspecialchars($pid, ENT_QUOTES);?>'
                       onclick='top.restoreSession()'>
                            <?php $display = (empty($portalLogin)) ? xlt('Create Onsite Portal Credentials') : xlt('Reset Onsite Portal Credentials'); ?>
                            <span><?php echo $display; ?></span>
                    </a>
                </td>
                <?php
        else :
                $portalUserSetting = false;
        endif; // allow patient portal
endif; // Onsite Patient Portal
if ($GLOBALS['portal_offsite_enable'] && $GLOBALS['portal_offsite_address']) :
    $portalStatus = sqlQuery("SELECT allow_patient_portal FROM patient_data WHERE pid=?", array($pid));
    if ($portalStatus['allow_patient_portal']=='YES') :
        $portalLogin = sqlQuery("SELECT pid FROM `patient_access_offsite` WHERE `pid`=?", array($pid));
        ?>
        <td style='padding-left:1em;'>
            <a class='css_button small_modal'
               href='create_portallogin.php?portalsite=off&patient=<?php echo htmlspecialchars($pid, ENT_QUOTES);?>'
               onclick='top.restoreSession()'>
                <span>
                    <?php $text = (empty($portalLogin)) ? xlt('Create Offsite Portal Credentials') : xlt('Reset Offsite Portal Credentials'); ?>
                    <?php echo $text; ?>
                </span>
            </a>
                </td>
            <?php
    else :
                $portalUserSetting = false;
    endif; // allow_patient_portal
endif; // portal_offsite_enable
if (!($portalUserSetting)) : // Show that the patient has not authorized portal access ?>
            <td style='padding-left:1em;'>
                <?php echo htmlspecialchars(xl('Patient has not authorized the Patient Portal.'), ENT_NOQUOTES);?>
            </td>
<?php endif;
        //Patient Portal

        // If patient is deceased, then show this (along with the number of days patient has been deceased for)
        $days_deceased = is_patient_deceased($pid);
if ($days_deceased != null) : ?>
            <td class="deceased" style="padding-left:1em;font-weight:bold;color:red">
                <?php
                if ($days_deceased == 0) {
                    echo xlt("DECEASED (Today)");
                } else if ($days_deceased == 1) {
                    echo xlt("DECEASED (1 day ago)");
                } else {
                    echo xlt("DECEASED") . " (" . text($days_deceased) . " " . xlt("days ago") . ")";
                } ?>
            </td>
<?php endif; ?>
    </tr>
</table>

<?php
endif; // $thisauth
?>

<?php
// Get the document ID of the patient ID card if access to it is wanted here.
$idcard_doc_id = false;
if ($GLOBALS['patient_id_category_name']) {
    $idcard_doc_id = get_document_by_catg($pid, $GLOBALS['patient_id_category_name']);
}

// Collect the patient menu then build it
$menuPatient = new PatientMenuRole();
$menu_restrictions = $menuPatient->getMenu();
// Sai custom code start -->
// code added for facesheet view 



 $facesheetResult = sqlQuery("select pd.billEHRPatientID,pd.pid,fs.URL from facesheet fs JOIN patient_data pd on fs.billEHRPatientID=pd.billEHRPatientID where pd.pid=?  and fs.isActive=1 order by fs.facesheetID desc limit 1",array($pid));

 if(isset($facesheetResult) && !empty($facesheetResult)){
	 $facesheetURL = $billehrUploadPath.$facesheetResult['URL'];
	 
 }
 else{
	 $facesheetURL ='';
 }
 
//echo $facesheetURL;	 
// Sai custom code end -->
?>
<table cellspacing='0' cellpadding='0' border='0' class="subnav">
    <tr>
        <td class="small" colspan='4'>


            <?php
            $first = true;
            foreach ($menu_restrictions as $key => $value) {
                if (!empty($value->children)) {
                    // flatten to only show children items
                    foreach ($value->children as $children_key => $children_value) {
                        if (!$first) {
                            echo "|";
                        }
                        $first = false;
                        $link = ($children_value->pid != "true") ? $children_value->url : $children_value->url . attr($pid);
                        echo '<a href="' . $link . '" onclick="' . $children_value->on_click .'"> ' . text($children_value->label) . ' </a>';
                    }
                } else {
                    if (!$first) {
                        echo "|";
                    }
                    $first = false;
                    $link = ($value->pid != "true") ? $value->url : $value->url . attr($pid);
                    echo '<a href="' . $link . '" onclick="' . $value->on_click .'"> ' . text($value->label) . ' </a>';
                }
            }
            ?>

            <!-- Sai custom code start -->
  |
<a href="patient_log.php" class='iframe small_modal' onclick='top.restoreSession()'>
  <?php echo htmlspecialchars(xl('Log'),ENT_NOQUOTES); ?></a>
  |
  <a href="../../patient_file/encounter/load_form.php?formname=soap" onclick='top.restoreSession()'>
  <?php echo htmlspecialchars(xl('SOAP'),ENT_NOQUOTES); ?></a>
  |
  <a href="../../patient_file/encounter/load_form.php?formname=ros" onclick='top.restoreSession()'>
  <?php echo htmlspecialchars(xl('Review Of Systems'),ENT_NOQUOTES); ?></a>
  |
  <a href="../../patient_file/encounter/load_form.php?formname=reviewofs" onclick='top.restoreSession()'>
  <?php echo htmlspecialchars(xl('Review Of Systems Checks'),ENT_NOQUOTES); ?></a>
  |
  <a href="../../patient_file/encounter/load_form.php?formname=vitals" onclick='top.restoreSession()'>
  <?php echo htmlspecialchars(xl('Vitals'),ENT_NOQUOTES); ?></a>
  |
  <!-- code added for patient statement phase-II BUG 10485---->
  <a href="../history/statements.php" onclick='top.restoreSession()'>
  <?php echo htmlspecialchars(xl('Patient Statements'),ENT_NOQUOTES); ?></a>
  <?php 
  if(isset($facesheetResult) && !empty($facesheetResult)){
  ?>|
  
  <a href="<?php echo $facesheetURL ?>">
    <strong><?php echo htmlspecialchars(xl('Face Sheet'),ENT_NOQUOTES); ?></strong> 
  </a>
  <?php 
  }
  ?>
        </td>
    </tr>
</table> <!-- end header -->
<br>

<!-- Modified by Sonali Bug 10547: Performance Tuning -->
<table cellspacing='0' cellpadding='0' border='0'>
 <tr>
  <td> <div id="newTab"> <ul class="tabNav">
	<li class="current"> 
	<!--<a href="#" onclick='toggleTabs("billing_ps_expand_table");'>-->
	<a href="" onclick='myAjaxBilling();'>
	<?php echo htmlspecialchars(xl('Billing'),ENT_NOQUOTES); ?></a>
	</li>
 
	</li>  
    
    <li class="current"> 
	<a href="pnotes_fragment.php" onclick='myAjaxNotes();'>
	<?php echo htmlspecialchars(xl('Notes'),ENT_NOQUOTES); ?></a>
	</li>  
    
    <li class="current"> 
	<a href="#" onclick='toggleTabs("patient_reminders_ps_expand_table");'>
	<?php echo htmlspecialchars(xl('Patient reminders '),ENT_NOQUOTES); ?></a>
	</li>
    
    <li class="current"> 
	<a href="#" onclick='myAjaxDisc();'>
	<?php echo htmlspecialchars(xl('Disclosures '),ENT_NOQUOTES); ?></a>
	</li>   
    
    <li class="current"> 
	<!--<a href="#" onclick='toggleTabs("vitals_ps_expand_table");'>-->
	<a href="#" onclick='myAjaxVitals();'>
	<?php echo htmlspecialchars(xl('Vitals '),ENT_NOQUOTES); ?></a>
	</li> 

	 

	<li class="current">  
	<a href="#" onclick='myAjaxCliReminder();'>
	<?php echo htmlspecialchars(xl('Clinical Reminders'),ENT_NOQUOTES); ?></a>
	</li>  

	<!--<li class="current">  
	<a href="#" onclick='myAjaxAppointments();'>
	<?php echo htmlspecialchars(xl('Appointments'),ENT_NOQUOTES); ?></a>
	</li> 

	
      <li class="current"> 
<a href="#" onclick='toggleTabs("other_ps_expand_table");'>
<?php echo htmlspecialchars(xl('Others '),ENT_NOQUOTES); ?></a>
</li>  -->

	<!--<li class="current"> 
		<a href="#" onclick='myAjaxOthers();'>
	<?php echo htmlspecialchars(xl('Others '),ENT_NOQUOTES); ?></a>
	</li>  -->

</div>
</td>
</tr>
</table>
<!-- Sai custom code end -->
<div style='margin-top:10px' class="main"> <!-- start main content div -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td class="demographics-box" align="left" valign="top">
                <!-- start left column div -->
                <div style='float:left; margin-right:20px'>

                    <table cellspacing=0 cellpadding=0>
    <!-- Sai custom code start -->
         <tr id="billing_ps_expand_table">
      	 <td>
		  <div id='loadingmessage' style='display:none'><img src='../../pic/ajax-loader.gif'/></div>
			<div id="billing_ps_expand" style="background-color:#FFFFFF">
        
        	</div>
		
       	</td>
      	</tr>
   
    </div> <!-- required for expand_collapse_widget -->
    
      	<tr id="demographics_ps_expand_table" >
	<!-- Sai custom code end -->
       <td>
<?php
// Demographics expand collapse widget
$widgetTitle = xl("Demographics");
$widgetLabel = "demographics";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "demographics_full.php";
$widgetButtonClass = "";
$linkMethod = "html";
$bodyClass = "";
$widgetAuth = acl_check('patients', 'demo', '', 'write');
$fixedWidth = true;
expand_collapse_widget(
    $widgetTitle,
    $widgetLabel,
    $widgetButtonLabel,
    $widgetButtonLink,
    $widgetButtonClass,
    $linkMethod,
    $bodyClass,
    $widgetAuth,
    $fixedWidth
);
?>
         <div id="DEM" >
          <ul class="tabNav">
            <?php display_layout_tabs('DEM', $result, $result2); ?>
          </ul>
          <div class="tabContainer">
            <?php display_layout_tabs_data('DEM', $result, $result2); ?>
          </div>
         </div>
        </div> <!-- required for expand_collapse_widget -->
       </td>
      </tr>

      <tr id="insurance_ps_expand_table" ><!-- Sai custom code  -->
       <td>
<?php
$insurance_count = 0;
foreach (array('primary','secondary','tertiary') as $instype) {
    $enddate = 'Present';
    $query = "SELECT * FROM insurance_data WHERE " .
    "pid = ? AND type = ? " .
    "ORDER BY date DESC";
    $res = sqlStatement($query, array($pid, $instype));
    while ($row = sqlFetchArray($res)) {
        if ($row['provider']) {
            $insurance_count++;
        }
    }
}

if ($insurance_count > 0) {
  // Insurance expand collapse widget
    $widgetTitle = xl("Insurance");
    $widgetLabel = "insurance";
    $widgetButtonLabel = xl("Edit");
    $widgetButtonLink = "demographics_full.php";
    $widgetButtonClass = "";
    $linkMethod = "html";
    $bodyClass = "";
    $widgetAuth = acl_check('patients', 'demo', '', 'write');
    $fixedWidth = true;
    expand_collapse_widget(
        $widgetTitle,
        $widgetLabel,
        $widgetButtonLabel,
        $widgetButtonLink,
        $widgetButtonClass,
        $linkMethod,
        $bodyClass,
        $widgetAuth,
        $fixedWidth
    );

    if ($insurance_count > 0) {
    ?>

        <ul class="tabNav"><?php
                    ///////////////////////////////// INSURANCE SECTION
                    $first = true;
        foreach (array('primary','secondary','tertiary') as $instype) {
            $query = "SELECT * FROM insurance_data WHERE " .
            "pid = ? AND type = ? " .
            "ORDER BY date DESC";
            $res = sqlStatement($query, array($pid, $instype));

            $enddate = 'Present';

            while ($row = sqlFetchArray($res)) {
                if ($row['provider']) {
                    $ins_description  = ucfirst($instype);
                                                $ins_description = xl($ins_description);
                    $ins_description  .= strcmp($enddate, 'Present') != 0 ? " (".xl('Old').")" : "";
                    ?>
                    <li <?php echo $first ? 'class="current"' : '' ?>><a href="#">
                                <?php echo htmlspecialchars($ins_description, ENT_NOQUOTES); ?></a></li>
                                <?php
                                $first = false;
                }

                $enddate = $row['date'];
            }
        }

                    // Display the eligibility tab
                    echo "<li><a href='#'>" .
                        htmlspecialchars(xl('Eligibility'), ENT_NOQUOTES) . "</a></li>";

                    ?></ul><?php
    } ?>

                <div class="tabContainer">
                    <?php
                    $first = true;
                    foreach (array('primary','secondary','tertiary') as $instype) {
                        $enddate = 'Present';

                        $query = "SELECT * FROM insurance_data WHERE " .
                        "pid = ? AND type = ? " .
                        "ORDER BY date DESC";
                        $res = sqlStatement($query, array($pid, $instype));
                        while ($row = sqlFetchArray($res)) {
                            if ($row['provider']) {
                                ?>
                                <div class="tab <?php echo $first ? 'current' : '' ?>">
                                <table border='0' cellpadding='0' width='100%'>
                                <?php
                                $icobj = new InsuranceCompany($row['provider']);
                                $adobj = $icobj->get_address();
                                $insco_name = trim($icobj->get_name());
                                ?>
                                <tr>
                                 <td valign='top' colspan='3'>
                                  <span class='text'>
                                    <?php
                                    if (strcmp($enddate, 'Present') != 0) {
                                        echo htmlspecialchars(xl("Old"), ENT_NOQUOTES)." ";
                                    }
                                    ?>
                                    <?php $tempinstype=ucfirst($instype);
                                    echo htmlspecialchars(xl($tempinstype.' Insurance'), ENT_NOQUOTES); ?>
                                    <?php if (strcmp($row['date'], '0000-00-00') != 0) { ?>
												<?php echo htmlspecialchars(xl('from','',' ',' ').preg_replace("/(\d+)\D+(\d+)\D+(\d+)/","$2/$3/$1",$row['date']),ENT_NOQUOTES); ?><!-- Sai custom code  -->
                                    <?php } ?>
                                            <?php echo htmlspecialchars(xl('until', '', ' ', ' '), ENT_NOQUOTES);
								    echo (strcmp($enddate, 'Present') != 0) ? preg_replace("/(\d+)\D+(\d+)\D+(\d+)/","$2/$3/$1",$enddate) : htmlspecialchars(xl('Present'),ENT_NOQUOTES); ?>:</span><!-- Sai custom code  -->
                                     </td>    
                                    </tr>
                                    <tr>
                                     <td valign='top'>
                                      <span class='text'>
                                        <?php
                                        if ($insco_name) {
                                            echo htmlspecialchars($insco_name, ENT_NOQUOTES) . '<br>';
                                            if (trim($adobj->get_line1())) {
                                                echo htmlspecialchars($adobj->get_line1(), ENT_NOQUOTES) . '<br>';
                                                echo htmlspecialchars($adobj->get_city() . ', ' . $adobj->get_state() . ' ' . $adobj->get_zip(), ENT_NOQUOTES);
                                            }
                                        } else {
                                            echo "<font color='red'><b>".htmlspecialchars(xl('Unassigned'), ENT_NOQUOTES)."</b></font>";
                                        }
                                        ?>
                                      <br>
                                        <?php echo htmlspecialchars(xl('Policy Number'), ENT_NOQUOTES); ?>:
                                        <?php echo htmlspecialchars($row['policy_number'], ENT_NOQUOTES) ?><br>
                                        <?php echo htmlspecialchars(xl('Plan Name'), ENT_NOQUOTES); ?>:
                                        <?php echo htmlspecialchars($row['plan_name'], ENT_NOQUOTES); ?><br>
                                        <?php echo htmlspecialchars(xl('Group Number'), ENT_NOQUOTES); ?>:
                                        <?php echo htmlspecialchars($row['group_number'], ENT_NOQUOTES); ?></span>
                                     </td>
                                     <td valign='top'>
                                        <span class='bold'><?php echo htmlspecialchars(xl('Subscriber'), ENT_NOQUOTES); ?>: </span><br>
                                        <span class='text'><?php echo htmlspecialchars($row['subscriber_fname'] . ' ' . $row['subscriber_mname'] . ' ' . $row['subscriber_lname'], ENT_NOQUOTES); ?>
                                    <?php
                                    if ($row['subscriber_relationship'] != "") {
                                        echo "(" . htmlspecialchars($row['subscriber_relationship'], ENT_NOQUOTES) . ")";
                                    }
                                    ?>
                                  <br>
                                    <?php echo htmlspecialchars(xl('S.S.'), ENT_NOQUOTES); ?>:
                                    <?php echo htmlspecialchars($row['subscriber_ss'], ENT_NOQUOTES); ?><br>
                                    <?php echo htmlspecialchars(xl('D.O.B.'), ENT_NOQUOTES); ?>:
                                    <?php
                                    if ($row['subscriber_DOB'] != "0000-00-00 00:00:00") {
				    echo htmlspecialchars(preg_replace("/(\d+)\D+(\d+)\D+(\d+)/","$2/$3/$1",$row['subscriber_DOB']),ENT_NOQUOTES); 
                                    }
                                    ?><br>
                                    <?php echo htmlspecialchars(xl('Phone'), ENT_NOQUOTES); ?>:
                                    <?php echo htmlspecialchars($row['subscriber_phone'], ENT_NOQUOTES); ?>
                                  </span>
                                 </td>
                                 <td valign='top'>
                                  <span class='bold'><?php echo htmlspecialchars(xl('Subscriber Address'), ENT_NOQUOTES); ?>: </span><br>
                                  <span class='text'><?php echo htmlspecialchars($row['subscriber_street'], ENT_NOQUOTES); ?><br>
                                    <?php echo htmlspecialchars($row['subscriber_city'], ENT_NOQUOTES); ?>
                                    <?php
                                    if ($row['subscriber_state'] != "") {
                                        echo ", ";
                                    }

                                    echo htmlspecialchars($row['subscriber_state'], ENT_NOQUOTES); ?>
                                    <?php
                                    if ($row['subscriber_country'] != "") {
                                        echo ", ";
                                    }

                                    echo htmlspecialchars($row['subscriber_country'], ENT_NOQUOTES); ?>
                                    <?php echo " " . htmlspecialchars($row['subscriber_postal_code'], ENT_NOQUOTES); ?></span>

                                <?php if (trim($row['subscriber_employer'])) { ?>
                                  <br><span class='bold'><?php echo htmlspecialchars(xl('Subscriber Employer'), ENT_NOQUOTES); ?>: </span><br>
                                  <span class='text'><?php echo htmlspecialchars($row['subscriber_employer'], ENT_NOQUOTES); ?><br>
                                    <?php echo htmlspecialchars($row['subscriber_employer_street'], ENT_NOQUOTES); ?><br>
                                    <?php echo htmlspecialchars($row['subscriber_employer_city'], ENT_NOQUOTES); ?>
                                    <?php
                                    if ($row['subscriber_employer_city'] != "") {
                                        echo ", ";
                                    }

                                    echo htmlspecialchars($row['subscriber_employer_state'], ENT_NOQUOTES); ?>
                                    <?php
                                    if ($row['subscriber_employer_country'] != "") {
                                        echo ", ";
                                    }

                                    echo htmlspecialchars($row['subscriber_employer_country'], ENT_NOQUOTES); ?>
                                    <?php echo " " . htmlspecialchars($row['subscriber_employer_postal_code'], ENT_NOQUOTES); ?>
                                  </span>
                                <?php } ?>

                                 </td>
                                </tr>
                                <tr>
                                 <td>
                                <?php if ($row['copay'] != "") { ?>
                                  <span class='bold'><?php echo htmlspecialchars(xl('CoPay'), ENT_NOQUOTES); ?>: </span>
                                  <span class='text'><?php echo htmlspecialchars($row['copay'], ENT_NOQUOTES); ?></span>
                  <br />
                                <?php } ?>
                                  <span class='bold'><?php echo htmlspecialchars(xl('Accept Assignment'), ENT_NOQUOTES); ?>:</span>
                                  <span class='text'>
                                <?php
                                if ($row['accept_assignment'] == "TRUE") {
                                    echo xl("YES");
                                }
                                if ($row['accept_assignment'] == "FALSE") {
                                    echo xl("NO");
                                }
                                ?>
                                  </span>
                                <?php if (!empty($row['policy_type'])) { ?>
                  <br />
                                  <span class='bold'><?php echo htmlspecialchars(xl('Secondary Medicare Type'), ENT_NOQUOTES); ?>: </span>
                                  <span class='text'><?php echo htmlspecialchars($policy_types[$row['policy_type']], ENT_NOQUOTES); ?></span>
                                <?php } ?>
                                 </td>
                                 <td valign='top'></td>
                                 <td valign='top'></td>
                               </tr>

                            </table>
                            </div>
                                <?php
                            } // end if ($row['provider'])
                            $enddate = $row['date'];
                            $first = false;
                        } // end while
                    } // end foreach

                    // Display the eligibility information
                    echo "<div class='tab'>";
                    show_eligibility_information($pid, true);
                    echo "</div>";

            ///////////////////////////////// END INSURANCE SECTION
            ?>
            </div>

<?php } // ?>

            </td>
        </tr>


<?php if (acl_check('patients', 'notes')) { ?>
<tr id="pnotes_ps_expand_table" style="display:none" ><!-- Sai custom code  -->

            <td width='650px'>
<?php
// Notes expand collapse widget
$widgetTitle = xl("Notes");
$widgetLabel = "pnotes";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "pnotes_full.php?form_active=1";
$widgetButtonClass = "";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = acl_check('patients', 'notes', '', 'write');
$fixedWidth = true;
expand_collapse_widget(
    $widgetTitle,
    $widgetLabel,
    $widgetButtonLabel,
    $widgetButtonLink,
    $widgetButtonClass,
    $linkMethod,
    $bodyClass,
    $widgetAuth,
    $fixedWidth
);
?>
                    <br/>
                    <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                </div>
            </td>
        </tr>
<?php } // end if notes authorized ?>

<?php if (acl_check('patients', 'reminder') && $GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) {
       		 	echo "<tr id='patient_reminders_ps_expand_table' style='display:none'><td width='650px'>";// Sai custom code start -->
                // patient reminders collapse widget
                $widgetTitle = xl("Patient Reminders");
                $widgetLabel = "patient_reminders";
                $widgetButtonLabel = xl("Edit");
                $widgetButtonLink = "../reminder/patient_reminders.php?mode=simple&patient_id=".$pid;
                $widgetButtonClass = "";
                $linkMethod = "html";
                $bodyClass = "notab";
                $widgetAuth = acl_check('patients', 'reminder', '', 'write');
                $fixedWidth = true;
                expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth); ?>
                    <br/>
               
                </div>
                        </td>
                </tr>
<?php } //end if prw is activated  ?>

<?php if (acl_check('patients', 'disclosure')) { ?>
  <tr id="disclosures_ps_expand_table" style="display:none"><!-- Sai custom code  -->
       <td width='650px'>
<?php
// disclosures expand collapse widget
$widgetTitle = xl("Disclosures");
$widgetLabel = "disclosures";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "disclosure_full.php";
$widgetButtonClass = "";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = acl_check('patients', 'disclosure', '', 'write');
$fixedWidth = true;
expand_collapse_widget(
    $widgetTitle,
    $widgetLabel,
    $widgetButtonLabel,
    $widgetButtonLink,
    $widgetButtonClass,
    $linkMethod,
    $bodyClass,
    $widgetAuth,
    $fixedWidth
);
?>
                    <br/>
                    <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                </div>
     </td>
    </tr>
<?php } // end if disclosures authorized ?>

<?php if ($GLOBALS['amendments'] && acl_check('patients', 'amendment')) { ?>
  <tr>
       <td width='650px'>
        <?php // Amendments widget
        $widgetTitle = xlt('Amendments');
        $widgetLabel = "amendments";
        $widgetButtonLabel = xlt("Edit");
        $widgetButtonLink = $GLOBALS['webroot'] . "/interface/patient_file/summary/list_amendments.php?id=" . attr($pid);
        $widgetButtonClass = "rx_modal";
        $linkMethod = "html";
        $bodyClass = "summary_item small";
        $widgetAuth = acl_check('patients', 'amendment', '', 'write');
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        $sql = "SELECT * FROM amendments WHERE pid = ? ORDER BY amendment_date DESC";
        $result = sqlStatement($sql, array($pid));

        if (sqlNumRows($result) == 0) {
                echo " <table><tr>\n";
                echo "  <td colspan='$numcols' class='text'>&nbsp;&nbsp;" . xlt('None') . "</td>\n";
                echo " </tr></table>\n";
        }

        while ($row=sqlFetchArray($result)) {
                echo "&nbsp;&nbsp;";
                echo "<a class= '" . $widgetButtonClass . "' href='" . $GLOBALS['webroot'] . "/interface/patient_file/summary/add_edit_amendments.php?id=" . attr($row['amendment_id']) . "' onclick='top.restoreSession()'>" . text($row['amendment_date']);
                echo "&nbsp; " . text($row['amendment_desc']);

                echo "</a><br>\n";
        } ?>
  </td>
    </tr>
<?php } // end amendments authorized ?>

<?php if (acl_check('patients', 'lab')) { ?>
    <tr>
     <td width='650px'>
<?php // labdata expand collapse widget
  $widgetTitle = xl("Labs");
  $widgetLabel = "labdata";
  $widgetButtonLabel = xl("Trend");
  $widgetButtonLink = "../summary/labdata.php";#"../encounter/trend_form.php?formname=labdata";
  $widgetButtonClass = "";
  $linkMethod = "html";
  $bodyClass = "notab";
  // check to see if any labdata exist
  $spruch = "SELECT procedure_report.date_collected AS date " .
            "FROM procedure_report " .
            "JOIN procedure_order ON  procedure_report.procedure_order_id = procedure_order.procedure_order_id " .
            "WHERE procedure_order.patient_id = ? " .
            "ORDER BY procedure_report.date_collected DESC ";
  $existLabdata = sqlQuery($spruch, array($pid));
if ($existLabdata) {
    $widgetAuth = true;
} else {
    $widgetAuth = false;
}

  $fixedWidth = true;
  expand_collapse_widget(
      $widgetTitle,
      $widgetLabel,
      $widgetButtonLabel,
      $widgetButtonLink,
      $widgetButtonClass,
      $linkMethod,
      $bodyClass,
      $widgetAuth,
      $fixedWidth
  );
?>
      <br/>
      <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
      </div>
     </td>
    </tr>
<?php } // end labs authorized ?>

<?php if ($vitals_is_registered && acl_check('patients', 'med')) { ?>
            <tr id="vitals_ps_expand_table" style="display:none"> <!-- Sai custom code  -->
     <td width='650px'>
<?php // vitals expand collapse widget
  $widgetTitle = xl("Vitals");
  $widgetLabel = "vitals";
  $widgetButtonLabel = xl("Trend");
  $widgetButtonLink = "../encounter/trend_form.php?formname=vitals";
  $widgetButtonClass = "";
  $linkMethod = "html";
  $bodyClass = "notab";
  // check to see if any vitals exist
  $existVitals = sqlQuery("SELECT * FROM form_vitals WHERE pid=?", array($pid));
if ($existVitals) {
    $widgetAuth = true;
} else {
    $widgetAuth = false;
}

  $fixedWidth = true;
  expand_collapse_widget(
      $widgetTitle,
      $widgetLabel,
      $widgetButtonLabel,
      $widgetButtonLink,
      $widgetButtonClass,
      $linkMethod,
      $bodyClass,
      $widgetAuth,
      $fixedWidth
  );
?>
      <br/>
      <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
      </div>
     </td>
    </tr>
<?php } // end if ($vitals_is_registered && acl_check('patients', 'med')) ?>
	<!-- Sai custom code start -->
	<!--Added by Pawan for BUG ID 10547 for clinical reminder -->              
                
  		<tr id="clinical_reminders_ps_expand_table" style="display:none" >
			<td width='650px'>
            
            <?php
           
            if ( (acl_check('patients', 'med')) && ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) ) {
            	// clinical summary expand collapse widget
            	$widgetTitle = xl("Clinical Reminders");
              	$widgetLabel = "clinical_reminders";
              	$widgetButtonLabel = xl("Edit");
              	$widgetButtonLink = "../reminder/clinical_reminders.php?patient_id=".$pid;;
              	$widgetButtonClass = "";
              	$linkMethod = "html";
              	$bodyClass = "summary_item small";
              	$widgetAuth = true;
              	$fixedWidth = false;
              	expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
               
            } // end if crw
           ?>         
            
            <br/>
            <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
            </div>
            </td>
        </tr>	   
        
         <!--Added by Pawan for BUG ID 10547 for appointments -->
          <tr id="appointments_ps_expand_table" style="display:none">
       		<td width='650px'>
            
            	
                
                
                
                 <br/>
            <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
            </div>
            </td>
            
          </tr>
<!-- Sai custom code end -->
<?php
// This generates a section similar to Vitals for each LBF form that
// supports charting.  The form ID is used as the "widget label".
//
$gfres = sqlStatement("SELECT grp_form_id AS option_id, grp_title AS title, grp_aco_spec " .
  "FROM layout_group_properties WHERE " .
  "grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_repeats > 0 AND grp_activity = 1 " .
  "ORDER BY grp_seq, grp_title");
while ($gfrow = sqlFetchArray($gfres)) {
    // $jobj = json_decode($gfrow['notes'], true);
    $LBF_ACO = empty($gfrow['grp_aco_spec']) ? false : explode('|', $gfrow['grp_aco_spec']);
    if ($LBF_ACO && !acl_check($LBF_ACO[0], $LBF_ACO[1])) {
        continue;
    } ?>
        <tr id="trend_ps_expand_table" style="display:none"> <!-- Sai custom code start -->
    <td width='650px'>
    <?php // vitals expand collapse widget
    $vitals_form_id = $gfrow['option_id'];
    $widgetTitle = $gfrow['title'];
    $widgetLabel = $vitals_form_id;
    $widgetButtonLabel = xl("Trend");
    $widgetButtonLink = "../encounter/trend_form.php?formname=$vitals_form_id";
    $widgetButtonClass = "";
    $linkMethod = "html";
    $bodyClass = "notab";
    $widgetAuth = false;
    if (!$LBF_ACO || acl_check($LBF_ACO[0], $LBF_ACO[1], '', 'write')) {
        // check to see if any instances exist for this patient
        $existVitals = sqlQuery(
            "SELECT * FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0",
            array($pid, $vitals_form_id)
        );
        $widgetAuth = $existVitals;
    }

    $fixedWidth = true;
    expand_collapse_widget(
        $widgetTitle,
        $widgetLabel,
        $widgetButtonLabel,
        $widgetButtonLink,
        $widgetButtonClass,
        $linkMethod,
        $bodyClass,
        $widgetAuth,
        $fixedWidth
    ); ?>
       <br/>
       <div style='margin-left:10px' class='text'>
        <image src='../../pic/ajax-loader.gif'/>
       </div>
       <br/>
      </div> <!-- This is required by expand_collapse_widget(). -->
     </td>
    </tr>
<?php
} // end while
?>

   </table>

  </div>
    <!-- end left column div -->

    <!-- start right column div -->
    <div>
    <table>
	<!--<tr id="other_ps_expand_table" style="display:none">--><!-- Sai custom code  -->
    <tr id="other_ps_expand_table" ><!-- Sai custom code  -->
    <td>

</div>	<!-- Sai custom code start -->
    <?php

    // If there is an ID Card or any Photos show the widget
    $photos = pic_array($pid, $GLOBALS['patient_photo_category_name']);
    if ($photos or $idcard_doc_id) {
        $widgetTitle = xl("ID Card") . '/' . xl("Photos");
        $widgetLabel = "photos";
        $linkMethod = "javascript";
        $bodyClass = "notab-right";
        $widgetAuth = false;
        $fixedWidth = false;
        expand_collapse_widget(
            $widgetTitle,
            $widgetLabel,
            $widgetButtonLabel,
            $widgetButtonLink,
            $widgetButtonClass,
            $linkMethod,
            $bodyClass,
            $widgetAuth,
            $fixedWidth
        );
?>
<br />
<?php
if ($idcard_doc_id) {
    image_widget($idcard_doc_id, $GLOBALS['patient_id_category_name']);
}

foreach ($photos as $photo_doc_id) {
    image_widget($photo_doc_id, $GLOBALS['patient_photo_category_name']);
}
    }
?>

<br />
</div>
<div>
    <?php
    // Advance Directives
    if ($GLOBALS['advance_directives_warning']) {
    // advance directives expand collapse widget
        $widgetTitle = xl("Advance Directives");
        $widgetLabel = "directives";
        $widgetButtonLabel = xl("Edit");
        $widgetButtonLink = "return advdirconfigure();";
        $widgetButtonClass = "";
        $linkMethod = "javascript";
        $bodyClass = "summary_item small";
        $widgetAuth = true;
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
          $counterFlag = false; //flag to record whether any categories contain ad records
          $query = "SELECT id FROM categories WHERE name='Advance Directive'";
          $myrow2 = sqlQuery($query);
        if ($myrow2) {
            $parentId = $myrow2['id'];
            $query = "SELECT id, name FROM categories WHERE parent=?";
            $resNew1 = sqlStatement($query, array($parentId));
            while ($myrows3 = sqlFetchArray($resNew1)) {
                $categoryId = $myrows3['id'];
                $nameDoc = $myrows3['name'];
                $query = "SELECT documents.date, documents.id " .
                   "FROM documents " .
                   "INNER JOIN categories_to_documents " .
                   "ON categories_to_documents.document_id=documents.id " .
                   "WHERE categories_to_documents.category_id=? " .
                   "AND documents.foreign_id=? " .
                   "ORDER BY documents.date DESC";
                $resNew2 = sqlStatement($query, array($categoryId, $pid));
                $limitCounter = 0; // limit to one entry per category
                while (($myrows4 = sqlFetchArray($resNew2)) && ($limitCounter == 0)) {
                    $dateTimeDoc = $myrows4['date'];
                // remove time from datetime stamp
                    $tempParse = explode(" ", $dateTimeDoc);
                    $dateDoc = $tempParse[0];
                    $idDoc = $myrows4['id'];
                    echo "<a href='$web_root/controller.php?document&retrieve&patient_id=" .
                    htmlspecialchars($pid, ENT_QUOTES) . "&document_id=" .
                    htmlspecialchars($idDoc, ENT_QUOTES) . "&as_file=true' onclick='top.restoreSession()'>" .
                    htmlspecialchars(xl_document_category($nameDoc), ENT_NOQUOTES) . "</a> " .
                    htmlspecialchars($dateDoc, ENT_NOQUOTES);
                    echo "<br>";
                    $limitCounter = $limitCounter + 1;
                    $counterFlag = true;
                }
            }
        }

        if (!$counterFlag) {
            echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'), ENT_NOQUOTES);
        } ?>
      </div>
    <?php
    }  // close advanced dir block

    // Show Clinical Reminders for any user that has rules that are permitted.
   /* $clin_rem_check = resolve_rules_sql('', '0', true, '', $_SESSION['authUser']);
    if (!empty($clin_rem_check) && $GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw'] &&
        acl_check('patients', 'alert')) {
        // clinical summary expand collapse widget
        $widgetTitle = xl("Clinical Reminders");
        $widgetLabel = "clinical_reminders";
        $widgetButtonLabel = xl("Edit");
        $widgetButtonLink = "../reminder/clinical_reminders.php?patient_id=".$pid;
        ;
        $widgetButtonClass = "";
        $linkMethod = "html";
        $bodyClass = "summary_item small";
        $widgetAuth = acl_check('patients', 'alert', '', 'write');
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        echo "<br/>";
        echo "<div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div><br/>";
        echo "</div>";
    } // end if crw*/

      // Show current and upcoming appointments.
      //
      // Recurring appointment support and Appointment Display Sets
      // added to Appointments by Ian Jardine ( epsdky ).
      //
    if (isset($pid) && !$GLOBALS['disable_calendar'] && acl_check('patients', 'appt')) {
      //
        $current_date2 = date('Y-m-d');
        $events = array();
        $apptNum = (int)$GLOBALS['number_of_appts_to_show'];
        if ($apptNum != 0) {
            $apptNum2 = abs($apptNum);
        } else {
            $apptNum2 = 10;
        }

        //
        $mode1 = !$GLOBALS['appt_display_sets_option'];
        $colorSet1 = $GLOBALS['appt_display_sets_color_1'];
        $colorSet2 = $GLOBALS['appt_display_sets_color_2'];
        $colorSet3 = $GLOBALS['appt_display_sets_color_3'];
        $colorSet4 = $GLOBALS['appt_display_sets_color_4'];
        //
        if ($mode1) {
            $extraAppts = 1;
        } else {
            $extraAppts = 6;
        }

        $events = fetchNextXAppts($current_date2, $pid, $apptNum2 + $extraAppts, true);
        //////
        if ($events) {
            $selectNum = 0;
            $apptNumber = count($events);
          //
            if ($apptNumber <= $apptNum2) {
                $extraApptDate = '';
                //
            } else if ($mode1 && $apptNumber == $apptNum2 + 1) {
                $extraApptDate = $events[$apptNumber - 1]['pc_eventDate'];
                array_pop($events);
                --$apptNumber;
                $selectNum = 1;
                //
            } else if ($apptNumber == $apptNum2 + 6) {
                $extraApptDate = $events[$apptNumber - 1]['pc_eventDate'];
                array_pop($events);
                --$apptNumber;
                $selectNum = 2;
                //
            } else { // mode 2 - $apptNum2 < $apptNumber < $apptNum2 + 6
                $extraApptDate = '';
                $selectNum = 2;
                //
            }

          //
            $limitApptIndx = $apptNum2 - 1;
            $limitApptDate = $events[$limitApptIndx]['pc_eventDate'];
          //
            switch ($selectNum) {
                //
                case 2:
                    $lastApptIndx = $apptNumber - 1;
                    $thisNumber = $lastApptIndx - $limitApptIndx;
                    for ($i = 1; $i <= $thisNumber; ++$i) {
                        if ($events[$limitApptIndx + $i]['pc_eventDate'] != $limitApptDate) {
                            $extraApptDate = $events[$limitApptIndx + $i]['pc_eventDate'];
                            $events = array_slice($events, 0, $limitApptIndx + $i);
                            break;
                        }
                    }

                    //
                case 1:
                    $firstApptIndx = 0;
                    for ($i = 1; $i <= $limitApptIndx; ++$i) {
                        if ($events[$limitApptIndx - $i]['pc_eventDate'] != $limitApptDate) {
                            $firstApptIndx = $apptNum2 - $i;
                            break;
                        }
                    }

                    //
            }

          //
            if ($extraApptDate) {
                if ($extraApptDate != $limitApptDate) {
                    $apptStyle2 = " style='background-color:" . attr($colorSet3) . ";'";
                } else {
                    $apptStyle2 = " style='background-color:" . attr($colorSet4) . ";'";
                }
            }
        }

        //////

        // appointments expand collapse widget
        $widgetTitle = xl("Appointments");
        $widgetLabel = "appointments";
        $widgetButtonLabel = xl("Add");
        $widgetButtonLink = "return newEvt();";
        $widgetButtonClass = "";
        $linkMethod = "javascript";
        $bodyClass = "summary_item small";
        $widgetAuth = $resNotNull // $resNotNull reflects state of query in fetchAppointments
        && (acl_check('patients', 'appt', '', 'write') || acl_check('patients', 'appt', '', 'addonly'));
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        $count = 0;
        //
        $toggleSet = true;
        $priorDate = "";
        $therapyGroupCategories = array();
        $query = sqlStatement("SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_cattype = 3 AND pc_active = 1");
        while ($result = sqlFetchArray($query)) {
            $therapyGroupCategories[] = $result['pc_catid'];
        }

        //
        foreach ($events as $row) { //////
            $count++;
            $dayname = date("l", strtotime($row['pc_eventDate'])); //////
            $dispampm = "am";
            $disphour = substr($row['pc_startTime'], 0, 2) + 0;
            $dispmin  = substr($row['pc_startTime'], 3, 2);
            if ($disphour >= 12) {
                $dispampm = "pm";
                if ($disphour > 12) {
                    $disphour -= 12;
                }
            }

            $etitle = xl('(Click to edit)');
            if ($row['pc_hometext'] != "") {
                $etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
            }

            //////
            if ($extraApptDate && $count > $firstApptIndx) {
                $apptStyle = $apptStyle2;
            } else {
                if ($row['pc_eventDate'] != $priorDate) {
                    $priorDate = $row['pc_eventDate'];
                    $toggleSet = !$toggleSet;
                }

                if ($toggleSet) {
                    $apptStyle = " style='background-color:" . attr($colorSet2) . ";'";
                } else {
                    $apptStyle = " style='background-color:" . attr($colorSet1) . ";'";
                }
            }

            //////
            echo "<div " . $apptStyle . ">";
            if (!in_array($row['pc_catid'], $therapyGroupCategories)) {
                echo "<a href='javascript:oldEvt(" . htmlspecialchars(preg_replace("/-/", "", $row['pc_eventDate']), ENT_QUOTES) . ', ' . htmlspecialchars($row['pc_eid'], ENT_QUOTES) . ")' title='" . htmlspecialchars($etitle, ENT_QUOTES) . "'>";
            } else {
                echo "<span title='" . htmlspecialchars($etitle, ENT_QUOTES) . "'>";
            }

            echo "<b>" . text(oeFormatShortDate($row['pc_eventDate'])) . ", ";
            echo text(sprintf("%02d", $disphour) .":$dispmin " . xl($dispampm) . " (" . xl($dayname))  . ")</b> ";
            if ($row['pc_recurrtype']) {
                echo "<img src='" . $GLOBALS['webroot'] . "/interface/main/calendar/modules/PostCalendar/pntemplates/default/images/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='".htmlspecialchars(xl("Repeating event"), ENT_QUOTES)."' alt='".htmlspecialchars(xl("Repeating event"), ENT_QUOTES)."'>";
            }

            echo "<span title='" . generate_display_field(array('data_type'=>'1','list_id'=>'apptstat'), $row['pc_apptstatus']) . "'>";
            echo "<br>" . xlt('Status') . "( " . htmlspecialchars($row['pc_apptstatus'], ENT_NOQUOTES) . " ) </span>";
            echo htmlspecialchars(xl_appt_category($row['pc_catname']), ENT_NOQUOTES) . "\n";
            if (in_array($row['pc_catid'], $therapyGroupCategories)) {
                echo "<br><span>" . xlt('Group name') .": " . text(getGroup($row['pc_gid'])['group_name']) . "</span>\n";
            }

            if ($row['pc_hometext']) {
                echo " <span style='color:green'> Com</span>";
            }

            echo "<br>" . htmlspecialchars($row['ufname'] . " " . $row['ulname'], ENT_NOQUOTES);
            echo !in_array($row['pc_catid'], $therapyGroupCategories) ? '</a>' : '<span>';
            echo "</div>\n";
            //////
        }

        if ($resNotNull) { //////
            if ($count < 1) {
                echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'), ENT_NOQUOTES);
            } else { //////
                if ($extraApptDate) {
                    echo "<div style='color:#0000cc;'><b>" . attr($extraApptDate) . " ( + ) </b></div>";
                } else {
                    echo "<div><hr></div>";
                }
            }

            echo "</div>";
        }
    } // End of Appointments.


    /* Widget that shows recurrences for appointments. */
    if (isset($pid) && !$GLOBALS['disable_calendar'] && $GLOBALS['appt_recurrences_widget'] &&
        acl_check('patients', 'appt')) {
         $widgetTitle = xl("Recurrent Appointments");
         $widgetLabel = "recurrent_appointments";
         $widgetButtonLabel = xl("Add");
         $widgetButtonLink = "return newEvt();";
         $widgetButtonClass = "";
         $linkMethod = "javascript";
         $bodyClass = "summary_item small";
         $widgetAuth = false;
         $fixedWidth = false;
         expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
         $count = 0;
         $toggleSet = true;
         $priorDate = "";

         //Fetch patient's recurrences. Function returns array with recurrence appointments' category, recurrence pattern (interpreted), and end date.
         $recurrences = fetchRecurrences($pid);
        if (empty($recurrences)) { //if there are no recurrent appointments:
            echo "<div>";
            echo "<span>" . "&nbsp;&nbsp;" . xlt('None') . "</span>";
            echo "</div></div>";
        } else {
            foreach ($recurrences as $row) {
                //checks if there are recurrences and if they are current (git didn't end yet)
                if (!recurrence_is_current($row['pc_endDate'])) {
                    continue;
                }

                echo "<div>";
                echo "<span>" . xlt('Appointment Category') . ": <b>" . xlt($row['pc_catname']) . "</b></span>";
                echo "<br>";
                echo "<span>" . xlt('Recurrence') . ': ' . text($row['pc_recurrspec']) . "</span>";
                echo "<br>";
                $red_text = ""; //if ends in a week, make font red
                if (ends_in_a_week($row['pc_endDate'])) {
                    $red_text = " style=\"color:red;\" ";
                }

                echo "<span" . $red_text . ">" . xlt('End Date') . ': ' . text(oeFormatShortDate($row['pc_endDate'])) . "</span>";
                echo "</div>";
            }

            echo "</div>";
        }
    }

     /* End of recurrence widget */

    // Show PAST appointments.
    // added by Terry Hill to allow reverse sorting of the appointments
    $direction = "ASC";
    if ($GLOBALS['num_past_appointments_to_show'] < 0) {
        $direction = "DESC";
        ($showpast = -1 * $GLOBALS['num_past_appointments_to_show']);
    } else {
        $showpast = $GLOBALS['num_past_appointments_to_show'];
    }

    if (isset($pid) && !$GLOBALS['disable_calendar'] && $showpast > 0 &&
      acl_check('patients', 'appt')) {
        $query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " .
        "e.pc_startTime, e.pc_hometext, u.fname, u.lname, u.mname, " .
        "c.pc_catname, e.pc_apptstatus " .
        "FROM openemr_postcalendar_events AS e, users AS u, " .
        "openemr_postcalendar_categories AS c WHERE " .
        "e.pc_pid = ? AND e.pc_eventDate < CURRENT_DATE AND " .
        "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " .
        "ORDER BY e.pc_eventDate $direction , e.pc_startTime DESC " .
        "LIMIT " . $showpast;

        $pres = sqlStatement($query, array($pid));

      // appointments expand collapse widget
        $widgetTitle = xl("Past Appointments");
        $widgetLabel = "past_appointments";
        $widgetButtonLabel = '';
        $widgetButtonLink = '';
        $widgetButtonClass = '';
        $linkMethod = "javascript";
        $bodyClass = "summary_item small";
        $widgetAuth = false; //no button
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        $count = 0;
        while ($row = sqlFetchArray($pres)) {
            $count++;
            $dayname = date("l", strtotime($row['pc_eventDate']));
            $dispampm = "am";
            $disphour = substr($row['pc_startTime'], 0, 2) + 0;
            $dispmin  = substr($row['pc_startTime'], 3, 2);
            if ($disphour >= 12) {
                $dispampm = "pm";
                if ($disphour > 12) {
                    $disphour -= 12;
                }
            }

            if ($row['pc_hometext'] != "") {
                $etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
            }

            echo "<a href='javascript:oldEvt(" . htmlspecialchars(preg_replace("/-/", "", $row['pc_eventDate']), ENT_QUOTES) . ', ' . htmlspecialchars($row['pc_eid'], ENT_QUOTES) . ")' title='" . htmlspecialchars($etitle, ENT_QUOTES) . "'>";
            echo "<b>" . htmlspecialchars(xl($dayname) . ", " . oeFormatShortDate($row['pc_eventDate']), ENT_NOQUOTES) . "</b> " . xlt("Status") .  "(";
            echo " " .  generate_display_field(array('data_type'=>'1','list_id'=>'apptstat'), $row['pc_apptstatus']) . ")<br>";   // can't use special char parser on this
            echo htmlspecialchars("$disphour:$dispmin ") . xl($dispampm) . " ";
            echo htmlspecialchars($row['fname'] . " " . $row['lname'], ENT_NOQUOTES) . "</a><br>\n";
        }

        if (isset($pres) && $res != null) {
            if ($count < 1) {
                echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'), ENT_NOQUOTES);
            }

            echo "</div>";
        }
    }

    // END of past appointments
?>
        </div>

        <!--<div id='stats_div'>
            <br/>
            <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
        </div>-->
    </td>
    </tr>

            <?php // TRACK ANYTHING -----

        // Determine if track_anything form is in use for this site.
            $tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE " .
                        "directory = 'track_anything' AND state = 1");
            $track_is_registered = $tmp['count'];
            if ($track_is_registered) {
                echo "<tr> <td>";
                // track_anything expand collapse widget
                $widgetTitle = xl("Tracks");
                $widgetLabel = "track_anything";
                $widgetButtonLabel = xl("Tracks");
                $widgetButtonLink = "../../forms/track_anything/create.php";
                $widgetButtonClass = "";
                $widgetAuth = "";  // don't show the button
                $linkMethod = "html";
                $bodyClass = "notab";
                // check to see if any tracks exist
                $spruch = "SELECT id " .
                "FROM forms " .
                "WHERE pid = ? " .
                "AND formdir = ? ";
                $existTracks = sqlQuery($spruch, array($pid, "track_anything"));

                $fixedWidth = false;
                expand_collapse_widget(
                    $widgetTitle,
                    $widgetLabel,
                    $widgetButtonLabel,
                    $widgetButtonLink,
                    $widgetButtonClass,
                    $linkMethod,
                    $bodyClass,
                    $widgetAuth,
                    $fixedWidth
                );
        ?>
      <br/>
      <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
      </div>
     </td>
    </tr><?php
            }  // end track_anything ?>
    </table>

</div><!-- Sai custom code  -->

  </td>
</div> <!-- end main content div --><!-- Sai custom code start -->
 </tr>
</table>

</div> <!-- end main content div -->

<script language='JavaScript'>
// Array of skip conditions for the checkSkipConditions() function.
var skipArray = [
<?php echo $condition_str; ?>
];
checkSkipConditions();
</script>

</body>
</html>
