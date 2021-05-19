<?php
/**
 * facilities_add.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/erx_javascript.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

$alertmsg = '';
// sai custom code start
if(isset($_GET['link']))
$facility_add=$_GET['link'];
// sai custom code end
?>
<html>
<head>
    <?php Header::setupHeader(['opener', 'jquery-ui']); ?>
<script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/AnchorPosition.js"></script>
<script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/PopupWindow.js"></script>
<script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/ColorPicker2.js"></script>

<!-- validation library -->
<!--//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation-->
<?php    $use_validate_js = 1;?>
<?php  require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>
<?php
//Gets validation rules from Page Validation list.
//Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
$collectthis = collectValidationPageRules("/interface/usergroup/facilities_add.php");
if (empty($collectthis)) {
    $collectthis = "undefined";
} else {
    $collectthis = $collectthis["facility-add"]["rules"];
}

// Old Browser comp trigger on js

if (isset($_POST["mode"]) && $_POST["mode"] == "facility") {
    echo '
<script type="text/javascript">
<!--
dlgclose();
//-->
</script>

	';
}
?>
<script type="text/javascript">
/// todo, move this to a common library
// sai custom code start
document.onkeydown = function(evt) 
{
    //masterkeypress(evt);
	evt = evt || window.event; // because of Internet Explorer quirks...
	k = evt.which || evt.charCode || evt.keyCode; // because of browser differences...	
	if (k == 83 && evt.altKey && evt.ctrlKey && !evt.shiftKey) 
	{
		 submitform();
	}
	if (k == 115 && evt.altKey && evt.ctrlKey && !evt.shiftKey) 
	{
		 submitform();
	}
}
// sai custom code end
function submitform() {
  //  var collectvalidation = '<?php echo($collectthis); ?>';
   // var valid = submitme(1, undefined, 'facility-add', collectvalidation);
  //  if (!valid) return;

    <?php if ($GLOBALS['erx_enable']) { ?>
    alertMsg='';
    f=document.forms[0];
    for(i=0;i<f.length;i++){
        if(f[i].type=='text' && f[i].value)
        {
            if(f[i].name == 'facility' || f[i].name == 'Washington')
            {
                alertMsg += checkLength(f[i].name,f[i].value,35);
                alertMsg += checkFacilityName(f[i].name,f[i].value);
            }
            else if(f[i].name == 'street')
            {
                alertMsg += checkLength(f[i].name,f[i].value,35);
                alertMsg += checkAlphaNumeric(f[i].name,f[i].value);
            }
            else if(f[i].name == 'phone' || f[i].name == 'fax')
            {
                alertMsg += checkPhone(f[i].name,f[i].value);
            }
            else if(f[i].name == 'federal_ein')
            {
                alertMsg += checkLength(f[i].name,f[i].value,10);
                alertMsg += checkFederalEin(f[i].name,f[i].value);
            }
        }
    }
    if(alertMsg)
    {
        alert(alertMsg);
        return false;
    }
    <?php } ?>
//Custom Code change by Mahesh
	 if (document.forms[0].facility.value.length>0 && document.forms[0].ncolor.value != '') {
		let post_url = $("#facility-add").attr("action");
		let request_method = $("#facility-add").attr("method");
		let form_data = $("#facility-add").serialize();

		$.ajax({
			url: post_url,
			type: request_method,
			data: form_data
		}).done(function (r) { 
		    dlgclose('refreshme', false);
		});
		 
	} else {
        if(document.forms[0].facility.value.length<=0){
        document.forms[0].facility.style.backgroundColor="red";
        document.forms[0].facility.focus();
		}
		else if(document.forms[0].ncolor.value == ''){
		document.forms[0].ncolor.style.backgroundColor="red";
	        document.forms[0].ncolor.focus();	
		}
    } 
   // return false;
}
// Code Change End

function toggle( target, div ) {

    $mode = $(target).find(".indicator").text();
    if ( $mode == "collapse" ) {
        $(target).find(".indicator").text( "expand" );
        $(div).hide();
    } else {
        $(target).find(".indicator").text( "collapse" );
        $(div).show();
    }

}

$(document).ready(function(){

    $("#dem_view").click( function() {
        toggle( $(this), "#DEM" );
    });
});

// Code Change by Mahesh Kunta
$(document).ready(function(){
    $("#cancel").click(function() {
          dlgclose();
     });

    /**
     * add required/star sign to required form fields
     
/**
	 for (var prop in collectvalidation) {
        //if (collectvalidation[prop].requiredSign)
        if (collectvalidation[prop].presence)
            jQuery("input[name='" + prop + "']").after('*');
    }
*/
});
//code Change End
var cp = new ColorPicker('window');
  // Runs when a color is clicked
function pickColor(color) {
    document.getElementById('ncolor').value = color;
}
var field;
function pick(anchorname,target) {
    var cp = new ColorPicker('window');
    field=target;
        cp.show(anchorname);
}
function displayAlert()
{
    if(document.getElementById('primary_business_entity').checked==false)
    alert("<?php echo addslashes(xl('Primary Business Entity tax id is used as account id for NewCrop ePrescription. Changing the facility will affect the working in NewCrop.'));?>");
    else if(document.getElementById('primary_business_entity').checked==true)
    alert("<?php echo addslashes(xl('Once the Primary Business Facility is set, it should not be changed. Changing the facility will affect the working in NewCrop ePrescription.'));?>");
}
</script>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">
<table>
<tr><td>
    <span class="title"><?php xl('Add Facility', 'e'); ?></span>&nbsp;&nbsp;&nbsp;</td>
    <td colspan=5 align=center style="padding-left:2px;">
        <a onclick="submitform();" class="css_button large_button" name='form_save' id='form_save' href='#'>
            <span class='css_button_span large_button_span'><?php xl('Save', 'e');?></span>
        </a>
        <a class="css_button large_button" id='cancel' href='#' >
            <span class='css_button_span large_button_span'><?php xl('Cancel', 'e');?></span>
        </a>
</td></tr>
</table>

<br>
<!-- Sai custom code start -->
<?php
		// code for billing facility validation
		$facility_billing = sqlQuery("select * from facility where billing_location='1'");
?>
<form name='facility-add' id='facility-add' method='post' action="facilities.php">
	 <input type=hidden name=link value="<?php echo $facility_add?>" >
    <input type=hidden name=mode value="facility">
    <!-- code added for billing facility validation -->
    <input type='hidden' name='billing_facility_id' id='billing_facility_id' size='20' value="<?php echo $facility_billing['id'] ;?>" />
    <input type='hidden' name='billing_facility_name' id='billing_facility_name' size='20' value='<?php echo htmlspecialchars($facility_billing['name'], ENT_QUOTES) ?>'/>
   <!-- Sai custom code end -->
    <table border=0 cellpadding=0 cellspacing=0>
        <tr>
        <td><span class="text"><?php xl('Name', 'e'); ?>: </span></td><td><input type=entry name=facility size=20 value=""></td>
        <td width=20>&nbsp;</td>
        <td><span class="text"><?php xl('Phone', 'e'); ?>: </span></td><td><input type=entry name=phone size=20 value=""></td>
        </tr>
        <tr>
        <td><span class="text"><?php xl('Address', 'e'); ?>: </span></td><td><input type=entry size=20 name=street value=""></td>
        <td>&nbsp;</td>
        <td><span class="text"><?php xl('Fax', 'e'); ?>: </span></td><td><input type=entry name=fax size=20 value=""></td>
        </tr>
        <tr>
        <td><span class="text"><?php xl('City', 'e'); ?>: </span></td><td><input type=entry size=20 name=city value=""></td>
        <td>&nbsp;</td>
        <td><span class="text"><?php xl('Zip Code', 'e'); ?>: </span></td><td><input type=entry size=20 name=postal_code value=""></td>
        </tr>
        <tr>
        <td><span class="text"><?php xl('State', 'e'); ?>: </span></td><td><input type=entry size=20 name=state value=""></td>
        <td>&nbsp;</td>
        <td><span class="text"><?php xl('Tax ID', 'e'); ?>: </span></td><td><select name=tax_id_type><option value="EI"><?php xl('EIN', 'e'); ?></option><option value="SY"><?php xl('SSN', 'e'); ?></option></select><input type=entry size=11 name=federal_ein value=""></td>
        </tr>
        <tr>
        <td height="22"><span class="text"><?php xl('Country', 'e'); ?>: </span></td><td><input type=entry size=20 name=country_code value=""></td>
        <td>&nbsp;</td>
        <td><span class="text"><?php ($GLOBALS['simplified_demographics'] ? xl('Facility Code', 'e') : xl('Facility NPI', 'e')); ?>:
        </span></td><td><input type=entry size=20 name=facility_npi value=""></td>
        </tr>
        <tr>
            <td>&nbsp;</td><td>&nbsp;</td><td width="20"></td><td><span class=text><?php (xl('Facility Taxonomy', 'e')); ?>:</span></td>
            <td><input type=entry size=20 name=facility_taxonomy value=""></td>
        </tr>
        <tr>
        <td><span class="text"><?php xl('Website', 'e'); ?>: </span></td><td><input type=entry size=20 name=website value=""></td>
        <td>&nbsp;</td>
        <td><span class="text"><?php xl('Email', 'e'); ?>: </span></td><td><input type=entry size=20 name=email value=""></td>
        </tr>

        <tr>
          <td><span class='text'><?php xl('Billing Location','e'); ?>: </span></td><td><input type='checkbox' name='billing_location' value = '1' id="billing_location"></td><!-- Sai custom code  -->
          <td>&nbsp;</td>
          <td><span class='text'><?php xl('Accepts Assignment', 'e'); ?><br>(<?php xl('only if billing location', 'e'); ?>): </span></td> <td><input type='checkbox' name='accepts_assignment' value = '1'></td>
        </tr>
        <tr>
          <td><span class='text'><?php xl('Service Location', 'e'); ?>: </span></td> <td><input type='checkbox' name='service_location' value = '1'></td>
          <td>&nbsp;</td>
          <td><span class='text'><?php echo htmlspecialchars(xl('Color'), ENT_QUOTES); ?>: </span></td> <td><input type=entry name=ncolor id=ncolor size=20 value=""><span>[<a href="javascript:void(0);" onClick="pick('pick','newcolor');return false;" NAME="pick" ID="pick"><?php echo htmlspecialchars(xl('Pick'), ENT_QUOTES); ?></a>]</span></td>
        </tr>
    <?php
    $disabled='';
    $resPBE = $facilityService->getPrimaryBusinessEntity(array("excludedId" => $my_fid));
    if (!empty($resPBE) && sizeof($resPBE)>0) {
        $disabled='disabled';
    }
    ?>
     <tr>
          <td><span class='text'><?php xl('Primary Business Entity', 'e'); ?>: </span></td>
          <td><input type='checkbox' name='primary_business_entity' id='primary_business_entity' value='1' <?php if ($facility['primary_business_entity'] == 1) {
                echo 'checked';
} ?> <?php if ($GLOBALS['erx_enable']) {
                ?> onchange='return displayAlert()' <?php
} ?> <?php echo $disabled;?>></td>
          <td>&nbsp;</td>
         </tr>
        <tr>
            <td><span class=text><?php xl('POS Code', 'e'); ?>: </span></td>
            <td colspan="6">
                <select name="pos_code">
                <?php
                $pc = new POSRef();

                foreach ($pc->get_pos_ref() as $pos) {
                    echo "<option value=\"" . $pos["code"] . "\" ";
                    echo ">" . $pos['code']  . ": ". text($pos['title']);
                    echo "</option>\n";
                }

                ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><span class="text"><?php xl('Billing Attn', 'e'); ?>:</span></td>
            <td colspan="4"><input type="text" name="attn" size="45"></td>
        </tr>
        <tr>
            <td><span class="text"><?php xl('CLIA Number', 'e'); ?>:</span></td>
            <td colspan="4"><input type="text" name="domain_identifier" size="45"></td>
        </tr>
        <tr>
            <td><span class="text"><?php xl('Facility ID', 'e'); ?>:</span></td>
            <td colspan="4"><input type="text" name="facility_id" size="20"></td>
 	    <td><span class="text"><?php xl('MedProvNumber','e'); ?>:</span></td>
            <td colspan="4"><input type="text" name="MedProvNum" size="15" onkeypress="return isNumber(event)"></td>
        </tr>
        <tr>
            <td><span class="text" name = "defBillingText" id = "defBillingText"><?php xl('Default Billing Location','e'); ?>:</span></td>
            <td colspan="4">
				<select name="billing_facility" id="billing_facility" >
			<?php
				
				$prow = sqlStatement("select id, name, state from facility where billing_location = 1");
				
				$fresult = array();				
					
				for ($iter = 0; $frow = sqlFetchArray($prow); $iter++)
					$fresult[$iter] = $frow;
					
				foreach($fresult as $iter) {     
					$billingFacility = $iter['name'];
					$billingFacilityState = $iter['state'];
					$selected='';

					if($iter['id']==$billing_facility) 
						$selected = "selected";
						echo "<option value=".$iter['id']." $selected>$billingFacility, $billingFacilityState</option>";
				}
				
			?>
			</td>
        </tr>
	<?php //Code added by Gangeya  for PAYEHR-532: pay to address for 837?>
        <tr>
        <td><span class="text" name = "payToLabel" id = "payToLabel"><?php xl('Pay To Address', 'e'); ?>: </span></td>
        </tr>
        <tr>
        <td><span class="text" name = "payTostreetText" id = "payTostreetText"><?php xl('Address', 'e'); ?>: </span></td>
        <td><input type=entry size=20 id=payTostreet name=payTostreet value=""></td>
        <td>&nbsp;</td>
        <td><span class="text" name = "payTocityText" id = "payTocityText"><?php xl('City', 'e'); ?>: </span></td>
        <td><input type=entry size=20 id=payTocity name=payTocity value=""></td>
        </tr>
        <tr>
        <td><span class="text" name = "payTostateText" id = "payTostateText"><?php xl('State', 'e'); ?>: </span></td>
        <td><input type=entry size=20 id=payTostate name=payTostate value=""></td>
        <td>&nbsp;</td>
        <td><span class="text" name = "payTopostal_codeText" id = "payTopostal_codeText"><?php xl('Zip Code', 'e'); ?>: </span></td>
        <td><input type=entry size=20 id=payTopostal_code name=payTopostal_code value=""></td>
        </tr>
        <tr height="25" style="valign:bottom;">
        <td><font class="mandatory">*</font><span class="text"> <?php echo xl('Required', 'e'); ?></span></td><td>&nbsp;</td><td>&nbsp;</td>
        <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
    </table>
</form>

<script language="JavaScript">

$(function() {
  $('#billing_location').change(function() {
    if ($(this).is(':checked')) {
        // disable the dropdown:
	//Code added by Gangeya  for PAYEHR-532: pay to address for 837
        $('#billing_facility').attr('disabled', 'disabled');
		document.getElementById("billing_facility").style.visibility="hidden"
		document.getElementById("defBillingText").style.visibility="hidden"
		document.getElementById("payToLabel").style.visibility="visible"
		document.getElementById("payTostreetText").style.visibility="visible"
		document.getElementById("payTostreet").style.visibility="visible"
		document.getElementById("payTocityText").style.visibility="visible"
		document.getElementById("payTocity").style.visibility="visible"
		document.getElementById("payTostateText").style.visibility="visible"
		document.getElementById("payTostate").style.visibility="visible"
		document.getElementById("payTopostal_codeText").style.visibility="visible"
		document.getElementById("payTopostal_code").style.visibility="visible"
    } else {
        $('#billing_facility').removeAttr('disabled');
		document.getElementById("billing_facility").style.visibility="visible"
		document.getElementById("defBillingText").style.visibility="visible"
		document.getElementById("payToLabel").style.visibility="hidden"
		document.getElementById("payTostreetText").style.visibility="hidden"
		document.getElementById("payTostreet").style.visibility="hidden"
		document.getElementById("payTocityText").style.visibility="hidden"
		document.getElementById("payTocity").style.visibility="hidden"
		document.getElementById("payTostateText").style.visibility="hidden"
		document.getElementById("payTostate").style.visibility="hidden"
		document.getElementById("payTopostal_codeText").style.visibility="hidden"
		document.getElementById("payTopostal_code").style.visibility="hidden"
    }
  });
});

<?php
if ($alertmsg = trim($alertmsg)) {
    echo "alert('$alertmsg');\n";
}
?>
//Sai custom code start -->
function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}
// Sai custom code end -->
</script>

</body>
</html>
