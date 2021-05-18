<?php

// created by Gangeya to show PQRS infomation in a pop up on encounter form while editing it.


require_once("../../globals.php");
require_once("$srcdir/sql.inc");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";

?>
<style>
.domain{
background:#66CCFF;
font:medium;
}
.measure{font-size:14px;}
.note{font-size:12px;}

</style>


<?php

function thisLineItem($row) 
{
    
?>


<!-------------------Domain : Effective Clinical Care - NQS Domain------------------------------------>


 <tr bgcolor="#6CAEFF" align="center" class="domain">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Effective Clinical Care - NQS Domain','e'  ) ?></td>
 </tr>
 
 <!-- 
 Domain : Effective Clinical Care - NQS Domain
 1) diabetes: Hemoglobin A1c Poor Control (PQRS # 001 - NQF 0059) - Cross Cutting Measure
 -->
 
  <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Diabetes: Hemoglobin A1c Poor Control (PQRS # 001 - NQF 0059) - Cross Cutting Measure','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('(Patients 18 through 75 years of age on date of encounter with a diagnosis for diabetes & Relevant CPT)','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent hemoglobin A1c level  > 9.0 %','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['H1C_gt_9'] == 1){echo "YES";} 
	else if($row['H1C_gt_9'] == ''){echo " ";}
	else if($row['H1C_gt_9'] == '0'){echo "NO";}?></td>
	
       
    <td class="detail" width="33%">
    
    <?php 
	
		
		if($row['H1C_gt_9'] == 1){
			echo "3046F";
		} 
		else{
			echo '';
		}
	?>
    </td>
    
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent hemoglobin A1c (HbA1c) level  < 7.0 %','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['H1C_lt_7'] == 1){echo "YES";} 
	else if($row['H1C_lt_7'] == ''){echo " ";}
	else if($row['H1C_lt_7'] == '0'){echo "NO";} ?></td>
    
   <td class="detail" width="33%">
    <?php 
	if($row['H1C_lt_7'] == 1){echo "3044F";} 
	else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent hemoglobin A1c (HbA1c) level  7.0 - 9.0 %','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['H1C_7To9'] == 1){echo "YES";} 
	else if($row['H1C_7To9'] == ''){echo " ";}
	else if($row['H1C_7To9'] == '0'){echo "NO";} ?></td>
    
    <td class="detail" width="33%">
    <?php 
	if($row['H1C_7To9'] == 1){echo "3045F";} 
	else {echo '';}
	?>
    </td>
 </tr>
 
<tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Hemoglobin A1c level was not performed during the measurement period (12 months).','e'  ) ?></td>
	<td class="detail" width="33%" colspan="2"><?php 
	if($row['H1C_NP'] == 1){echo "YES";} 
	else if($row['H1C_NP'] == ''){echo " ";}
	else if($row['H1C_NP'] == '0'){echo "NO";} ?></td>
	
	<td class="detail" width="33%">
    <?php 
	if($row['H1C_NP'] == 1){echo "3046F - 8P";} 
	else {echo '';}
	?>
    </td>
	
 </tr>
 
 
<!--
Domain : Effective Clinical Care - NQS Domain
 2) Age-Related Macular Degeneration (AMD): Dilated Macular Examination (PQRS # 14 - NQF 0087)	
-->
 
  <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Age-Related Macular Degeneration (AMD): Dilated Macular Examination (PQRS # 14 - NQF 0087)','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 50 years and older with a Diagnosis of AMD & Relevant CPT','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Dilated macular exam performed, including documentation of the presence or absence of macular thickening or hemorrhage AND the level of macular degeneration severity','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AMD_Dilated_1'] == 1){echo "YES";} 
	else if($row['AMD_Dilated_1'] == ''){echo " ";}
	else if($row['AMD_Dilated_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['AMD_Dilated_1'] == 1){echo "2019F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of medical reason(s) for not performing a dilated macular examination','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AMD_Dilated_2'] == 1){echo "YES";} 
	else if($row['AMD_Dilated_2'] == ''){echo " ";}
	else if($row['AMD_Dilated_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AMD_Dilated_2'] == 1){echo "2019F - 1P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of patient reason(s) for not performing a dilated macular examination','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AMD_Dilated_3'] == 1){echo "YES";} 
	else if($row['AMD_Dilated_3'] == ''){echo " ";}
	else if($row['AMD_Dilated_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AMD_Dilated_3'] == 1){echo "2019F - 2P";} 
			else {echo '';}
	?>
    </td>
    </tr>
    
    <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Dilated macular exam was not performed, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AMD_Dilated_4'] == 1){echo "YES";} 
	else if($row['AMD_Dilated_4'] == ''){echo " ";}
	else if($row['AMD_Dilated_4'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AMD_Dilated_4'] == 1){echo "2019F - 8P";} 
			else {echo '';}
	?>
    </td>
    </tr>
 
 
 
 <!--
 Domain : Effective Clinical Care - NQS Domain
  3) Age-Related Macular Degeneration (AMD): Counseling on Antioxidant Supplement (PQRS # 140 - NQF 0566)
 -->
 
 	 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Age-Related Macular Degeneration (AMD): Counseling on Antioxidant Supplement (PQRS # 140 - NQF 0566)','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 50 years and older with a Diagnosis of AMD & Relevant CPT','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Counseling about the benefits and/or risks of the Age-Related Eye Disease Study (AREDS) formulation for preventing progression of age-related macular degeneration (AMD) provided to patient and/or caregiver(s)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AMD_Counseling_1'] == 1){echo "YES";} 
	else if($row['AMD_Counseling_1'] == ''){echo " ";}
	else if($row['AMD_Counseling_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['AMD_Counseling_1'] == 1){echo "4177F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('AREDS counseling not performed, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AMD_Counseling_2'] == 1){echo "YES";} 
	else if($row['AMD_Counseling_2'] == ''){echo " ";}
	else if($row['AMD_Counseling_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AMD_Counseling_2'] == 1){echo "4177F - 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
<!--
 Domain : Effective Clinical Care - NQS Domain
  4) Atrial Fibrillation and Atrial Flutter: Chronic Anticoagulation Therapy (PQRS # 326 - NQF 1525)
 --> 
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Atrial Fibrillation and Atrial Flutter: Chronic Anticoagulation Therapy (PQRS # 326 - NQF 1525)','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 18 years and older with a Diagnosis of Atrial Fibrillation & Relevant CPT','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Warfarin OR another oral anticoagulant that is FDA approved prescribed','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AF_CAT_1'] == 1){echo "YES";} 
	else if($row['AF_CAT_1'] == ''){echo " ";}
	else if($row['AF_CAT_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['AF_CAT_1'] == 1){echo "G8967";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of medical reason(s) for not prescribing warfarin OR another oral anticoagulant that is FDA approved for the prevention of thromboembolism [e.g., patients with mitral stenosis or prosthetic heart valves, patients with transient or reversible causes of AF (e.g., pneumonia, hyperthyroidism, pregnancy, cardiac surgery), allergy, risk of bleeding, other medical reasons]','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AF_CAT_2'] == 1){echo "YES";} 
	else if($row['AF_CAT_2'] == ''){echo " ";}
	else if($row['AF_CAT_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AF_CAT_2'] == 1){echo "G8968";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of patient reason(s) for not prescribing warfarin OR another oral anticoagulant that is FDA approved for the prevention of thromboembolism (e.g., economic, social, and/or religious impediments, noncompliance, patient refusal, other patient reasons)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AF_CAT_3'] == 1){echo "YES";} 
	else if($row['AF_CAT_3'] == ''){echo " ";}
	else if($row['AF_CAT_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AF_CAT_3'] == 1){echo "G8969";} 
			else {echo '';}
	?>
    </td>
    </tr>
    
    <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('No risk factors or one moderate risk factor for thromboembolism','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AF_CAT_4'] == 1){echo "YES";} 
	else if($row['AF_CAT_4'] == ''){echo " ";}
	else if($row['AF_CAT_4'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AF_CAT_4'] == 1){echo "G8970";} 
			else {echo '';}
	?>
    </td>
    </tr>
    
	 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Warfarin OR another oral anticoagulant that is FDA approved not prescribed, reason not given','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AF_CAT_5'] == 1){echo "YES";} 
	else if($row['AF_CAT_5'] == ''){echo " ";}
	else if($row['AF_CAT_5'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AF_CAT_5'] == 1){echo "G8971";} 
			else {echo '';}
	?>
    </td>
    </tr>
	
	
     <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 	</tr>
	
     <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('One or more high risk factors for thromboembolism OR more than one moderate risk factor for thromboembolism','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AF_CAT_6'] == 1){echo "YES";} 
	else if($row['AF_CAT_6'] == ''){echo " ";}
	else if($row['AF_CAT_6'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AF_CAT_6'] == 1){echo "G8972";} 
			else {echo '';}
	?>
    </td>
    </tr>
    
    
 <!--
 Domain : Effective Clinical Care - NQS Domain
  5) Primary Open-Angle Glaucoma (POAG): Optic Nerve Evaluation (PQRS # 12 - NQF 0086)
 -->
  
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Primary Open-Angle Glaucoma (POAG): Optic Nerve Evaluation (PQRS # 12 - NQF 0086)','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 18 years and older with a Diagnosis of POAG & Relevant CPT','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Optic nerve head evaluation performed','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['POAG_Optic_1'] == 1){echo "YES";} 
	else if($row['POAG_Optic_1'] == ''){echo " ";}
	else if($row['POAG_Optic_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['POAG_Optic_1'] == 1){echo "2027F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of medical reason(s) for not performing an optic nerve head evaluation','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['POAG_Optic_2'] == 1){echo "YES";} 
	else if($row['POAG_Optic_2'] == ''){echo " ";}
	else if($row['POAG_Optic_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['POAG_Optic_2'] == 1){echo "2027F - 1P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Optic nerve head evaluation was not performed, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['POAG_Optic_3'] == 1){echo "YES";} 
	else if($row['POAG_Optic_3'] == ''){echo " ";}
	else if($row['POAG_Optic_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['POAG_Optic_3'] == 1){echo "2027F - 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
  <!--
 Domain : Effective Clinical Care - NQS Domain  
  6) Breast Cancer Screening – Measure #112 (NQF 2372)
 -->
  
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Measure #112 (NQF 2372): Breast Cancer Screening – National Quality Strategy Domain: Effective Clinical Care)','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Patients 50 through 74 years of age on date of encounter','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Screening mammography results documented and reviewed','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME112_1'] == 1){echo "YES";} 
	else if($row['ME112_1'] == ''){echo " ";}
	else if($row['ME112_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['ME112_1'] == 1){echo "3014F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of medical reason(s) for not performing a mammogram (i.e., women who had a bilateral mastectomy or two unilateral mastectomies)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME112_2'] == 1){echo "YES";} 
	else if($row['ME112_2'] == ''){echo " ";}
	else if($row['ME112_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME112_2'] == 1){echo "3014F - 1P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Screening mammography results were not documented and reviewed, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME112_3'] == 1){echo "YES";} 
	else if($row['ME112_3'] == ''){echo " ";}
	else if($row['ME112_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME112_3'] == 1){echo "3014F- 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
   <!--
 Domain : Effective Clinical Care - NQS Domain  
  7) Colorectal Cancer Screening – Measure #113 (NQF 0034)
 -->
  
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Measure #113 (NQF 0034): Colorectal Cancer Screening – National Quality Strategy Domain: Effective Clinical Care)','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Patients 51 through 75 years of age on date of encounter','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Colorectal cancer screening results documented and reviewed','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME113_1'] == 1){echo "YES";} 
	else if($row['ME113_1'] == ''){echo " ";}
	else if($row['ME113_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['ME113_1'] == 1){echo "3017F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of medical reason(s) for not performing a colorectal cancer screening (i.e., diagnosis of colorectal cancer or total colectomy)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME113_2'] == 1){echo "YES";} 
	else if($row['ME113_2'] == ''){echo " ";}
	else if($row['ME113_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME113_2'] == 1){echo "3017F - 1P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Colorectal cancer screening results were not documented and reviewed, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME113_3'] == 1){echo "YES";} 
	else if($row['ME113_3'] == ''){echo " ";}
	else if($row['ME113_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME113_3'] == 1){echo "3017F - 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
    <!--
 Domain : Effective Clinical Care - NQS Domain  
  8) Controlling High Blood Pressure – Measure #236 (NQF 0018)
 -->
  
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Measure #236 (NQF 0018): Controlling High Blood Pressure – National Quality Strategy Domain: Effective Clinical Care','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Patients 18 through 85 years of age on date of encounter','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent systolic blood pressure < 140 mmHg','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if(($row['htn_sp_lt_140'] == 1) && ($row['htn_dp_lt_90'] == 1)){echo "YES";} 
	else if($row['htn_sp_lt_140'] == ''){echo " ";}
	else if($row['htn_sp_lt_140'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if(($row['htn_sp_lt_140'] == 1) && ($row['htn_dp_lt_90'] == 1)){echo "G8752";} 
			else {echo '';}
	?>
    </td>
 </tr>
  <?php if(($row['htn_dhtn_dp_gte_90p_lt_90'] == 1) && ($row['htn_sp_gte_140'] == 1)) { ?>
 <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%"  style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
    <td>&nbsp;</td>
    <td class="detail" width="33%"  style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
 <?php } else { ?>
 <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
 <?php } ?>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent diastolic blood pressure < 90 mmHg','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if(($row['htn_dp_lt_90'] == 1) && ($row['htn_sp_lt_140'] == 1)){echo "YES";} 
	else if($row['htn_dp_lt_90'] == ''){echo " ";}
	else if($row['htn_dp_lt_90'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if(($row['htn_dp_lt_90'] == 1) && ($row['htn_sp_lt_140'] == 1)){echo "G8754";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent systolic blood pressure < 140 mmHg','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if(($row['htn_sp_lt_140'] == 1) && ($row['htn_dp_gte_90'] == 1)){echo "YES";} 
	else if($row['htn_sp_lt_140'] == ''){echo " ";}
	else if($row['htn_sp_lt_140'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if(($row['htn_sp_lt_140'] == 1) && ($row['htn_dp_gte_90'] == 1)){echo "G8752";} 
			else {echo '';}
	?>
    </td>
 </tr>
  <?php if(($row['htn_dhtn_dp_gte_90p_lt_90'] == 1) && ($row['htn_sp_gte_140'] == 1)) { ?>
 <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%"  style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
    <td>&nbsp;</td>
    <td class="detail" width="33%"  style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
 <?php } else { ?>
 <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
 <?php } ?>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent diastolic blood pressure = 90 mmHg','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if(($row['htn_dp_gte_90'] == 1) && ($row['htn_sp_lt_140'] == 1)){echo "YES";} 
	else if($row['htn_dp_gte_90'] == ''){echo " ";}
	else if($row['htn_dp_gte_90'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if(($row['htn_dp_gte_90'] == 1) && ($row['htn_sp_lt_140'] == 1)){echo "G8755";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent systolic blood pressure = 140 mmHg','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if(($row['htn_sp_gte_140'] == 1) && ($row['htn_dp_lt_90'] == 1)){echo "YES";} 
	else if($row['htn_sp_gte_140'] == ''){echo " ";}
	else if($row['htn_sp_gte_140'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if(($row['htn_sp_gte_140'] == 1) && ($row['htn_dp_lt_90'] == 1)){echo "G8753";} 
			else {echo '';}
	?>
    </td>
 </tr>
  <?php if(($row['htn_dhtn_dp_gte_90p_lt_90'] == 1) && ($row['htn_sp_gte_140'] == 1)) { ?>
 <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%"  style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
    <td>&nbsp;</td>
    <td class="detail" width="33%"  style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
 <?php } else { ?>
 <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
 <?php } ?>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent diastolic blood pressure < 90 mmHg','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if(($row['htn_dp_lt_90'] == 1) && ($row['htn_sp_gte_140'] == 1)){echo "YES";} 
	else if($row['htn_dp_lt_90'] == ''){echo " ";}
	else if($row['htn_dp_lt_90'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if(($row['htn_dp_lt_90'] == 1) && ($row['htn_sp_gte_140'] == 1)){echo "G8754";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent systolic blood pressure = 140 mmHg','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if(($row['htn_sp_gte_140'] == 1) && ($row['htn_dhtn_dp_gte_90p_lt_90'] == 1)){echo "YES";} 
	else if($row['htn_sp_gte_140'] == ''){echo " ";}
	else if($row['htn_sp_gte_140'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if(($row['htn_sp_gte_140'] == 1) && ($row['htn_dhtn_dp_gte_90p_lt_90'] == 1)){echo "G8753";} 
			else {echo '';}
	?>
    </td>
 </tr>
  <?php if(($row['htn_dhtn_dp_gte_90p_lt_90'] == 1) && ($row['htn_sp_gte_140'] == 1)) { ?>
 <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%"  style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
    <td>&nbsp;</td>
    <td class="detail" width="33%"  style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
 <?php } else { ?>
 <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
 <?php } ?>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Most recent diastolic blood pressure = 90 mmHg','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if(($row['htn_dhtn_dp_gte_90p_lt_90'] == 1) && ($row['htn_sp_gte_140'] == 1)){echo "YES";} 
	else if($row['htn_dp_gte_90'] == ''){echo " ";}
	else if($row['htn_dp_gte_90'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if(($row['htn_dhtn_dp_gte_90p_lt_90'] == 1) && ($row['htn_sp_gte_140'] == 1)){echo "G8755";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
  <tr bgcolor="#CEE4FF">
  	<td class="detail" width="33%"><?php xl('Documentation of end stage renal disease (ESRD), dialysis, renal transplant or pregnancy.','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['htn_esrd'] == 1){echo "YES";} 
	else if($row['htn_esrd'] == ''){echo " ";}
	else if($row['htn_esrd'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['htn_esrd'] == 1){echo "G9231";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
  <tr bgcolor="#CEE4FF">
  	<td class="detail" width="33%"><?php xl('No documentation of blood pressure measurement, reason not given','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['htn_bpm'] == 1){echo "YES";} 
	else if($row['htn_bpm'] == ''){echo " ";}
	else if($row['htn_bpm'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['htn_bpm'] == 1){echo "G8756";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
<!-------------------------------Domain : Communication and Care Coordination - NQS Domain------------------------------------>

	<tr bgcolor="#6CAEFF" align="center" class="domain">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Communication and Care Coordination - NQS Domain','e'  ) ?></td>
 </tr>
 
  <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Primary Open-Angle Glaucoma (POAG): Reduction of Intraocular Pressure (IOP) by 15% OR Documentation of a Plan of Care (PQRS # 141 - NQF 0563)','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 18 years and older with a Diagnosis of POAG & Relevant CPT','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Intraocular pressure (IOP) reduced by a value of greater than or equal to 15% from the pre-intervention level							
','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['POAG_IOP_1'] == 1){echo "YES";} 
	else if($row['POAG_IOP_1'] == ''){echo " ";}
	else if($row['POAG_IOP_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['POAG_IOP_1'] == 1){echo "3284F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Glaucoma plan of care documented','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['POAG_IOP_2'] == 1){echo "YES";} 
	else if($row['POAG_IOP_2'] == ''){echo " ";}
	else if($row['POAG_IOP_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['POAG_IOP_2'] == 1){echo "0517F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Glaucoma plan of care not documented, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['POAG_IOP_3'] == 1){echo "YES";} 
	else if($row['POAG_IOP_3'] == ''){echo " ";}
	else if($row['POAG_IOP_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['POAG_IOP_3'] == 1){echo "0517F - 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('IOP measurement not documented, reason not otherwise specified							
','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['POAG_IOP_4'] == 1){echo "YES";} 
	else if($row['POAG_IOP_4'] == ''){echo " ";}
	else if($row['POAG_IOP_4'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['POAG_IOP_4'] == 1){echo "3284F - 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
  <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Intraocular pressure (IOP) reduced by a value less than 15% from the pre-intervention level','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['POAG_IOP_5'] == 1){echo "YES";} 
	else if($row['POAG_IOP_5'] == ''){echo " ";}
	else if($row['POAG_IOP_5'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['POAG_IOP_5'] == 1){echo "3285F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
 <tr><td></td></tr>

 <!-------------------------------Domain : Patient Safety - NQS Domain------------------------------------>

	<tr bgcolor="#6CAEFF" align="center" class="domain">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Patient Safety - NQS Domain','e'  ) ?></td>
 </tr>
 
  <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Falls: Risk Assessment (PQRS # 154 - NQF 0101) - Cross Cutting Measure','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 65 years and older','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Falls risk assessment documented','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['gc_ra_1'] == 1){echo "YES";} 
	else if($row['gc_ra_1'] == ''){echo " ";}
	else if($row['gc_ra_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['gc_ra_1'] == 1){echo "3288F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of medical reason(s) for not completing a risk assessment for falls (i.e., patient is not ambulatory, bed ridden, immobile, confined to chair, wheelchair bound, dependent on helper pushing wheelchair, independent in wheelchair or minimal help in wheelchair)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['gc_ra_2'] == 1){echo "YES";} 
	else if($row['gc_ra_2'] == ''){echo " ";}
	else if($row['gc_ra_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['gc_ra_2'] == 1){echo "3288F - 1P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('No documentation of falls status','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['gc_ra_3'] == 1){echo "YES";} 
	else if($row['gc_ra_3'] == ''){echo " ";}
	else if($row['gc_ra_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['gc_ra_3'] == 1){echo "1101F - 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Patient screened for future fall risk; documentation of no falls in the past year or only one fall without injury in the past year','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['gc_ra_6'] == 1){echo "YES";} 
	else if($row['gc_ra_6'] == ''){echo " ";}
	else if($row['gc_ra_6'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['gc_ra_6'] == 1){echo "1101F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Falls risk assessment not completed, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['gc_ra_4'] == 1){echo "YES";} 
	else if($row['gc_ra_4'] == ''){echo " ";}
	else if($row['gc_ra_4'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['gc_ra_4'] == 1){echo "3288F - 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
  <tr bgcolor="#9bc8ff">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Patient screened for future fall risk; documentation of two or more falls in the past year or any fall with injury in the past year','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['gc_ra_5'] == 1){echo "YES";} 
	else if($row['gc_ra_5'] == ''){echo " ";}
	else if($row['gc_ra_5'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['gc_ra_5'] == 1){echo "1100F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
 
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Measure #130 (NQF 0419): Documentation of Current Medications in the Medical Record – National Quality Strategy Domain: Patient Safety','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Patients aged = 18 years on date of encounter','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Eligible professional attests to documenting in the medical record they obtained, updated, or reviewed the patient’s current medications','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME130_1'] == 1){echo "YES";} 
	else if($row['ME130_1'] == ''){echo " ";}
	else if($row['ME130_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['ME130_1'] == 1){echo "G8427";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Eligible professional attests to documenting in the medical record the patient is not eligible for a current list of medications being obtained, updated, or reviewed by the eligible professional','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME130_2'] == 1){echo "YES";} 
	else if($row['ME130_2'] == ''){echo " ";}
	else if($row['ME130_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME130_2'] == 1){echo "G8430";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Current list of medications not documented as obtained, updated, or reviewed by the eligible professional, reason not given','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME130_3'] == 1){echo "YES";} 
	else if($row['ME130_3'] == ''){echo " ";}
	else if($row['ME130_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME130_3'] == 1){echo "G8428";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
 <tr><td></td></tr>


<!------------------------------Domain : Communication and Care Coordination - NQS Domain II----------------------------->
 
 <tr bgcolor="#6CAEFF" align="center" class="domain">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Communication and Care Coordination - NQS Domain','e'  ) ?></td>
 </tr>
  
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Falls: Plan of Care (PQRS # 155 - NQF 0101) - Cross Cutting Measure','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 65 years and older with a History of Falls','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Falls plan of care documented','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['gc_poc_1'] == 1){echo "YES";} 
	else if($row['gc_poc_1'] == ''){echo " ";}
	else if($row['gc_poc_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['gc_poc_1'] == 1){echo "0518F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of medical reason(s) for no plan of care for falls (ie, patient is not ambulatory, bed ridden, immobile, confined to chair, wheelchair bound, dependent on helper pushing wheelchair, independent in wheelchair or minimal help in wheelchair)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['gc_poc_2'] == 1){echo "YES";} 
	else if($row['gc_poc_2'] == ''){echo " ";}
	else if($row['gc_poc_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['gc_poc_2'] == 1){echo "0518F - 1P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Plan of care not documented, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['gc_poc_3'] == 1){echo "YES";} 
	else if($row['gc_poc_3'] == ''){echo " ";}
	else if($row['gc_poc_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['gc_poc_3'] == 1){echo "0518F - 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
 <!------------------------------Domain : Community/Population Health - NQS Domain----------------------------->
 
 <tr bgcolor="#6CAEFF" align="center" class="domain">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Community/Population Health - NQS Domain','e'  ) ?></td>
 </tr>
  
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Preventive Care and Screening: Influenza Immunization (PQRS # 110 - NQF 0041) - Cross Cutting Measure','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 6 Months and older seen for a visit between October 1 and March 31 & Relevant CPT','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Influenza immunization administered or previously received','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['pcs_ii_1'] == 1){echo "YES";} 
	else if($row['pcs_ii_1'] == ''){echo " ";}
	else if($row['pcs_ii_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['pcs_ii_1'] == 1){echo "G8482";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Influenza immunization was not administered for reasons documented by clinician (e.g., patient allergy or other medical reasons, patient declined or other patient reasons, vaccine not available or other system reasons)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['pcs_ii_2'] == 1){echo "YES";} 
	else if($row['pcs_ii_2'] == ''){echo " ";}
	else if($row['pcs_ii_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['pcs_ii_2'] == 1){echo "G8483";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Influenza immunization was not administered, reason not given','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['pcs_ii_3'] == 1){echo "YES";} 
	else if($row['pcs_ii_3'] == ''){echo " ";}
	else if($row['pcs_ii_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['pcs_ii_3'] == 1){echo "G8484";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <!-- New added for MP acount -->
  <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Measure #128 (NQF 0421): Preventive Care and Screening: Body Mass Index (BMI) Screening and Follow-Up Plan – National Quality Strategy Domain: Community/Population Health','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Patients aged =18 years on date of encounter','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('BMI is documented within normal parameters and no follow-up plan is required','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['pcs_bmi_1'] == 1){echo "YES";} 
	else if($row['pcs_bmi_1'] == ''){echo " ";}
	else if($row['pcs_bmi_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['pcs_bmi_1'] == 1){echo "G8420";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('BMI is documented above normal parameters and a follow-up plan is documented','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['pcs_bmi_2'] == 1){echo "YES";} 
	else if($row['pcs_bmi_2'] == ''){echo " ";}
	else if($row['pcs_bmi_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['pcs_bmi_2'] == 1){echo "G8417";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('BMI is documented below normal parameters and a follow-up plan is documented','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['pcs_bmi_3'] == 1){echo "YES";} 
	else if($row['pcs_bmi_3'] == ''){echo " ";}
	else if($row['pcs_bmi_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['pcs_bmi_3'] == 1){echo "G8418";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('BMI not documented, documentation the patient is not eligible for BMI calculation','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['pcs_bmi_4'] == 1){echo "YES";} 
	else if($row['pcs_bmi_4'] == ''){echo " ";}
	else if($row['pcs_bmi_4'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['pcs_bmi_4'] == 1){echo "G8422";} 
			else {echo '';}
	?>
    </td>
 </tr>
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('BMI is documented as being outside of normal limits, follow-up plan is not documented, documentation the patient is not eligible','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['pcs_bmi_5'] == 1){echo "YES";} 
	else if($row['pcs_bmi_5'] == ''){echo " ";}
	else if($row['pcs_bmi_5'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['pcs_bmi_5'] == 1){echo "G8938";} 
			else {echo '';}
	?>
    </td>
 </tr>
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('BMI not documented and no reason is given','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['pcs_bmi_6'] == 1){echo "YES";} 
	else if($row['pcs_bmi_6'] == ''){echo " ";}
	else if($row['pcs_bmi_6'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['pcs_bmi_6'] == 1){echo "G8421";} 
			else {echo '';}
	?>
    </td>
 </tr>
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('BMI documented outside normal parameters, no follow-up plan documented, no reason given','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['pcs_bmi_7'] == 1){echo "YES";} 
	else if($row['pcs_bmi_7'] == ''){echo " ";}
	else if($row['pcs_bmi_7'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['pcs_bmi_7'] == 1){echo "G8419";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Measure #134 (NQF 0418): Preventive Care and Screening: Screening for Clinical Depression and Follow-Up Plan – National Quality Strategy Domain: Community/Population Health','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Patients aged = 12 years on date of encounter','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Screening for clinical depression is documented as being positive AND a follow-up plan is documented','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME134_1'] == 1){echo "YES";} 
	else if($row['ME134_1'] == ''){echo " ";}
	else if($row['ME134_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['ME134_1'] == 1){echo "G8431";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Screening for clinical depression is documented as negative, a follow-up plan is not required','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME134_2'] == 1){echo "YES";} 
	else if($row['ME134_2'] == ''){echo " ";}
	else if($row['ME134_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME134_2'] == 1){echo "G8510";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Screening for clinical depression not documented, documentation stating the patient is not eligible','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME134_3'] == 1){echo "YES";} 
	else if($row['ME134_3'] == ''){echo " ";}
	else if($row['ME134_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME134_3'] == 1){echo "G8433";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Screening for clinical depression documented as positive, a follow-up plan not documented, documentation stating the patient is not eligible','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME134_4'] == 1){echo "YES";} 
	else if($row['ME134_4'] == ''){echo " ";}
	else if($row['ME134_4'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME134_4'] == 1){echo "G8940";} 
			else {echo '';}
	?>
    </td>
 </tr>
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Clinical depression screening not documented, reason not given','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME134_5'] == 1){echo "YES";} 
	else if($row['ME134_5'] == ''){echo " ";}
	else if($row['ME134_5'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME134_5'] == 1){echo "G8432";} 
			else {echo '';}
	?>
    </td>
 </tr>
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Screening for clinical depression documented as positive, follow-up plan not documented, reason not given','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME134_6'] == 1){echo "YES";} 
	else if($row['ME134_6'] == ''){echo " ";}
	else if($row['ME134_6'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME134_6'] == 1){echo "G8511";} 
			else {echo '';}
	?>
    </td>
 </tr>




 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Measure #226 (NQF 0028): Preventive Care and Screening: Tobacco Use: Screening and Cessation Intervention – National Quality Strategy Domain: Community / Population Health','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Patients aged = 18 years on date of encounter','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Patient screened for tobacco use AND received tobacco cessation intervention (counseling, pharmacotherapy, or both), if identified as a tobacco user','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME226_1'] == 1){echo "YES";} 
	else if($row['ME226_1'] == ''){echo " ";}
	else if($row['ME226_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['ME226_1'] == 1){echo "4004F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Current tobacco non-user','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME226_2'] == 1){echo "YES";} 
	else if($row['ME226_2'] == ''){echo " ";}
	else if($row['ME226_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME226_2'] == 1){echo "1036F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of medical reason(s) for not screening for tobacco use (eg, limited life expectancy, other medical reasons)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME226_3'] == 1){echo "YES";} 
	else if($row['ME226_3'] == ''){echo " ";}
	else if($row['ME226_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME226_3'] == 1){echo "4004F-1P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Tobacco screening OR tobacco cessation intervention not performed, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ME226_4'] == 1){echo "YES";} 
	else if($row['ME226_4'] == ''){echo " ";}
	else if($row['ME226_4'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ME226_4'] == 1){echo "4004F-8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
 
  <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Measure #317: Preventive Care and Screening: Screening for High Blood Pressure and Follow-Up Documented – National Quality Strategy Domain: Community / Population Health','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 18 years and older','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Normal blood pressure reading documented, follow-up not required','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['htn_nbpr'] == 1){echo "YES";} 
	else if($row['htn_nbpr'] == ''){echo " ";}
	else if($row['htn_nbpr'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['htn_nbpr'] == 1){echo "G8783";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Pre-Hypertensive or Hypertensive blood pressure reading documented, AND the indicated follow-up is documented','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['htn_hbp_fd'] == 1){echo "YES";} 
	else if($row['htn_hbp_fd'] == ''){echo " ";}
	else if($row['htn_hbp_fd'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['htn_hbp_fd'] == 1){echo "G8950";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Patient not eligible (e.g., documentation the patient is not eligible due to active diagnosis of hypertension, patient refuses, urgent or emergent situation, documentation the patient is not eligible)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['htn_bpr_pne'] == 1){echo "YES";} 
	else if($row['htn_bpr_pne'] == ''){echo " ";}
	else if($row['htn_bpr_pne'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['htn_bpr_pne'] == 1){echo "G8784";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
  <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Blood pressure reading not documented, reason not given','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['htn_bpr_rng'] == 1){echo "YES";} 
	else if($row['htn_bpr_rng'] == ''){echo " ";}
	else if($row['htn_bpr_rng'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['htn_bpr_rng'] == 1){echo "G8785";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
   <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Pre-Hypertensive or Hypertensive blood pressure reading documented, indicated follow-up not documented, reason not given','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['htn_hbp_fnd_rng'] == 1){echo "YES";} 
	else if($row['htn_hbp_fnd_rng'] == ''){echo " ";}
	else if($row['htn_hbp_fnd_rng'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['htn_hbp_fnd_rng'] == 1){echo "G8952";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
<!------------------------ Domain : National Quality Strategy Domain ----------------------->
 
 <tr bgcolor="#6CAEFF" align="center" class="domain">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('National Quality Strategy Domain','e'  ) ?></td>
 </tr>
  
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Communication and Care Coordination : (PQRS #047 - NQF 0326) - Care Plan ','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 65 years and older','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Advance Care Planning discussed and documented; advance care plan or surrogate decision maker documented in the medical record','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['CP_1'] == 1){echo "YES";} 
	else if($row['CP_1'] == ''){echo " ";}
	else if($row['CP_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['CP_1'] == 1){echo "1123F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Advance Care Planning discussed and documented in the medical record; patient did not wish or was not able to name a surrogate decision maker or provide an advance care plan','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['CP_2'] == 1){echo "YES";} 
	else if($row['CP_2'] == ''){echo " ";}
	else if($row['CP_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['CP_2'] == 1){echo "1124F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Advance care planning not documented, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['CP_3'] == 1){echo "YES";} 
	else if($row['CP_3'] == ''){echo " ";}
	else if($row['CP_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['CP_3'] == 1){echo "1123F - 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 
 <!------------------------ Domain : National Quality Strategy Domain ----------------------->

  
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('Effective Clinical Care : (PQRS #091 - NQF 0653) - Acute Otitis Externa (AOE) : Topical Therapy','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 2 years and older with a diagnosis of AOE','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Topical preparations (including OTC) prescribed for acute otitis externa','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AOE_Topical_1'] == 1){echo "YES";} 
	else if($row['AOE_Topical_1'] == ''){echo " ";}
	else if($row['AOE_Topical_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['AOE_Topical_1'] == 1){echo "4130F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of medical reason(s) for not prescribing topical preparations (including OTC) for acute otitis externa (eg, coexisting acute otitis media, tympanic membrane perforation)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AOE_Topical_2'] == 1){echo "YES";} 
	else if($row['AOE_Topical_2'] == ''){echo " ";}
	else if($row['AOE_Topical_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AOE_Topical_2'] == 1){echo "4130F - 1P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of patient reason(s) for not prescribing topical preparations (including OTC) for acute otitis externa','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AOE_Topical_3'] == 1){echo "YES";} 
	else if($row['AOE_Topical_3'] == ''){echo " ";}
	else if($row['AOE_Topical_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AOE_Topical_3'] == 1){echo "4130F - 2P";} 
			else {echo '';}
	?>
    </td>
 </tr>

 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Topical preparations (including OTC) for acute otitis externa (AOE) not prescribed, reason not otherwise specified','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AOE_Topical_4'] == 1){echo "YES";} 
	else if($row['AOE_Topical_4'] == ''){echo " ";}
	else if($row['AOE_Topical_4'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AOE_Topical_4'] == 1){echo "4130F - 8P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
<!------------------------ Domain : National Quality Strategy Domain ----------------------->
 
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl(' Efficiency and Cost Reduction : (PQRS #093 - NQF 0654) - Acute Otitis Externa (AOE) : Systemic Antimicrobial Therapy ','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients aged 2 years and older with a diagnosis of AOE','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Systemic antimicrobial therapy not prescribed','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AOE_Systemic_1'] == 1){echo "YES";} 
	else if($row['AOE_Systemic_1'] == ''){echo " ";}
	else if($row['AOE_Systemic_1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['AOE_Systemic_1'] == 1){echo "4132F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of medical reason(s) for prescribing systemic antimicrobial therapy (eg, coexisting diabetes, immune deficiency)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AOE_Systemic_2'] == 1){echo "YES";} 
	else if($row['AOE_Systemic_2'] == ''){echo " ";}
	else if($row['AOE_Systemic_2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AOE_Systemic_2'] == 1){echo "4131F - 1P";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Systemic antimicrobial therapy prescribed','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['AOE_Systemic_3'] == 1){echo "YES";} 
	else if($row['AOE_Systemic_3'] == ''){echo " ";}
	else if($row['AOE_Systemic_3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['AOE_Systemic_3'] == 1){echo "4131F";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <!------------------------ Domain : National Quality Strategy Domain ----------------------->
 
 <tr bgcolor="#9bc8ff" class="measure">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl(' Efficiency and Cost Reduction : (PQRS #419) - Overuse Of Neuroimaging For Patients With Primary Headache And A Normal Neurological Examination','e'  ) ?></td>
 </tr>
   <tr bgcolor="#9bc8ff" class="note">
 	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('All patients with a diagnosis of primary headache','e'  ) ?></td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Advanced brain imaging (CTA, CT, MRA or MRI) was NOT ordered','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ONP_H1'] == 1){echo "YES";} 
	else if($row['ONP_H1'] == ''){echo " ";}
	else if($row['ONP_H1'] == '0'){echo "NO";}?></td>
	<td class="detail" width="33%">
    <?php if($row['ONP_H1'] == 1){echo "G9534";} 
			else {echo '';}
	?>
    </td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of Medical reason(s) for ordering an advanced brain imaging study (i.e., patient has an abnormal neurological examination; patient has the coexistence of seizures, or both; recent onset of severe headache; change in the type of headache; signs of increased intracranial pressure (e.g., papilledema, absent venous pulsations on funduscopic examination, altered mental status, focal neurologic deficits, signs of meningeal irritation); HIV-positive patients with a new type of headache; immunocompromised patient with unexplained headache symptoms; patient on coagulopathy/anti-coagulation or anti-platelet therapy; very young patients with unexplained headache symptoms)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ONP_H2'] == 1){echo "YES";} 
	else if($row['ONP_H2'] == ''){echo " ";}
	else if($row['ONP_H2'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ONP_H2'] == 1){echo "G9536";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Documentation of System reason(s) for ordering an advanced brain imaging study (i.e., needed as part of a clinical trial; other clinician ordered the study)','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ONP_H3'] == 1){echo "YES";} 
	else if($row['ONP_H3'] == ''){echo " ";}
	else if($row['ONP_H3'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ONP_H3'] == 1){echo "G9537";} 
			else {echo '';}
	?>
    </td>
 </tr>

 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Advanced brain imaging (CTA, CT, MRA or MRI) was ordered','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ONP_H4'] == 1){echo "YES";} 
	else if($row['ONP_H4'] == ''){echo " ";}
	else if($row['ONP_H4'] == '0'){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ONP_H4'] == 1){echo "G9538";} 
			else {echo '';}
	?>
    </td>
 </tr>
    
 <tr bgcolor="#9bc8ff">
	<td class="detail" width="33%" colspan="3" style="font-weight:bold"><?php xl('AND','e'  ) ?></td>
 </tr>
    
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="33%"><?php xl('Patients with a normal neurological examination','e'  ) ?></td>
	<td class="detail" width="33%"><?php 
	if($row['ONP_H1'] == 1 || $row['ONP_H4'] == 1){echo "YES";} 
	else if($row['ONP_H1'] == '' && $row['ONP_H4'] == ''){echo " ";}
	else if($row['ONP_H1'] == 0 || $row['ONP_H4'] == 0){echo "NO";} ?></td>
    <td class="detail" width="33%">
    <?php if($row['ONP_H1'] == 1 || $row['ONP_H4'] == 1){echo "G9535";} 
			else {echo '';}
	?>
    </td>
 </tr>
 
 <!--------------------- Notes --------------------->
 
 <tr bgcolor="#6CAEFF" style="font-weight:bold">
 	<td class="detail" width="50%"><?php xl('Note','e'  ) ?></td>
 </tr>
 <tr bgcolor="#CEE4FF">
 	<td class="detail" width="50%" colspan="3"><?php echo $row['Note']?></td>
 </tr>
 
 
 
<?php
}
?>


<html>
<head>
<?php html_header_show();?>
<title><?php xl('PQRS Information','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h3><?php xl('PQRS Information','e')?></h3>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>
 <tr bgcolor="#6CAEFF" style="font-weight:bold">
	  <td class="dehead" width="33%"><?php xl('PQRS Measure','e'  ) ?></td>
	  <td class="dehead" width="33%"><?php xl('Status','e'  ) ?></td>
      <td class="dehead" width="33%"><?php xl('Code','e'  ) ?></td>
 </tr>
<?php


  
	$query = "select * from pqrs where encounter = $encounter";


	$res = sqlStatement($query);
	while ($row = sqlFetchArray($res)) 
	{
		thisLineItem($row);
	}


?>

</table>
</center>
</body>
</html>
