<?php
require_once("/custom/code_types.inc.php");

function echoLine($lino, $codetype, $code, $modifier, $ndc_info='',
  $auth = TRUE, $del = FALSE, $units = NULL, $fee = NULL, $id = NULL,
  $billed = FALSE, $code_text = NULL, $justify = NULL, $provider_id = 0)
{
  global $code_types, $ndc_applies, $ndc_uom_choices, $justinit, $pid;
  global $contraception, $usbillstyle;

  if ($codetype == 'COPAY') {
    if (!$code_text) $code_text = 'Cash';
    if ($fee > 0) $fee = 0 - $fee;
  }
  if (! $code_text) {
    $query = "select id, units, code_text from codes where code_type = '" .
      $code_types[$codetype]['id'] . "' and " .
      "code = '$code' and ";
    if ($modifier) {
      $query .= "modifier = '$modifier'";
    } else {
      $query .= "(modifier is null or modifier = '')";
    }
    $result = sqlQuery($query);
    $code_text = $result['code_text'];
    if (empty($units)) $units = max(1, intval($result['units']));
    if (!isset($fee)) {
      // Fees come from the prices table now.
      $query = "SELECT prices.pr_price " .
        "FROM patient_data, prices WHERE " .
        "patient_data.pid = '$pid' AND " .
        "prices.pr_id = '" . $result['id'] . "' AND " .
        "prices.pr_selector = '' AND " .
        "prices.pr_level = patient_data.pricelevel " .
        "LIMIT 1";
      echo "\n<!-- $query -->\n"; // debugging
      $prrow = sqlQuery($query);
      $fee = empty($prrow) ? 0 : $prrow['pr_price'];
    }
  }
  $fee = sprintf('%01.2f', $fee);
  if (empty($units)) $units = 1;
  $units = max(1, intval($units));
  // We put unit price on the screen, not the total line item fee.
  $price = $fee / $units;
  $strike1 = ($id && $del) ? "<strike>" : "";
  $strike2 = ($id && $del) ? "</strike>" : "";
  echo " <tr>\n";
  echo "  <td class='billcell'>$strike1" .
    ($codetype == 'COPAY' ? xl($codetype) : $codetype) . $strike2;
  if ($id) {
    echo "<input type='hidden' name='bill[$lino][id]' value='$id'>";
  }
  echo "<input type='hidden' name='bill[$lino][code_type]' value='$codetype'>";
  echo "<input type='hidden' name='bill[$lino][code]' value='$code'>";
  echo "<input type='hidden' name='bill[$lino][billed]' value='$billed'>";
  echo "</td>\n";
  if ($codetype != 'COPAY') {
    echo "  <td class='billcell'>$strike1$code$strike2</td>\n";
  } else {
    echo "  <td class='billcell'>&nbsp;</td>\n";
  }
  if ($billed) {
    if (modifiers_are_used(true)) {
      echo "  <td class='billcell'>$strike1$modifier$strike2" .
        "<input type='hidden' name='bill[$lino][mod]' value='$modifier'></td>\n";
    }
    if (fees_are_used()) {
      echo "  <td class='billcell' align='right'>" . oeFormatMoney($price) . "</td>\n";
      if ($codetype != 'COPAY') {
        echo "  <td class='billcell' align='center'>$units</td>\n";
      } else {
        echo "  <td class='billcell'>&nbsp;</td>\n";
      }
      echo "  <td class='billcell' align='center'$usbillstyle>$justify</td>\n";
    }

    // Show provider for this line.
    echo "  <td class='billcell' align='center'>";
    genProviderSelect('', '-- Default --', $provider_id, true);
    echo "</td>\n";
    echo "  <td class='billcell' align='center'$usbillstyle><input type='checkbox'" .
      ($auth ? " checked" : "") . " disabled /></td>\n";
    echo "  <td class='billcell' align='center'><input type='checkbox'" .
      " disabled /></td>\n";
  }
  else { // not billed
    if (modifiers_are_used(true)) {
      if ($codetype != 'COPAY' && ($code_types[$codetype]['mod'] || $modifier)) {
        echo "  <td class='billcell'><input type='text' name='bill[$lino][mod]' " .
          "value='$modifier' size='" . $code_types[$codetype]['mod'] . "'></td>\n";
      } else {
        echo "  <td class='billcell'>&nbsp;</td>\n";
      }
    }
    if (fees_are_used()) {
      if ($codetype == 'COPAY' || $code_types[$codetype]['fee'] || $fee != 0) {
        echo "  <td class='billcell' align='right'>" .
          "<input type='text' name='bill[$lino][price]' " .
          "value='$price' size='6'";
        if (acl_check('acct','disc'))
          echo " style='text-align:right'";
        else
          echo " style='text-align:right;background-color:transparent' readonly";
        echo "></td>\n";
        echo "  <td class='billcell' align='center'>";
        if ($codetype != 'COPAY') {
          echo "<input type='text' name='bill[$lino][units]' " .
          "value='$units' size='2' style='text-align:right'>";
        } else {
          echo "<input type='hidden' name='bill[$lino][units]' value='$units'>";
        }
        echo "</td>\n";
        if ($code_types[$codetype]['just'] || $justify) {
          echo "  <td class='billcell' align='center'$usbillstyle>";
          echo "<select name='bill[$lino][justify]' onchange='setJustify(this)'>";
          echo "<option value='$justify'>$justify</option></select>";
          echo "</td>\n";
          $justinit .= "setJustify(f['bill[$lino][justify]']);\n";
        } else {
          echo "  <td class='billcell'$usbillstyle>&nbsp;</td>\n";
        }
      } else {
        echo "  <td class='billcell'>&nbsp;</td>\n";
        echo "  <td class='billcell'>&nbsp;</td>\n";
        echo "  <td class='billcell'$usbillstyle>&nbsp;</td>\n"; // justify
      }
    }

    // Provider drop-list for this line.
    echo "  <td class='billcell' align='center'>";
    genProviderSelect("bill[$lino][provid]", '-- Default --', $provider_id);
    echo "</td>\n";
    echo "  <td class='billcell' align='center'$usbillstyle><input type='checkbox' name='bill[$lino][auth]' " .
      "value='1'" . ($auth ? " checked" : "") . " /></td>\n";
    echo "  <td class='billcell' align='center'><input type='checkbox' name='bill[$lino][del]' " .
      "value='1'" . ($del ? " checked" : "") . " /></td>\n";
  }

  echo "  <td class='billcell'>$strike1" . ucfirst(strtolower($code_text)) . "$strike2</td>\n";
  echo " </tr>\n";

  // If NDC info exists or may be required, add a line for it.
  if ($codetype == 'HCPCS' && $ndc_applies && !$billed) {
    $ndcnum = ''; $ndcuom = ''; $ndcqty = '';
    if (preg_match('/^N4(\S+)\s+(\S\S)(.*)/', $ndc_info, $tmp)) {
      $ndcnum = $tmp[1]; $ndcuom = $tmp[2]; $ndcqty = $tmp[3];
    }
    echo " <tr>\n";
    echo "  <td class='billcell' colspan='2'>&nbsp;</td>\n";
    echo "  <td class='billcell' colspan='6'>&nbsp;NDC:&nbsp;";
    echo "<input type='text' name='bill[$lino][ndcnum]' value='$ndcnum' " .
      "size='11' style='background-color:transparent'>";
    echo " &nbsp;Qty:&nbsp;";
    echo "<input type='text' name='bill[$lino][ndcqty]' value='$ndcqty' " .
      "size='3' style='background-color:transparent;text-align:right'>";
    echo " ";
    echo "<select name='bill[$lino][ndcuom]' style='background-color:transparent'>";
    foreach ($ndc_uom_choices as $key => $value) {
      echo "<option value='$key'";
      if ($key == $ndcuom) echo " selected";
      echo ">$value</option>";
    }
    echo "</select>";
    echo "</td>\n";
    echo " </tr>\n";
  }
  else if ($ndc_info) {
    echo " <tr>\n";
    echo "  <td class='billcell' colspan='2'>&nbsp;</td>\n";
    echo "  <td class='billcell' colspan='6'>&nbsp;NDC Data: $ndc_info</td>\n";
    echo " </tr>\n";
  }

  // For IPPF.  Track contraceptive services.
  if (!$del) $contraception |= contraceptionClass($codetype, $code);
}

// This writes a product (drug_sales) line item to the output page.
//
function echoProdLine($lino, $drug_id, $del = FALSE, $units = NULL,
  $fee = NULL, $sale_id = 0, $billed = FALSE)
{
  global $code_types, $ndc_applies, $pid, $usbillstyle;

  $drow = sqlQuery("SELECT name FROM drugs WHERE drug_id = '$drug_id'");
  $code_text = $drow['name'];

  $fee = sprintf('%01.2f', $fee);
  if (empty($units)) $units = 1;
  $units = max(1, intval($units));
  // We put unit price on the screen, not the total line item fee.
  $price = $fee / $units;
  $strike1 = ($sale_id && $del) ? "<strike>" : "";
  $strike2 = ($sale_id && $del) ? "</strike>" : "";
  echo " <tr>\n";
  echo "  <td class='billcell'>{$strike1}Product$strike2";
  echo "<input type='hidden' name='prod[$lino][sale_id]' value='$sale_id'>";
  echo "<input type='hidden' name='prod[$lino][drug_id]' value='$drug_id'>";
  echo "<input type='hidden' name='prod[$lino][billed]' value='$billed'>";
  echo "</td>\n";
  echo "  <td class='billcell'>$strike1$drug_id$strike2</td>\n";
  if (modifiers_are_used(true)) {
    echo "  <td class='billcell'>&nbsp;</td>\n";
  }
  if ($billed) {
    if (fees_are_used()) {
      echo "  <td class='billcell' align='right'>" . oeFormatMoney($price) . "</td>\n";
      echo "  <td class='billcell' align='center'>$units</td>\n";
      echo "  <td class='billcell' align='center'$usbillstyle>&nbsp;</td>\n"; // justify
    }
    echo "  <td class='billcell' align='center'>&nbsp;</td>\n";             // provider
    echo "  <td class='billcell' align='center'$usbillstyle>&nbsp;</td>\n"; // auth
    echo "  <td class='billcell' align='center'><input type='checkbox'" .   // del
      " disabled /></td>\n";
  } else {
    if (fees_are_used()) {
      echo "  <td class='billcell' align='right'>" .
        "<input type='text' name='prod[$lino][price]' " .
        "value='$price' size='6'";
      if (acl_check('acct','disc'))
        echo " style='text-align:right'";
      else
        echo " style='text-align:right;background-color:transparent' readonly";
      echo "></td>\n";
      echo "  <td class='billcell' align='center'>";
      echo "<input type='text' name='prod[$lino][units]' " .
        "value='$units' size='2' style='text-align:right'>";
      echo "</td>\n";
      echo "  <td class='billcell'$usbillstyle>&nbsp;</td>\n"; // justify
    }
    echo "  <td class='billcell' align='center'>&nbsp;</td>\n"; // provider
    echo "  <td class='billcell' align='center'$usbillstyle>&nbsp;</td>\n"; // auth
    echo "  <td class='billcell' align='center'><input type='checkbox' name='prod[$lino][del]' " .
      "value='1'" . ($del ? " checked" : "") . " /></td>\n";
  }

  echo "  <td class='billcell'>$strike1" . ucfirst(strtolower($code_text)) . "$strike2</td>\n";
  echo " </tr>\n";
}

// Build a drop-down list of providers.  This includes users who
// have the word "provider" anywhere in their "additional info"
// field, so that we can define providers (for billing purposes)
// who do not appear in the calendar.
//
function genProviderSelect($selname, $toptext, $default=0, $disabled=false) {
  $query = "SELECT id, lname, fname FROM users WHERE " .
    "( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
    "AND active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
    "ORDER BY lname, fname";
  $res = sqlStatement($query);
  echo "   <select name='$selname'";
  if ($disabled) echo " disabled";
  echo ">\n";
  echo "    <option value=''>$toptext\n";
  while ($row = sqlFetchArray($res)) {
    $provid = $row['id'];
    echo "    <option value='$provid'";
    if ($provid == $default) echo " selected";
    echo ">" . $row['lname'] . ", " . $row['fname'] . "\n";
  }
  echo "   </select>\n";
}
?>