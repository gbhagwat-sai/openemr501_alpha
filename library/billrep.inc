<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");
    require_once("{$GLOBALS['srcdir']}/formdata.inc.php");
    require_once(dirname(__FILE__) . "/../interface/reports/report.inc.php");
function GenerateTheQueryPart()
{
    global $query_part,$query_part2,$billstring,$auth;
    //Search Criteria section.
    $billstring='';
    $auth='';
    $query_part='';
    $query_part2='';
    if (isset($_REQUEST['final_this_page_criteria'])) {
        foreach ($_REQUEST['final_this_page_criteria'] as $criteria_key => $criteria_value) {
            $criteria_value=PrepareSearchItem($criteria_value); // this escapes for sql
            $SplitArray=array();
          //---------------------------------------------------------
            if (strpos($criteria_value, "billing.billed = '1'")!== false) {
                $billstring .= ' AND '.$criteria_value;
            } elseif (strpos($criteria_value, "billing.billed = '0'")!== false) {
                //3 is an error condition
                $billstring .= ' AND '."(billing.billed is null or billing.billed = '0' or (billing.billed = '1' and billing.bill_process = '3'))";
            } elseif (strpos($criteria_value, "billing.billed = '7'")!== false) {
                $billstring .= ' AND '."billing.bill_process = '7'";
            } //---------------------------------------------------------
            elseif (strpos($criteria_value, "billing.id = 'null'")!== false) {
                $billstring .= ' AND '."billing.id is null";
            } //---------------------------------------------------------
            elseif (strpos($criteria_value, "billing.id = 'not null'")!== false) {
                $billstring .= ' AND '."billing.id is not null";
            } //---------------------------------------------------------
            elseif (strpos($criteria_value, "patient_data.fname")!== false) {
                $SplitArray=explode(' like ', $criteria_value);
                $query_part .= " AND ($criteria_value or patient_data.lname like ".$SplitArray[1].")";
            } //---------------------------------------------------------
            elseif (strpos($criteria_value, "billing.authorized")!== false) {
                $auth = ' AND '.$criteria_value;
            } //---------------------------------------------------------
            elseif (strpos($criteria_value, "form_encounter.pid")!== false) {//comes like '781,780'
                $SplitArray=explode(" = '", $criteria_value);//comes like 781,780'
                $SplitArray[1]=substr($SplitArray[1], 0, -1);//comes like 781,780
                $query_part .= ' AND form_encounter.pid in ('.$SplitArray[1].')';
                $query_part2 .= ' AND pid in ('.$SplitArray[1].')';
            } //---------------------------------------------------------
            elseif (strpos($criteria_value, "form_encounter.encounter")!== false) {//comes like '781,780'
                $SplitArray=explode(" = '", $criteria_value);//comes like 781,780'
                $SplitArray[1]=substr($SplitArray[1], 0, -1);//comes like 781,780
                $query_part .= ' AND form_encounter.encounter in ('.$SplitArray[1].')';
            } //---------------------------------------------------------
            elseif (strpos($criteria_value, "insurance_data.provider = '1'")!== false) {
                $query_part .= ' AND '."insurance_data.provider > '0' and insurance_data.date <= form_encounter.date";
            } elseif (strpos($criteria_value, "insurance_data.provider = '0'")!== false) {
                $query_part .= ' AND '."(insurance_data.provider = '0' or insurance_data.date > form_encounter.date)";
            } //---------------------------------------------------------
            else {
                $query_part .= ' AND '.$criteria_value;
            }
        }
    }
}
// Sai custom code start
	 function _set_claim_inc_count($limit, $count) {
  // When the limit is exceeded, find out what the unlimited count would be.
  $GLOBALS['CLAIM_INC_COUNT'] = $count;
  // if ($limit != "all" && $GLOBALS['CLAIM_INC_COUNT'] >= $limit) {
  if ($limit != "all") {
   // $tmp = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE $where", $whereBindArray);
   $tmp = sqlQuery("SELECT count(*) AS count FROM form_encounter LEFT OUTER JOIN billing ON 
            billing.encounter = form_encounter.encounter AND billing.pid = form_encounter.pid
			LEFT OUTER JOIN patient_data on patient_data.pid = form_encounter.pid 
            LEFT OUTER JOIN claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter 
            LEFT OUTER JOIN insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' 
			LEFT JOIN claim_status on claim_status.id=form_encounter.claim_status_id ORDER BY form_encounter.encounter, form_encounter.pid, billing.code_type, billing.code ASC			
			 ");
			
    $GLOBALS['PATIENT_INC_COUNT'] = $tmp['count'];
  }
}
	 // Sai custom code end
    //date must be in nice format (e.g. 2002-07-11)
function getBillsBetween(
    $code_type,
    $cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id"
) {

    GenerateTheQueryPart();
    global $query_part,$billstring,$auth;
    // Selecting by the date in the billing table is wrong, because that is
    // just the data entry date; instead we want to go by the encounter date
    // which is the date in the form_encounter table.
    //
GenerateTheQueryPart();
		global $query_part,$billstring,$auth;
		// Selecting by the date in the billing table is wrong, because that is
		// just the data entry date; instead we want to go by the encounter date
		// which is the date in the form_encounter table.
		
		// Sai custom code start
		$findme = "billing.fee";
		$findme1 = "form_encounter.claim_status_id";
		
		$new_str = "(Select sum(fee) from billing where code_type='CPT4' and encounter=form_encounter.encounter)";
	
		$pos = strpos($query_part, $findme);
		
		if($pos !== false)
			$query_part = str_replace($findme,$new_str,$query_part);
			
    $sql = "SELECT distinct form_encounter.date AS enc_date, form_encounter.pid AS enc_pid, " .
    "form_encounter.encounter AS enc_encounter, form_encounter.provider_id AS enc_provider_id, billing.* " .
    "FROM form_encounter " .
    "LEFT OUTER JOIN billing ON " .
    "billing.encounter = form_encounter.encounter AND " .
    "billing.pid = form_encounter.pid AND " .
    "billing.code_type LIKE ? AND " .
    "billing.activity = 1 " .
    "LEFT OUTER JOIN patient_data on patient_data.pid = form_encounter.pid " .
    "LEFT OUTER JOIN claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter " .
    "LEFT OUTER JOIN insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' ".
    "WHERE 1=1 $query_part  " . " $auth " ." $billstring " ." AND (patient_data.stop_stmt NOT like '%self_pay%' || patient_data.stop_stmt IS NULL) ".
    "ORDER BY form_encounter.encounter, form_encounter.pid, billing.code_type, billing.code ASC";
    //echo $sql;
    $res = sqlStatement($sql, array($code_type));
    $all = false;
    for ($iter=0; $row=sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }
	return $all;
}
function getBillsBetweenReport(
    $code_type,
    $cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id"
) {

    GenerateTheQueryPart();
    global $query_part,$query_part2,$billstring,$auth;
    // Selecting by the date in the billing table is wrong, because that is
    // just the data entry date; instead we want to go by the encounter date
    // which is the date in the form_encounter table.
    //
    $sql = "SELECT distinct form_encounter.date AS enc_date, form_encounter.pid AS enc_pid, " .
    "form_encounter.encounter AS enc_encounter, form_encounter.provider_id AS enc_provider_id, billing.* " .
    "FROM form_encounter " .
    "LEFT OUTER JOIN billing ON " .
    "billing.encounter = form_encounter.encounter AND " .
    "billing.pid = form_encounter.pid AND " .
    "billing.code_type LIKE ? AND " .
    "billing.activity = 1 " .
    "LEFT OUTER JOIN patient_data on patient_data.pid = form_encounter.pid " .
    "LEFT OUTER JOIN claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter " .
    "LEFT OUTER JOIN insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' ".
    "WHERE 1=1 $query_part  " . " $auth " ." $billstring " .
    "ORDER BY form_encounter.encounter, form_encounter.pid, billing.code_type, billing.code ASC";
    //echo $sql;
    $res = sqlStatement($sql, array($code_type));
    $all = false;
    for ($iter=0; $row=sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    $query = sqlStatement("SELECT pid, 'COPAY' AS code_type, pay_amount AS code, date(post_time) AS date ".
    "FROM ar_activity where 1=1 $query_part2 and payer_type=0 and account_code='PCP'");
    //new fees screen copay gives account_code='PCP' openemr payment screen copay gives code='CO-PAY'
    for ($iter; $row=sqlFetchArray($query); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}
function getBillsListBetween(
    $code_type,
    $cols = "billing.id, form_encounter.date, billing.pid, billing.code_type, billing.code, billing.user"
) {

    GenerateTheQueryPart();
    global $query_part,$billstring,$auth;
    // See above comment in getBillsBetween().
    //
		$findme = "billing.fee";
		$findme1 = "form_encounter.claim_status_id";
		
		$new_str = "(Select sum(fee) from billing where code_type='CPT4' and encounter=form_encounter.encounter)";		
		$new_str1 = " claim_status.status=";
		
		
		$pos = strpos($query_part, $findme);
		if($pos !== false)
		$query_part = str_replace($findme,$new_str,$query_part);
    $sql = "select distinct $cols " .
    "from form_encounter, billing, patient_data, claims, insurance_data where " .
    "billing.encounter = form_encounter.encounter and " .
    "billing.pid = form_encounter.pid and " .
    "patient_data.pid = form_encounter.pid and " .
    "claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter and ".
    "insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' ".
    $auth  .
    $billstring . $query_part . " and " .
    "billing.code_type like ? and " .
    "billing.activity = 1 " .
    "order by billing.pid, billing.date ASC";

    $res = sqlStatement($sql, array($code_type));
    $string = "( ";
    for ($iter=0; $row=sqlFetchArray($res); $iter++) {
        $string .= $row{"id"}.",";
    }

    $string = substr($string, 0, strlen($string)-1);
    $string .= ")";
    return $string;
}

	/* Added By: Sonali. 
	             New function is added for Encounter summary page */
				 
	    function GenerateTheEncounterQueryPart()
     {

        global $query_part2,$query_part3,$billstring2,$auth2;
        //Search Criteria section.
        $billstring2='';
        $query_part2='';
        $query_part3='';		
        if(isset($_REQUEST['final_this_page_criteria']))
         {
            foreach($_REQUEST['final_this_page_criteria'] as $criteria_key => $criteria_value)
             {
              $criteria_value=PrepareSearchItem(stripslashes($criteria_value));
              $SplitArray=array();
              //---------------------------------------------------------
             if(strpos($criteria_value,"u.fname")!== false)
               {              
			    $SplitArray=split(' in ',$criteria_value);
				// echo $str =  mysql_real_escape_string($SplitArray[1]); 
				 $criteria = str_replace(","," ' ,' ",$SplitArray[1]);				
                 $query_part2 .= " AND u.id in $criteria"; 
				
               }
              //---------------------------------------------------------             
              elseif(strpos($criteria_value,"fe.facility")!== false)
               {
                 $SplitArray=split(' in ',$criteria_value);
				 $criteria = str_replace(","," ' ,' ",$SplitArray[1]);
                 $query_part2 .= " AND fe.facility_id in $criteria"; 
               }
			   //---------------------------------------------------------             
              elseif(strpos($criteria_value,"ic.id")!== false)
               {                
				 $SplitArray=split(' in ',$criteria_value);						
				  $criteria = str_replace(",","','",$SplitArray[1]);
                 $query_part2 .= " AND ic.id in $criteria"; 
               }
			   //---------------------------------------------------------    
			    elseif(strpos($criteria_value,"id.id")!== false)
               {                
				 $SplitArray=split(' in ',$criteria_value);						
				  $criteria = str_replace(",","','",$SplitArray[1]);
				  		//echo "select max(id),pid from insurance_data where provider in $criteria and type='primary' group by pid " . "<br>";
					
					
					
								  
				  $ins_qry = sqlStatement("
				  select fe.encounter as encounter
from insurance_data as insd join form_encounter as fe on fe.pid=insd.pid where
 date_format(insd.date,'%Y-%m-%d') <= fe.date and type='primary' and insd.provider in $criteria  and insd.id=
(select max(id) from insurance_data where type='primary' and pid=insd.pid)		  
				   ");
				  $string1=" ( ";
				  for($iter=0; $row=sqlFetchArray($ins_qry); $iter++)
					{
						$string1 .= $row{"encounter"}.",";
					}
					$string1 = substr($string1,0,strlen($string1)-1);
					$string1 .= ")";
					
					if(strlen($string1)<=3)
					$string1 = " (null) ";
                 $query_part2 .= " AND fe.encounter in $string1 "; 
				 
				 $query_part2 .= "pleasesplitquery";
				 
				 
				 
				 $ins_qry = sqlStatement("
				  select fe.id as encounter
from insurance_data as insd join form_encounter_draft as fe on fe.pid=insd.pid where
 date_format(insd.date,'%Y-%m-%d') <= fe.date and type='primary' and insd.provider in $criteria  and insd.id=
(select max(id) from insurance_data where type='primary' and pid=insd.pid)	  
				   ");
				  $string1=" ( ";
				  for($iter=0; $row=sqlFetchArray($ins_qry); $iter++)
					{
						$string1 .= $row{"encounter"}.",";
					}
					$string1 = substr($string1,0,strlen($string1)-1);
					$string1 .= ")";
					
					if(strlen($string1)<=3)
					$string1 = " (null) ";
                 $query_part2 .= " AND fe.id in $string1 "; 
				 
				 
			
				

				  

               }
			   //---------------------------------------------------------             
              elseif(strpos($criteria_value,"fe.final_status")!== false)
               {  
			     $SplitArray=split(' in ',$criteria_value);				
				 $criteria = str_replace(",","','",$SplitArray[1]);
                 $query_part3 .= " AND fe.final_status in $criteria";                
               }
              //---------------------------------------------------------
              elseif(strpos($criteria_value,"fe.batch_id")!== false)
               {
			   $SplitArray=split(' like ',$criteria_value);
                $query_part2 .= " AND $criteria_value";
               }            
              //---------------------------------------------------------
              else
               {
                $query_part2 .= ' AND '.$criteria_value;
               }
              }
         }
     }
	function getEncountersBetween(){
	
	GenerateTheEncounterQueryPart();
    global $query_part2,$query_part3,$billstring2,$auth2,$cnt;
	$cnt++;
	// echo "<br>@@".$query_part2."====".$query_part3;	
	
		
				
				if (strpos($query_part2,'pleasesplitquery') !== false) {
				 $basic_query_part2 = $query_part2;
				 $pieces = explode("pleasesplitquery ", $query_part2);

					$query_part2=$pieces[0]	; 
				}
				
				 
	
		
$sql1="select fe.id,fe.pid,b.bill_process,b.billed,date(b.date) as CreatedDate,pd.pubpid as ExternalID,
Concat(pd.lname, ' ', pd.fname, ' ', pd.mname) AS PatientName, 
Date(pd.DOB) AS DOB,Concat(u.lname, ' ', u.fname, ' ', u.mname) AS RenderingProvider, 
fe.facility as ServiceLocation, b.Encounter,DATE(fe.date) AS DOS,fe.batch_id as BatchNo,
(
select ic.name
from insurance_data as insd left join insurance_companies as ic on ic.id = provider where pid = fe.pid 
and date_format(date,'%Y-%m-%d') <= fe.date and type='primary' order by date DESC limit 1
) as InsuranceComp,
ic.name as LastBilledTo,
group_concat(IF(b.code_type = 'CPT4', b.code,NULL) SEPARATOR ' , ') AS CPT, 
(select pos_name from pos_list where id=fe.POS_ID) AS POS, 
group_concat(IF(b.code_type = 'ICD9',b.code,NULL) SEPARATOR ' , ') AS DX, 
SUM(b.fee) AS TotalCharge, 
'Approved' as final_status
from billing b left join form_encounter fe on b.encounter = fe.encounter 
left join users u on u.id = fe.provider_id   
left join patient_data pd on pd.pid = fe.pid  
left join insurance_companies ic on ic.id=b.payer_id 
where 1=1  $query_part2   " ." $billstring2 ".
" and b.activity = '1' group by b.encounter";
/*
(
select ic.name
from insurance_data as insd left join insurance_companies as ic on ic.id = provider where pid = fe.pid 
and date_format(date,'%Y-%m-%d') <= fe.date and type='primary' order by date DESC limit 1
) as InsuranceComp,

*/

	if ($basic_query_part2) {
			 $pieces = explode("pleasesplitquery ", $basic_query_part2);
					$query_part2=$pieces[1]	;
		}


$sql2="select fe.id,fe.pid,b.bill_process,b.billed,date(b.date) as CreatedDate,pd.pubpid as ExternalID,
Concat(pd.lname, ' ', pd.fname, ' ', pd.mname) AS PatientName, 
Date(pd.DOB) AS DOB,Concat(u.lname, ' ', u.fname, ' ', u.mname) AS RenderingProvider, 
fe.facility as ServiceLocation, IF(b.Encounter != 1,b.Encounter,NULL) as Encounter ,DATE(fe.date) AS DOS,fe.batch_id as BatchNo,
(
select ic.name
from insurance_data as insd left join insurance_companies as ic on ic.id = provider where pid = fe.pid 
and date_format(date,'%Y-%m-%d') <= fe.date and type='primary' order by date DESC limit 1
) as InsuranceComp,
ic.name as LastBilledTo,
group_concat(IF(b.code_type = 'CPT4', b.code,NULL) SEPARATOR ' , ') AS CPT, 
(select pos_name from pos_list where id=fe.POS_ID) AS POS, 
group_concat(IF(b.code_type = 'ICD9',b.code,NULL) SEPARATOR ' , ') AS DX, 
SUM(b.fee) AS TotalCharge, 
fe.final_status as final_status
from billing_draft b left join form_encounter_draft fe on b.draft_id = fe.id 
left join users u on u.id = fe.provider_id   
left join patient_data pd on pd.pid = fe.pid 
left join insurance_companies ic on ic.id=b.provider_id 
where 1=1 "."  $query_part2  "." $query_part3  " ." $billstring2 ".
"and b.Encounter=1 and b.activity = '1' group by b.draft_id
";
$sql = $sql1 ." union ". $sql2;

$strcomp = "save";
//echo "<br><br>@@@@".strpos($query_part3,$strcomp); echo "@@";
if(strpos($query_part3,$strcomp)!== false)
{
 // echo $sql;
 $res = sqlStatement($sql1);
 }
 else  {
  // echo $sql2;
   $res = sqlStatement($sql2);
   }
   

        for($iter=0; $row=sqlFetchArray($res); $iter++)

        {
            $all[$iter] = $row;
        }		
        return $all;
	
	}
 // Sai custom code end	
function billCodesList($list, $skip = "()")
{
    if ($list == "()") {
        return;
    }

    if ($skip == "()") {
        sqlStatement("update billing set billed=1 where id in $list");
    } else {
        sqlStatement("update billing set billed=1 where id in $list and id not in $skip");
    }

    return;
}

function ReturnOFXSql()
{
    GenerateTheQueryPart();
    global $query_part,$billstring,$auth;

    $sql = "SELECT distinct billing.*, concat(patient_data.fname, ' ', patient_data.lname) as name from billing "
    . "join patient_data on patient_data.pid = billing.pid "
    . "join form_encounter on "
    . "billing.encounter = form_encounter.encounter AND "
    . "billing.pid = form_encounter.pid "
    . "join claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter "
    . "join insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' "
    . "where billed = '1' "
    . "$auth "
    . "$billstring  $query_part  "
    . "order by billing.pid,billing.encounter";

    return $sql;
}
