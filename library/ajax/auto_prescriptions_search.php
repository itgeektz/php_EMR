<?php
/**
 * auto search codes
*
* Copyright (C) ViSolve <services@visolve.com>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* @package OpenEMR
* @author  ViSolve <services@visolve.com>
* @link    http://www.open-emr.org
*/

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once('../../interface/globals.php');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');


$file = fopen("/tmp/test.txt","w");


if($_REQUEST['pres_auto'] == 'pres_auto' ){


$search_term = $_REQUEST['search_term'];
$pat_cat = $_SESSION['pat_cat'];


$response = array();
$tmp = array();
$res = sqlStatement("select prm.generic_name as generic_name,prm.item_code,prm.item_name , prm.price,prm.rate_cat,prp.pat_cat from
		pharmacy_rate_master as prm left join pharmacy_rate_pat_master as prp on prm.rate_cat=prp.rate_cat where prp.pat_cat ='$pat_cat' and prm.generic_name like '%$search_term%' and  prp.status='ACTIVE' limit 15");

while ($row = sqlFetchArray($res)) {
	$tmp['id']==$row['id'];
	$tmp['drug'] = $row['generic_name'];
	$tmp['drug_code'] = $row['item_code'];
	$tmp['item_name'] = $row['item_name'];
	if($row['price']=='-1')
		$tmp['bill_type']=0;
		else
			$tmp['bill_type']=1;
			$tmp['rate_cat'] = $row['rate_cat'];
			$tmp['rate'] = $row['price'];
			$tmp['pat_cat'] =$row['pat_cat'];
			$tmp['price'] = $row['price'];


			array_push($response, $tmp);
}

echo json_encode($response);

}

if($_REQUEST['pres_age'] == 'pres_age')
{
	
	
	$code = $_REQUEST['drug_code'];
	$pid  = $_REQUEST['pid'];
	$medication = $_REQUEST['medication'];
	$encounter = $_REQUEST['encounter'];
	
	$list_of_encounter='';
	$lis_enc=sqlStatement("select distinct(encounter) from rmc_prescriptions where pid=? and encounter < ? order by encounter desc limit 2 " ,array($pid,$encounter));
	while($r_enc=sqlFetchArray($lis_enc))
	{
		if(empty($list_of_encounter))
			$list_of_encounter=$r_enc['encounter'];
			else
				$list_of_encounter.=", ".$r_enc['encounter'];
	}

	
	$response_data = array();
	$temp = array();
	
	//finding date Differnce for Calculate not 	
	function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
	{
		$datetime1 = date_create($date_1);
		$datetime2 = date_create($date_2);
			
		$interval = date_diff($datetime1, $datetime2);
			
		return $interval->format($differenceFormat);
			
	}
		
	if(!$medication){
	
		$result = sqlStatement("select pre.drug,pre.dosage,pre.drug_interval,pre.duration,pre.duration1,pre.quantity,en.date,timestampdiff(day,en.date,NOW()) as Days,timestampdiff(month,en.date,NOW()) as months,timestampdiff(year,en.date,NOW()) as years from rmc_prescriptions as pre inner join form_encounter as en on pre.encounter = en.encounter where pre.drug_code = ? and  pre.pid = ? order by en.date DESC limit 1",array($code,$pid));
		
		if(sqlNumRows($result) > 0)
		{
		
			while ($row = sqlFetchArray($result))
			{
					
					
				$formated_date = (new DateTime($row['date']))->format('Y-m-d');
					
					
					
				if($row['duration1'] == 'day' )
				{
					if($row['duration'] >= $row['Days'])
					{
						$temp['drug'] = $row['drug'];
						$temp['duration'] = $row['duration'];
						if($temp['duration'] > 1){
		
							$temp['duration1'] = $row['duration1']."s";
								
						}else{
		
							$temp['duration1'] = $row['duration1'];
		
						}
						$temp['dosage'] = $row['dosage'];
						$temp['quantity'] = $row['quantity'];
						$temp['interval'] = $row['drug_interval'];
						$temp['prescribed_date'] = $formated_date;
						$days = $row['duration']-$row['Days'];
						if($days > 1){
		
							$temp['pres_remain_days'] =$days." days";
		
						}
						else{
		
							$temp['pres_remain_days'] =$days." day";
						}
					
							echo json_encode($temp);
						
					}
				}
				if($row['duration1'] == 'month')
				{
					$end_date = date('y-m-d',strtotime($row['date']."+".$row['duration']." month"));
					$month_as_days =  dateDifference($row['date'], $end_date);
						
					if( $month_as_days  >= $row['Days'])
					{
						$temp['drug'] = $row['drug'];
						$temp['duration'] = $row['duration'];
						if($temp['duration'] > 1){
		
							$temp['duration1'] = $row['duration1']."s";
		
						}else{
		
							$temp['duration1'] = $row['duration1'];
		
						}
						$temp['dosage'] = $row['dosage'];
						$temp['quantity'] = $row['quantity'];
						$temp['interval'] = $row['drug_interval'];
						$temp['prescribed_date'] = $formated_date;
						$days = $month_as_days - $row['Days'];
		
						if($days > 1){
		
							$temp['pres_remain_days'] =$days." days";
		
						}
						else{
		
							$temp['pres_remain_days'] =$days." day";
						}
		
					
							echo json_encode($temp);
						
					}
				}
				if($row['duration1'] == 'year')
				{
					$end_date = date('y-m-d',strtotime($row['date']."+".$row['duration']."year"));
					$year_as_days =  dateDifference($row['date'], $end_date);
			 		
					if( $year_as_days  >= $row['Days'])
					{
		
		
						$temp['drug'] = $row['drug'];
						$temp['duration'] = $row['duration'];
						if($temp['duration'] > 1){
								
							$temp['duration1'] = $row['duration1']."s";
								
						}else{
								
							$temp['duration1'] = $row['duration1'];
								
						}
						$temp['dosage'] = $row['dosage'];
						$temp['quantity'] = $row['quantity'];
						$temp['interval'] = $row['drug_interval'];
						$temp['prescribed_date'] = $formated_date;
						$days = $year_as_days - $row['Days'];
							
						if($days > 1){
								
							$temp['pres_remain_days'] =$days." days";
								
						}
						else{
								
							$temp['pres_remain_days'] =$days." day";
						}
					
							echo json_encode($temp);
						
					}
		
				}
			
			}
			
		
		}
	}
	else{
		
		//$result = sqlStatement("select pre.drug,pre.dosage,pre.drug_interval,pre.duration,pre.duration1,pre.quantity,en.date,timestampdiff(day,en.date,NOW()) as Days,timestampdiff(month,en.date,NOW()) as months,timestampdiff(year,en.date,NOW()) as years from rmc_prescriptions as pre inner join form_encounter as en on pre.encounter = en.encounter where  pre.pid = ".$pid."  and pre.medication = 1  and pre.encounter in(".$list_of_encounter.") order by en.date DESC ");
		$result = sqlStatement("select pre.id,pre.pid,pre.encounter,pre.drug,pre.dosage,pre.drug_interval,pre.duration,pre.duration1,pre.quantity,MAX(en.date)as max_date,timestampdiff(day,MAX(en.date),NOW()) as Days,timestampdiff(month,MAX(en.date),NOW()) as months,timestampdiff(year,MAX(en.date),NOW()) as years from rmc_prescriptions as pre inner join form_encounter as en on pre.encounter = en.encounter where  pre.pid =".$pid."  and pre.medication = 1  and pre.encounter in(".$list_of_encounter.")  group by pre.drug_code");
		
		
		if(sqlNumRows($result) > 0)
		{
		
			while ($row = sqlFetchArray($result))
			{
				$formated_date = (new DateTime($row['max_date']))->format('Y-m-d');
				if($row['duration1'] == 'day' )
				{		
				
					$temp['drug'] = $row['drug']; 
					$temp['duration'] = $row['duration'];
					if($temp['duration'] > 1){
						
					$temp['duration1'] = $row['duration1']."s";
					
					}else{
						
						$temp['duration1'] = $row['duration1'];
						
					}
					$temp['dosage'] = $row['dosage'];
					$temp['quantity'] = $row['quantity'];
					$temp['interval'] = $row['drug_interval'];
					$temp['prescribed_date'] = $formated_date;
					$days = $row['duration']-$row['Days'];
					
					if( $days < 1 ){
						
						$days = 0;
					}
					if($days > 1){
						
						$temp['pres_remain_days'] =$days." days";
						
					}
					else{
						
						$temp['pres_remain_days'] =$days." day";
					}
				
						array_push($response_data, $temp);
				
	  			}
 			if($row['duration1'] == 'month')
 			{
 					$end_date = date('y-m-d',strtotime($row['max_date']."+".$row['duration']." month"));
 					$month_as_days =  dateDifference($row['max_date'], $end_date);
			
		 			
						$temp['drug'] = $row['drug'];
						$temp['duration'] = $row['duration'];
					    if($temp['duration'] > 1){
						
							$temp['duration1'] = $row['duration1']."s";
								
						}else{
						
							$temp['duration1'] = $row['duration1'];
						
						}
						$temp['dosage'] = $row['dosage'];
						$temp['quantity'] = $row['quantity'];
						$temp['interval'] = $row['drug_interval'];
						$temp['prescribed_date'] = $formated_date;
						$days = $month_as_days - $row['Days'];

						if( $days < 1 ){
						
							$days = 0;
						}
						if($days > 1){
						
							$temp['pres_remain_days'] =$days." days";
						
						}
						else{
						
							$temp['pres_remain_days'] =$days." day";
						}
						
					
							array_push($response_data, $temp);
					
			}
			if($row['duration1'] == 'year')
			{ 			
		 			$end_date = date('y-m-d',strtotime($row['max_date']."+".$row['duration']."year"));
 					$year_as_days =  dateDifference($row['max_date'], $end_date);
	 					
 				
 						$temp['drug'] = $row['drug'];
 						$temp['duration'] = $row['duration'];
 						if($temp['duration'] > 1){
 						
 							$temp['duration1'] = $row['duration1']."s";
 						
 						}else{
 						
 							$temp['duration1'] = $row['duration1'];
 						
 						}
 						$temp['dosage'] = $row['dosage'];
 						$temp['quantity'] = $row['quantity'];
 						$temp['interval'] = $row['drug_interval'];
 						$temp['prescribed_date'] = $formated_date;
 						$days = $year_as_days - $row['Days'];

 						if( $days < 1 ){
 						
 							$days = 0;
 						}
 						
 						if($days > 1){
 						
 							$temp['pres_remain_days'] =$days." days";
 						
 						}
 						else{
 						
 							$temp['pres_remain_days'] =$days." day";
 						}
 						
 							array_push($response_data, $temp);
 								
			}
	
			
		
		}

			echo json_encode($response_data);
				
	
	}
	
	
}

}



?>
