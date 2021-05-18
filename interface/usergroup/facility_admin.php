<?php
require_once("../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/erx_javascript.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

if (isset($_GET["fid"])) {
    $my_fid = $_GET["fid"];
}

if (isset($_POST["fid"])) {
    $my_fid = $_POST["fid"];
}

if (isset($_POST["mode"]) && $_POST["mode"] == "facility") {
    echo '
<script type="text/javascript">
<!--
dlgclose();
//-->
</script>

	';
}
// Sai custom code start
if(isset($_GET['link'])){
	$facility_add = $_GET['link'];
}

// code added for billing location validation
if($_POST['a_var']){
  $result=$_POST['a_var'];
  echo json_encode($result);
  exit;
  }
  // Sai custom code end
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
    $collectthis = collectValidationPageRules("/interface/usergroup/facility_admin.php");
    if (empty($collectthis)) {
        $collectthis = "undefined";
    } else {
        $collectthis = $collectthis["facility-form"]["rules"];
    }
    ?>

   

</head>
<body class="body_top" style="width:600px;height:330px !important;">

<table>
    <tr>
        <td>
            <span class="title"><?php xl('Edit Facility', 'e'); ?></span>&nbsp;&nbsp;&nbsp;</td><td>
            <a class="css_button large_button" name='form_save' id='form_save' onclick='submitform()' href='#' >
                <span class='css_button_span large_button_span'><?php xl('Save', 'e');?></span>
            </a>
            <a class="css_button large_button" id='cancel' href='#'>
                <span class='css_button_span large_button_span'><?php xl('Cancel', 'e');?></span>
            </a>
        </td>
    </tr>
</table>

<form name='facility-form' id="facility-form" method='post' action="facilities.php">
	<input type=hidden name=link value="<?php echo $facility_add;?>"><!-- Sai custom code -->
    <input type=hidden name=mode value="facility">
    <input type=hidden name=newmode value="admin_facility"> <!--    Diffrentiate Admin and add post backs -->
    <input type=hidden name=fid value="<?php echo $my_fid;?>">
    	<!-- Sai custom code start -->
    <?php $facility = $facilityService->getById($my_fid);
    
		// code for billing facility validation
		$facility_billing = sqlQuery("select * from facility where billing_location='1'");

	 ?>
    	<!-- Sai custom code end -->
    <table border=0 cellpadding=0 cellspacing=1 style="width:630px;">
    <!-- Sai custom code start -->
    <!-- code added for billing facility validation--->
    	<input type='hidden' name='billing_facility_id' id='billing_facility_id' size='20' value="<?php echo $facility_billing['id'] ;?>" />
        <input type='hidden' name='billing_facility_name' id='billing_facility_name' size='20' value='<?php echo htmlspecialchars($facility_billing['name'], ENT_QUOTES) ?>'/>
      <!-- Sai custom code end -->  
        <tr>
            <td width='150px'><span class='text'><?php xl('Name', 'e'); ?>: </span></td>
            <td width='220px'><input type='entry' name='facility' size='20' value='<?php echo htmlspecialchars($facility['name'], ENT_QUOTES) ?>'></td>
            <td width='200px'><span class='text'><?php xl('Phone', 'e'); ?> <?php xl('as', 'e'); ?> (000) 000-0000:</span></td>
            <td width='220px'><input type='entry' name='phone' size='20' value='<?php echo htmlspecialchars($facility['phone'], ENT_QUOTES) ?>'></td>
        </tr>
        <tr>
            <td><span class=text><?php xl('Address', 'e'); ?>: </span></td><td><input type=entry size=20 name=street value="<?php echo htmlspecialchars($facility["street"], ENT_QUOTES) ?>"></td>
            <td><span class='text'><?php xl('Fax', 'e'); ?> <?php xl('as', 'e'); ?> (000) 000-0000:</span></td>
            <td><input type='entry' name='fax' size='20' value='<?php echo htmlspecialchars($facility['fax'], ENT_QUOTES) ?>'></td>
        </tr>
        <tr>

            <td><span class=text><?php xl('City', 'e'); ?>: </span></td>
            <td><input type=entry size=20 name=city value="<?php echo htmlspecialchars($facility["city"], ENT_QUOTES) ?>"></td>
            <td><span class=text><?php xl('Zip Code', 'e'); ?>: </span></td><td><input type=entry size=20 name=postal_code value="<?php echo htmlspecialchars($facility["postal_code"], ENT_QUOTES) ?>"></td>
        </tr>
        <?php
        $ssn='';
        $ein='';
        if ($facility['tax_id_type']=='SY') {
            $ssn='selected';
        } else {
            $ein='selected';
        }
        ?>
        <tr>
            <td><span class=text><?php xl('State', 'e'); ?>: </span></td><td><input type=entry size=20 name=state value="<?php echo htmlspecialchars($facility["state"], ENT_QUOTES) ?>"></td>
            <td><span class=text><?php xl('Tax ID', 'e'); ?>: </span></td><td><select name=tax_id_type><option value="EI" <?php echo $ein;?>><?php xl('EIN', 'e'); ?></option><option value="SY" <?php echo $ssn;?>><?php xl('SSN', 'e'); ?></option></select><input type=entry size=11 name=federal_ein value="<?php echo htmlspecialchars($facility["federal_ein"], ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <td><span class=text><?php xl('Country', 'e'); ?>: </span></td><td><input type=entry size=20 name=country_code value="<?php echo htmlspecialchars($facility["country_code"], ENT_QUOTES) ?>"></td>
            <td width="21"><span class=text><?php ($GLOBALS['simplified_demographics'] ? xl('Facility Code', 'e') : xl('Facility NPI', 'e')); ?>:
          </span></td><td><input type=entry size=20 name=facility_npi value="<?php echo htmlspecialchars($facility["facility_npi"], ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <td>&nbsp;</td><td>&nbsp;</td><td><span class=text><?php (xl('Facility Taxonomy', 'e')); ?>:</span></td>
            <td><input type=entry size=20 name=facility_taxonomy value="<?php echo htmlspecialchars($facility["facility_taxonomy"], ENT_QUOTES) ?>"></td>
        </tr>


        <tr>
        <td><span class=text><?php xl('Website', 'e'); ?>: </span></td><td><input type=entry size=20 name=website value="<?php echo htmlspecialchars($facility["website"], ENT_QUOTES) ?>"></td>
            <td><span class=text><?php xl('Email', 'e'); ?>: </span></td><td><input type=entry size=20 name=email value="<?php echo htmlspecialchars($facility["email"], ENT_QUOTES) ?>"></td>
        </tr>

        <tr>
            <td><span class='text'><?php xl('Billing Location', 'e'); ?>: </span></td>
          <td><input type='checkbox' name='billing_location' value='1' id="billing_location" <?php if ($facility['billing_location'] != 0) echo 'checked'; ?>></td><!-- Sai custom code -->

            <td rowspan='2'><span class='text'><?php xl('Accepts Assignment', 'e'); ?><br>(<?php xl('only if billing location', 'e'); ?>): </span></td>
            <td><input type='checkbox' name='accepts_assignment' value='1' <?php if ($facility['accepts_assignment'] == 1) {
                echo 'checked';
} ?>></td>
        </tr>
        <tr>
            <td><span class='text'><?php xl('Service Location', 'e'); ?>: </span></td>
            <td><input type='checkbox' name='service_location' value='1' <?php if ($facility['service_location'] == 1) {
                echo 'checked';
} ?>></td>
            <td>&nbsp;</td>
        </tr>
        <?php
        $disabled='';
        $resPBE = $facilityService->getPrimaryBusinessEntity(array("excludedId" => $my_fid));
        if ($resPBE) {
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
                <!-- Sai custom code start -->
	  <td><span class='text'><?php xl('Active','e'); ?></span>
          <!-- Added by Gangeya : BUG ID 8302-->
         
          <input type="checkbox" name="active" <?php if ($facility['active'] == 1) echo " checked"; ?> /></td>
         </tr>
	 <tr>
	  <td><span class='text'><?php echo htmlspecialchars(xl('Color'),ENT_QUOTES); ?>: </span><span class="mandatory">&nbsp;*</span></td> 
      
      <td>
          <input type=entry name=ncolor id=ncolor size=12 value="<?php echo htmlspecialchars($facility{"color"}, ENT_QUOTES) ?>">&nbsp;
          [<a href="javascript:void(0);" onClick="pick('pick','newcolor');return false;" NAME="pick" ID="pick"><?php  echo htmlspecialchars(xl('Pick'),ENT_QUOTES); ?></a>]
      </td>
	  <td><span class='text'><?php echo htmlspecialchars(xl('Term Date'),ENT_QUOTES); ?>: </span></td>
      <td>
        <input type='text' name='term_date' id="term_date" size='10' value='<?php echo htmlspecialchars($facility{"term_date"}, ENT_QUOTES) ?>'
        onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc,'<?php echo date("m/d/Y"); ?>')' title='yyyy-mm-dd'>
        <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_term_date' border='0' alt='[?]' style='cursor:pointer' 
        title='<?php xl('Click here to choose a date','e'); ?>'>
      </td>
<!-- Sai custom code end -->
        <tr>
            <td><span class=text><?php xl('POS Code', 'e'); ?>: </span></td>
            <td colspan="6">
                <select name="pos_code">
                    <?php
                    $pc = new POSRef();

                    foreach ($pc->get_pos_ref() as $pos) {
                        echo "<option value=\"" . $pos["code"] . "\" ";
                        if ($facility['pos_code'] == $pos['code']) {
                            echo "selected";
                        }

                        echo ">" . $pos['code']  . ": ". text($pos['title']);
                        echo "</option>\n";
                    }

                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><span class="text"><?php xl('Billing Attn', 'e'); ?>:</span></td>
            <td colspan="4"><input type="text" name="attn" size="45" value="<?php echo htmlspecialchars($facility['attn'], ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <td><span class="text"><?php xl('CLIA Number', 'e'); ?>:</span></td>
            <td colspan="4"><input type="text" name="domain_identifier" size="45" value="<?php echo htmlspecialchars($facility['domain_identifier'], ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <td><span class="text"><?php xl('Facility ID', 'e'); ?>:</span></td>
            <td colspan="4"><input type="text" name="facility_id" size="45" value="<?php echo htmlspecialchars($facility['facility_code'], ENT_QUOTES) ?>"></td>
        </tr>
		<!-- Sai custom code start -->
        <tr>
            <td><span class="text"><?php xl('MedProvNumber','e'); ?>:</span></td>
            <td colspan="4"><input type="text" name="MedProvNum" size="15" value="<?php echo htmlspecialchars($facility['MedProvNum'], ENT_QUOTES) ?>" onkeypress="return isNumber(event)"></td>
		</tr>
		<tr>
			<td><span class="text" name = "defBillingText" id = "defBillingText"><?php xl('Default Billing Location','e'); ?>:</span></td>
            <td colspan="4">
				<select name="billing_facility" id="billing_facility">
			<?php
				
				$prow = sqlStatement("select id, name, state from facility where billing_location = 1");
				
				$fresult = array();				
					
				for ($iter = 0; $frow = sqlFetchArray($prow); $iter++)
					$fresult[$iter] = $frow;
					
				foreach($fresult as $iter) {     
					$billingFacility = $iter['name'];
					$billingFacilityState = $iter['state'];
					$selected='';

					if($iter['id']==$facility['defaultBillingFacility']) 
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
        <td><span class=text name = "payTostreetText" id = "payTostreetText"><?php xl('Address', 'e'); ?>: </span></td>
        <td><input type=entry size=20 id=payTostreet name=payTostreet value="<?php echo htmlspecialchars($facility["payTostreet"], ENT_QUOTES) ?>"></td>
        <td>&nbsp;</td>
        <td><span class="text" name = "payTocityText" id = "payTocityText"><?php xl('City', 'e'); ?>: </span></td>
        <td><input type=entry size=20 id=payTocity name=payTocity value="<?php echo htmlspecialchars($facility["payTocity"], ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
        <td><span class="text" name = "payTostateText" id = "payTostateText"><?php xl('State', 'e'); ?>: </span></td>
        <td><input type=entry size=20 id=payTostate name=payTostate value="<?php echo htmlspecialchars($facility["payTostate"], ENT_QUOTES) ?>"></td>
        <td>&nbsp;</td>
        <td><span class="text" name = "payTopostal_codeText" id = "payTopostal_codeText"><?php xl('Zip Code', 'e'); ?>: </span></td>
        <td><input type=entry size=20 id=payTopostal_code  name=payTopostal_code value="<?php echo htmlspecialchars($facility["payTopostal_code"], ENT_QUOTES) ?>"></td>
        </tr>
	<!-- Sai custom code end -->

        <tr height="20" valign="bottom">
            <td colspan=2><span class="text"><font class="mandatory">*</font> <?php echo xl('Required', 'e');?></span></td>
        </tr>

    </table>
</form>

</body>
 <script type="text/javascript">
    // Sai custom code start
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
    // Sai custom code end

        /*
         * validation on the form with new client side validation (using validate.js).
         * this enable to add new rules for this form in the pageValidation list.
         * */
        var collectvalidation = <?php echo($collectthis); ?>;
        /**
             * add required/star sign to required form fields
             */
            for (var prop in collectvalidation) {
                //if (collectvalidation[prop].requiredSign)
                if (collectvalidation[prop].presence)
                    jQuery("input[name='" + prop + "']").after('*');
            }

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
                alert("<?php echo addslashes(xl('Primary Business Entity tax id is used as the account id for NewCrop ePrescription.'));?>");
            else if(document.getElementById('primary_business_entity').checked==true)
                alert("<?php echo addslashes(xl('Once the Primary Business Facility is set, changing the facility id will affect NewCrop ePrescription.'));?>");
        }

        function submitform() {

            var valid = submitme(1, undefined, 'facility-form', collectvalidation);
            if (!valid) return;

            <?php if ($GLOBALS['erx_enable']) { ?>
            alertMsg = '';
            f = document.forms[0];
            for (i = 0; i < f.length; i++) {
                if (f[i].type == 'text' && f[i].value) {
                    if (f[i].name == 'facility' || f[i].name == 'Washington') {
                        alertMsg += checkLength(f[i].name, f[i].value, 35);
                        alertMsg += checkFacilityName(f[i].name, f[i].value);
                    }
                    else if (f[i].name == 'street') {
                        alertMsg += checkLength(f[i].name, f[i].value, 35);
                        alertMsg += checkAlphaNumeric(f[i].name, f[i].value);
                    }
                    else if (f[i].name == 'phone' || f[i].name == 'fax') {
                        alertMsg += checkPhone(f[i].name, f[i].value);
                    }
                    else if (f[i].name == 'federal_ein') {
                        alertMsg += checkLength(f[i].name, f[i].value, 10);
                        alertMsg += checkFederalEin(f[i].name, f[i].value);
                    }
                }
            }
            if (alertMsg) {
                alert(alertMsg);
                return false;
            }
            <?php } ?>

            top.restoreSession();

            let post_url = $("#facility-form").attr("action");
            let request_method = $("#facility-form").attr("method");
            let form_data = $("#facility-form").serialize();
            console.log(post_url);
               console.log(request_method);
                  console.log(form_data);

            $.ajax({
                url: post_url,
                type: request_method,
                data: form_data
            }).done(function (r) { //
               // dlgclose('refreshme', false);
               dlgclose();
            });
            return false;
        }

        $(document).ready(function(){
            $("#cancel").click(function() {
                dlgclose();
            });
         // Sai custom code start
     });

            
    </script>
<!-- Sai custom code start -->
<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
//Code added by Gangeya  for PAYEHR-532: pay to address for 837
$(document).ready(function() {
	if(document.getElementById("billing_location").checked == true){
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
	}
    else {
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

function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}
 Calendar.setup({inputField:"term_date", ifFormat:"%Y-%m-%d", button:"img_term_date"});
</script>
<!-- Sai custom code end -->
</html>
