<?php
/*
* Created By 	: Kiran Malave
* Crated Date 	: 11 Jan 2017 
* Description 	: Display entered PQRS measures for the selected encounter
*/
//print "hwer"; exit;
require_once("../../globals.php");
require_once("$srcdir/sql.inc");
//require_once("../../../custom/code_types.inc.php");
//require_once("$srcdir/patient.inc");
//require_once("$srcdir/acl.inc");
//require_once("$srcdir/formatting.inc.php");
//require_once "$srcdir/options.inc.php";
//require_once "$srcdir/formdata.inc.php";

function select($query)
	{
		$res = sqlStatement($query);
		$data = array();
		while ($row = sqlFetchArray($res)) 
		{
			$data[] = $row;	
		}
		return $data;
	}

	function pqrsDetails($encounterID)
	{
		
        $reportedID = array();
        $reportedMeasureCriteriaID = array();
        $reportedMeasureChildCriteriaID = array();
       
        /*
        * Code Title : Checked exiting reported PQRS for patient.
        * Description : This section compare the reported PQRS and eligible PQRS measure.
        * Code : Start
        */
        $mode ="";
        $reportedPQRSDetails = GetReportedPQRSDetails($encounterID);
      // echo "<pre>"; print_r($reportedPQRSDetails);exit;

        if(!empty($reportedPQRSDetails))
        {
            foreach ($reportedPQRSDetails as $key => $value)
            {

                $reportedMeasureID[] = $value['PQRSMeasureID'];
                $reportedMeasureCriteriaID[] = $value['PQRSMeasureCriteriaID'];
                $reportedMeasureChildCriteriaID[] = $value['PQRSMeasureChildCriteriaID'];
            }
        }
       /*  print "<pre>";
         print_r( $reportedMeasureCriteriaID); //exit;   
        /*
        * Code Title : To get all reported PQRS list against the patient.
        * Description : This section give us all measure list which is reported to that patient.
        * Code : Start
        */
        $pqrsDetails = GetPQRSDetails();
		
		//echo "<pre>"; print_r($pqrsDetails);
		
        $patientPQRS['PQRS']= array();
        $i=0;
        if(!empty($pqrsDetails))
        {
            foreach ($pqrsDetails as $key => $value) 
            {
                if (!in_array($value['PQRSMeasureID'], $reportedID))
                {

                    //echo $reportedID."====I am here <br>";
                    $patientPQRS["PQRS"][$i]['measureID'] = $value['PQRSMeasureID'];
                    $patientPQRS["PQRS"][$i]['measureName'] = $value['measureName'];
                    $patientPQRS["PQRS"][$i]['PQRSMeasureNumber'] = $value['PQRSMeasureNumber'];
                    $patientPQRS["PQRS"][$i]['measureDescription'] = $value['measureDescription'];
                    $subcritiria = GetMesureCritiria($value['PQRSMeasureID']);

                    $subcritiriasub = $subcritiria;
                    foreach ($subcritiria as $subkey => $subvalue)
                    {

                    	$foundrealtion="";
                    	$measureIndex="";
                    	if($subvalue['CombinedPQRSChildMeasureCriteriaid'] !='' && $subvalue['CombinedPQRSChildMeasureCriteriaid'] !='0')
                    	{
                    		$foundrealtion = $subkey;
                    		$foundationval = $subvalue['CombinedPQRSChildMeasureCriteriaid'];
                    	}
                    	if(isset($foundationval) && !empty($foundationval))
                    	{
                    		foreach ($subcritiriasub as $indexkey => $subcheckvalue)
	                    	{
	                    		if($foundationval == $subcheckvalue['PQRSMeasureChildCriteriaID'])
	                    		{
	                    			$measureIndex = $indexkey;
	                    		}
	                    	}
	                    }
	                    //print "mesure index".$measureIndex."  foundation ".$foundrealtion."    criteriaCondition".$subvalue['criteriaCondition'] ."<br>";  
                    	if(($measureIndex !=='') && ($foundrealtion !=='') &&  $subvalue['criteriaCondition']== "AND")
                    	{

                    		$subcritiria[$foundrealtion]['displayCriteriaText'] = $subcritiria[$measureIndex]['displayCriteriaText']."<br><br> ".$subcritiria[$foundrealtion]['displayCriteriaText'];

	                    	$subcritiria[$foundrealtion]['criteriaText'] = $subcritiria[$measureIndex]['criteriaText']." ".$subcritiria[$foundrealtion]['criteriaText'];
	                    	
	                    	if($subcritiria[$measureIndex]['criteriaModifier'] !='' && $subcritiria[$measureIndex]['criteriaModifier'] !='Null')
	                    		$ideti = $subcritiria[$measureIndex]['criteriaIdentifier']."-". $subcritiria[$measureIndex]['criteriaModifier'];
	                    	else
	                    		$ideti = $subcritiria[$measureIndex]['criteriaIdentifier'];

	                    	if($subcritiria[$foundrealtion]['criteriaModifier'] !='' && $subcritiria[$foundrealtion]['criteriaModifier'] !='Null')
	                    		$foundrealtionideti = $subcritiria[$foundrealtion]['criteriaIdentifier']."-". $subcritiria[$foundrealtion]['criteriaModifier'];
	                    	else
	                    		$foundrealtionideti = $subcritiria[$foundrealtion]['criteriaIdentifier'];
							
							if(isset($foundrealtionideti) && !empty($foundrealtionideti))
								$subcritiria[$foundrealtion]['criteriaIdentifier'] = $ideti.",".$foundrealtionideti;
							else
								$subcritiria[$foundrealtion]['criteriaIdentifier'] = $ideti;
							
							if(isset($subcritiria[$foundrealtion]['criteriaModifier']) && !empty($subcritiria[$foundrealtion]['criteriaModifier']))
								$subcritiria[$foundrealtion]['criteriaModifier'] = $subcritiria[$measureIndex]['criteriaModifier'].",".$subcritiria[$foundrealtion]['criteriaModifier'];
							else
								$subcritiria[$foundrealtion]['criteriaModifier'] = $subcritiria[$measureIndex]['criteriaModifier'];

	                    	$subcritiria[$foundrealtion]['id'] = $subcritiria[$measureIndex]['id']."_".$subcritiria[$foundrealtion]['id'];
	                    		unset($subcritiria[$measureIndex]);


                        }
                        // this code is added for mark selected measure criterias
                       
                        if(in_array($subvalue['PQRSMeasureCriteriaID'],$reportedMeasureCriteriaID) && in_array($subvalue['PQRSMeasureChildCriteriaID'],$reportedMeasureChildCriteriaID))
                        {
                            $subcritiria[$subkey]['isSelected'] = "1";
                        }

                    }

                   /// print_r($subcritiria);
                    $subcritiria = array_values($subcritiria);
                    $patientPQRS["PQRS"][$i]['criteria'] =  $subcritiria;
                   	$i++;   
                 }
            }
        }
        else
        {
                $patientPQRS["PQRS"]= array();
        }
       return $patientPQRS;
    }

    function GetReportedPQRSDetails($encounterID='')
    {

        $select="SELECT * FROM encounterpqrs WHERE encounterID='".$encounterID."'";
        $result = select($select);
        return $result;
    }

	function GetPQRSDetails()
	{
		$query = "select * from pqrsmeasure where isActive = '1' ";
		$pqrsMesures = select($query);
		return $pqrsMesures;
	}
	function GetMesureCritiria($procedureCodes='')
    {
        $sql = "SELECT *
                        FROM (

                        SELECT pmc.PQRSMeasureCriteriaID, 0 as PQRSMeasureChildCriteriaID, pmc.criteriaSequence, 0 AS combinationSequence, pmc.criteriaCondition, pmc.PQRSMeasureNumber, pmc.hasChilds, pmc.displayCriteriaText, pmc.criteriaText, pmc.criteriaIdentifier, pmc.criteriaModifier, pmc.PQRSReportingPerformanceMetTypeID,0 as isCombined ,0 as CombinedPQRSChildMeasureCriteriaid,0 as isSelected,CONCAT(PQRSMeasureCriteriaID,'_','0') as id
                        FROM pqrsmeasure as pm INNER JOIN pqrsmeasurecriteria as pmc ON pm.PQRSMeasureID = pmc.PQRSMeasureNumber
                        WHERE (hasChilds =0 OR hasChilds=null) 
                        AND pm.isActive = 1 
                        AND pm.PQRSMeasureID =".$procedureCodes."
                        UNION ALL
                        SELECT pmcs.PQRSMeasureCriteriaID, pmcs.PQRSMeasureChildCriteriaID, pmc.criteriaSequence, pmcs.combinationSequence, pmcs.combinedPQRSChildMeasureCriteriaCondition AS criteriaCondition, pmc.PQRSMeasureNumber, 0 as hasChilds, pmcs.displayCriteriaText, pmcs.criteriaText, pmcs.criteriaIdentifier, pmcs.criteriaModifier, pmcs.PQRSReportingPerformanceMetTypeID,pmcs.isCombined,pmcs.CombinedPQRSChildMeasureCriteriaid,0 as isSelected,CONCAT(pmcs.PQRSMeasureCriteriaID,'_',pmcs.PQRSMeasureChildCriteriaID) as id
                        FROM  pqrsmeasure as pm INNER JOIN pqrsmeasurecriteria as pmc ON pm.PQRSMeasureID = pmc.PQRSMeasureNumber INNER JOIN pqrsmeasurechildcriteria AS pmcs ON pmc.PQRSMeasureCriteriaID = pmcs.PQRSMeasureCriteriaID
                        
                        AND pmc.hasChilds =1
                        AND pm.isActive = 1
                        WHERE pm.PQRSMeasureID =".$procedureCodes."
                        )d
                        ORDER BY d.criteriaSequence , d.combinationSequence";

        //echo $sql;
                        //INNER JOIN pqrsmeasurecriteria AS pmc ON pmc.PQRSMeasureCriteriaID = pmcs.PQRSMeasureCriteriaID
                       
       	$pqrsMesures = select($sql);
		return $pqrsMesures;

    }
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
	
	$billerPQRSid= "SELECT billEHREncounterID FROM form_encounter WHERE encounter='".$encounter."'";
    $result = select($billerPQRSid);
	//print_r($result);
	
	if(isset($result) && !empty($result))
	{
		$pqrsData = pqrsDetails($result[0]['billEHREncounterID']);	
	}	
	else
	{
		echo "no PQRS found"; exit;
	}
	
	/*print "<pre>";
	print_r($pqrsData);*/
?>
<table>
	<tr style="font-weight:bold" bgcolor="#6CAEFF">
		<td class="dehead" width="50%">PQRS Measure</td>
		<td class="dehead" align="center" width="15%">Status</td>
		<td class="dehead" width="35%">Code</td>
 	</tr>
 	<?php 
 	foreach ($pqrsData['PQRS'] as $key => $value) {
 		?>
 		<tr style="font-weight:bold" bgcolor="#6CAEFF">
			<td class="dehead" ><?php echo "#".$value['PQRSMeasureNumber']." - ".$value['measureName']; ?></td>
			<td class="dehead">&nbsp;</td>
			<td class="dehead">&nbsp;</td>
		</tr>

			<?php 
			if(isset($value['criteria']) && !empty($value['criteria']))
			{
				foreach ($value['criteria'] as $key => $criteriaValue) {
				?>	
				<?php 
							if($criteriaValue['isSelected'] == '1'){
					?>
				<tr  style="background:#004A63;color:#fff">
				<?php } else {?>
					<tr bgcolor="#94C0F7" >
					<?php } ?>
					<td class="dehead"><?php echo $criteriaValue['displayCriteriaText'];?></td>
					<td class="dehead" align="center"><?php if($criteriaValue['isSelected'] == '1'){ echo "Selected"; } ?></td>
					
					<td class="dehead">
					
						<?php 
							if($criteriaValue['isSelected'] == '1'){
								
								if(isset($criteriaValue['criteriaModifier']) && !empty($criteriaValue['criteriaModifier']))
								{
									echo $criteriaValue['criteriaIdentifier'].'-'.$criteriaValue['criteriaModifier'];
								}
								else
								{
									echo $criteriaValue['criteriaIdentifier'];
								}
							}
						?>

					</td>
				</tr>
				<?php
				}
			}?>	
 		
 	<?php
 	}
 	?>
 </table>
</body>
</html>
