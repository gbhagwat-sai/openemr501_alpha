<?php
require_once("../globals.php");
require_once("$srcdir/mpdf/mpdf.php");


// The location/name of a temporary file to hold printable statements.
//

$STMT_TEMP_FILE = $GLOBALS['temporary_files_dir'] . "/openemr_statements.txt";
$STMT_TEMP_FILE_PDF = $GLOBALS['temporary_files_dir'] . "/openemr_statements.pdf";

$STMT_PRINT_CMD = $GLOBALS['print_command']; 

function getInfoByID($table_name,$getVal,$clause){
$res1 = sqlStatement("select $getVal FROM $table_name where $clause");
$result = "";
  	while ($row1 = sqlFetchArray($res1)) {
	$result = $row[0];	
  }
  return $result;
}



// new function is added for patient statement by pawan

function my_statement($stmt,$parameter){


$root = realpath($_SERVER["DOCUMENT_ROOT"]);



$filepath =$GLOBALS['OE_SITE_DIR'] . "/Statement.html";



	

	//$html = "";
	//$client ="GHANDOUR";
	//$client ="GOLDEN";
	//$client ="LIM";
	//$client ="MARK";
	//$client ="MOUSAVI";
	//$client ="PSS";
	//$client ="PCL";
	//$client ="RUEDA";
	$client ="GIESEN";

/********************* code of create statement start***************************/
	if (! $stmt['pid']) return ""; // get out if$ no data
		
	// Query for getting Facility (service location)
	$atres = sqlStatement("select f.name,f.phone,f.street,f.city,f.state,f.postal_code from facility f " .
		" left join users u on f.id=u.facility_id " .
		" left join  billing b on b.provider_id=u.id and b.pid = '".$stmt['pid']."' " .
		" where  service_location=1");
  	$row = sqlFetchArray($atres);
  
 
 	// Facility (service location)
 	$clinic_name = "{$row['name']}";
 	$clinic_addr = "{$row['street']}";
 	$clinic_csz = "{$row['city']},{$row['state']},{$row['postal_code']}";
 	$clinic_phone = "{$row['phone']}";
		
	// service location
 	$remit_name = $clinic_name;
 	$remit_addr = $clinic_addr;
 	$remit_csz = $clinic_csz;
		
	// code for getting Facility (billing location) contact
	$atres = sqlStatement("select name,attn,phone,street,city,state,postal_code from facility_billing");
  	$row = sqlFetchArray($atres);
 
 	// code added for patient statement phase III part A ---getting data from statement_config table
	$confres = sqlStatement("select * from statement_config where id=1");
  	$confrow = sqlFetchArray($confres);
 
 	// Billing Location
 	/*$billing_contact = "{$row['attn']}";
 	$billing_phone = "{$row['phone']}";
 	$billing_name = "{$row['name']}";
 	$billing_addr = "{$row['street']}";
 	$billing_csz = "{$row['city']},{$row['state']},{$row['postal_code']}";*/
	
	// billing location informatio
	$billing_name = "{$confrow['billing_name']}";
	$billing_phone = "{$confrow['billing_phone']}";
 	$billing_address = "{$confrow['billing_address']}";
 	$billing_csz = "{$confrow['billing_city']}, {$confrow['billing_state']} {$confrow['billing_zip']}";
	
	// paractice Information
	//guardian name & cantact info
	$practice_name = $confrow['practice_name'];
	$practice_phone = $confrow['practice_phone'];
	$practice_address =$confrow['practice_address'];
	$practice_csz = "{$confrow['practice_city']}, {$confrow['practice_state']} {$confrow['practice_zip']}";
	
	// golbal messages
	$gl_message1 = $confrow['gl_message1'];
	$gl_message2 = $confrow['gl_message2'];
	
	// payment mode Information
	
	$payment_mode_string = $confrow['payment_mode'];
	
	$card_type_string = $confrow['card_type'];
	
	if(!empty($payment_mode_string))	
		$payment_mode1 = explode(",",$payment_mode_string);
		
	if(!empty($card_type_string))
			$card_type1 = explode(",",$card_type_string);
			
	// last patient payment information
	$pid = $stmt['pid'];
	$patpayres = sqlStatement("select date(deposit_date) as post_date,pay_total as paid,patient_id from ar_session where payer_id=0 and patient_id=$pid   and pay_total!='0.00' 
order by post_date desc limit 1");
  	$patpayrow = sqlFetchArray($patpayres);
	
	// Sai custom code start
	// code modified to avoid default date printing in patient statement start
	$last_payment_date = $patpayrow['post_date'];

	if($last_payment_date !=='0000-00-00'){
		$time = strtotime($last_payment_date);
		$last_patient_payment_date = date('m/d/Y',$time);
	}
	else{
		$last_patient_payment_date='';
	}
	// code modified to avoid default date printing in patient statement end
	// Sai custom code end
	$last_patient_payment = $patpayrow['paid'];
	
	
	
	/*if($last_patient_payment =="0"){
		$last_patient_payment=round($last_patient_payment,2);
	}
	else{
		$last_patient_payment=sprintf("%01.2f",$last_patient_payment);
	}*/
		
	
		
	// patient details
	$patient_name = $stmt['to'][0];
	$patient_addr1 = $stmt['to'][1];
	$patient_addr2 = $stmt['to'][2];
	$patient_phone = $stmt['to'][3];
	

	$patient_name = str_replace(',', '', $stmt['to'][0]);
	$patient_addr1 = str_replace(',', '', $stmt['to'][1]);
	$patient_addr2 = str_replace(',', ',', $stmt['to'][2]);
	$patient_phone = str_replace(',', '', $stmt['to'][3]);


			
	//gaurdian details
	//$acres = sqlStatement("select pubpid,guardiansname,phone_contact,gstreet,gcity,gstate,gpostal_code from patient_data where pid='".$stmt['pid']."' ");
	$acres = sqlStatement("select pubpid,guardiansname,phone_contact,gstreet,gcity,gstate,gpostal_code,general_reason,Alert_note from patient_data where pid='".$stmt['pid']."' ");
	$acrow = sqlFetchArray($acres);
	
	$account_number = $acrow['pubpid'];
		 
	//guardian name & cantact info
	$guardiansname = $acrow['guardiansname'];
	$guardian_phone_contact = $acrow['phone_contact'];
	$guardian_addr = "{$acrow['gstreet']}";
	$guardian_csz = "{$acrow['gcity']}, {$acrow['gstate']} {$acrow['gpostal_code']}";
	
	//general reason
	$general_reason = $acrow['general_reason'];
	
	// alert note
	//$alert_note = $acrow['Alert_note'];
	
	if($guardiansname == ""){
		$guardiansname = $patient_name;
		$guardian_addr = $patient_addr1;
		$guardian_csz =$patient_addr2;
		$guardian_phone_contact = $patient_phone;
		
	
	}
		 
	//Statement details
	$statment_date = date("m/d/Y",strtotime($stmt['today']));
	$statment_due_date=date('M d,Y', strtotime("+30 days"));
	
	// code for statement count and date
	$stmtdate = date("Ym");
	$stmt_arr1 = explode("/",$_SERVER['REQUEST_URI']);
	
	if(strchr($stmt_arr1[1],"-"))
	 	$stmt_arr_str1= "-";
	else
	 	$stmt_arr_str1="_";
	
	$stmt_arr2 = explode($stmt_arr_str1,$stmt_arr1[1]);
	$stmt_str = strtoupper($stmt_arr2[1]);
	
	if($stmt_str=='OA')
	   $stmt_str = "AO";
	$stmt_count1 = str_pad($stmt_count, 4, "0", STR_PAD_LEFT);
		 
	//for getting patient insurance information START
	$patintFirtInsurance = "";$patintSecondInsurance="";$patintThirdInsurance="";
		 
	// code added for getting insurance company data for log
	$patintFirtInsurance_id = "";$patintSecondInsurance_id="";$patintThirdInsurance_id="";
	$patintFirtInsurance_name = "";$patintSecondInsurance_name="";$patintThirdInsurance_name="";
		 
	$res1 = sqlStatement("SELECT idt.id,idt.pid,idt.type,idt.date,ics.id as insu_id,ics.name,idt.policy_number 
			FROM insurance_data idt INNER JOIN insurance_companies ics ON ics.id = idt.provider 
			WHERE idt.pid = '".$stmt['pid']."' AND 
			idt.date = (SELECT MAX(date) FROM insurance_data WHERE type = idt.type AND pid = idt.pid)
			GROUP BY idt.type");
		
	while ($row1 = sqlFetchArray($res1)) {
		
		$ins_name =$row1['name'];
		$ins_name1=strtolower($ins_name);
		$insu_name = ucwords($ins_name1);
		 $patient_insurance[] = $insu_name.' - '.$row1['policy_number'];
		 $patient_insurance_id[] =$row1['insu_id'];
		 $patient_insurance_name[] =$row1['name'];
	}
		
	if(count($patient_insurance)>0){
	 	 $patintFirtInsurance = $patient_insurance[0];
	 	 $patintSecondInsurance = $patient_insurance[1];
		 $patintThirdInsurance = $patient_insurance[2];
			 
		 // code added for getting insurance company data for log
		/*  $patintFirtInsurance_id = $patient_insurance_id[0];
		  $patintSecondInsurance_id = $patient_insurance_id[1];
		  $patintThirdInsurance_id = $patient_insurance_id[2];
			  
		  $patintFirtInsurance_name = $patient_insurance_name[0];
		  $patintSecondInsurance_name = $patient_insurance_name[1];
		  $patintThirdInsurance_name = $patient_insurance_name[2];*/
			  
	}
		
	$insuCount = 0;
		
	if(strlen($patintFirtInsurance) != 0)
	{
	 	$insuCount = $insuCount + 1;
	}
	if(strlen($patintSecondInsurance) != 0)
	{
		$insuCount = $insuCount + 1;
	}
	if(strlen($patintThirdInsurance) != 0)
	{
		$insuCount = $insuCount + 1;
	}
		 
	
		
	// code for balance and other details
	$totalBalance = 0;
	$total_pripaid ="";
	$total_secpaid="";
	$InsBal=0;
	$cnt = 1;
	$flag = 0;
		
	
	
	
	
	
		
	$line_array =$stmt['lines'];
	$line_count=count($line_array);
	
	/***************************** code for line items start***************************************/
	$out_html = array();
	$out_data = array();
	
	$line_data = array();
	
	foreach ($stmt['lines'] as $line1){ 
			
		foreach($line1 as $line){
				
			$DOS = date("m/d/Y",strtotime($line['DOS']));
			
			//$balane = ($line['Charge']-($line['Adjustment']+$line['PriPaid']+$line['SecPaid']+$line['w_o']));
			$balane = ($line['Charge'] - ($line['Adjustment']+$line['PriPaid'] + $line['SecPaid'] 
				+ $line['TerPaid'] + $line['PtPaid'] + $line['w_o']));
					
			$stmt_count = $line['stmt_count'];
		
			// condition to disply patient and insurance balance statement
			
			if($line['claim_status_id'] == 10 || $line['claim_status_id'] == 11){
				$insuBal += 0;
				$patBal += ($line['Charge'] - ($line['Adjustment']+$line['PriPaid'] + $line['SecPaid'] + $line['TerPaid'] + $line['PtPaid'] + $line['w_o']));
				$totalBal = $insuBal + $patBal;
			}
			else{
				$insuBal += ($line['Charge'] - ($line['Adjustment']+$line['PriPaid'] + $line['SecPaid'] + $line['TerPaid'] + $line['PtPaid'] + $line['w_o']));
				$patBal += 0;
				$totalBal = $insuBal + $patBal;
			}
				
				 
		 	$totalBalance += $balane;
		 	$total_pripaid += $line['PriPaid'];
		 	$total_secpaid += $line['SecPaid'];
			$mybalance = $balane;
		 	$balane = "$".sprintf("%01.2f",$balane);
			 
			if($line['Location'] == ""){
				$location = "-";
		 	}
		 	else
		 		$location = $line['Location'];
			 
			//if($line['Reason'] == ""){
			//		$reason = "-";
			// }
			// else
			//	 $reason = $line['Reason'];
				 
			 $procedure = $line['CPT'];
			 $Charge = "$".$line['Charge'];
			 $PriPaid = "$".$line['PriPaid'];
			 $SecPaid = "$".$line['SecPaid'];
			 $Adjustment = "$".$line['Adjustment'];
			 $InsBal += ($line['Charge'] - ($line['Adjustment']+$line['PriPaid']+$line['SecPaid']+$line['w_o']));
			 
			 //Added by Gangeya to show Adjustment + W/o in table adjustment Column.
				 
			 $adj = $line['Adjustment'] + $line['w_o'];
			$adj = "$".sprintf("%01.2f",$adj);
				 
			 // Added by pawan for patient payment column in lines
			 $patPaid = "$".$line['PtPaid'];
		
			
			$provider =$line['Provider'];
			
			$line_html = "<tr><td>$DOS</td><td>$provider</td><td>$location</td> <td>$procedure</td> <td>$Charge</td> <td>$PriPaid</td> <td>$SecPaid</td> <td>$adj</td><td>$balane</td></tr>";	
			$line_data ['dos'] = $DOS ;
			$line_data ['provider'] = $provider ;
			$line_data ['location'] = $location ;
			$line_data ['procedure'] = $procedure ;
			$line_data ['Charge'] = $Charge ;
			$line_data ['PriPaid'] = $PriPaid ;
			$line_data ['SecPaid'] = $SecPaid ;
			$line_data ['adj'] = $adj ;
			$line_data ['balane'] = $balane ;
			$line_data ['PtPaid'] =  $patPaid ;
				
			// creating array of lines
			$out_html [] = $line_html;
			
			$out_data[] = $line_data;
				
			 $loc[] = $location;
		
		}// end of inner foreach loop 
			
	}// end of outer foreach loop
	
	if($totalBal =="0"){
		$total_balance=round($totalBal,2);
	}
	else{
		$total_balance=sprintf("%01.2f",$totalBal);
	}
	
	$total_balance1= "$".$total_balance;
	
	$final_lines =array_chunk($out_data,6);
	// code added for pagebreak 31-07-2015
	$final_count=count($out_data);
	$pagecount= $final_count / 6 ;
	$totalcount=ceil($pagecount);
	//echo $final_count;
		
	$page_count=0;
	
			
		
	$grand_total = 0;		
	//$html ="";
	for($j=0;$j<$pagecount;$j++){
	
		$line_total = 0;
		
		
		$page_count++;
		$statement_template = file_get_contents($filepath);
		
		
		
		
		$labels = array('[billing_name]', '[billing_address]','[billing_csz]','[billing_phone]','[patient_name]','[account_no]','[patient_addr1]','[patient_addr2]','[patient_phone]','[guardians_name]','[guardian_addr]','[guardian_csz]','[guardian_phone]','[statement_date]','[total_balance]','[practice_name]','[practice_address]','[practice_csz]','[practice_phone]','[gl_message1]','[gl_message2]','[last_patient_payment]','[last_patient_payment_date]','[general_reason]');
		
		if($flag==1){
			$values   = array($billing_name,$billing_address,$billing_csz,$billing_phone,$patient_name,$account_number,$patient_addr1,$patient_addr2,$patient_phone,$guardiansname,$guardian_addr,$guardian_csz,$guardian_phone_contact,$statment_date,$total_balance1,$practice_name,$practice_address,$practice_csz,$practice_phone,$pcl_message1,$gl_message2,$last_patient_payment,$last_patient_payment_date,$general_reason);
		}
		else{
		
			$values   = array($billing_name,$billing_address,$billing_csz,$billing_phone,$patient_name,$account_number,$patient_addr1,$patient_addr2,$patient_phone,$guardiansname,$guardian_addr,$guardian_csz,$guardian_phone_contact,$statment_date,$total_balance1,$practice_name,$practice_address,$practice_csz,$practice_phone,$gl_message1,$gl_message2,$last_patient_payment,$last_patient_payment_date,$general_reason);
		}
		
		$output  = str_replace($labels, $values, $statement_template);
		
		
		// code added for dynamic payment options
		$style_payment_mode = 'style="display:block"';
		if(!empty($payment_mode1)){
			foreach($payment_mode1 as $value){
					$pay_mode = strtolower($value);
					$lable2 = "[style_".$pay_mode."]";
					$output  = str_replace($lable2, $style_payment_mode, $output);
			
			}
			
			// code for if card payment mode then only display related row otherwise none
		
			if (in_array("Card", $payment_mode1)){
		
			$output  = str_replace('[payment_style]', $style_payment_mode, $output);
		}
		// code for if check payment mode then only display check no
		if (in_array("Cheque", $payment_mode1)){
		
				$output  = str_replace('[check_style]', $style_payment_mode, $output);
			}
		}
		if(!empty($card_type1)){
			foreach($card_type1 as $value){
					$card_type = strtolower($value);
				
					if($card_type =="american express")
						$lable3 = "[style_american]";
					else
						$lable3 = "[style_".$card_type."]";
					
					$output  = str_replace($lable3, $style_payment_mode, $output);
			
			}
		}
		
		
		
		
		/*if (in_array('Card',$payment_mode1))
			$output  = str_replace('[style_card]', $style_payment_mode, $statement_template);
				
		if (in_array('Cash',$payment_mode1))
			$output  = str_replace('[style_cash]', $style_payment_mode, $statement_template);
				
		if (in_array('Cheque',$payment_mode1))
			$output  = str_replace('[style_cheque]', $style_payment_mode, $statement_template);*/
		
		
		
		//for($i=$j;$i<=5;$i++){
				//$i= $j;
				$line_detail = $final_lines[$j];
				$line_count = count($line_detail);
				//echo "<pre>";
				
				//print_r($line_detail);
				//echo $line_count;
				
				for($i=0;$i<6;$i++){
					$label1 = "[dos".$i."]";
					$label2 = "[provider".$i."]";
					$label3 = "[location".$i."]";
					$label4 = "[procedure".$i."]";
					$label5 = "[Charge".$i."]";
					$label6 = "[PriPaid".$i."]";
					$label7 = "[SecPaid".$i."]";
					$label8 = "[PtPaid".$i."]";
					$label9 = "[balane".$i."]";
					$label10 = "[remark".$i."]";
					
					$label11 =  "[style".$i."]";
					
					$dos = $line_detail[$i]['dos'];
					$provider = $line_detail[$i]['provider'];
					$location = $line_detail[$i]['location'];
					$location1 = ucwords(strtolower($location)); 
					$procedure = $line_detail[$i]['procedure'];
					$Charge = $line_detail[$i]['Charge'];
					$PriPaid = $line_detail[$i]['PriPaid'];
					$SecPaid = $line_detail[$i]['SecPaid'];
					$PtPaid = $line_detail[$i]['PtPaid'];
					$balane = $line_detail[$i]['balane'];
					$remark = '';
					
					$style = 'style="display:none"';
					
					$output = str_replace($label1,$dos, $output);
					$output = str_replace($label2,$provider, $output); 
					$output = str_replace($label3,$location1, $output); 
					$output = str_replace($label4,$procedure, $output); 
					$output = str_replace($label5,$Charge, $output); 
					$output = str_replace($label6,$PriPaid, $output); 
					$output = str_replace($label7,$SecPaid, $output); 
					$output = str_replace($label8,$PtPaid, $output); 
					$output = str_replace($label9,$balane, $output); 
					$output = str_replace($label10,$remark, $output); 
					
					$balane1 = str_replace('$', '', $balane);
					
					$line_total += $balane1;
					
					if(empty($dos)){
						$output = str_replace($label11,$style, $output); 
						
					}
			}
		
		
		$line_total2 = "$".sprintf("%01.2f",$line_total);
		
		//$output = str_replace('[line_total]',$line_total2, $output); 
		
		$grand_total += $line_total;
		
		
		$grand_style ='style="display:block"';
		$grand_total2 = "$".sprintf("%01.2f",$grand_total);
		
		$total_due = "Total Balance Due ".$grand_total2;
		
		$subtotal_style ='style="display:block"';
		
		if(($page_count == $totalcount) && ($totalcount!= 1)){
			
			$output = str_replace('[subtotal_style]',$subtotal_style, $output);
			$output = str_replace('[line_total]',$line_total2, $output);
			$output = str_replace('[grand_style]',$grand_style, $output);
			$output = str_replace('[grand_total]',$grand_total2, $output);
			$output = str_replace('[total_style]',$grand_style, $output);
			$output = str_replace('[total_due]',$total_due, $output);
			
		}
		elseif($totalcount == 1){
			//$output = str_replace('[subtotal_style]',$subtotal_style, $output);
			
			$output = str_replace('[grand_style]',$grand_style, $output);
			$output = str_replace('[grand_total]',$grand_total2, $output);
			$output = str_replace('[total_style]',$grand_style, $output);
			$output = str_replace('[total_due]',$total_due, $output);
			
		
		}
		else{
			$output = str_replace('[subtotal_style]',$grand_style, $output);
			$output = str_replace('[line_total]',$line_total2, $output);
		
		}
		
		// code for last patient payment
	
		if(!empty($last_patient_payment)){
		
			$output = str_replace('[last_patient_payment_style]',$subtotal_style, $output);
		}
		
		$output .='<div style="text-align:center;font-size:10px;"><br>';
		$output .="Page $page_count of $totalcount";	
		
		if($page_count!=$totalcount){
			$output .='<pagebreak resetpagenum ="BLANK" suppress="off" />';	
		}
		
		$output .= "</div>";
		$html .= $output ;
			
	}
	
	
	// die;
	/*********************code of create statement end*****************************/
	
	return array($html,$totalcount);
	//return $html;
	//echo $html;
	
	//echo $message_template;

} 

?>
