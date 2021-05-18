<?php 
require_once("../globals.php");
 require_once($GLOBALS['fileroot']."/library/acl.inc");
 require_once($GLOBALS['fileroot']."/custom/code_types.inc.php");
 require_once($GLOBALS['fileroot']."/library/patient.inc");
 require_once($GLOBALS['fileroot']."/library/lists.inc");
 include_once("$srcdir/registry.inc");
?>
<script type="text/javascript" src="scripts/stmenu.js"></script>
<script type="text/javascript">
// Master values for current pid and encounter.
 var active_pid = 0;
 var active_encounter = 0;

 // Current selections in the top and bottom frames.
 var topName = '';
 var botName = '';
function loadFrame2(fname, frame, url) {
  var usage = fname.substring(3);
  if (active_pid == 0 && usage > '0') {
   alert('<?php xl('You must first select or add a patient.','e') ?>');
   return false;
  }
  if (active_encounter == 0 && usage > '1') {
   alert('<?php xl('You must first select or create an encounter.','e') ?>');
   return false;
  }  
 }
</script>
<script type="text/javascript">
stm_bm(["menu1c53",980,"","scripts/blank.gif",0,"","",1,0,250,0,1000,1,1,1,"","100%",0,0,1,2,"default","hand","",1,25],this);
stm_bp("p0",[0,4,0,0,0,0,0,0,100,"",-2,"",-2,50,0,0,"#999999","transparent","scripts/line1.gif",3,0,0,"#000000"]);
stm_ai("p0i0",[0,"Calender","","",-1,-1,0,"../main/main_info.php","RTop","","","","",0,0,0,"","",0,0,0,1,1,"#E6EFF9",1,"#E6EFF9",1,"","scripts/line.gif",3,3,0,0,"#E6EFF9","#000000","#FFFFFF","#78bc1c","9pt Verdana","9pt Verdana",0,0,"","scripts/left1.gif","","scripts/right1.gif",9,9,43],100,30);
stm_ai("p0i1",[6,2,"transparent","scripts/line2.gif",2,43,0]);
stm_aix("p0i2","p0i0",[0,"Messages","","",-1,-1,0,"messages/messages.php","RBot"],100,30);
stm_aix("p0i3","p0i1",[6,1]);
stm_aix("p0i4","p0i0",[0,"Patients/Clients","","",-1,-1,0,"#","_self"],100,30);
stm_bp("p1",[1,4,17,0,0,0,0,0,100,"progid:DXImageTransform.Microsoft.RandomDissolve(,enabled=0,Duration=0.30)",12,"",-2,80,0,0,"#999999","#F7F7F7","",3,0,0,"#CCCCCC"]);
stm_ai("p1i0",[0,"  New/Search  ","","",-1,-1,0,"../new/new.php","RTop","","","","",0,0,0,"","",0,0,0,0,1,"#E6EFF9",1,"#FFFFFF",0,"","",3,3,0,0,"#E6EFF9","#000000","#505050","#505050","8pt Verdana","8pt Verdana",0,0,"","","","",0,0,0],0,30);
stm_aix("p1i1","p0i1",[6,1,"#C1C5C9","",-1,-1]);
stm_aix("p1i2","p1i0",[0,"  Summary  ","","",-1,-1,0,"../patient_file/summary/demographics.php","RTop"],0,30);
stm_aix("p1i3","p1i1",[6,1,"#E2E2E2"]);
stm_aix("p1i4","p1i2",[0,"  Visits  "],0,30);
stm_bpx("p2","p1",[1,2,0]);
stm_aix("p2i0","p1i2",[0,"  Create Visit  ","","",-1,-1,0,"../forms/newpatient/new.php?autoloaded=1&calenc=","RBot"],100,30);
stm_aix("p2i1","p1i3",[]);
stm_aix("p2i2","p2i0",[0,"  Current  ","","",-1,-1,0,"../patient_file/encounter/encounter_top.php","RBot"],100,30);
stm_aix("p2i3","p1i3",[]);
stm_aix("p2i4","p2i0",[0,"  Visit History  ","","",-1,-1,0,"../patient_file/history/encounters.php","RBot"],100,30);
stm_ep();
stm_aix("p1i5","p1i3",[]);
stm_aix("p1i6","p1i2",[0,"  Records  "],0,30);
stm_bpx("p3","p2",[]);
stm_aix("p3i0","p2i0",[0,"  Patient Record Request  ","","",-1,-1,0,"../patient_file/transaction/record_request.php","RTop"],100,30);
stm_ep();
stm_aix("p1i7","p1i3",[]);
stm_aix("p1i8","p1i2",[0,"  Visit Forms  "],0,30);
stm_bpx("p4","p2",[]);
stm_aix("p4i0","p2i0",[0,"  Test  ","","",-1,-1,0,"../patient_file/encounter/load_form.php?formname=LBFTest","RBot"],200,30);
stm_aix("p4i1","p1i3",[]);
stm_aix("p4i2","p2i0",[0,"  Misc Billing Options For HCFA  ","","",-1,-1,0,"<?php 
$reg = getRegisteredCustomItem('1','1','0','Misc Billing Options HCFA');
if (!empty($reg)) {
    foreach ($reg as $entry) {
    $option_id = $entry['directory'];
	  $title = trim($entry['nickname']);
      if ($option_id == 'fee_sheet' ) continue;
      if ($option_id == 'newpatient') continue;
	  if (empty($title)) $title = $entry['name'];
      $link = '../patient_file/encounter/load_form.php?formname='.urlencode($option_id);
	  print($link);
   }
}
?>","RBot"],200,30);
stm_aix("p4i3","p1i3",[]);
stm_aix("p4i4","p2i0",[0,"  Procedure Order  ","","",-1,-1,0,"<?php 
$reg = getRegisteredCustomItem('1','1','0','Procedure Order');
if (!empty($reg)) {
    foreach ($reg as $entry) {
    $option_id = $entry['directory'];
	  $title = trim($entry['nickname']);
      if ($option_id == 'fee_sheet' ) continue;
      if ($option_id == 'newpatient') continue;
	  if (empty($title)) $title = $entry['name'];
      $link = '../patient_file/encounter/load_form.php?formname='.urlencode($option_id);
	  print($link);
   }
}
?>","RBot"],200,30);
stm_aix("p4i5","p1i3",[]);
stm_aix("p4i6","p2i0",[0,"  Review Of Systems  ","","",-1,-1,0,"<?php 

$reg = getRegisteredCustomItem('1','1','0','Review Of Systems');
if (!empty($reg)) {
    foreach ($reg as $entry) {
    $option_id = $entry['directory'];
	  $title = trim($entry['nickname']);
      if ($option_id == 'fee_sheet' ) continue;
      if ($option_id == 'newpatient') continue;
	  if (empty($title)) $title = $entry['name'];
      $link = '../patient_file/encounter/load_form.php?formname='.urlencode($option_id);
	  print($link);
   }
}
?>","RBot"],200,30);
stm_aix("p4i7","p1i3",[]);
stm_aix("p4i8","p2i0",[0,"  Review Of Systems Checks  ","","",-1,-1,0,"<?php 
$reg = getRegisteredCustomItem('1','1','0','Review of Systems Checks');
if (!empty($reg)) {
    foreach ($reg as $entry) {
    $option_id = $entry['directory'];
	  $title = trim($entry['nickname']);
      if ($option_id == 'fee_sheet' ) continue;
      if ($option_id == 'newpatient') continue;
	  if (empty($title)) $title = $entry['name'];
      $link = '../patient_file/encounter/load_form.php?formname='.urlencode($option_id);
	  print($link);
   }
}
?>","RBot"],200,30);
stm_aix("p4i9","p1i3",[]);
stm_aix("p4i10","p2i0",[0,"  SOAP  ","","",-1,-1,0,"<?php 
$reg = getRegisteredCustomItem('1','1','0','SOAP');
if (!empty($reg)) {
    foreach ($reg as $entry) {
    $option_id = $entry['directory'];
	  $title = trim($entry['nickname']);
      if ($option_id == 'fee_sheet' ) continue;
      if ($option_id == 'newpatient') continue;
	  if (empty($title)) $title = $entry['name'];
      $link = '../patient_file/encounter/load_form.php?formname='.urlencode($option_id);
	  print($link);
   }
}
?>","RBot"],200,30);
stm_aix("p4i11","p1i3",[]);
stm_aix("p4i12","p2i0",[0,"  Speech Dictation  ","","",-1,-1,0,"<?php 
$reg = getRegisteredCustomItem('1','1','0','Speech Dictation');
if (!empty($reg)) {
    foreach ($reg as $entry) {
    $option_id = $entry['directory'];
	  $title = trim($entry['nickname']);
      if ($option_id == 'fee_sheet' ) continue;
      if ($option_id == 'newpatient') continue;
	  if (empty($title)) $title = $entry['name'];
      $link = '../patient_file/encounter/load_form.php?formname='.urlencode($option_id);
	  print($link);
   }
}
?>","RBot"],200,30);
stm_aix("p4i13","p1i3",[]);
stm_aix("p4i14","p2i0",[0,"  Vitals  ","","",-1,-1,0,"<?php 
$reg = getRegisteredCustomItem('1','1','0','Vitals');
if (!empty($reg)) {
    foreach ($reg as $entry) {
    $option_id = $entry['directory'];
	  $title = trim($entry['nickname']);
      if ($option_id == 'fee_sheet' ) continue;
      if ($option_id == 'newpatient') continue;
	  if (empty($title)) $title = $entry['name'];
      $link = '../patient_file/encounter/load_form.php?formname='.urlencode($option_id);
	  print($link);
   }
}
?>","RBot"],200,30);
stm_ep();
stm_ep();
stm_aix("p0i5","p0i3",[]);
stm_aix("p0i6","p0i4",[0,"Fees"],80,30);
stm_bpx("p5","p1",[1,4,24]);
stm_aix("p5i0","p1i0",[0,"  Fee Sheet  ","","",-1,-1,0,"../patient_file/encounter/load_form.php?formname=fee_sheet","RBot"],0,30);
stm_aix("p5i1","p1i3",[]);
<?php if ($GLOBALS['use_charges_panel'])
{
?>
stm_aix("p5i2","p2i0",[0,"  Charges  ","","",-1,-1,0,"patient_file/encounter/encounter_bottom.php","RBot"],0,30);
stm_aix("p5i3","p1i3",[]);
<?php } ?>
stm_aix("p5i4","p5i0",[0,"  Checkout  ","","",-1,-1,0,"../patient_file/pos_checkout.php?framed=1","RBot"],0,30);
stm_aix("p5i5","p1i3",[]);
stm_aix("p5i6","p1i0",[0,"  Billing  ","","",-1,-1,0,"../billing/billing_report.php","RTop"],0,30);
stm_aix("p5i7","p1i3",[]);
stm_aix("p5i8","p5i6",[0,"  Payments  ","","",-1,-1,0,"../billing/new_payment.php","RTop"],0,30);
stm_ep();
stm_aix("p0i7","p0i3",[]);
stm_aix("p0i8","p0i4",[0,"Inventory"],90,30);
stm_bpx("p6","p1",[1,4,16]);
stm_aix("p6i0","p1i0",[0,"  Management  ","","",-1,-1,0,"../drugs/drug_inventory.php","RTop"],0,30);
stm_aix("p6i1","p1i3",[]);
stm_aix("p6i2","p1i0",[0,"  Destroyed  ","","",-1,-1,0,"<?php if ($GLOBALS['inhouse_pharmacy']) {?>javascript:window.open('<?php echo "$web_root/interface/reports/" ?>destroyed_drugs_report.php','_blank','width=750,height=550,resizable=1,scrollbars=1');<?php }?>","_self"],0,30);
stm_ep();
stm_aix("p0i9","p0i3",[]);
stm_aix("p0i10","p0i4",[0,"Procedures","","",-1,-1,0,""],100,30);
stm_bpx("p7","p1",[1,4,7]);
stm_aix("p7i0","p5i4",[0,"  Configuration  ","","",-1,-1,0,"../orders/types.php","RTop"],0,30);
stm_aix("p7i1","p1i3",[]);
stm_aix("p7i2","p5i4",[0,"  Pending Review  ","","",-1,-1,0,"javascript:loadFrame2('pen0','RTop','../orders/orders_results.php?review=1');","_self"],0,30);
stm_aix("p7i3","p1i3",[]);
stm_aix("p7i4","p5i4",[0,"  Patient Results  ","","",-1,-1,0,"../orders/orders_results.php","RTop"],0,30);
stm_aix("p7i5","p1i3",[]);
stm_aix("p7i6","p5i4",[0,"  Batch Results  ","","",-1,-1,0,"../orders/orders_results.php?batch=1","RTop"],0,30);
stm_ep();
stm_aix("p0i11","p0i3",[]);
stm_aix("p0i12","p0i10",[0,"Administration"],100,30);
stm_bpx("p8","p2",[1,4]);
stm_aix("p8i0","p5i4",[0,"  Globals  ","","",-1,-1,0,"../super/edit_globals.php","RTop"],0,30);
stm_aix("p8i1","p1i3",[]);
stm_aix("p8i2","p5i4",[0,"  Facilities  ","","",-1,-1,0,"../usergroup/facilities.php","RTop"],0,30);
stm_aix("p8i3","p1i3",[]);
stm_aix("p8i4","p5i4",[0,"  Users  ","","",-1,-1,0,"../usergroup/usergroup_admin.php","RTop"],0,30);
stm_aix("p8i5","p1i3",[]);
stm_aix("p8i6","p5i4",[0,"  Addr Book  ","","",-1,-1,0,"../usergroup/addrbook_list.php","RTop"],0,30);
stm_aix("p8i7","p1i3",[]);
stm_aix("p8i8","p5i4",[0,"  Practice  ","","",-1,-1,0,"../../controller.php?practice_settings","RTop"],0,30);
stm_aix("p8i9","p1i3",[]);
stm_aix("p8i10","p5i4",[0,"  Services  ","","",-1,-1,0,"../patient_file/encounter/superbill_custom_full.php","RTop"],0,30);
stm_aix("p8i11","p1i3",[]);
stm_aix("p8i12","p5i4",[0,"  Layouts  ","","",-1,-1,0,"../super/edit_layout.php","RTop"],0,30);
stm_aix("p8i13","p1i3",[]);
stm_aix("p8i14","p5i4",[0,"  Lists  ","","",-1,-1,0,"../super/edit_list.php","RTop"],0,30);
stm_aix("p8i15","p1i3",[]);
stm_aix("p8i16","p5i4",[0,"  ACL  ","","",-1,-1,0,"../usergroup/adminacl.php","RTop"],0,30);
stm_aix("p8i17","p1i3",[]);
stm_aix("p8i18","p5i4",[0,"  Files  ","","",-1,-1,0,"../super/manage_site_files.php","RTop"],0,30);
stm_aix("p8i19","p1i3",[]);
stm_aix("p8i20","p5i4",[0,"  Backup  ","","",-1,-1,0,"../main/backup.php","RTop"],0,30);
stm_aix("p8i21","p1i3",[]);
stm_aix("p8i22","p5i4",[0,"  Rules  ","","",-1,-1,0,"../super/rules/index.php?action=browse!list","RTop"],0,30);
stm_aix("p8i23","p1i3",[]);
stm_aix("p8i24","p5i4",[0,"  Alerts  ","","",-1,-1,0,"../super/rules/index.php?action=alerts!listactmgr","RTop"],0,30);
stm_aix("p8i25","p1i3",[]);
stm_aix("p8i26","p5i4",[0,"  Patient Reminders  ","","",-1,-1,0,"../patient_file/reminder/patient_reminders.php?mode=admin&patient_id=","RTop"],0,30);
stm_aix("p8i27","p1i3",[]);
stm_aix("p8i28","p2i0",[0,"  Other  "],0,30);
stm_bpx("p9","p2",[]);
stm_aix("p9i0","p5i4",[0,"  Language  ","","",-1,-1,0,"../language/language.php","RTop"],100,30);
stm_aix("p9i1","p1i3",[]);
stm_aix("p9i2","p5i4",[0,"  Forms  ","","",-1,-1,0,"../forms_admin/forms_admin.php","RTop"],100,30);
stm_aix("p9i3","p1i3",[]);
stm_aix("p9i4","p5i4",[0,"  Calendar  ","","",-1,-1,0,"../main/calendar/index.php?module=PostCalendar&type=admin&func=modifyconfig","RTop"],100,30);
stm_aix("p9i5","p1i3",[]);
stm_aix("p9i6","p5i4",[0,"  Logs  ","","",-1,-1,0,"../logview/logview.php","RTop"],100,30);
stm_aix("p9i7","p1i3",[]);
stm_aix("p9i8","p5i4",[0,"  Database  ","","",-1,-1,0,"../../phpmyadmin/index.php","RBot"],100,30);
stm_aix("p9i9","p1i3",[]);
stm_aix("p9i10","p5i4",[0,"  Certificates  ","","",-1,-1,0,"../usergroup/ssl_certificates_admin.php","RTop"],100,30);
stm_aix("p9i11","p1i3",[]);
stm_aix("p9i12","p5i4",[0,"  RxNorm  ","","",-1,-1,0,"../../interface/code_systems/standard_tables_manage.php?mode=rxnorm","RTop"],100,30);
stm_aix("p9i13","p1i3",[]);
stm_aix("p9i14","p5i4",[0,"  SNOMED  ","","",-1,-1,0,"../../interface/code_systems/standard_tables_manage.php?mode=snomed","RTop"],100,30);
stm_ep();
stm_ep();
stm_aix("p0i13","p0i3",[]);
stm_aix("p0i14","p0i10",[0,"Reports"],80,30);
stm_bpx("p10","p8",[]);
stm_aix("p10i0","p2i0",[0,"  Clients  "],120,30);
stm_bpx("p11","p2",[]);
stm_aix("p11i0","p5i4",[0,"  List  ","","",-1,-1,0,"../reports/patient_list.php","RTop"],160,30);
stm_aix("p11i1","p1i3",[]);
stm_aix("p11i2","p5i4",[0,"  Rx  ","","",-1,-1,0,"../reports/prescriptions_report.php","RTop"],160,30);
stm_aix("p11i3","p1i3",[]);
stm_aix("p11i4","p5i4",[0,"  Clinical  ","","",-1,-1,0,"../reports/clinical_reports.php","RTop"],160,30);
stm_aix("p11i5","p1i3",[]);
stm_aix("p11i6","p5i4",[0,"  Referrals  ","","",-1,-1,0,"../reports/referrals_report.php","RTop"],160,30);
stm_aix("p11i7","p1i3",[]);
stm_aix("p11i8","p5i4",[0,"  Immunization Registry  ","","",-1,-1,0,"../reports/immunization_report.php","RTop"],160,30);
stm_ep();
stm_aix("p10i1","p1i3",[]);
stm_aix("p10i2","p2i0",[0,"  Clinic  "],120,30);
stm_bpx("p12","p2",[]);
stm_aix("p12i0","p5i4",[0,"  Standard Measures  ","","",-1,-1,0,"../reports/cqm.php?type=standard","RTop"],160,30);
stm_aix("p12i1","p1i3",[]);
stm_aix("p12i2","p5i4",[0,"  Quality Measures (CQM)  ","","",-1,-1,0,"../reports/cqm.php?type=cqm","RTop"],160,30);
stm_aix("p12i3","p1i3",[]);
stm_aix("p12i4","p5i4",[0,"  Automated Measures  ","","",-1,-1,0,"../reports/cqm.php?type=amc","RTop"],160,30);
stm_aix("p12i5","p1i3",[]);
stm_aix("p12i6","p5i4",[0,"  AMC Tracking  ","","",-1,-1,0,"../reports/amc_tracking.php","RTop"],160,30);
stm_ep();
stm_aix("p10i3","p1i3",[]);
stm_aix("p10i4","p2i0",[0,"  Visits  "],120,30);
stm_bpx("p13","p2",[]);
stm_aix("p13i0","p5i4",[0,"  Appointments  ","","",-1,-1,0,"../reports/appointments_report.php","RTop"],160,30);
stm_aix("p13i1","p1i3",[]);
stm_aix("p13i2","p5i4",[0,"  Encounters  ","","",-1,-1,0,"../reports/encounters_report.php","RTop"],160,30);
stm_aix("p13i3","p1i3",[]);
stm_aix("p13i4","p5i4",[0,"  Appt-Enc  ","","",-1,-1,0,"../reports/appt_encounter_report.php","RTop"],160,30);
stm_aix("p13i5","p1i3",[]);
stm_aix("p13i6","p5i4",[0,"  SuperBill  ","","",-1,-1,0,"../reports/custom_report_range.php","RTop"],160,30);
stm_aix("p13i7","p1i3",[]);
stm_aix("p13i8","p5i4",[0,"  Eligibility  ","","",-1,-1,0,"../reports/edi_270.php","RTop"],160,30);
stm_aix("p13i9","p1i3",[]);
stm_aix("p13i10","p5i4",[0,"  Eligibility Response  ","","",-1,-1,0,"../reports/edi_271.php","RTop"],160,30);
stm_aix("p13i11","p1i3",[]);
stm_aix("p13i12","p5i4",[0,"  Chart Activity  ","","",-1,-1,0,"../reports/chart_location_activity.php","RTop"],160,30);
stm_aix("p13i13","p1i3",[]);
stm_aix("p13i14","p5i4",[0,"  Chart Out  ","","",-1,-1,0,"../reports/charts_checked_out.php","RTop"],160,30);
stm_aix("p13i15","p1i3",[]);
stm_aix("p13i16","p8i10",[0,"  Services  ","","",-1,-1,0,"../reports/services_by_category.php","RTop"],160,30);
stm_aix("p13i17","p1i3",[]);
stm_aix("p13i18","p5i4",[0,"  Syndromic Surveillance  ","","",-1,-1,0,"../reports/non_reported.php","RTop"],160,30);
stm_ep();
stm_aix("p10i5","p1i3",[]);
stm_aix("p10i6","p2i0",[0,"  Financial  "],120,30);
stm_bpx("p14","p2",[]);
stm_aix("p14i0","p5i4",[0,"  Sales  ","","",-1,-1,0,"../reports/sales_by_item.php","RTop"],100,30);
stm_aix("p14i1","p1i3",[]);
stm_aix("p14i2","p5i4",[0,"  Cash Rec  ","","",-1,-1,0,"../billing/sl_receipts_report.php","RTop"],100,30);
stm_aix("p14i3","p1i3",[]);
stm_aix("p14i4","p5i4",[0,"  Front Rec  ","","",-1,-1,0,"../reports/front_receipts_report.php","RTop"],100,30);
stm_aix("p14i5","p1i3",[]);
stm_aix("p14i6","p5i4",[0,"  Pmt Method  ","","",-1,-1,0,"../reports/receipts_by_method_report.php","RTop"],100,30);
stm_aix("p14i7","p1i3",[]);
stm_aix("p14i8","p5i4",[0,"  Collections  ","","",-1,-1,0,"../reports/collections_report.php","RTop"],100,30);
stm_ep();
stm_aix("p10i7","p1i3",[]);
stm_aix("p10i8","p2i0",[0,"  Inventory  "],120,30);
stm_bpx("p15","p2",[]);
stm_aix("p15i0","p11i0",[0,"  List  ","","",-1,-1,0,"../reports/inventory_list.php","RTop"],100,30);
stm_aix("p15i1","p1i3",[]);
stm_aix("p15i2","p5i4",[0,"  Activity  ","","",-1,-1,0,"../reports/inventory_activity.php","RTop"],100,30);
stm_aix("p15i3","p1i3",[]);
stm_aix("p15i4","p5i4",[0,"  Transactions  ","","",-1,-1,0,"../reports/inventory_transactions.php","RTop"],100,30);
stm_ep();
stm_aix("p10i9","p1i3",[]);
stm_aix("p10i10","p2i0",[0,"  Procedurs  "],120,30);
stm_bpx("p16","p2",[]);
stm_aix("p16i0","p6i2",[0,"  Pending Res  ","","",-1,-1,0,"javascript:window.open('../orders/pending_orders.php','_blank','width=750,height=550,resizable=1,scrollbars=1');","_self"],100,30);
stm_aix("p16i1","p1i3",[]);

<?php if (!empty($GLOBALS['code_types']['IPPF']))
{?>
stm_aix("p16i2","p2i0",[0,"  Pending F/U  ","","",-1,-1,0,"javascript:window.open('../orders/pending_followup.php','_blank','width=750,height=550,resizable=1,scrollbars=1');","_self"],100,30);
stm_aix("p16i3","p1i3",[]);
<?php } ?>
stm_aix("p16i4","p6i2",[0,"  Statistics  ","","",-1,-1,0,"javascript:window.open('../orders/procedure_stats.php','_blank','width=750,height=550,resizable=1,scrollbars=1');","_self"],100,30);
stm_ep();
stm_aix("p10i11","p1i3",[]);
stm_aix("p10i12","p2i0",[0,"  Insurance  "],120,30);
stm_bpx("p17","p2",[]);
stm_aix("p17i0","p5i4",[0,"  Distribution  ","","",-1,-1,0,"../reports/insurance_allocation_report.php","RTop"],100,30);
stm_aix("p17i1","p1i3",[]);
stm_aix("p17i2","p5i4",[0,"  Indigents  ","","",-1,-1,0,"../billing/indigent_patients_report.php","RTop"],100,30);
stm_aix("p17i3","p1i3",[]);
stm_aix("p17i4","p5i4",[0,"  Unique SP  ","","",-1,-1,0,"../reports/unique_seen_patients_report.php","RTop"],100,30);
stm_ep();
stm_aix("p10i13","p1i3",[]);
stm_aix("p10i14","p2i0",[0,"  Blank Forms  "],120,30);
stm_bpx("p18","p2",[]);
stm_aix("p18i0","p6i2",[0,"  Demographics  ","","",-1,-1,0,"javascript:window.open('../patient_file/summary/demographics_print.php','_blank','width=750,height=550,resizable=1,scrollbars=1');","_self"],100,30);
stm_aix("p18i1","p1i3",[]);
stm_aix("p18i2","p6i2",[0,"  Fee Sheet  ","","",-1,-1,0,"javascript:window.open('../patient_file/printed_fee_sheet.php','_blank','width=750,height=550,resizable=1,scrollbars=1');","_self"],100,30);
stm_aix("p18i3","p1i3",[]);
stm_aix("p18i4","p6i2",[0,"  Referral  ","","",-1,-1,0,"javascript:window.open('../patient_file/transaction/print_referral.php','_blank','width=750,height=550,resizable=1,scrollbars=1');","_self"],100,30);
stm_aix("p18i5","p1i3",[]);
stm_aix("p18i6","p6i2",[0,"  Test  ","","",-1,-1,0,"javascript:window.open('<?php
  $lres = sqlStatement("SELECT * FROM list_options " .
  "WHERE list_id = 'lbfnames' ORDER BY seq, title");
  while ($lrow = sqlFetchArray($lres)) {
    $option_id = $lrow['option_id']; 
    $title = $lrow['title'];
    $link = '../forms/LBF/printable.php?formname='.urlencode($option_id);
	print($link);
  }
?>','_blank','width=750,height=550,resizable=1,scrollbars=1');","_self"],100,30);
stm_ep();
stm_ep();
stm_aix("p0i15","p0i3",[]);
stm_aix("p0i16","p0i10",[0,"Miscellaneous"],100,30);
stm_bpx("p19","p1",[1,4,3]);
stm_aix("p19i0","p5i4",[0,"  Patient Education  ","","",-1,-1,0,"../reports/patient_edu_web_lookup.php","RTop"],0,30);
stm_aix("p19i1","p1i3",[]);
stm_aix("p19i2","p5i4",[0,"  Authorizations  ","","",-1,-1,0,"../main/authorizations/authorizations.php","RBot"],0,30);
stm_aix("p19i3","p1i3",[]);
stm_aix("p19i4","p8i6",[0,"  Addr Book  ","","",-1,-1,0,"../usergroup/addrbook_list.php","RTop"],0,30);
stm_aix("p19i5","p1i3",[]);
stm_aix("p19i6","p5i4",[0,"  Order Catalog  ","","",-1,-1,0,"../orders/types.php","RTop"],0,30);
stm_aix("p19i7","p1i3",[]);
stm_aix("p19i8","p5i4",[0,"  Chart Tracker  ","","",-1,-1,0,"../../custom/chart_tracker.php","RTop"],0,30);
stm_aix("p19i9","p1i3",[]);
stm_aix("p19i10","p5i4",[0,"  Ofc Notes  ","","",-1,-1,0,"../main/onotes/office_comments.php","RTop"],0,30);
stm_aix("p19i11","p1i3",[]);
stm_aix("p19i12","p5i4",[0,"  BatchCom  ","","",-1,-1,0,"../batchcom/batchcom.php","RTop"],0,30);
stm_aix("p19i13","p1i3",[]);
stm_aix("p19i14","p5i4",[0,"  Password  ","","",-1,-1,0,"../usergroup/user_info.php","RTop"],0,30);
stm_aix("p19i15","p1i3",[]);
stm_aix("p19i16","p5i4",[0,"  Preferences  ","","",-1,-1,0,"../super/edit_globals.php?mode=user","RTop"],0,30);
stm_ep();
stm_aix("p0i17","p0i3",[]);
stm_aix("p0i18","p0i10",[0,"Support"],100,30);
stm_bpx("p20","p1",[1,4,10]);
stm_aix("p20i0","p6i2",[0,"  Manual  ","","",-1,-1,0,"http://open-emr.org/wiki/index.php/OpenEMR_4.1_Users_Guide","_blank"],0,30);
stm_aix("p20i1","p1i3",[]);
stm_aix("p20i2","p6i2",[0,"  Online Support  ","","",-1,-1,0,"http://open-emr.org/","_blank"],0,30);
stm_ep();
stm_ep();
stm_cf([0,0,0,"RTop","Title",0]);
stm_em();
</script>
