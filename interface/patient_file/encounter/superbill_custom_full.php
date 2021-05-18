<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/options.inc.php");

// gacl control
$thisauthview = acl_check('admin', 'superbill', false, 'view');
$thisauthwrite = acl_check('admin', 'superbill', false, 'write');

if (!($thisauthwrite || $thisauthview)) {
    echo "<html>\n<body>\n";
    echo "<p>" . xlt('You are not authorized for this.') . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
}
// For revenue codes
$institutional = $GLOBALS['ub04_support'] == "1" ? true : false;

// Translation for form fields.
function ffescape($field)
{
    $field = add_escape_custom($field);
    return trim($field);
}

// Format dollars for display.
//
function bucks($amount)
{
    if ($amount) {
        $amount = oeFormatMoney($amount);
        return $amount;
    }

    return '';
}

$alertmsg = '';
$pagesize = 100;
$mode = $_POST['mode'];
$code_id = 0;
$related_code = '';
$active = 1;
$reportable = 0;
$financial_reporting = 0;
$revenue_code = '';
$pr_selector=''; // Sai custom code

if (isset($mode) && $thisauthwrite) {
    $code_id    = empty($_POST['code_id']) ? '' : $_POST['code_id'] + 0;
    $code       = $_POST['code'];
    $code_type  = $_POST['code_type'];
    $code_text  = $_POST['code_text'];
    $modifier   = $_POST['modifier'];
    $superbill  = $_POST['form_superbill'];
    $related_code = $_POST['related_code'];
    $cyp_factor = is_numeric($_POST['cyp_factor']) ? $_POST['cyp_factor'] + 0 : 0;
    $active     = empty($_POST['active']) ? 0 : 1;
    $reportable = empty($_POST['reportable']) ? 0 : 1; // dx reporting
    $financial_reporting = empty($_POST['financial_reporting']) ? 0 : 1; // financial service reporting
    $revenue_code = $_POST['revenue_code'];
// sai custom code start
  $delete_id=$_POST['delete_id'];
  $pr_selector = $_POST['pr_selector'];
  //BUG ID 11211 : to add EAA from front end
  $cpt_eaa = $_POST['cpt_eaa'];
  
  if(!empty($delete_id)){
   $code_id=implode("','",$delete_id);  
    $pr_selector_arr = array_keys($delete_id);
    $pr_selector = implode("','",$pr_selector_arr);
    }
    // sai custom code end  
    $taxrates = "";
    if (!empty($_POST['taxrate'])) {
        foreach ($_POST['taxrate'] as $key => $value) {
            $taxrates .= "$key:";
        }
    }

    if ($mode == "delete") {
// Sai custom code start
    if($pr_selector)    
    {
    $pr_selector = str_replace("0","''",$pr_selector);  
        sqlStatement("UPDATE prices set active=0 WHERE pr_id in ('$code_id') ");
    }
    else
    {   
         sqlStatement("UPDATE codes set active=0 WHERE id in ('$code_id') ");
         sqlStatement("DELETE FROM cpt_eaa WHERE codes_id in ('$code_id') "); //BUG ID 11211 : to add EAA from front end
    }
        $code_id = 0;
    $pr_selector=0;
    $delete_id="";
    $pr_selector_arr = "";
// Sai custom code end  
    } else if ($mode == "add" || $mode == "modify_complete") { // this covers both adding and modifying
// Sai custom code start
    $crow = sqlQuery("SELECT COUNT(*) AS count,active FROM codes WHERE " .
            "code_type = '"    . ffescape($code_type)    . "' AND " .
            "code = '"         . ffescape($code)         . "' AND " .
            "modifier = '"     . ffescape($modifier)     . "' AND " .
    "id != '$code_id' ");

    if ($crow['count']>0) {
    
    
        // **** Modified by Sonali : 10123 Same Procedure code. ****//
    
                
        $prow = sqlQuery("SELECT id,active from codes where code='$code' and code_type='$code_type' ");
        $code_id = $prow['id']; 
        if($prow['active']==0){
            $query = "UPDATE codes SET active=1 WHERE id = '$code_id'";
            sqlStatement($query);
        }
        else
        {       
            //BUG ID 11211 : to add EAA from front end
            $errow = sqlQuery("SELECT COUNT(*) AS count,active FROM cpt_eaa WHERE " .     
                            "codes_id = '"     . ffescape($code_id)     . "' ");

            $prrow = sqlQuery("SELECT COUNT(*) AS count,active FROM prices WHERE " .     
                            "pr_id = '"         . ffescape($code_id)         . "' AND " .
                            "pr_selector = '"     . ffescape($modifier)     . "' ");

            if($prrow['count']>0){
                if($prrow['active']==0){
                    $query = "DELETE from  prices WHERE pr_id = '$code_id' and pr_selector='$modifier'";
                    sqlStatement($query);
    
                    foreach ($_POST['fee'] as $key => $value) {
                        $value = $value + 0;
                        if ($value) {  
                            sqlStatement("INSERT INTO prices ( " . "pr_id, pr_selector, pr_level, pr_price,active ) VALUES ( " .
                                          "'$code_id', '$modifier', '$key', '$value','1' )");

                           
                        }
                    }
                }       
            }
            else if($errow['count']>0){
                if($errow['active']==0){
                    $queryd = "DELETE FROM cpt_eaa WHERE codes_id = '$code_id'"; //BUG ID 11211
                    sqlStatement($queryd);
                }
                else
                    $alertmsg = xl('Cannot add/update this entry because a duplicate already exists!'); 
            }
            else{   
                foreach ($_POST['fee'] as $key => $value) {
                    $value = $value + 0;
                    if ($value) {  
                        sqlStatement("INSERT INTO prices ( " . "pr_id, pr_selector, pr_level, pr_price,active ) VALUES ( " .
                                     "'$code_id', '$modifier', '$key', '$value','1' )");
                        
                    }
                }
                //BUG ID 11211 
                sqlStatement("INSERT INTO cpt_eaa ( " . "code, NP_EAA, codes_id, active ) VALUES ( " .
                                     "'$code', '$cpt_eaa', '$code_id', '1' )");

                 

                
                $code = $code_type = $code_text = $modifier = $superbill = $cpt_eaa = "";
                $code_id = 0;
                $related_code = '';
                $cyp_factor = 0;
                $taxrates = '';
                $active = 1;
                $reportable = 0;
            }
        }
        // Sai custom code end      
    }
    else {
            $sql =
                "code = '"         . ffescape($code)         . "', " .
                "code_type = '"    . ffescape($code_type)    . "', " .
                "code_text = '"    . ffescape($code_text)    . "', " .
                "modifier = '"     . ffescape($modifier)     . "', " .
                "superbill = '"    . ffescape($superbill)    . "', " .
                "related_code = '" . ffescape($related_code) . "', " .
                "cyp_factor = '"   . ffescape($cyp_factor)   . "', " .
                "taxrates = '"     . ffescape($taxrates)     . "', " .
                "active = "        . add_escape_custom($active) . ", " .
                "financial_reporting = " . add_escape_custom($financial_reporting) . ", " .
                "revenue_code = '" . ffescape($revenue_code) . "', " .
                "reportable = "    . add_escape_custom($reportable);
            if ($code_id) {
                $query = "UPDATE codes SET $sql WHERE id = ?";
                sqlStatement($query, array($code_id));
                sqlStatement("DELETE FROM prices WHERE pr_id = ? AND " .
                    "pr_selector = ''", array($code_id));
            sqlStatement("DELETE FROM cpt_eaa WHERE codes_id = '$code_id'"); //BUG ID 11211 // Sai custom code
             

                ////////////////////////////////
            } else {
                $code_id = sqlInsert("INSERT INTO codes SET $sql");
                
            }

            if (!$alertmsg) {
                foreach ($_POST['fee'] as $key => $value) {
                    $value = $value + 0;
                    if ($value) {
                        sqlStatement("INSERT INTO prices ( " .
                                "pr_id, pr_selector, pr_level, pr_price,active ) VALUES ( " .
                                "'$code_id', '', '$key', '$value','1' )");// Sai custom code

                        
                    }
                }
            //BUG ID 11211 
            sqlStatement("INSERT INTO cpt_eaa ( " . "code, NP_EAA, codes_id, active ) VALUES ( " .
                                     "'$code', '$cpt_eaa', '$code_id', '1' )");// Sai custom code

            

                $code = $code_type = $code_text = $modifier = $superbill = $cpt_eaa = "";// Sai custom code
                $code_id = 0;
                $related_code = '';
                $cyp_factor = 0;
                $taxrates = '';
                $active = 1;
                $reportable = 0;
                $revenue_code = '';
            }
        }
    } else if ($mode == "edit") { // someone clicked [Edit]
  // Sai custom code start
    if($pr_selector==='0')
        $sql = "SELECT codes.*,if(cpt_eaa.codes_id = '$code_id', cpt_eaa.NP_EAA, '') as NP_EAA FROM codes left join cpt_eaa on cpt_eaa.code = codes.code 
                WHERE codes.id = '$code_id'";
        
    else{
        $sql = "SELECT codes.*,prices.pr_id,prices.pr_selector, if(cpt_eaa.codes_id = '$code_id', cpt_eaa.NP_EAA, '') as NP_EAA
                FROM codes left join prices on prices.pr_id = codes.id left join cpt_eaa on cpt_eaa.codes_id = codes.id
                WHERE codes.id=prices.pr_id and codes.id = ? and codes.code = cpt_eaa.code";
        }
        //BUG ID 11211 
        //$sql = "SELECT codes.*,prices.pr_id,prices.pr_selector FROM codes,prices WHERE codes.id=prices.pr_id and id = '$code_id'";
    
// Sai custom code end  
        $results = sqlStatement($sql, array($code_id));
        while ($row = sqlFetchArray($results)) {
            $code         = $row['code'];
            $code_text    = $row['code_text'];
            $code_type    = $row['code_type'];
            $modifier     = $row['modifier'];
      // Sai custom code start
      if($row['pr_selector'])
      $pr_selector = $row['pr_selector'];
      else
      $pr_selector = $row['modifier'];
      // Sai custom code end
            // $units        = $row['units'];
            $superbill    = $row['superbill'];
            $related_code = $row['related_code'];
            $revenue_code = $row['revenue_code'];
            $cyp_factor   = $row['cyp_factor'];
            $taxrates     = $row['taxrates'];
            $active       = 0 + $row['active'];
            $reportable   = 0 + $row['reportable'];
            $financial_reporting  = 0 + $row['financial_reporting'];
            $cpt_eaa        = $row['NP_EAA']; //BUG ID 11211 // Sai custom code
        }
    } else if ($mode == "modify") { // someone clicked [Modify]
        // this is to modify external code types, of which the modifications
        // are stored in the codes table
        $code_type_name_external = $_POST['code_type_name_external'];
        $code_external = $_POST['code_external'];
        $code_id = $_POST['code_id'];
        $results = return_code_information($code_type_name_external, $code_external, false); // only will return one item
        while ($row = sqlFetchArray($results)) {
            $code         = $row['code'];
            $code_text    = $row['code_text'];
            $code_type    = $code_types[$code_type_name_external]['id'];
            $modifier     = $row['modifier'];
            // $units        = $row['units'];
            $superbill    = $row['superbill'];
            $related_code = $row['related_code'];
            $revenue_code = $row['revenue_code'];
            $cyp_factor   = $row['cyp_factor'];
            $taxrates     = $row['taxrates'];
            $active       = $row['active'];
            $reportable   = $row['reportable'];
            $financial_reporting  = $row['financial_reporting'];
        }
    }

    // If codes history is enabled in the billing globals save data to codes history table
    if ($GLOBALS['save_codes_history'] && $alertmsg=='' &&
        ( $mode == "add" || $mode == "modify_complete" || $mode == "delete" ) ) {
        $action_type= empty($_POST['code_id']) ? 'new' : $mode;
        $action_type= ($action_type=='add') ? 'update' : $action_type ;
        $code       = $_POST['code'];
        $code_type  = $_POST['code_type'];
        $code_text  = $_POST['code_text'];
        $modifier   = $_POST['modifier'];
        $superbill  = $_POST['form_superbill'];
        $related_code = $_POST['related_code'];
        $revenue_code = $_POST['revenue_code'];
        $cyp_factor = $_POST['cyp_factor'] + 0;
        $active     = empty($_POST['active']) ? 0 : 1;
        $reportable = empty($_POST['reportable']) ? 0 : 1; // dx reporting
        $financial_reporting = empty($_POST['financial_reporting']) ? 0 : 1; // financial service reporting
        $fee=json_encode($_POST['fee']);
        $code_sql= sqlFetchArray(sqlStatement("SELECT (ct_label) FROM code_types WHERE ct_id=?", array($code_type)));
        $code_name='';

        if ($code_sql) {
            $code_name=$code_sql['ct_label'];
        }

        $categorey_id= $_POST['form_superbill'];
        $categorey_sql=sqlFetchArray(sqlStatement("SELECT (title ) FROM list_options WHERE list_id='superbill'".
            " AND option_id=?", array($categorey_id)));

        $categorey_name='';

        if ($categorey_sql) {
            $categorey_name=$categorey_sql['title'];
        }

        $date=date('Y-m-d H:i:s');
        $date=oeFormatShortDate($date);
        $results =  sqlStatement(
            "INSERT INTO codes_history ( " .
            "date, code, modifier, active,diagnosis_reporting,financial_reporting,category,code_type_name,".
            "code_text,code_text_short,prices,action_type, update_by ) VALUES ( " .
            "?, ?,? ,? ,? ,? ,? ,? ,? ,? ,? ,? ,?)",
            array($date,$code,$modifier,$active,$reportable,$financial_reporting,$categorey_name,$code_name,$code_text,'',$fee,$action_type,$_SESSION['authUser'])
        );
    }
}

$related_desc = '';
if (!empty($related_code)) {
    $related_desc = $related_code;
}

$fstart = $_REQUEST['fstart'] + 0;
if (isset($_REQUEST['filter'])) {
    $filter = array();
    $filter_key = array();
    foreach ($_REQUEST['filter'] as $var) {
        $var = $var+0;
        array_push($filter, $var);
        $var_key = convert_type_id_to_key($var);
        array_push($filter_key, $var_key);
    }
}

$search = $_REQUEST['search'];
$search_reportable = $_REQUEST['search_reportable'];
$search_financial_reporting = $_REQUEST['search_financial_reporting'];
// sai custom code start
$bill_filter = $_REQUEST['bill_filter'] + 0;

$where = "1 = 1 and codes.active=1  ";
if ($filter) {
  $where .= " AND code_type = '$filter[0]'";
  if($bill_filter == 1){
     $where .= " AND id in (select pr_id from prices) ";
  }
  if($bill_filter == 2){
     $where .= " AND id not in (select pr_id from prices) ";
  }
}
if (!empty($search)) {
  $where .= " AND code LIKE '" . ffescape($search) . "%'";
}

$crow = sqlQuery("SELECT count(*) AS count FROM codes left join (select * from prices group by pr_id,pr_selector) as prices on codes.id=prices.pr_id and prices.active=1  WHERE $where");
$count = $crow['count'];
// sai custom code end
if ($fstart < 0) {
    $fstart = 0;
}

$fend = $fstart + $pagesize;
if ($fend > $count) {
    $fend = $count;
}
?>

<html>
<head>
    <title><?php echo xlt("Codes"); ?></title>
    <?php html_header_show(); ?>

    <link rel="stylesheet" href="<?php echo attr($css_header);?>" type="text/css">
    <script type="text/javascript" src="../../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="../../../library/textformat.js"></script>
    <script type="text/JavaScript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
    <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-ui-1-12-1/themes/base/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui-1-12-1/jquery-ui.min.js"></script>
<style>
    .ui-autocomplete { max-height: 350px; max-width: 35%; overflow-y: auto; overflow-x: hidden; }
</style>
    <script>
    <?php if ($institutional) { ?>
    $( function() {
        var cache = {};
        $( ".revcode" ).autocomplete({
            minLength: 1,
            source: function( request, response ) {
                var term = request.term;
                request.code_group = "revenue_code";
                if ( term in cache ) {
                  response( cache[ term ] );
                  return;
                }
                $.getJSON( "<?php echo $GLOBALS['web_root'] ?>/interface/billing/ub04_helpers.php", request, function( data, status, xhr ) {
                  cache[ term ] = data;
                  response( data );
                });
            }
        }).dblclick(function(event) {
            $(this).autocomplete('search'," ");
        });
    });
    <?php } ?>

        // This is for callback by the find-code popup.
        // Appends to or erases the current list of related codes.
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0];
            var s = f.related_code.value;
            if (code) {
                if (s.length > 0) s += ';';
                s += codetype + ':' + code;
            } else {
                s = '';
            }
            f.related_code.value = s;
            f.related_desc.value = s;
        }

        // This is for callback by the find-code popup.
        // Returns the array of currently selected codes with each element in codetype:code format.
        function get_related() {
            return document.forms[0].related_code.value.split(';');
        }

        // This is for callback by the find-code popup.
        // Deletes the specified codetype:code from the currently selected list.
        function del_related(s) {
            my_del_related(s, document.forms[0].related_code, false);
            my_del_related(s, document.forms[0].related_desc, false);
        }

        // This invokes the find-code popup.
        function sel_related() {
            var f = document.forms[0];
            var i = f.code_type.selectedIndex;
            var codetype = '';
            if (i >= 0) {
                var myid = f.code_type.options[i].value;
                <?php
                foreach ($code_types as $key => $value) {
                    $codeid = $value['id'];
                    $coderel = $value['rel'];
                    if (!$coderel) {
                        continue;
                    }

                    echo "  if (myid == $codeid) codetype = '$coderel';";
                }
                ?>
            }
            if (!codetype) {
                alert('<?php echo addslashes(xl('This code type does not accept relations.')); ?>');
                return;
            }
            dlgopen('find_code_dynamic.php', '_blank', 900, 600);
        }

        // Some validation for saving a new code entry.
        function validEntry(f) {
            if (!f.code.value) {
                alert('<?php echo addslashes(xl('No code was specified!')); ?>');
                return false;
            }
            <?php if ($GLOBALS['ippf_specific']) { ?>
            if (f.code_type.value == 12 && !f.related_code.value) {
                alert('<?php echo addslashes(xl('A related IPPF code is required!')); ?>');
                return false;
            }
            <?php } ?>
            return true;
        }

        function submitAdd() {
            var f = document.forms[0];
            if (!validEntry(f)) return;
            f.mode.value = 'add';
            f.code_id.value = '';
            f.submit();
        }

        function submitUpdate() {
            var f = document.forms[0];
            if (! parseInt(f.code_id.value)) {
                alert('<?php echo addslashes(xl('Cannot update because you are not editing an existing entry!')); ?>');
                return;
            }
            if (!validEntry(f)) return;
            f.mode.value = 'add';
            f.submit();
        }

        function submitModifyComplete() {
            var f = document.forms[0];
            f.mode.value = 'modify_complete';
            f.submit();
        }

        function submitList(offset) {
            var f = document.forms[0];
            var i = parseInt(f.fstart.value) + offset;
            if (i < 0) i = 0;
            f.fstart.value = i;
            f.submit();
        }

        function submitEdit(id) {
            var f = document.forms[0];
            f.mode.value = 'edit';
            f.code_id.value = id;
            f.submit();
        }

        function submitModify(code_type_name,code,id) {
            var f = document.forms[0];
            f.mode.value = 'modify';
            f.code_external.value = code;
            f.code_id.value = id;
            f.code_type_name_external.value = code_type_name;
            f.submit();
        }



        function submitDelete(id) {
            var f = document.forms[0];
            f.mode.value = 'delete';
            f.code_id.value = id;
            f.submit();
        }

        function getCTMask() {
            var ctid = document.forms[0].code_type.value;
            <?php
            foreach ($code_types as $key => $value) {
                $ctid   = attr($value['id']);
                $ctmask = attr($value['mask']);
                echo " if (ctid == '$ctid') return '$ctmask';\n";
            }
            ?>
            return '';
        }

// sai custom code start
// This is to allow selection of all diagnosis entries in form for deletion.
function selectAll() {
    if(document.getElementById("checkAll").checked==true) {
        document.getElementById("checkAll").checked=true;<?php
        for($i = 1; $i <= $count; $i++) {
            echo "document.getElementById(\"check$i\").checked=true; "; 
        } ?>
    }
    else {
        document.getElementById("checkAll").checked=false;<?php
        for($i = 1; $i <= $count; $i++) {
            echo "document.getElementById(\"check$i\").checked=false; "; 
        } ?>
    }
}
// sai custom code end
    </script>

</head>
<body class="body_top" >

<form method='post' action='superbill_custom_full.php' name='theform'>

    <input type='hidden' name='mode' value=''>

    <br>

    <center>
        <table border='0' cellpadding='0' cellspacing='0'>

            <tr>
                <td colspan="3"> <?php echo xlt('Not all fields are required for all codes or code types.'); ?><br><br></td>
            </tr>
<?php
// sai custom code start
// code added for ICD-10 Code activation by pawan
$qry_icd10 = "select * from globals where gl_name='enable_icd10' ";
$icd10_res = sqlStatement($qry_icd10);
$icd10_row = sqlFetchArray($icd10_res);

$icd10_active = $icd10_row['gl_value'];

if(empty($icd10_active)){
    unset($code_types[ICD10]);
}

// sai custom code end
?>
            <tr>
                <td><?php echo xlt('Type'); ?>:</td>
                <td width="5">
                </td>
                <td>

                    <?php if ($mode != "modify") { ?>
                    <select name="code_type">
                        <?php } ?>

                        <?php $external_sets = array(); ?>
                        <?php foreach ($code_types as $key => $value) { ?>
                            <?php if (!($value['external'])) { ?>
                                <?php if ($mode != "modify") { ?>
                                    <option value="<?php  echo attr($value['id']) ?>"<?php if ($code_type == $value['id']) {
                                        echo " selected";
} ?>><?php echo xlt($key) ?></option>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($value['external']) {
                                array_push($external_sets, $key);
} ?>
                        <?php } // end foreach ?>

                        <?php if ($mode != "modify") { ?>
                    </select>
                <?php } ?>

                    <?php if ($mode == "modify") { ?>
                        <input type='text' size='4' name='code_type' readonly='readonly' style='display:none' value='<?php echo attr($code_type) ?>' />
                        <?php echo attr($code_type_name_external) ?>
                    <?php } ?>

                    &nbsp;&nbsp;
                    <?php echo xlt('Code'); ?>:

                    <?php if ($mode == "modify") { ?>
                        <input type='text' size='6' name='code' readonly='readonly' value='<?php echo attr($code) ?>' />
                    <?php } else { ?>
                        <input type='text' size='6' name='code' value='<?php echo attr($code) ?>'
                               onkeyup='maskkeyup(this,getCTMask())'
                               onblur='maskblur(this,getCTMask())'
                        />
                    <?php } ?>

                    <?php if (modifiers_are_used()) { ?>
                        &nbsp;&nbsp;<?php echo xlt('Modifier'); ?>:
                        <?php if ($mode == "modify") { ?>
                            <input type='text' size='6' name='modifier' readonly='readonly' value='<?php echo attr($modifier) ?>'>
                        <?php } else { ?>
                            <input type='text' size='6' name='modifier' value='<?php echo attr($modifier) ?>'>
                        <?php } ?>
                    <?php } else { ?>
                        <input type='hidden' name='modifier' value=''>
                    <?php } ?>

                    &nbsp;&nbsp;
                    <input type='checkbox' name='active' value='1'<?php if (!empty($active) || ($mode == 'modify' && $active == null)) {
                        echo ' checked';
} ?> />
                    <?php echo xlt('Active'); ?>
                </td>
            </tr>

            <tr>
                <td><?php echo xlt('Description'); ?>:</td>
                <td></td>
                <td>
                    <?php if ($mode == "modify") { ?>
                        <input type='text' size='50' name="code_text" readonly="readonly" value='<?php echo attr($code_text) ?>'>
                    <?php } else { ?>
                        <input type='text' size='50' name="code_text" value='<?php echo attr($code_text) ?>'>
                    <?php } ?>
                <?php if ($institutional) { ?>
                    <?php echo xlt('Revenue Code'); ?>:
                    <?php if ($mode == "modify") { ?>
                        <input type='text' size='6' name="revenue_code" readonly="readonly" value='<?php echo attr($revenue_code) ?>'>
                    <?php } else { ?>
                        <input type='text' size='6' class='revcode' name="revenue_code" title='<?php echo xla('Type to search and select revenue code'); ?>' value='<?php echo attr($revenue_code) ?>'>
                    <?php } ?>
                <?php } ?>
                </td>
            </tr>

            <tr>
                <td><?php echo xlt('Category'); ?>:</td>
                <td></td>
                <td>
                    <?php
                    generate_form_field(array('data_type'=>1,'field_id'=>'superbill','list_id'=>'superbill'), $superbill);
                    ?>
                    &nbsp;&nbsp;
                    <input type='checkbox' title='<?php echo xlt("Syndromic Surveillance Report") ?>' name='reportable' value='1'<?php if (!empty($reportable)) {
                        echo ' checked';
} ?> />
                    <?php echo xlt('Diagnosis Reporting'); ?>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <input type='checkbox' title='<?php echo xlt("Service Code Finance Reporting") ?>' name='financial_reporting' value='1'<?php if (!empty($financial_reporting)) {
                        echo ' checked';
} ?> />
                    <?php echo xlt('Service Reporting'); ?>
                </td>
            </tr>

            <tr<?php if (empty($GLOBALS['ippf_specific'])) {
                echo " style='display:none'";
} ?>>
                <td><?php echo xlt('CYP Factor'); ?>:</td>
                <td></td>
                <td>
                    <input type='text' size='10' maxlength='20' name="cyp_factor" value='<?php echo attr($cyp_factor) ?>'>
                </td>
            </tr>


            <tr<?php if (!related_codes_are_used()) {
                echo " style='display:none'";
} ?>>
                <td><?php echo xlt('Relate To'); ?>:</td>
                <td></td>
                <td>
                    <input type='text' size='50' name='related_desc'
                           value='<?php echo attr($related_desc) ?>' onclick="sel_related()"
                           title='<?php echo xla('Click to select related code'); ?>' readonly />
                    <input type='hidden' name='related_code' value='<?php echo attr($related_code) ?>' />
                </td>
            </tr>

            <tr>
                <td><?php echo xlt('Fees'); ?>:</td>
                <td></td>
                <td>
                    <?php
                    $pres = sqlStatement("SELECT lo.option_id, lo.title, p.pr_price " .
                        "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
                        "p.pr_id = ? AND p.pr_selector = '' AND p.pr_level = lo.option_id " .
                        "WHERE lo.list_id = 'pricelevel' AND lo.activity = 1 ORDER BY lo.seq, lo.title", array($code_id));
                    for ($i = 0; $prow = sqlFetchArray($pres); ++$i) {
                        if ($i) {
                            echo "&nbsp;&nbsp;";
                        }

                        echo text(xl_list_label($prow['title'])) . " ";
                        echo "<input type='text' size='6' name='fee[" . attr($prow['option_id']) . "]' " .
                            "value='" . attr($prow['pr_price']) . "' >\n";
                    }
                    ?>
        <!--BUG ID 11211 -->
        <?php echo "EAA : ";?>
        <input type="text" size="6" name="cpt_eaa" value='<?php echo $cpt_eaa ?>'> <!-- Sai custom code start -->
                </td>
            </tr>

            <?php
            $taxline = '';
            $pres = sqlStatement("SELECT option_id, title FROM list_options " .
                "WHERE list_id = 'taxrate' AND activity = 1 ORDER BY seq");
            while ($prow = sqlFetchArray($pres)) {
                if ($taxline) {
                    $taxline .= "&nbsp;&nbsp;";
                }

                $taxline .= "<input type='checkbox' name='taxrate[" . attr($prow['option_id']) . "]' value='1'";
                if (strpos(":$taxrates", $prow['option_id']) !== false) {
                    $taxline .= " checked";
                }

                $taxline .= " />\n";
                $taxline .=  text(xl_list_label($prow['title'])) . "\n";
            }

            if ($taxline) {
                ?>
                <tr>
                    <td><?php echo xlt('Taxes'); ?>:</td>
                    <td></td>
                    <td>
                        <?php echo $taxline ?>
                    </td>
                </tr>
            <?php
            } ?>

            <tr>
                <td colspan="3" align="center">
                    <input type="hidden" name="code_id" value="<?php echo attr($code_id) ?>"><br>
                    <input type="hidden" name="code_type_name_external" value="<?php echo attr($code_type_name_external) ?>">
                    <input type="hidden" name="code_external" value="<?php echo attr($code_external) ?>">
                    <?php if ($thisauthwrite) { ?>
                        <?php if ($mode == "modify") { ?>
                            <a href='javascript:submitModifyComplete();' class='link'>[<?php echo xlt('Update'); ?>]</a>
                        <?php } else { ?>
                            <a href='javascript:submitUpdate();' class='link'>[<?php echo xlt('Update'); ?>]</a>
                            &nbsp;&nbsp;
                            <a href='javascript:submitAdd();' class='link'>[<?php echo xlt('Add as New'); ?>]</a>
                        <?php } ?>
                    <?php } ?>
                </td>
            </tr>
        </table>
        <br>
        <table border='0' cellpadding='5' cellspacing='0' width='96%'>
            <tr>

                <td class='text'>
                    <select name='filter[]' multiple='multiple'>
                        <?php
                        foreach ($code_types as $key => $value) {
                            echo "<option value='" . attr($value['id']) . "'";
                            if (isset($filter) && in_array($value['id'], $filter)) {
                                echo " selected";
                            }

                            echo ">" . xlt($key) . "</option>\n";
                        }
                        ?>
                    </select>
                    &nbsp;&nbsp;&nbsp;&nbsp;
<!-- Sai custom code start -->
    <select name='bill_filter' onchange='submitBill(0)'>
    <option value='0' <?php if($bill_filter==0) echo "selected"; ?> >All</option>
    <option value='1' <?php if($bill_filter==1) echo "selected"; ?> >Billable</option>
    <option value='2' <?php if($bill_filter==2) echo "selected"; ?> >NonBillable</option>
   </select>


                    <input type="text" name="search" size="5" value="<?php echo attr($search) ?>">&nbsp;
                    <input type="submit" name="go" value='<?php echo xla('Search'); ?>'>&nbsp;&nbsp;
                    <input type='checkbox' title='<?php echo xlt("Only Show Diagnosis Reporting Codes") ?>' name='search_reportable' value='1'<?php if (!empty($search_reportable)) {
                        echo ' checked';
} ?> />
                    <?php echo xlt('Diagnosis Reporting Only'); ?>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <input type='checkbox' title='<?php echo xlt("Only Show Service Code Finance Reporting Codes") ?>' name='search_financial_reporting' value='1'<?php if (!empty($search_financial_reporting)) {
                        echo ' checked';
} ?> />
                    <?php echo xlt('Service Reporting Only'); ?>
                    <input type='hidden' name='fstart' value='<?php echo attr($fstart) ?>'>
   <input type='hidden' name='pr_selector' value='<?php echo $pr_selector;?>' id='pr_selector'>
    <!-- Sai custom code end -->
                </td>

                <td class='text' align='right'>
                    <?php if ($fstart) { ?>
                        <a href="javascript:submitList(<?php echo attr($pagesize) ?>)">
                            &lt;&lt;
                        </a>
                        &nbsp;&nbsp;
                    <?php } ?>
                    <?php echo ($fstart + 1) . " - $fend of $count" ?>
                    &nbsp;&nbsp;
                    <a href="javascript:submitList(<?php echo attr($pagesize) ?>)">
                        &gt;&gt;
                    </a>
                </td>

            </tr>
        </table>

</form>

<table border='0' cellpadding='5' cellspacing='0' width='96%'>
    <tr>
        <td><span class='bold'><?php echo xlt('Code'); ?></span></td>
        <td><span class='bold'><?php echo xlt('Mod'); ?></span></td>
        <?php if ($institutional) { ?>
            <td><span class='bold'><?php echo xlt('Revenue'); ?></span></td>
        <?php } ?>
        <td><span class='bold'><?php echo xlt('Act'); ?></span></td>
        <td><span class='bold'><?php echo xlt('Dx Rep'); ?></span></td>
        <td><span class='bold'><?php echo xlt('Serv Rep'); ?></span></td>
        <td><span class='bold'><?php echo xlt('Type'); ?></span></td>
        <td><span class='bold'><?php echo xlt('Description'); ?></span></td>
        <td><span class='bold'><?php echo xlt('Short Description'); ?></span></td>
        <?php if (related_codes_are_used()) { ?>
            <td><span class='bold'><?php echo xlt('Related'); ?></span></td>
        <?php } ?>
        <?php
        $pres = sqlStatement("SELECT title FROM list_options " .
            "WHERE list_id = 'pricelevel' AND activity = 1 ORDER BY seq, title");
        while ($prow = sqlFetchArray($pres)) {
            echo "  <td class='bold' align='right' nowrap>" . text(xl_list_label($prow['title'])) . "</td>\n";
        }
        ?>
        <td></td>
  <td><input type=checkbox id='checkAll' onclick='selectAll()'><?php echo"<a class='link' href='javascript:submitDelete()'>[" . xl('Delete') . "]</a>"; ?></td> <!-- Sai custom code  -->
        <td></td>
    </tr>
    <?php

   /* if (isset($_REQUEST['filter'])) {
        $res = main_code_set_search($filter_key, $search, null, null, false, null, false, $fstart, ($fend - $fstart), $filter_elements);
    }*/
    if (isset($_REQUEST['filter'])) {
         $res = sqlStatement("SELECT codes.*,prices.pr_selector,prices.pr_id,prices.active FROM codes left join (select * from prices group by pr_id,pr_selector) as prices on codes.id=prices.pr_id and prices.active=1   WHERE $where " .
      " ORDER BY code_type, code, code_text LIMIT $fstart, " . ($fend));
        $test = "SELECT codes.*,prices.pr_selector,prices.pr_id,prices.active FROM codes left join (select * from prices group by pr_id,pr_selector) as prices on codes.id=prices.pr_id and prices.active=1   WHERE $where " .
      " ORDER BY code_type, code, code_text";
    }

    for ($i = 0; $row = sqlFetchArray($res);
    $i++) {
        $all[$i] = $row;
    }

    if (!empty($all)) {
        $count = 0;
        foreach ($all as $iter) {
            $count++;

            $has_fees = false;
            foreach ($code_types as $key => $value) {
                if ($value['id'] == $iter['code_type']) {
                    $has_fees = $value['fee'];
                    break;
                }
            }
 // Sai custom code start 
    echo " <tr id='row$count'>\n";
            echo "  <td class='text'>" . text($iter["code"]) . "</td>\n";
        echo "  <td class='text'>" . ($iter["pr_selector"] ? $iter["pr_selector"] : ($iter["modifier"] ? $iter["modifier"] : ""))  . "</td>\n";
         
            if ($institutional) {
                echo "  <td class='text'>" . ($iter['revenue_code'] > '' ? text($iter['revenue_code']) : 'none') ."</td>\n";
            }
            if ($iter["code_external"] > 0) {
                // If there is no entry in codes sql table, then default to active
                //  (this is reason for including NULL below)
                echo "  <td class='text'>" . ( ($iter["active"] || $iter["active"]==null) ? xlt('Yes') : xlt('No')) . "</td>\n";
            } else {
                echo "  <td class='text'>" . ( ($iter["active"]) ? xlt('Yes') : xlt('No')) . "</td>\n";
            }

            echo "  <td class='text'>" . ($iter["reportable"] ? xlt('Yes') : xlt('No')) . "</td>\n";
               echo "  <td class='text'>$key</td>\n";
        echo "  <td class='text'>" . ($iter["financial_reporting"] ? xlt('Yes') : xlt('No')) . "</td>\n";
            echo "  <td class='text'>" . text($iter['code_type_name']) . "</td>\n";
            echo "  <td class='text'>" . text($iter['code_text']) . "</td>\n";
            echo "  <td class='text'>" . text($iter['code_text_short']) . "</td>\n";
// Sai custom code end
            if (related_codes_are_used()) {
                // Show related codes.
                echo "  <td class='text'>";
                $arel = explode(';', $iter['related_code']);
                foreach ($arel as $tmp) {
                    list($reltype, $relcode) = explode(':', $tmp);
                    $code_description = lookup_code_descriptions($reltype.":".$relcode);
                    echo text($relcode) . ' ' . text(trim($code_description)) . '<br />';
                }

                echo "</td>\n";
            }

            $pres = sqlStatement("SELECT p.pr_price " .
                "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
                "p.pr_id = ? AND p.pr_selector = '' AND p.pr_level = lo.option_id " .
                "WHERE lo.list_id = 'pricelevel' and p.pr_selector = '".$iter["pr_selector"]."' AND lo.activity = 1 ORDER BY lo.seq", array($iter['id']));
            while ($prow = sqlFetchArray($pres)) {
                echo "<td class='text' align='right'>" . text(bucks($prow['pr_price'])) . "</td>\n";
            }
    $pr_selector_arr = ($iter['pr_selector'] ? $iter['pr_selector'] : '0');
    echo "  <td align='right'><input type=checkbox id='check$count' name=\"delete_id[".$pr_selector_arr."]\" value=\"" .
                  htmlspecialchars( $iter['id'] , ENT_QUOTES) . "\" ></td>\n";  
             
        echo "  <td align='right'><a class='link' href='javascript:submitEdit("   . $iter['id'] . ",\"$pr_selector_arr\")'>[" . xl('Edit') . "]</a></td>\n";
      
            if ($thisauthwrite) {
                if ($iter["code_external"] > 0) {
                    echo "  <td align='right'><a class='link' href='javascript:submitModify(\"" . attr($iter['code_type_name']) . "\",\"" . attr($iter['code']) . "\",\"" . attr($iter['id']) . "\")'>[" . xlt('Modify') . "]</a></td>\n";
                } else {
                    echo "  <td align='right'><a class='link' href='javascript:submitDelete(" . attr($iter['id']) . ")'>[" . xlt('Delete') . "]</a></td>\n";
                    echo "  <td align='right'><a class='link' href='javascript:submitEdit(" . attr($iter['id']) . ")'>[" . xlt('Edit') . "]</a></td>\n";
                }
            }

            echo " </tr>\n";
// sai custom code end
        }
    }

    ?>

</table>

</center>

<script language="JavaScript">
    <?php
    if ($alertmsg) {
        echo "alert('" . addslashes($alertmsg) . "');\n";
    }
    ?>
</script>

</body>
</html>
