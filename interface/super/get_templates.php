<?php
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../globals.php");

if(isset($_POST['id']))
{
	
	$res=array();
	
	$sql=sqlStatement("select id, name, path from custom_template_documents where template_group_id=".$_POST['id']);

	
	if(sqlNumRows($sql) > 0)
	{
		$res['status']="1";
		$htm='';
		while($row=sqlFetchArray($sql))
		{
			$name=explode(".",$row['name']);
		  
			$htm.='<li><a href="#" class="view-template" name="'.strtoupper($name[0]).'" id="temp_'.$row['id'].'" path="'.$row['path'].'">'.strtoupper($name[0]).'</li>';
		  
	    }
	  
	    $res['msg']=$htm;
	    echo json_encode($res);
	    die();
	}
	else 
	{
		$res['status']="0";
		echo json_encode($res);
		
	}
	
}
?>