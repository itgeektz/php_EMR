<?php
include_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"]."/api.inc"); 
include_once(dirname(__FILE__).'sql.inc');

function lab_report( $pid, $encounter, $cols, $id) {


 $data = formFetch("form_lab_rmc",$id);

  if($data){
  	$codes =  json_decode($data['lab_codes']);
   	print "<table>";
   	foreach ($codes as $category => $cat_values)
 	{
 	
 		if($category != 'source')
 		{	
		 		print "<thead>";
			 	print "<tr><th style='text-align:left; font-size:14px; width:170px;'>".$category.":</th></tr>";	
		 		print "</thead>";
		 		foreach ($cat_values as $value)
		 		{
		 						
		 			$check_res=sqlStatement("SELECT title FROM  list_options WHERE codes = '".$value."' ORDER BY seq ASC");
		 			$numrows=sqlNumRows($check_res);
		 			if($numrows >0)
		 			{
		 					
		 				while( $row = sqlFetchArray($check_res) )
		 				{
		 					print "<tr><td></td><td style='font-size:12px; font-family: sans-serif;'>".$row['title']."</td></tr>";
		 				}
		 			}	
		 		}
 		} 		
 	}
 	
 	print "</table>";
  	
 }
	 
}
