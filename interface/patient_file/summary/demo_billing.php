<?php

 require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/classes/Address.class.php");
 require_once("$srcdir/classes/InsuranceCompany.class.php");
 require_once("$srcdir/classes/Document.class.php");
 require_once("$srcdir/options.inc.php");
 require_once("../history/history.inc.php");
 require_once("$srcdir/formatting.inc.php");
 require_once("$srcdir/edi.inc");
 require_once("$srcdir/clinical_rules.php");
 
 

// get an array from Photos category
function pic_array($pid,$picture_directory) {
    $pics = array();
    $sql_query = "select documents.id from documents join categories_to_documents " .
                 "on documents.id = categories_to_documents.document_id " .
                 "join categories on categories.id = categories_to_documents.category_id " .
                 "where categories.name like ? and documents.foreign_id = ?";
    if ($query = sqlStatement($sql_query, array($picture_directory,$pid))) {
      while( $results = sqlFetchArray($query) ) {
            array_push($pics,$results['id']);
        }
      }
    return ($pics);
}


// Get the document ID of the first document in a specific catg.
function get_document_by_catg($pid,$doc_catg) {

    $result = array();

	if ($pid and $doc_catg) {
		$result = sqlQuery("SELECT d.id, d.date, d.url FROM " .
	    "documents AS d, categories_to_documents AS cd, categories AS c " .
	    "WHERE d.foreign_id = ? " .
	    "AND cd.document_id = d.id " .
	    "AND c.id = cd.category_id " . 
	    "AND c.name LIKE ? " .
	    "ORDER BY d.date DESC LIMIT 1", array($pid, $doc_catg) );
	}

	return($result['id']);
}

// Display image in 'widget style'
function image_widget($doc_id,$doc_catg)
{
    global $pid, $web_root;
    $docobj = new Document($doc_id);
    $image_file = $docobj->get_url_file();
    $extension = substr($image_file, strrpos($image_file,"."));
    $viewable_types = array('.png','.jpg','.jpeg','.png','.bmp'); // image ext supported by fancybox viewer
    if ( in_array($extension,$viewable_types) ) { // extention matches list
    	$to_url = "<td> <a href = $web_root" .
				"/controller.php?document&retrieve&patient_id=$pid&document_id=$doc_id" .
				"/tmp$extension" .  // Force image type URL for fancybox
				" onclick=top.restoreSession(); class='image_modal'>" .
                " <img src = $web_root" .
				"/controller.php?document&retrieve&patient_id=$pid&document_id=$doc_id" .
				" width=100 alt='$doc_catg:$image_file'>  </a> </td> <td valign='center'>".
                htmlspecialchars($doc_catg) . '<br />&nbsp;' . htmlspecialchars($image_file) .
				"</td>";
    }
    else {
		$to_url = "<td> <a href='" . $web_root . "/controller.php?document&retrieve" .
                    "&patient_id=$pid&document_id=$doc_id'" .
                    " onclick='top.restoreSession()' class='css_button_small'>" .
                    "<span>" .
                    htmlspecialchars( xl("View"), ENT_QUOTES )."</a> &nbsp;" . 
					htmlspecialchars( "$doc_catg - $image_file", ENT_QUOTES ) .
                    "</span> </td>";
	}
    echo "<table><tr>";
    echo $to_url;
    echo "</tr></table>";
}

// Get the document ID of the patient ID card if access to it is wanted here.
$idcard_doc_id = false;
if ($GLOBALS['patient_id_category_name']) {
  $idcard_doc_id = get_document_by_catg($pid, $GLOBALS['patient_id_category_name']);
}

if ($_POST['action'] == 'demo_notes') {


	
	// Notes expand collapse widget
	$widgetTitle = xl("Notes");
	$widgetLabel = "pnotes";
	$widgetButtonLabel = xl("Edit");
	$widgetButtonLink = "pnotes_full.php";
	$widgetButtonClass = "";
	$linkMethod = "html";
	$bodyClass = "notab";
	$widgetAuth = true;
	$fixedWidth = true;
	expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
	  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
	  $widgetAuth, $fixedWidth);
	?>
	
						<br/>
						<div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
					</div>
				</td>
			</tr>
            
 
<?php 

}
elseif($_POST['action'] == 'demo_billing'){


	// Billing expand collapse widget
	/*$widgetTitle = xl("Billing");
	$widgetLabel = "billing";
	$widgetButtonLabel = xl("Edit");
	$widgetButtonLink = "return newEvt();";
	$widgetButtonClass = "";
	$linkMethod = "javascript";
	$bodyClass = "notab";
	$widgetAuth = false;
	$fixedWidth = true;
	if ($GLOBALS['force_billing_widget_open']) {
	  $forceExpandAlways = true;
	}
	else {
	  $forceExpandAlways = false;
	}
	expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
	  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
	  $widgetAuth, $fixedWidth, $forceExpandAlways);*/
	  
	 $result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");

	 $result2 = getEmployerData($pid);

	 $result3 = getInsuranceData($pid, "primary", "copay, provider, DATE_FORMAT(`date`,'%m/%d/%Y') as effdate");

	 $insco_name = "";

	 if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
	     $insco_name = getInsuranceProvider($result3['provider']);
	 }
	  
	?>
	        <br>
	<?php
/*Sonali : For Displaying Excess balance for patient*/
//PATIENT BALANCE,INS BALANCE 

	 
	 
	 // Calling store procedure to get unapplied pre payment
	 /*$payment_rs= mysql_query("CALL get_applied_unapplied_amount($pid,@global_amount,@unapplied_pre_payment,@applied_pre_payment)");
     $payment_rs = mysql_query( "SELECT @global_amount,@unapplied_pre_payment,@applied_pre_payment");
     $payment_row = mysql_fetch_assoc($payment_rs);
	 
	 $global_amount = $payment_row['@global_amount'];
	 $unapplied_pre_payment = $payment_row['@unapplied_pre_payment'] - $global_amount;
	 
	// print_r($enc_row);
	  
	  $applied_pre_payment = $payment_row['@applied_pre_payment'];
	  
	  $pre_payment = $unapplied_pre_payment - $applied_pre_payment;*/
	  
	 $query="SELECT SUM(pay_total) AS unapplied_pre_payment,sum(global_amount) as global_amount FROM ar_session WHERE patient_id = ? and payment_type='patient'  ";	
  	 $bres = sqlStatement($query,array($pid));
	  $brow = sqlFetchArray($bres);
	  $global_amount = $brow['global_amount'];
	 $unapplied_pre_payment = $brow['unapplied_pre_payment'] - $global_amount;
	 
	  $query1="select SUM(pay_amount) as applied_pre_payment from ar_activity as act join ar_session as ars on ars.session_id=act.session_id and ars.patient_id=?  ";	
  	  $bres1 = sqlStatement($query1,array($pid));
	  $brow1 = sqlFetchArray($bres1);
	  $applied_pre_payment = $brow1['applied_pre_payment'];
	  $pre_payment = $unapplied_pre_payment - $applied_pre_payment;
   
   		
		 $draftbalance = get_draft_charges($pid);
		  $draft_bal_arr = explode("@",$draftbalance);		 
		 $ins_draft_bal =$draft_bal_arr['1']; 
		 $pat_draft_bal = $draft_bal_arr['0'] - $draft_bal_arr['1'];
		 
		 //$patientbalance =   get_patient_balance($pid, "true");
		/* $patbalance =   get_patient_balance($pid, "true");
		 $patientbalance = $patbalance - $pre_payment + $pat_draft_bal;
		 
		 
		//Debit the patient balance from insurance balance
		//$insurancebalance = get_patient_balance($pid, "false") ;
		$insbal = get_patient_balance($pid, "false");
		$insurancebalance = $insbal + $ins_draft_bal;
		
		$overallpatientbalance =  $patientbalance;
		//$overallpatientbalance = $pre_payment;
		//$overallpatientbalance = get_patient_balance($pid, true);
	   $totalbalance=$patientbalance + $insurancebalance;*/
	   
	    $newbalance=getBalanceData($pid);
		  $bal_arr = explode("~",$newbalance);  
		  $insurancebalance = $bal_arr[0]+$bal_arr[1]+$bal_arr[2]+ $ins_draft_bal;;
		  $overallpatientbalance = $bal_arr[3]- $pre_payment + $pat_draft_bal;;
		  $totalbalance = $insurancebalance+$overallpatientbalance;
	  
	   
	// if ($GLOBALS['oer_config']['ws_accounting']['enabled']) {

	 // Show current balance and billing note, if any.
	   $openbracket1="";
	   $closebracket1="";

	   if($overallpatientbalance<0)
	   {
		   $openbracket1 = "(";
		   $closebracket1 = ")";
	   }
	 
	 echo "        <div id='billing_note' style='margin-left: 10px; margin-right: 10px'>" .
	   "<span class='bold'><font color='#ee6600'>" .
	   htmlspecialchars(xl('Patient Balance Due'),ENT_NOQUOTES) .
	   ": $openbracket1 $" . htmlspecialchars(text(oeFormatMoney($overallpatientbalance))) .
	   " $closebracket1</font></span><br>";

	   $openbracket2="";
	   $closebracket2="";

	   if($insurancebalance<0)
	   {
		   $openbracket2 = "(";
		   $closebracket2 = ")";
	   }

	    echo "        <div style='margin-left: 1px; margin-right: 10px'>" .
	   "<span class='bold'><font color='#ee6600'>" .
	   htmlspecialchars(xl('Insurance Balance Due'),ENT_NOQUOTES) .
	   ": $openbracket2 $" . htmlspecialchars(text(oeFormatMoney($insurancebalance))) .
	   " $closebracket2</font></span><br>";

	   $openbracket="";
	   $closebracket="";

	   if($totalbalance<0)
	   {
		   $openbracket = "(";
		   $closebracket = ")";
	   }

	    echo "        <div style='margin-left: 1px; margin-right: 10px'>" .
	   "<span class='bold'><font color='#ee6600'>" .
	   htmlspecialchars(xl('Total Balance Due'),ENT_NOQUOTES) .
	   ": $openbracket $" . htmlspecialchars(text(oeFormatMoney($totalbalance))) .
	   " $closebracket</font></span><br>";
	   
	  if ($result['genericname2'] == 'Billing') {
		   echo "<span class='bold'><font color='red'>" .
		    htmlspecialchars(xl('Billing Note'),ENT_NOQUOTES) . ":" .
		    htmlspecialchars($result['genericval2'],ENT_NOQUOTES) .
		    "</font></span><br>";
	  } 

	  if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
		   echo "<span class='bold'>" .
		    htmlspecialchars(xl('Primary Insurance'),ENT_NOQUOTES) . ': ' . htmlspecialchars($insco_name,ENT_NOQUOTES) .
		    "</span>&nbsp;&nbsp;&nbsp;";

		   if ($result3['copay'] > 0) {
			    echo "<span class='bold'>" .
			     htmlspecialchars(xl('Copay'),ENT_NOQUOTES) . ': ' .  htmlspecialchars($result3['copay'],ENT_NOQUOTES) .
			     "</span>&nbsp;&nbsp;&nbsp;";
		   }

		   echo "<span class='bold'>" .
		    htmlspecialchars(xl('Effective Date'),ENT_NOQUOTES) . ': ' .  htmlspecialchars($result3['effdate'],ENT_NOQUOTES) .
		    "</span>";
	  }
	  echo "</div><br>";
	// }
	?>
	        
	        
	<?php        

}
elseif($_POST['action'] == 'demo_appointments'){

// Show current and upcoming appointments.
	
	 $query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " .
	  "e.pc_startTime, e.pc_hometext, u.fname, u.lname, u.mname, " .
	  "c.pc_catname " .
	  "FROM openemr_postcalendar_events AS e, users AS u, " .
	  "openemr_postcalendar_categories AS c WHERE " .
	  "e.pc_pid = ? AND e.pc_eventDate >= CURRENT_DATE AND " .
	  "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " .
	  "ORDER BY e.pc_eventDate, e.pc_startTime";
	  
	 $res = sqlStatement($query, array($pid) );

// appointments expand collapse widget
	$widgetTitle = xl("Appointments");
	$widgetLabel = "appointments";
	$widgetButtonLabel = xl("Add");
	$widgetButtonLink = "return newEvt();";
	$widgetButtonClass = "";
	$linkMethod = "javascript";
	$bodyClass = "summary_item small";
	$widgetAuth = (isset($res) && $res != null);
	$fixedWidth = false;
	expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);

	$count = 0;
	while($row = sqlFetchArray($res)) {
		  $count++;
		  $dayname = date("l", strtotime($row['pc_eventDate']));
		  $dispampm = "am";
		  $disphour = substr($row['pc_startTime'], 0, 2) + 0;
		  $dispmin  = substr($row['pc_startTime'], 3, 2);
		  if ($disphour >= 12) {
			   $dispampm = "pm";
			   if ($disphour > 12) $disphour -= 12;
		  }
		
		$etitle = xl('(Click to edit)');
		if ($row['pc_hometext'] != "") {
			$etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
		}
        
		echo "<a href='javascript:oldEvt(" . htmlspecialchars($row['pc_eid'],ENT_QUOTES) .
			")' title='" . htmlspecialchars($etitle,ENT_QUOTES) . "'>";
			  echo "<b>" . htmlspecialchars(xl($dayname) . ", " . $row['pc_eventDate'],ENT_NOQUOTES) . "</b><br>";
			  echo htmlspecialchars("$disphour:$dispmin " . xl($dispampm) . " " . xl_appt_category($row['pc_catname']),ENT_NOQUOTES) . "<br>\n";
			  echo htmlspecialchars($row['fname'] . " " . $row['lname'],ENT_NOQUOTES) . "</a><br>\n";
	}
	
	if (isset($res) && $res != null) {
		if ( $count < 1 ) { echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'),ENT_NOQUOTES); }
				echo "</div>";
	}
		
		
			

}
elseif($_POST['action'] == 'demo_others'){
?>
<div>
	<?php
      
     // If there is an ID Card or any Photos show the widget
     $photos = pic_array($pid, $GLOBALS['patient_photo_category_name']);
	 
	 
     if ($photos or $idcard_doc_id )
     {
     	$widgetTitle = xl("ID Card") . '/' . xl("Photos");
        $widgetLabel = "photos";
        $linkMethod = "javascript";
        $bodyClass = "notab-right";
        $widgetAuth = false;
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel ,
                        $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
                        $widgetAuth, $fixedWidth);
    ?>
    <br />
	<?php
    if ($idcard_doc_id) {
    	image_widget($idcard_doc_id, $GLOBALS['patient_id_category_name']);
	}

    foreach ($photos as $photo_doc_id) {
    	image_widget($photo_doc_id, $GLOBALS['patient_photo_category_name']);
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
		expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
		
		$counterFlag = false; //flag to record whether any categories contain ad records
		$query = "SELECT id FROM categories WHERE name='Advance Directive'";
		$myrow2 = sqlQuery($query);
		
		if ($myrow2) {
			  $parentId = $myrow2['id'];
			  $query = "SELECT id, name FROM categories WHERE parent=?";
			  $resNew1 = sqlStatement($query, array($parentId) );
		
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
				  $resNew2 = sqlStatement($query, array($categoryId, $pid) );
				  $limitCounter = 0; // limit to one entry per category
		
				  while (($myrows4 = sqlFetchArray($resNew2)) && ($limitCounter == 0)) {
						  $dateTimeDoc = $myrows4['date'];
					  // remove time from datetime stamp
						  $tempParse = explode(" ",$dateTimeDoc);
						  $dateDoc = $tempParse[0];
						  $idDoc = $myrows4['id'];
						  echo "<a href='$web_root/controller.php?document&retrieve&patient_id=" .
								htmlspecialchars($pid,ENT_QUOTES) . "&document_id=" .
									htmlspecialchars($idDoc,ENT_QUOTES) . "&as_file=true'>" .
									htmlspecialchars(xl_document_category($nameDoc),ENT_NOQUOTES) . "</a> " .
									htmlspecialchars($dateDoc,ENT_NOQUOTES);
						  echo "<br>";
						  $limitCounter = $limitCounter + 1;
						  $counterFlag = true;
				  }
			  }
		}
				  
		if (!$counterFlag) {
			  echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'),ENT_NOQUOTES);
		} ?>
		
</div><?php
	}  // close advanced dir block
 
	// This is a feature for a specific client.  -- Rod
	if ($GLOBALS['cene_specific']) {
		 echo "   <br />\n";

       	$imagedir  = $GLOBALS['OE_SITE_DIR'] . "/documents/$pid/demographics";
       	$imagepath = "$web_root/sites/" . $_SESSION['site_id'] . "/documents/$pid/demographics";

 	 	echo "   <a href='' onclick=\"return sendimage($pid, 'photo');\" " .
				"title='Click to attach patient image'>\n";

 	 	if (is_file("$imagedir/photo.jpg")) {
			echo "   <img src='$imagepath/photo.jpg' /></a>\n";
  		} 
		else {
			echo "   Attach Patient Image</a><br />\n";
  		}
  		echo "   <br />&nbsp;<br />\n";

 	 	echo "   <a href='' onclick=\"return sendimage($pid, 'fingerprint');\" " .
				"title='Click to attach fingerprint'>\n";
  		if (is_file("$imagedir/fingerprint.jpg")) {
			echo "   <img src='$imagepath/fingerprint.jpg' /></a>\n";
  		} else {
			echo "   Attach Biometric Fingerprint</a><br />\n";
  		}
	  		echo "   <br />&nbsp;<br />\n";
	}

	
	// This stuff only applies to athletic team use of OpenEMR.  The client
	// insisted on being able to quickly change fitness and return date here:
	//
	if (false && $GLOBALS['athletic_team']) {
		  //                  blue      green     yellow    red       orange
		  $fitcolors = array('#6677ff','#00cc00','#ffff00','#ff3333','#ff8800','#ffeecc','#ffccaa');
		  if (!empty($GLOBALS['fitness_colors'])) $fitcolors = $GLOBALS['fitness_colors'];
		  $fitcolor = $fitcolors[0];
		  $form_fitness   = $_POST['form_fitness'];
		  $form_userdate1 = fixDate($_POST['form_userdate1'], '');
		  $form_issue_id  = $_POST['form_issue_id'];
		  if ($form_submit) {
				$returndate = $form_userdate1 ? "'$form_userdate1'" : "NULL";
				sqlStatement("UPDATE patient_data SET fitness = ?, " .
				  "userdate1 = ? WHERE pid = ?", array($form_fitness, $returndate, $pid) );
				// Update return date in the designated issue, if requested.
				if ($form_issue_id) {
					  sqlStatement("UPDATE lists SET returndate = ? WHERE " .
						"id = ?", array($returndate, $form_issue_id) );
				}
		} else {
				$form_fitness = $result['fitness'];
				if (! $form_fitness) $form_fitness = 1;
				$form_userdate1 = $result['userdate1'];
		}
		
		$fitcolor = $fitcolors[$form_fitness - 1];
		echo "   <form method='post' action='demographics.php' onsubmit='return validate()'>\n";
		echo "   <span class='bold'>Fitness to Play:</span><br />\n";
		echo "   <select name='form_fitness' style='background-color:$fitcolor'>\n";
		$res = sqlStatement("SELECT * FROM list_options WHERE " .
				"list_id = 'fitness' ORDER BY seq");
		while ($row = sqlFetchArray($res)) {
			$key = $row['option_id'];
			echo "    <option value='" . htmlspecialchars($key,ENT_QUOTES) . "'";
			if ($key == $form_fitness) echo " selected";
				echo ">" . htmlspecialchars($row['title'],ENT_NOQUOTES) . "</option>\n";
			}
			
			echo "   </select>\n";
			echo "   <br /><span class='bold'>Return to Play:</span><br>\n";
			echo "   <input type='text' size='10' name='form_userdate1' id='form_userdate1' " .
				"value='$form_userdate1' " .
				"title='" . htmlspecialchars(xl('yyyy-mm-dd Date of return to play'),ENT_QUOTES) . "' " .
				"onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />\n" .
				"   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' " .
				"id='img_userdate1' border='0' alt='[?]' style='cursor:pointer' " .
				"title='" . htmlspecialchars(xl('Click here to choose a date'),ENT_QUOTES) . "'>\n";
			echo "   <input type='hidden' name='form_original_userdate1' value='" . htmlspecialchars($form_userdate1,ENT_QUOTES) . "' />\n";
			echo "   <input type='hidden' name='form_issue_id' value='' />\n";
			echo "<p><input type='submit' name='form_submit' value='Change' /></p>\n";
			echo "   </form>\n";
		}

		
}
			?>
		</div>

		<!--<div id='stats_div'>
            <br/>
            <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
        </div>-->
    </td>
    </tr>
    </table>

	</div> <!-- end right column div -->
<?php
}
else{

	echo "Add new";

}


?>
