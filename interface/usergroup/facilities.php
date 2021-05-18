<?php
/**
 * Facilities.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ranganath Pathak <pathak01@hotmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Ranganath Pathak <pathak01@hotmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/acl.inc");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

$alertmsg = '';
// sai custom code start
$facility_add;
  if(isset($_GET['link']))
    $facility_add = $_GET['link'];
  if(isset($_POST['link']))
    $facility_add = $_POST['link'];
    
  


/*    Inserting New facility     */

//added by Gangeya BUG ID 10758 : active date at the time of adding a user.
$active_date = date('Y-m-d H:i:s');
// sai custom code start
//print_r($_POST);
//Query updated by Gangeya BUG ID 10758
if (isset($_POST["mode"]) && $_POST["mode"] == "facility" && $_POST["newmode"] != "admin_facility") {
    $newFacility = array(
      "name" => trim(isset($_POST["facility"]) ? $_POST["facility"] : ''),
      "phone" => trim(isset($_POST["phone"]) ? $_POST["phone"] : ''),
      "fax" => trim(isset($_POST["fax"]) ? $_POST["fax"] : ''),
      "street" => trim(isset($_POST["street"]) ? $_POST["street"] : ''),
      "city" => trim(isset($_POST["city"]) ? $_POST["city"] : ''),
      "state" => trim(isset($_POST["state"]) ? $_POST["state"] : ''),
      "postal_code" => trim(isset($_POST["postal_code"]) ? $_POST["postal_code"] : ''),
      "country_code" => trim(isset($_POST["country_code"]) ? $_POST["country_code"] : ''),
      "federal_ein" => trim(isset($_POST["federal_ein"]) ? $_POST["federal_ein"] : ''),
      "defaultBillingFacility" => trim(isset($_POST["billing_facility"]) ? $_POST["billing_facility"] : ''),
      "website" => trim(isset($_POST["website"]) ? $_POST["website"] : ''),
      "email" => trim(isset($_POST["email"]) ? $_POST["email"] : ''),
      "color" => trim(isset($_POST["ncolor"]) ? $_POST["ncolor"] : ''),
      "service_location" => trim(isset($_POST["service_location"]) ? $_POST["service_location"] : ''),
      "billing_location" => trim(isset($_POST["billing_location"]) ? $_POST["billing_location"] : ''),
      "accepts_assignment" => trim(isset($_POST["accepts_assignment"]) ? $_POST["accepts_assignment"] : ''),
      "pos_code" => trim(isset($_POST["pos_code"]) ? $_POST["pos_code"] : ''),
      "domain_identifier" => trim(isset($_POST["domain_identifier"]) ? $_POST["domain_identifier"] : ''),
      "attn" => trim(isset($_POST["attn"]) ? $_POST["attn"] : ''),
      "tax_id_type" =>  trim(isset($_POST["tax_id_type"]) ? $_POST["tax_id_type"] : ''),
      "primary_business_entity" => trim(isset($_POST["primary_business_entity"]) ? $_POST["primary_business_entity"] : ''),
      "active_date" => $active_date,
      "facility_npi" => trim(isset($_POST["facility_npi"]) ? $_POST["facility_npi"] : ''),
      "MedProvNum" =>  trim(isset($_POST["MedProvNum"]) ? $_POST["MedProvNum"] : ''), 
      "created_by" => $_SESSION['authUserID'],
      "created_date" =>date('Y-m-d h:i:s'),
      "facility_taxonomy" => trim(isset($_POST["facility_taxonomy"]) ? $_POST["facility_taxonomy"] : ''),
      "facility_code" => trim(isset($_POST["facility_id"]) ? $_POST["facility_id"] : ''),
      "payTostreet" => trim(isset($_POST["payTostreet"]) ? $_POST["payTostreet"] : ''),
      "payTocity" => trim(isset($_POST["payTocity"]) ? $_POST["payTocity"] : ''),
      "payTostate" => trim(isset($_POST["payTostate"]) ? $_POST["payTostate"] : ''),
      "payTopostal_code" => trim(isset($_POST["payTopostal_code"]) ? $_POST["payTopostal_code"] : '')
      //Code added by Gangeya  for PAYEHR-532: pay to address for 837
    );

    $insert_id = $facilityService->insert($newFacility);

   
          

    exit(); // sjp 12/20/17 for ajax save
}


/*    Editing existing facility         */
if (isset($_POST["mode"]) && $_POST["mode"] == "facility" && $_POST["newmode"] == "admin_facility") {
    $newFacility = array(
      "fid" => trim(isset($_POST["fid"]) ? $_POST["fid"] : ''),
      "name" => trim(isset($_POST["facility"]) ? $_POST["facility"] : ''),
      "phone" => trim(isset($_POST["phone"]) ? $_POST["phone"] : ''),
      "fax" => trim(isset($_POST["fax"]) ? $_POST["fax"] : ''),
      "street" => trim(isset($_POST["street"]) ? $_POST["street"] : ''),
      "city" => trim(isset($_POST["city"]) ? $_POST["city"] : ''),
      "state" => trim(isset($_POST["state"]) ? $_POST["state"] : ''),
      "postal_code" => trim(isset($_POST["postal_code"]) ? $_POST["postal_code"] : ''),
      "country_code" => trim(isset($_POST["country_code"]) ? $_POST["country_code"] : ''),
      "federal_ein" => trim(isset($_POST["federal_ein"]) ? $_POST["federal_ein"] : ''),
      "website" => trim(isset($_POST["website"]) ? $_POST["website"] : ''),
      "email" => trim(isset($_POST["email"]) ? $_POST["email"] : ''),
      "color" => trim(isset($_POST["ncolor"]) ? $_POST["ncolor"] : ''),
      "service_location" => trim(isset($_POST["service_location"]) ? $_POST["service_location"] : ''),
      "billing_location" => trim(isset($_POST["billing_location"]) ? $_POST["billing_location"] : ''),
      "accepts_assignment" => trim(isset($_POST["accepts_assignment"]) ? $_POST["accepts_assignment"] : ''),
      "pos_code" => trim(isset($_POST["pos_code"]) ? $_POST["pos_code"] : ''),
      "defaultBillingFacility" => trim(isset($_POST["billing_facility"]) ? $_POST["billing_facility"] : ''),
     
     "term_date" => trim(isset($_POST["term_date"]) ? $_POST["term_date"] : ''), // sai custom code
     "domain_identifier" => trim(isset($_POST["domain_identifier"]) ? $_POST["domain_identifier"] : ''),
      "attn" => trim(isset($_POST["attn"]) ? $_POST["attn"] : ''),
       "active" => trim(isset($_POST["active"]) && trim($_POST['active']) == 'on' ? 1 : 0),
      "tax_id_type" =>  trim(isset($_POST["tax_id_type"]) ? $_POST["tax_id_type"] : ''),
      "MedProvNum" =>  trim(isset($_POST["MedProvNum"]) ? $_POST["MedProvNum"] : ''), // sai custom code
      "modified_by" =>  trim($_SESSION['authUserID']),// sai custom code
      "modified_date" => date('Y-m-d h:i:s'), //sai custom code
      
      "primary_business_entity" => trim(isset($_POST["primary_business_entity"]) ? $_POST["primary_business_entity"] : ''),
      "facility_npi" => trim(isset($_POST["facility_npi"]) ? $_POST["facility_npi"] : ''),
      "facility_taxonomy" => trim(isset($_POST["facility_taxonomy"]) ? $_POST["facility_taxonomy"] : ''),
      "facility_code" => trim(isset($_POST["facility_id"]) ? $_POST["facility_id"] : ''),
      "payTostreet" => trim(isset($_POST["payTostreet"]) ? $_POST["payTostreet"] : ''),
      "payTocity" => trim(isset($_POST["payTocity"]) ? $_POST["payTocity"] : ''),
      "payTostate" => trim(isset($_POST["payTostate"]) ? $_POST["payTostate"] : ''),
      "payTopostal_code" => trim(isset($_POST["payTopostal_code"]) ? $_POST["payTopostal_code"] : '')
      //Code added by Gangeya  for PAYEHR-532: pay to address for 837
    );
// sai custom code start
// Added by Gangeya : BUG ID 8302
/*if(trim($_POST['active']) == 'on')
{
  //print_r($_POST['active']);
  $fac_id = $_POST["fid"];
  echo "update facility set active = '1',modified_by = '".$_SESSION['authUserID']."',
                modified_date = '".date('Y-m-d h:i:s')."' where id='$fac_id'";
 //exit;
sqlStatement("update facility set active = '1',modified_by = '".$_SESSION['authUserID']."',
                modified_date = '".date('Y-m-d h:i:s')."' where id='$fac_id'" );

}*/
  // Modified by Sonali Dhumal on 15/1/2013 
  //Issue : Need an option to change, insert and delete the facility from the lookup
   // Close this window and redisplay the updated list of facility. 
  $facility =  $_POST['facility'];



 if($facility_add == "facility_add") {
  if ($facility || $_POST["mode"] == "facility") { 
    echo "<html><body><script language='JavaScript'>\n";
    echo " var myboss = opener ? opener : parent;\n";   
    echo " if (myboss.refreshFacility) myboss.refreshFacility('$facility','$fac_id');\n";
    echo " else if (myboss.reloadFacility) myboss.reloadFacility();\n";
    echo " else myboss.location.reload();\n";
    echo " if (parent.$ && parent.$.fancybox) parent.$.fancybox.close();\n";
    echo " else window.close();\n";
    echo "</script></body></html>\n"; 
    $facility_add=''; 
    }  
  }
 // sai custom code end 

 // print_r($newFacility);
    $facilityService->update($newFacility);

  

    // Update facility name for all users with this facility.
    // This is necassary because some provider based code uses facility name for lookups instead of facility id.
    //
    $facilityService->updateUsersFacility($newFacility['name'], $newFacility['fid']);
    exit(); // sjp 12/20/17 for ajax save
}

?>
<!DOCTYPE html >
<html>
<head>

<title><?php echo xlt("Facilities") ; ?></title>

    <?php Header::setupHeader(['common', 'jquery-ui']); ?>
<!-- sai custom code start -->
<script type="text/javascript">

function refreshme() {
    top.restoreSession();
   // document.location.reload();
    location.reload();
}
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



$(document).ready(function(){

    $(".medium_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 700, 590, '', '', {
            allowResize: false,
            allowDrag: true, // note these default to true if not defined here. left as example.
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $(".addfac_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 700, 620, '', '', {
            allowResize: false,
            allowDrag: true,
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

});

</script>
</head>

<body class="body_top">

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
            <div class="page-header clearfix">
                <h2 class="clearfix"><?php echo xlt("Facilities") ; ?></h2>
            </div>
            <a href="facilities_add.php?link=<?php echo $facility_add;?>" class="addfac_modal btn btn-default btn-add"><span><?php echo xlt('Add Facility');?></span></a><!-- sai custom code  -->
            </div>
        </div>
    <!-- sai custom code start -->
    <form name='facilitylist' method='post' action='facilities.php' onsubmit='return top.restoreSession()'>
    <input type='checkbox' name='form_inactive' value='1' onclick='submit()' <?php if ($_POST['form_inactive']) echo 'checked '; ?>/>
    <span class='text' style = "margin-left:-3px"> <?php xl('Include inactive facilities','e'); ?> </span>
</form>
<!-- sai custom code end -->
        <br>
        <div class="row">
            <div class="col-xs-12">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo xlt('Name'); ?></th>
                                <th><?php echo xlt('Address'); ?></th>
                                <th><?php echo xlt('Phone'); ?></th>
          <th style="border-style:1px solid #000"><?php xl('MedProvNum','e'); ?></th><!-- sai custom code  -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $fres = 0;
                         
                            $fres = $facilityService->getAll();
                        
                           
                            if ($fres) {
                                $result2 = array();
                                for ($iter3 = 0; $iter3 < sizeof($fres); $iter3++) {
                                    $result2[$iter3] = $fres[$iter3];
                                }

                                foreach ($result2 as $iter3) {
                                    $varstreet="";//these are assigned conditionally below,blank assignment is done so that old values doesn't get propagated to next level.
                                    $varcity="";
                                    $varstate="";
                                    $varstreet=$iter3["street"];
                                    if ($iter3["street"]!="") {
                                        $varstreet=$iter3["street"].",";
                                    }

                                    if ($iter3["city"]!="") {
                                        $varcity=$iter3["city"].",";
                                    }

                                    if ($iter3["state"]!="") {
                                        $varstate=$iter3["state"].",";
                                    }
                            ?>
                            <tr height="22">
                                 <td valign="top" class="text"><b><a href="facility_admin.php?link=<?php echo $facility_add;?>&fid=<?php echo attr($iter3["id"]); ?>" class="medium_modal"><span><?php echo text($iter3["name"]);?></span></a></b>&nbsp;</td><!-- sai custom code  -->
                                 <td valign="top" class="text"><?php echo text($varstreet.$varcity.$varstate.$iter3["country_code"]." ".$iter3["postal_code"]); ?>&nbsp;</td>
                                 <td><?php echo text($iter3["phone"]);?>&nbsp;</td>
                                <td><?php echo htmlspecialchars($iter3["MedProvNum"]);?>&nbsp;</td><!-- sai custom code start -->
                            </tr>
                            <?php
                                }
                            }

                            if (count($result2)<=0) {?>
                            <tr height="25">
                                <td colspan="3"  style="text-align:center;font-weight:bold;"> <?php echo xlt("Currently there are no facilities."); ?></td>
                            </tr>
                            <?php
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- end of div container -->
    <script language="JavaScript">
    <?php
    if ($alertmsg = trim($alertmsg)) {
        echo "alert('$alertmsg');\n";
    }
    ?>
    </script>

</body>
</html>
