<?php
/**
 * Encounter list.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once("../../../custom/code_types.inc.php");
if ($GLOBALS['enable_group_therapy']) {
    require_once("$srcdir/group.inc");
}

$is_group = ($attendant_type == 'gid') ? true : false;

if ($is_group && !acl_check("groups", "glog", false, array('view','write'))) {
    echo xlt("access not allowed");
    exit();
}


// "issue" parameter exists if we are being invoked by clicking an issue title
// in the left_nav menu.  Currently that is just for athletic teams.  In this
// case we only display encounters that are linked to the specified issue.
$issue = empty($_GET['issue']) ? 0 : 0 + $_GET['issue'];

$claim_type = empty($_GET['claim_type']) ? 'All' : $_GET['claim_type']; // Sai custom code

 //maximum number of encounter entries to display on this page:
 // $N = 12;

 //Get the default encounter from Globals
 $default_encounter = $GLOBALS['default_encounter_view']; //'0'=clinical, '1' = billing

 // Get relevant ACL info.
 $auth_notes_a  = acl_check('encounters', 'notes_a');
 $auth_notes    = acl_check('encounters', 'notes');
 $auth_coding_a = acl_check('encounters', 'coding_a');
 $auth_coding   = acl_check('encounters', 'coding');
 $auth_relaxed  = acl_check('encounters', 'relaxed');
 $auth_med      = acl_check('patients', 'med');
 $auth_demo     = acl_check('patients', 'demo');
 // Sai custom code start
 if(isset($_GET['set_pid'])){
 
 	$pid=$_GET['set_pid'];
 
 	$patient_data = sqlQuery("select CONCAT(fname,' ',lname) as patient_name,pid,pubpid,stop_stmt from patient_data where pid =$pid");
	
	$patient_name = $patient_data['patient_name'];
	$patient_id = $patient_data['pid'];
	$patient_extid = $patient_data['pubpid'];
	
	
 }
 
// code added for BUG ID 11240 Self Pay Validation 
$patient_self = sqlQuery("select stop_stmt from patient_data where pid =$pid"); 
$self_pay = $patient_self['stop_stmt'];
$self_payArray = explode("|",$self_pay);
$self_pay = $self_payArray[0];
///////////////////////////////////////////////////////
 // Sai custom code end

 $tmp = getPatientData($pid, "squad");
if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
    $auth_notes_a = $auth_notes = $auth_coding_a = $auth_coding = $auth_med = $auth_demo = $auth_relaxed = 0;
}

if (!($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
    echo "<body>\n<html>\n";
    echo "<p>(".htmlspecialchars(xl('Encounters not authorized'), ENT_NOQUOTES).")</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

// Perhaps the view choice should be saved as a session variable.
//
$tmp = sqlQuery("select authorized from users " .
  "where id = ?", array($_SESSION['authUserID']));
$billing_view = ($tmp['authorized']) ? 0 : 1;

if (isset($_GET['billing'])) {
    $billing_view = empty($_GET['billing']) ? 0 : 1;
} else {
    $billing_view = ($default_encounter == 0) ? 0 : 1;
}

//Get Document List by Encounter ID
function getDocListByEncID($encounter, $raw_encounter_date, $pid)
{
    global $ISSUE_TYPES, $auth_med;

    $documents = getDocumentsByEncounter($pid, $encounter);
    if (!empty($documents) && count($documents) > 0) {
        foreach ($documents as $documentrow) {
            if ($auth_med) {
                $irow = sqlQuery("SELECT type, title, begdate FROM lists WHERE id = ? LIMIT 1", array($documentrow['list_id']));
                if ($irow) {
                    $tcode = $irow['type'];
                    if ($ISSUE_TYPES[$tcode]) {
                        $tcode = $ISSUE_TYPES[$tcode][2];
                    }
                    echo text("$tcode: " . $irow['title']);
                }
            } else {
                echo "(" . xlt('No access') . ")";
            }

            // Get the notes for this document and display as title for the link.
            $queryString = "SELECT date,note FROM notes WHERE foreign_id = ? ORDER BY date";
            $noteResultSet = sqlStatement($queryString, array($documentrow['id']));
            $note = '';
            while ($row = sqlFetchArray($noteResultSet)) {
                $note .= attr(oeFormatShortDate(date('Y-m-d', strtotime($row['date'])))) . " : " . attr($row['note']) . "\n";
            }
            $docTitle = ( $note ) ? $note : xla("View document");

            $docHref = $GLOBALS['webroot']."/controller.php?document&view&patient_id=".attr($pid)."&doc_id=".attr($documentrow['id']);
            echo "<div class='text docrow' id='" . attr($documentrow['id'])."' title='". $docTitle . "'>\n";
            echo "<a href='$docHref' onclick='top.restoreSession()' >". xlt('Document') . ": " . text(basename($documentrow['url'])) . ' (' . text(xl_document_category($documentrow['name'])) . ')' . "</a>";
            echo "</div>";
        }
    }
}

// This is called to generate a line of output for a patient document.
//
function showDocument(&$drow)
{
    global $ISSUE_TYPES, $auth_med;

    $docdate = $drow['docdate'];

    // if doc is already tagged by encounter it already has its own row so return
    $doc_tagged_enc = $drow['encounter_id'];
    if ($doc_tagged_enc) {
        return;
    }

    echo "<tr class='text docrow' id='".htmlspecialchars($drow['id'], ENT_QUOTES)."' title='". htmlspecialchars(xl('View document'), ENT_QUOTES) . "'>\n";

  // show date
    echo "<td>" . htmlspecialchars(oeFormatShortDate($docdate), ENT_NOQUOTES) . "</td>\n";

  // show associated issue, if any
    echo "<td>";
    if ($auth_med) {
        $irow = sqlQuery("SELECT type, title, begdate " .
        "FROM lists WHERE " .
        "id = ? " .
        "LIMIT 1", array($drow['list_id']));
        if ($irow) {
              $tcode = $irow['type'];
            if ($ISSUE_TYPES[$tcode]) {
                $tcode = $ISSUE_TYPES[$tcode][2];
            }
              echo htmlspecialchars("$tcode: " . $irow['title'], ENT_NOQUOTES);
        }
    } else {
        echo "(" . htmlspecialchars(xl('No access'), ENT_NOQUOTES) . ")";
    }
    echo "</td>\n";

  // show document name and category
    echo "<td colspan='3'>".
    htmlspecialchars(xl('Document') . ": " . basename($drow['url']) . ' (' . xl_document_category($drow['name']) . ')', ENT_NOQUOTES) .
    "</td>\n";
    echo "<td colspan=5>&nbsp;</td>\n";
    echo "</tr>\n";
}

function generatePageElement($start, $pagesize, $billing, $issue, $text)
{
    if ($start<0) {
        $start = 0;
    }
    $url="encounters.php?"."pagestart=".attr($start)."&"."pagesize=".attr($pagesize)."&claim_type=".$claim_type; // Sai custom code
    $url.="&billing=".$billing;
    $url.="&issue=".$issue;

    echo "<A HREF='".$url."' onclick='top.restoreSession()'>".$text."</A>";
}
?>
<html>
<head>
<?php html_header_show();?>
<!-- Main style sheet comes after the page-specific stylesheet to facilitate overrides. -->
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/encounters.css" type="text/css">
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-2-2/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajtooltip.js"></script>

<script language="JavaScript">

//function toencounter(enc, datestr) {
function toencounter(rawdata) {
    var parts = rawdata.split("~");
    var enc = parts[0];
    var datestr = parts[1];

    top.restoreSession();
    parent.left_nav.setEncounter(datestr, enc, window.name);
    parent.left_nav.loadFrame('enc2', window.name, 'patient_file/encounter/encounter_top.php?set_encounter=' + enc);
}

function todocument(docid) {
  h = '<?php echo $GLOBALS['webroot'] ?>/controller.php?document&view&patient_id=<?php echo $pid ?>&doc_id=' + docid;
  top.restoreSession();
  location.href = h;
}

 // Helper function to set the contents of a div.
function setDivContent(id, content) {
    $("#"+id).html(content);
}

 // Called when clicking on a billing note.
function editNote(feid) {
  top.restoreSession();
  var c = "<iframe src='edit_billnote.php?feid=" + feid +
    "' style='width:100%;height:88pt;'></iframe>";
  setDivContent('note_' + feid, c);
}
// sai custom code
function popupUploadForm(feid){

        var newWindow = window.open('edit_billnote.php?feid='+ feid, 'name', 'height=300,width=1000, scrollbars=1');
         //var newWindow = window.open('edit_billnote.php?feid='+ feid, 'name', '_blank', 'toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=400, height=400');
         //var newWindow =  window.open("edit_billnote.php", "_blank", "toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=400, height=400"); 
        
    }
 // Sai custom code end 
 // Called when the billing note editor closes.
 function closeNote(feid, fenote) {
    var c = "<div id='"+ feid +"' title='<?php echo htmlspecialchars(xl('Click to edit'), ENT_QUOTES); ?>' class='text billing_note_text'>" +
            fenote + "</div>";
    setDivContent('note_' + feid, c);
 }

function changePageSize()
{
    billing=$(this).attr("billing");
    pagestart=$(this).attr("pagestart");
    issue=$(this).attr("issue");
    pagesize=$(this).val();
    top.restoreSession();
    window.location.href="encounters.php?billing="+billing+"&issue="+issue+"&pagestart="+pagestart+"&pagesize="+pagesize;
}
window.onload=function()
{
    $("#selPagesize").change(changePageSize);
}
// Sai custom code start 
window.onkeydown = function(evt) 
{
	evt = evt || window.event;
    if (evt.keyCode == 78 && evt.altKey) 
	{
        top.window.parent.left_nav.loadFrame2('new0','RTop','new/new.php');
    }
	if (evt.keyCode == 110 && evt.altKey) 
	{
        top.window.parent.left_nav.loadFrame2('new0','RTop','new/new.php');
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
	else if (evt.keyCode == 105&& evt.altKey)
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
	else if (evt.keyCode == 77 && evt.altKey) 
	{	
		top.window.parent.left_nav.loadFrame2('cal0','RTop','main/main_info.php');
	}
	else if (evt.keyCode == 109 && evt.altKey) 
	{	
		top.window.parent.left_nav.loadFrame2('cal0','RTop','main/main_info.php');
	}
	else if (evt.keyCode == 76 && evt.altKey) 
	{	
		top.window.parent.left_nav.loadFrame2('ens1','RTop','patient_file/history/encounters.php');
	}
	else if (evt.keyCode == 108&& evt.altKey) 
	{	
		top.window.parent.left_nav.loadFrame2('ens1','RTop','patient_file/history/encounters.php');
	}	
	else if (evt.keyCode == 81 && evt.altKey) 
	{	
		top.window.parent.left_nav.loadFrame2('dem1','RTop','patient_file/summary/demographics.php');
	}	
	else if (evt.keyCode == 113 && evt.altKey) 
	{	
		top.window.parent.left_nav.loadFrame2('dem1','RTop','patient_file/summary/demographics.php');
	}
	else 
	{
		return true; // it's not a key we recognize, move on...
	}
	return false;
}
// Sai custom code end 
// Mouseover handler for encounter form names. Brings up a custom tooltip
// to display the form's contents.
function efmouseover(elem, ptid, encid, formname, formid) {
 ttMouseOver(elem, "encounters_ajax.php?ptid=" + ptid + "&encid=" + encid +
  "&formname=" + formname + "&formid=" + formid);
}

</script>

</head>

<body class="body_bottom">
<!-- Sai custom code start -->
<?php
 if(isset($_GET['set_pid'])){
		echo "<p ><h3 title='pid : $patient_id'>Patient :$patient_name</h3></p> ";
}
?>

<form name="claimStatus" action="encounters.php" method="get" >
<!-- Sai custom code end -->
<div id="encounters"> <!-- large outer DIV -->

<font class='title'>
<?php
if ($issue) {
    echo htmlspecialchars(xl('Past Encounters for'), ENT_NOQUOTES) . ' ';
    $tmp = sqlQuery("SELECT title FROM lists WHERE id = ?", array($issue));
    echo htmlspecialchars($tmp['title'], ENT_NOQUOTES);
} else {
    //There isn't documents for therapy group yet
    echo $attendant_type == 'pid' ? htmlspecialchars(xl('Past Encounters and Documents'), ENT_NOQUOTES) : htmlspecialchars(xl('Past Therapy Group Encounters'), ENT_NOQUOTES);
}
?>
</font>
&nbsp;&nbsp;
<?php
// Setup the GET string to append when switching between billing and clinical views.


$pagestart=0;
if (isset($_GET['pagesize'])) {
    $pagesize=$_GET['pagesize'];
} else {
    if (array_key_exists('encounter_page_size', $GLOBALS)) {
        $pagesize=$GLOBALS['encounter_page_size'];
    } else {
        $pagesize=0;
    }
}
if (isset($_GET['pagestart'])) {
    $pagestart=$_GET['pagestart'];
} else {
    $pagestart=0;
}
$getStringForPage="&pagesize=".attr($pagesize)."&pagestart=".attr($pagestart);

?>
<?php
if(isset($_GET['set_pid'])){

	
}
else{
 if ($billing_view) { ?>
	<a href='encounters.php?billing=0&issue=<?php echo $issue.$getStringForPage; ?>' onclick='top.restoreSession()' style="font-size:8pt; font-weight:bold">(<?php echo htmlspecialchars( xl('To Clinical View'), ENT_NOQUOTES); ?>)</a>
<?php } else { ?>
		<a href='encounters.php?billing=1&issue=<?php echo $issue.$getStringForPage; ?>' onclick='top.restoreSession()' style="font-size:8pt; font-weight:bold">(<?php echo htmlspecialchars( xl('To Billing View'), ENT_NOQUOTES); ?>)</a>
<?php } ?>
	<span style="font-size:12px">Claim Status : </span>

	<?php
	if ($billing_view)
	$billing_type=1;
	else
	$billing_type=0;
	?>
	<select name="all_claim_status" id="all_claim_status" onChange="submitClaimForm(this.value,<?php echo $billing_type; ?>)">
	    <option value="All"  <?php if($claim_type=="All") echo "selected";?> >All</option>
	    <option value="Open"  <?php if($claim_type=="Open") echo "selected";?> >Open</option>
	    <option value="Close"  <?php if($claim_type=="Close") echo "selected";?> >Close</option>
	    <option value="Excess"  <?php if($claim_type=="Excess") echo "selected";?> >Excess</option>
	</select>

<?php
}
?>
<?php
// code added for SWAP Reporting Tool
$swap = $_SESSION['login_by'];

if(empty($swap))
{
?>
<!-- Sai custom code end -->
<span style="float:right">
    <?php echo htmlspecialchars(xl('Results per page'), ENT_NOQUOTES); ?>:
    <select id="selPagesize" billing="<?php echo htmlspecialchars($billing_view, ENT_QUOTES); ?>" issue="<?php echo htmlspecialchars($issue, ENT_QUOTES); ?>" pagestart="<?php echo htmlspecialchars($pagestart, ENT_QUOTES); ?>" >
<?php
    $pagesizes=array(5,10,15,20,25,50,0);
for ($idx=0; $idx<count($pagesizes); $idx++) {
    echo "<OPTION value='" . $pagesizes[$idx] . "'";
    if ($pagesize==$pagesizes[$idx]) {
        echo " SELECTED='true'>";
    } else {
        echo ">";
    }
    if ($pagesizes[$idx]==0) {
        echo htmlspecialchars(xl('ALL'), ENT_NOQUOTES);
    } else {
        echo $pagesizes[$idx];
    }
    echo "</OPTION>";
}
?>
    </select>
</span>
<!-- Sai custom code start -->
<?php
} // end of mstr if

?>
<br>
<hr align="left" color="#000000" size="2">
<table border="1">
 <tr class='text' bgcolor="#79d0f4">
 <!-- Sai custom code end -->
  <th><?php echo htmlspecialchars(xl('Date'), ENT_NOQUOTES); ?></th>

<?php if ($billing_view) { ?>
  <th class='billing_note'><?php echo htmlspecialchars(xl('Billing Note'), ENT_NOQUOTES); ?></th>
<?php } else { ?>
<?php if ($attendant_type == 'pid' && !$issue) { // only for patient encounter and if listing for multiple issues?>
  <th><?php echo htmlspecialchars(xl('Issue'), ENT_NOQUOTES);       ?></th>
<?php } ?>
  <th><?php echo htmlspecialchars(xl('Reason/Form'), ENT_NOQUOTES); ?></th>
    <?php if ($attendant_type == 'pid') { ?>
  <th><?php echo htmlspecialchars(xl('Provider'), ENT_NOQUOTES);    ?></th>
    <?php } else { ?>
        <th><?php echo htmlspecialchars(xl('Counselors'), ENT_NOQUOTES);    ?></th>
    <?php } ?>
<?php } ?>

<?php if ($billing_view) { ?>
  <th><?php echo xl('Code', 'e'); ?></th>
  <th class='right'><?php echo htmlspecialchars(xl('Chg'), ENT_NOQUOTES); ?></th>
  <th class='right'><?php echo htmlspecialchars(xl('Paid'), ENT_NOQUOTES); ?></th>
  <th class='right'><?php echo htmlspecialchars(xl('Adj'), ENT_NOQUOTES); ?></th>
  <th class='right'><?php echo htmlspecialchars( xl('W/O'), ENT_NOQUOTES); ?></th>   <!-- Sai custom code  -->
  <th class='right'><?php echo htmlspecialchars(xl('Bal'), ENT_NOQUOTES); ?></th>
   <th class='right'><?php echo htmlspecialchars( xl('Co-Ins'), ENT_NOQUOTES); ?></th><!-- Sai custom code  -->
    <th class='right'><?php echo htmlspecialchars( xl('Interest'), ENT_NOQUOTES); ?></th><!-- Sai custom code  -->
<?php } elseif ($attendant_type == 'pid') { ?>
  <th colspan=''><?php echo htmlspecialchars((($GLOBALS['phone_country_code'] == '1') ? xl('Billing') : xl('Coding')), ENT_NOQUOTES); ?></th>
<?php } ?>

<?php if ($attendant_type == 'pid' && !$GLOBALS['ippf_specific']) { ?>
  <th>&nbsp;<?php echo htmlspecialchars((($GLOBALS['weight_loss_clinic']) ? xl('Payment') : xl('Insurance')), ENT_NOQUOTES); ?></th>
<?php } ?>

<?php if ($GLOBALS['enable_group_therapy'] && !$billing_view && $therapy_group == 0) { ?>
    <!-- Two new columns if therapy group is enable only in patient  encounter - encounter type and group name (empty if isn't group type) -->
    <th><?php echo htmlspecialchars(xl('Encounter type'), ENT_NOQUOTES);    ?></th>
    <th><?php echo htmlspecialchars(xl('Group name'), ENT_NOQUOTES);    ?></th>
<?php }?>

<?php if ($billing_view) { ?> <!-- Sai custom code start -->
<th><?php echo xl('EOB','e'); ?></th> <?php } ?><!-- Sai custom code start -->
 </tr>

<?php
$drow = false;
if (!$billing_view) {
  // Query the documents for this patient.  If this list is issue-specific
  // then also limit the query to documents that are linked to the issue.
    $queryarr = array($pid);
    $query = "SELECT d.id, d.type, d.url, d.docdate, d.list_id, d.encounter_id, c.name " .
    "FROM documents AS d, categories_to_documents AS cd, categories AS c WHERE " .
    "d.foreign_id = ? AND cd.document_id = d.id AND c.id = cd.category_id ";
    if ($issue) {
        $query .= "AND d.list_id = ? ";
        $queryarr[] = $issue;
    }
    $query .= "ORDER BY d.docdate DESC, d.id DESC";
    $dres = sqlStatement($query, $queryarr);
    $drow = sqlFetchArray($dres);
}

// $count = 0;

$sqlBindArray = array();
// Sai custom code start 
$from1 = "FROM form_encounter AS fe " .
  "JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND " .
  "f.formdir like '%newpatient%' AND f.deleted = 0 ";
if ($issue) {
  $from1 .= "JOIN issue_encounter AS ie ON ie.pid = ? AND " .
    "ie.list_id = ? AND ie.encounter = fe.encounter ";
  array_push($sqlBindArray, $pid, $issue);
}
$from1 .= "LEFT JOIN users AS u ON u.id = fe.provider_id WHERE fe.pid = ? ";
$sqlBindArray[] = $pid;

$query = "SELECT fe.*, f.user, u.fname, u.mname, u.lname " . $from1 .  
        "ORDER BY fe.date DESC, fe.id DESC";
		
		$encounter_arr = array();
		$encounter="";
		$res8 = sqlStatement($query, $sqlBindArray);
		$from2 ="";
		while ($result8 = sqlFetchArray($res8)) {
		$encounter = get_invoice_encounter_list_status($pid,$result8['encounter'],$claim_type,true);		
		if($encounter){
		$encounter_arr[] = $encounter;
		if(strlen($from2) ==0 )
			$from2 .= " AND fe.encounter = $encounter "; 
		else
			$from2 .= " OR fe.encounter = $encounter "; 
			}
		}


$from = "FROM form_encounter AS fe " .
  "JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND " .
  "f.formdir like '%newpatient%' AND f.deleted = 0 "; // Sai custom code start 
if ($issue) {
  $from .= "JOIN issue_encounter AS ie ON ie.pid = ? AND " .
    "ie.list_id = ? AND ie.encounter = fe.encounter ";
  array_push($sqlBindArray, $pid, $issue);
}
$from .= "LEFT JOIN users AS u ON u.id = fe.provider_id WHERE fe.pid = ? ";

// code added for SWAP Reporting Tool getting specific encounter

if(isset($_GET['set_encounter'])){
 
 	$encounter_id=$_GET['set_encounter'];
	
	$from .= " AND fe.encounter=$encounter_id ";
	
}

//if(count($encounter_arr)>0){
//$encounter_str = implode(",",$encounter_arr);
//array_push($sqlBindArray, $encounter_str);
//$from .= " AND f.encounter in ( ? ) ";
if($claim_type !="All"){
if(strlen($from2) == 0 || strlen($from2) == '' )
$from .= " AND fe.encounter = '' ";
else
$from .= $from2;
}


//$sqlBindArray[] = $pid;

// Sai custom code end 
$query = "SELECT fe.*, f.user, u.fname, u.mname, u.lname " . $from .
        "ORDER BY fe.date DESC, fe.id DESC";

$countQuery = "SELECT COUNT(*) as c " . $from;

$countRes = sqlStatement($countQuery, $sqlBindArray);
$count = sqlFetchArray($countRes);
$numRes = $count['c'];


if ($pagesize>0) {
    $query .= " LIMIT " . escape_limit($pagestart) . "," . escape_limit($pagesize);
}
$upper  = $pagestart+$pagesize;
if (($upper>$numRes) || ($pagesize==0)) {
    $upper=$numRes;
}


if (($pagesize > 0) && ($pagestart>0)) {
    generatePageElement($pagestart-$pagesize, $pagesize, $billing_view, $issue, "&lArr;" . htmlspecialchars(xl("Prev"), ENT_NOQUOTES) . " ");
}
echo ($pagestart + 1)."-".$upper." " . htmlspecialchars(xl('of'), ENT_NOQUOTES) . " " .$numRes;
if (($pagesize>0) && ($pagestart+$pagesize <= $numRes)) {
    generatePageElement($pagestart+$pagesize, $pagesize, $billing_view, $issue, " " . htmlspecialchars(xl("Next"), ENT_NOQUOTES) . "&rArr;");
}


$res4 = sqlStatement($query, $sqlBindArray);


while ($result4 = sqlFetchArray($res4)) {
        // $href = "javascript:window.toencounter(" . $result4['encounter'] . ")";
        $reason_string = "";
        $auth_sensitivity = true;

        $raw_encounter_date = '';

        $raw_encounter_date = date("Y-m-d", strtotime($result4{"date"}));
        $encounter_date = date("D F jS", strtotime($result4{"date"}));

        //fetch acl for given pc_catid
        $postCalendarCategoryACO = fetchPostCalendarCategoryACO($result4['pc_catid']);
    if ($postCalendarCategoryACO) {
        $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
        $authPostCalendarCategory = acl_check($postCalendarCategoryACO[0], $postCalendarCategoryACO[1]);
    } else { // if no aco is set for category
        $authPostCalendarCategory = true;
    }

        // if ($auth_notes_a || ($auth_notes && $result4['user'] == $_SESSION['authUser']))
        $reason_string .= htmlspecialchars($result4{"reason"}, ENT_NOQUOTES) . "<br>\n";
        // else
        //   $reason_string = "(No access)";

    if ($result4['sensitivity']) {
        $auth_sensitivity = acl_check('sensitivities', $result4['sensitivity']);
        if (!$auth_sensitivity || !$authPostCalendarCategory) {
            $reason_string = "(".htmlspecialchars(xl("No access"), ENT_NOQUOTES).")";
        }
    }

        // This generates document lines as appropriate for the date order.
    while ($drow && $raw_encounter_date && $drow['docdate'] > $raw_encounter_date) {
        showDocument($drow);
        $drow = sqlFetchArray($dres);
    }

        // Fetch all forms for this encounter, if the user is authorized to see
        // this encounter's notes and this is the clinical view.
        $encarr = array();
        $encounter_rows = 1;
    if (!$billing_view && $auth_sensitivity && $authPostCalendarCategory &&
            ($auth_notes_a || ($auth_notes && $result4['user'] == $_SESSION['authUser']))) {
        $attendant_id = $attendant_type == 'pid' ? $pid : $therapy_group;
        $encarr = getFormByEncounter($attendant_id, $result4['encounter'], "formdir, user, form_name, form_id, deleted");
        $encounter_rows = count($encarr);
    }

        $rawdata = $result4['encounter'] . "~" . oeFormatShortDate($raw_encounter_date);
        echo "<tr class='encrow text' id='" . htmlspecialchars($rawdata, ENT_QUOTES) .
          "'>\n";

        // show encounter date
        echo "<td valign='top' title='" . htmlspecialchars(xl('View encounter', '', '', ' ') .
          "$pid.{$result4['encounter']}", ENT_QUOTES) . "'>" .
          htmlspecialchars(oeFormatShortDate($raw_encounter_date), ENT_NOQUOTES) . "</td>\n";

    if ($billing_view) {
        // Show billing note that you can click on to edit.
        $feid = $result4['id'] ? htmlspecialchars($result4['id'], ENT_QUOTES) : 0; // form_encounter id
        echo "<td valign='top'>";
        echo "<div id='note_$feid'>";
        //echo "<div onclick='editNote($feid)' title='Click to edit' class='text billing_note_text'>";
        echo "<div id='$feid' title='". htmlspecialchars(xl('Click to edit'), ENT_QUOTES) . "' class='text billing_note_text' onclick='popupUploadForm()'>";
       // echo $result4['billing_note'] ? nl2br(htmlspecialchars($result4['billing_note'], ENT_NOQUOTES)) : htmlspecialchars(xl('Add', '', '[', ']'), ENT_NOQUOTES);
       /* if($result4['billing_note']){
            echo nl2br(htmlspecialchars($result4['billing_note'], ENT_NOQUOTES));
        }*/

		// Sai custom code start -->
			// code added for BUG 10686 Billing note feature
			//$note_result= sqlStatement("select id from billing_notes where encounter=$feid limit 1");
			
			$query="select id from billing_notes where encounter=$feid limit 1";
			
			$note_result = sqlStatement($query);
 			$note_row = sqlFetchArray($note_result);
			$note_id=$note_row['id'];
			
			if(empty($note_id)){
				
           		echo  htmlspecialchars( xl('ADD','','[',']'), ENT_NOQUOTES);
			}
			else{
				echo  htmlspecialchars( xl('EDIT','','[',']'), ENT_NOQUOTES);
			
			}
        echo "</div>";
        echo "</div>";
			// code added for statement log BUG ID 10485 patient statement phase II start
			
			
			$encounter_id =$result4['encounter'];
			$log_result= sqlStatement("select date,user,filename from statement_log where encounter_id=$encounter_id order by date desc");
			
			while($log_row =sqlFetchArray($log_result)){
					$log_date = $log_row['date'];
					
					$date1 = strtotime($log_row['date']);
					$date2 = date("m/d/Y h:i:s A", $date1);
					
					$log_user = $log_row['user'];
					$filename = $log_row['filename'];
					$statement_id =  substr($filename, 0, -3);
					$pdf_path =$statement_path.$filename;
					
					//echo "Patient Statement Printed on $log_date by ($log_user) Statement <a href='$pdf_path' target='_blank'>(".$statement_id.")</a> <br>";
					echo "Patient Statement Printed on $date2 by ($log_user) <br>";
					echo "<hr>";
			}
			
			// code added for statement log BUG ID 10485 patient statement phase II end
		// Sai custom code end -->
        echo "</td>\n";

        //  *************** end billing view *********************
    } else {
        if ($attendant_type == 'pid' && !$issue) { // only for patient encounter and if listing for multiple issues
            // show issues for this encounter
            echo "<td>";
            if ($auth_med && $auth_sensitivity && $authPostCalendarCategory) {
                $ires = sqlStatement("SELECT lists.type, lists.title, lists.begdate " .
                                    "FROM issue_encounter, lists WHERE " .
                                    "issue_encounter.pid = ? AND " .
                                    "issue_encounter.encounter = ? AND " .
                                    "lists.id = issue_encounter.list_id " .
                                    "ORDER BY lists.type, lists.begdate", array($pid,$result4['encounter']));
                for ($i = 0; $irow = sqlFetchArray($ires); ++$i) {
                    if ($i > 0) {
                        echo "<br>";
                    }
                    $tcode = $irow['type'];
                    if ($ISSUE_TYPES[$tcode]) {
                        $tcode = $ISSUE_TYPES[$tcode][2];
                    }
                        echo htmlspecialchars("$tcode: " . $irow['title'], ENT_NOQUOTES);
                }
            } else {
                echo "(" . htmlspecialchars(xl('No access'), ENT_NOQUOTES) . ")";
            }
            echo "</td>\n";
        } // end if (!$issue)

        // show encounter reason/title
        echo "<td>".$reason_string;

        //Display the documents tagged to this encounter
        getDocListByEncID($result4['encounter'], $raw_encounter_date, $pid);

        echo "<div style='padding-left:10px;'>";

        // Now show a line for each encounter form, if the user is authorized to
        // see this encounter's notes.

        foreach ($encarr as $enc) {
            if ($enc['formdir'] == 'newpatient' || $enc['formdir'] == 'newGroupEncounter') {
                continue;
            }

            // skip forms whose 'deleted' flag is set to 1 --JRM--
            if ($enc['deleted'] == 1) {
                continue;
            }

            // Skip forms that we are not authorized to see. --JRM--
            // pardon the wonky logic
            $formdir = $enc['formdir'];
            if (($auth_notes_a) ||
                ($auth_notes && $enc['user'] == $_SESSION['authUser']) ||
                ($auth_relaxed && ($formdir == 'sports_fitness' || $formdir == 'podiatry'))) {
            } else {
                continue;
            }

            // Show the form name.  In addition, for the specific-issue case show
            // the data collected by the form (this used to be a huge tooltip
            // but we did away with that).
            //
            $formdir = $enc['formdir'];
            if ($issue) {
                echo htmlspecialchars(xl_form_title($enc['form_name']), ENT_NOQUOTES);
                echo "<br>";
                echo "<div class='encreport' style='padding-left:10px;'>";
          // Use the form's report.php for display.  Forms with names starting with LBF
          // are list-based forms sharing a single collection of code.
                if (substr($formdir, 0, 3) == 'LBF') {
                    include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
                    call_user_func("lbf_report", $pid, $result4['encounter'], 2, $enc['form_id'], $formdir);
                } else {
                    include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
                    call_user_func($formdir . "_report", $pid, $result4['encounter'], 2, $enc['form_id']);
                }
                echo "</div>";
            } else {
                echo "<div " .
                "onmouseover='efmouseover(this,$pid," . $result4['encounter'] .
                ",\"$formdir\"," . $enc['form_id'] . ")' " .
                "onmouseout='ttMouseOut()'>";
                echo htmlspecialchars(xl_form_title($enc['form_name']), ENT_NOQUOTES);
                echo "</div>";
            }
        } // end encounter Forms loop

        echo "</div>";
        echo "</td>\n";

        if ($attendant_type == 'pid') {
            // show user (Provider) for the encounter
            $provname = '&nbsp;';
            if (!empty($result4['lname']) || !empty($result4['fname'])) {
                $provname = htmlspecialchars($result4['lname'], ENT_NOQUOTES);
                if (!empty($result4['fname']) || !empty($result4['mname'])) {
                    $provname .= htmlspecialchars(', ' . $result4['fname'] . ' ' . $result4['mname'], ENT_NOQUOTES);
                }
            }
            echo "<td>$provname</td>\n";

            // for therapy group view
        } else {
            $counselors ='';
            foreach (explode(',', $result4['counselors']) as $userId) {
                $counselors .= getUserNameById($userId) . ', ';
            }
            $counselors = rtrim($counselors, ", ");
            echo "<td>" . text($counselors) . "</td>\n";
        }
    } // end not billing view

        //this is where we print out the text of the billing that occurred on this encounter
        $thisauth = $auth_coding_a;
    if (!$thisauth && $auth_coding) {
        if ($result4['user'] == $_SESSION['authUser']) {
            $thisauth = $auth_coding;
        }
    }
        $coded = "";
        $arid = 0;
    if ($thisauth && $auth_sensitivity && $authPostCalendarCategory) {
        $binfo = array('', '', '', '', '');
        if ($subresult2 = getBillingByEncounter($pid, $result4['encounter'], "code_type, code, modifier, code_text, fee")) {
            // Get A/R info, if available, for this encounter.
            $arinvoice = array();
            $arlinkbeg = "";
            $arlinkend = "";
            if ($billing_view) {
                    $tmp = sqlQuery("SELECT id FROM form_encounter WHERE " .
                                "pid = ? AND encounter = ?", array($pid,$result4['encounter']));
                    $arid = 0 + $tmp['id'];
                if ($arid) {
                    $arinvoice = ar_get_invoice_summary($pid, $result4['encounter'], true);
                }
                if ($arid) {
                    $arlinkbeg = "<a href='../../billing/sl_eob_invoice.php?id=" .
                    htmlspecialchars($arid, ENT_QUOTES)."'" .
                            " target='_blank' class='text' style='color:#00cc00'>";
                    $arlinkend = "</a>";
                }
            }

            // Throw in product sales.
            $query = "SELECT s.drug_id, s.fee, d.name " .
              "FROM drug_sales AS s " .
              "LEFT JOIN drugs AS d ON d.drug_id = s.drug_id " .
              "WHERE s.pid = ? AND s.encounter = ? " .
              "ORDER BY s.sale_id";
            $sres = sqlStatement($query, array($pid,$result4['encounter']));
            while ($srow = sqlFetchArray($sres)) {
                $subresult2[] = array('code_type' => 'PROD',
                'code' => 'PROD:' . $srow['drug_id'], 'modifier' => '',
                'code_text' => $srow['name'], 'fee' => $srow['fee']);
            }

            // This creates 5 columns of billing information:
            // billing code, charges, payments, adjustments, balance.
            foreach ($subresult2 as $iter2) {
                // Next 2 lines were to skip diagnoses, but that seems unpopular.
                // if ($iter2['code_type'] != 'COPAY' &&
                //   !$code_types[$iter2['code_type']]['fee']) continue;
                $title = htmlspecialchars(($iter2['code_text']), ENT_QUOTES);
                $codekey = $iter2['code'];
                $codekeydisp = $iter2['code_type']." - ".$iter2['code'];
                if ($iter2['code_type'] == 'COPAY') {
                    $codekey = 'CO-PAY';
                    $codekeydisp = xl('CO-PAY');
                }
                if ($iter2['modifier']) {
                    $codekey .= ':' . $iter2['modifier'];
                    $codekeydisp .= ':' . $iter2['modifier'];
                }

                $codekeydisp = htmlspecialchars($codekeydisp, ENT_NOQUOTES);

                if ($binfo[0]) {
                    $binfo[0] .= '<br>';
                }
                if ($issue && !$billing_view) {
                  // Single issue clinical view: show code description after the code.
                    $binfo[0] .= "$arlinkbeg$codekeydisp $title$arlinkend";
                } else {
                  // Otherwise offer the description as a tooltip.
                    $binfo[0] .= "<span title='$title'>$arlinkbeg$codekeydisp$arlinkend</span>";
                }
                if ($billing_view) {
                    if ($binfo[1]) {
                        for ($i = 1; $i < 8;++$i) { // Sai custom code start
                            $binfo[$i] .= '<br>';
                        }
                    }
                    if (empty($arinvoice[$codekey])) {
                        // If no invoice, show the fee.
                        if ($arlinkbeg) {
                            $binfo[1] .= '&nbsp;';
                        } else {
                            $binfo[1] .= htmlspecialchars(oeFormatMoney($iter2['fee']), ENT_NOQUOTES);
                        }

                        for ($i = 2; $i < 8;
                        ++$i) {// Sai custom code
                            $binfo[$i] .= '&nbsp;';
                        }
                    } else {
                        $binfo[1] .= htmlspecialchars(oeFormatMoney($arinvoice[$codekey]['chg'] + $arinvoice[$codekey]['adj']), ENT_NOQUOTES);
                        $binfo[2] .= htmlspecialchars(oeFormatMoney($arinvoice[$codekey]['chg'] - $arinvoice[$codekey]['bal']), ENT_NOQUOTES);
                        $binfo[3] .= htmlspecialchars(oeFormatMoney($arinvoice[$codekey]['adj']), ENT_NOQUOTES);
			  // Sai custom code start -->
			    $binfo[4] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['w_o']), ENT_NOQUOTES);
                            $binfo[5] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['bal']-$arinvoice[$codekey]['w_o']), ENT_NOQUOTES);							
			    $binfo[6] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['co_ins']), ENT_NOQUOTES);
			    $binfo[7] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['interest']), ENT_NOQUOTES);
		// Sai custom code end -->				 
                        unset($arinvoice[$codekey]);
                    }
                }
            } // end foreach

            // Pick up any remaining unmatched invoice items from the accounting
            // system.  Display them in red, as they should be unusual.
            if (!empty($arinvoice)) {
                foreach ($arinvoice as $codekey => $val) {
                    if ($binfo[0]) {
                        for ($i = 0; $i < 8;
                        ++$i) {// Sai custom code 
                            $binfo[$i] .= '<br>';
                        }
                    }
                    for ($i = 0; $i < 8;
                    ++$i) {// Sai custom code 
                        $binfo[$i] .= "<font color='red'>";
                    }
                    $binfo[0] .= htmlspecialchars($codekey, ENT_NOQUOTES);
                    $binfo[1] .= htmlspecialchars(oeFormatMoney($val['chg'] + $val['adj']), ENT_NOQUOTES);
                    $binfo[2] .= htmlspecialchars(oeFormatMoney($val['chg'] - $val['bal']), ENT_NOQUOTES);
                    $binfo[3] .= htmlspecialchars(oeFormatMoney($val['adj']), ENT_NOQUOTES);
			// Sai custom code start -->
			$binfo[4] .= htmlspecialchars( oeFormatMoney($val['w_o']), ENT_NOQUOTES);
                        $binfo[5] .= htmlspecialchars( oeFormatMoney($val['bal']-$val['w_o']), ENT_NOQUOTES);
			$binfo[6] .= htmlspecialchars( oeFormatMoney($val[$codekey]['co_ins']), ENT_NOQUOTES);
			$binfo[7] .= htmlspecialchars( oeFormatMoney($val[$codekey]['interest']), ENT_NOQUOTES);
                        for ($i = 0; $i < 8; ++$i) $binfo[$i] .= "</font>";
			// Sai custom code end -->
                    }
                }
        } // end if there is billing


        echo "<td class='text' >".$binfo[0]."</td>\n";
        if($billing_view){
		    for ($i = 1; $i < 8; ++$i) { // Sai custom code start -->
            echo "<td class='text right'>". $binfo[$i]."</td>\n";
            }
        }
    } // end if authorized

    else {
        echo "<td class='text' valign='top' colspan='5' rowspan='$encounter_rows'>(".htmlspecialchars(xl("No access"), ENT_NOQUOTES).")</td>\n";
    }

        // show insurance
    if ($attendant_type == 'pid' && !$GLOBALS['ippf_specific']) {
            //$insured = oeFormatShortDate($raw_encounter_date); // Sai custom code start -->
			 $insured = ""; // Sai custom code start -->
        if ($auth_demo) {
            $responsible = -1;
            if ($arid) {
                    $responsible = ar_responsible_party($pid, $result4['encounter']);
            }
            $subresult5 = getInsuranceDataByDate($pid, $raw_encounter_date, "primary");
		// Sai custom code start -->
				// code added for BUG ID 11240 Self Pay Validation 
				
				if($self_pay=="self_pay"){
				
					$insured .= "<span class='text' style='color:red'>&nbsp;" . htmlspecialchars( xl('Patient'), ENT_NOQUOTES) .
									"</span><br>\n";
				}
				else{
		// Sai custom code end -->
            if ($subresult5 && $subresult5{"provider_name"}) {
                $style = $responsible == 1 ? " style='color:red'" : "";
                $insured = "<span class='text'$style>&nbsp;" . htmlspecialchars(xl('Primary'), ENT_NOQUOTES) . ": " .
                htmlspecialchars($subresult5{"provider_name"}, ENT_NOQUOTES) . "</span><br>\n";
            }
            $subresult6 = getInsuranceDataByDate($pid, $raw_encounter_date, "secondary");
            if ($subresult6 && $subresult6{"provider_name"}) {
                $style = $responsible == 2 ? " style='color:red'" : "";
                $insured .= "<span class='text'$style>&nbsp;" . htmlspecialchars(xl('Secondary'), ENT_NOQUOTES) . ": " .
                htmlspecialchars($subresult6{"provider_name"}, ENT_NOQUOTES) . "</span><br>\n";
            }
            $subresult7 = getInsuranceDataByDate($pid, $raw_encounter_date, "tertiary");
            if ($subresult6 && $subresult7{"provider_name"}) {
                $style = $responsible == 3 ? " style='color:red'" : "";
                $insured .= "<span class='text'$style>&nbsp;" . htmlspecialchars(xl('Tertiary'), ENT_NOQUOTES) . ": " .
                htmlspecialchars($subresult7{"provider_name"}, ENT_NOQUOTES) . "</span><br>\n";
            }
            if ($responsible == 0) {
                $insured .= "<span class='text' style='color:red'>&nbsp;" . htmlspecialchars(xl('Patient'), ENT_NOQUOTES) .
                            "</span><br>\n";
            }
				}// end of self pay else // Sai custom code start -->
        } else {
            $insured = " (".htmlspecialchars(xl("No access"), ENT_NOQUOTES).")";
        }

        echo "<td>".$insured."</td>\n";
    }
	// Sai custom code start -->
		$totoal_bal = 0;
		if ($billing_view) {
	$filename='';
	$session='';
		$eqry = "select file,session from eob_file,ar_activity where session=session_id and encounter=".$result4['encounter'];
				$eres = sqlStatement($eqry);
				while($erow = sqlFetchArray($eres)){
				$filename=$erow['file'];
				$session = $erow['session'];
				}
				if($filename){
				//$filepath = $GLOBALS['OE_SITE_DIR'].'/eob/'.$session.'/'.$filename;
				// code added for single version code BUG ID 10911
				$client =$_SESSION['site_id'];
				$filepath = "../../../sites/$client/eob/".$session.'/'.$filename;
				
		echo "<td><a href='$filepath'  target='_blank'>".$filename."</td>";}
		else
		echo "<td>&nbsp;</td>";
		}
		// Sai custom code start -->
        echo "</tr>\n";
} // end while


// Dump remaining document lines if count not exceeded.
while ($drow /* && $count <= $N */) {
    showDocument($drow);
    $drow = sqlFetchArray($dres);
}
?>
</tbody> <!-- Sai custom code  -->
</table>
<!-- Sai custom code start -->
<div class="footer">
  <p align="center">
  CPT<sup>&reg;</sup> 2017 American Medical Association. All rights reserved.<br>
  CPT<sup>&reg;</sup> is a registered trademark of the American Medical Association
  </p>
</div>
<style>
.footer {
    position: relative;
    left: 0;
    bottom: 0;
    width: 100%;
    background-color: none;
    color: black;
    text-align: center;
}
</style>
<!-- Sai custom code end -->
</div> <!-- end 'encounters' large outer DIV -->
</form> <!-- Sai custom code end -->
<div id='tooltipdiv'
 style='position:absolute;width:400pt;border:1px solid black;padding:2px;background-color:#ffffaa;visibility:hidden;z-index:1000;font-size:9pt;'
></div>

</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".encrow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".encrow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".encrow").click(function() { toencounter(this.id); });

    $(".docrow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".docrow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".docrow").click(function() { todocument(this.id); });

    $(".billing_note_text").mouseover(function() { $(this).toggleClass("billing_note_text_highlight"); });
    $(".billing_note_text").mouseout(function() { $(this).toggleClass("billing_note_text_highlight"); });
   //$(".billing_note_text").click(function(evt) { evt.stopPropagation(); editNote(this.id); });
	// code added for billing note BUG 10228-10229
	 $(".billing_note_text").click(function(evt) { evt.stopPropagation(); popupUploadForm(this.id); }); // Sai custom code end 
});

</script>
<!-- Sai custom code start -->
<script type="text/javascript" language="javascript" >
function submitClaimForm(claim_type,billing_type){
location.href = 'encounters.php?claim_type='+claim_type+'&billing='+billing_type;  
document.getElementById("claimStatus").submit();
}
</script>
<!-- Sai custom code end -->
</html>
