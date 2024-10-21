<?php

require_once("../globals.php");
require_once("$srcdir/html2pdf/vendor/autoload.php");
require_once("$srcdir/pnotes.inc");
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/forms.inc');

$addsuccess=0;

if(isset($_POST))
{

	if(isset($_POST['doc_id']) && isset($_POST['doc_name']))
	{

		     	$pdf = new HTML2PDF(
		     						  $GLOBALS['pdf_layout'],
		     						  $GLOBALS['pdf_size'],
		     						  $GLOBALS['pdf_language'],
		      						  true, // default unicode setting is true
		      						  'UTF-8', // default encoding setting is UTF-8
		      						  array($GLOBALS['pdf_left_margin'],$GLOBALS['pdf_top_margin'],$GLOBALS['pdf_right_margin'],$GLOBALS['pdf_bottom_margin']),
		      						  $_SESSION['language_direction'] == 'rtl' ? true : false
		  							);
		  		 $pdf->setDefaultFont('dejavusans');
			
		  		 $sql="select * from  documents where id=".$_POST['doc_id'];
		  		 $res=sqlQuery($sql);  
		     
		  		 $pdf->writeHTML($_POST['textarea15'], false);
		   
		   		 $fname=$_POST['doc_name'];
		    	 $path=$OE_SITE_DIR."/documents/".$res['foreign_id'];
			
			    if(is_dir($path))
		   		{
		      		if (file_exists($path."/".$fname.".pdf")) 
		      		{
		        		unlink($path."/".$fname.".pdf");
		    		}
		    		$pdf->Output( $path."/".$fname.".pdf", 'F');
		    		$file_name=$path."/".$fname.".pdf";
		   		}
		   		else 
		   		{
		    		mkdir($path,'0755');
		    		$pdf->Output( $path."/".$fname.".pdf", 'F');
		    		$file_name=$path."/".$fname.".pdf";
		   		}
		   		$size=filesize($file_name);
		   		$mimetype='application/pdf';
		   		$doc_data=add_escape_custom(htmlentities($_POST['textarea15']));
		   		$qry="update documents set document_data='$doc_data', mimetype='$mimetype' ,size='$size' where id='".$_POST['doc_id']."'";
		   		sqlQuery($qry);
		   	$addsuccess=2;
		  }
 	 else{

		    $id=sqlQuery("select id from sequences");
		    
		    
   			$id1=$id['id']+1;
    		$pdf = new HTML2PDF (
    								$GLOBALS['pdf_layout'],
      								$GLOBALS['pdf_size'],
      								$GLOBALS['pdf_language'],
      								true, // default unicode setting is true
      								'UTF-8', // default encoding setting is UTF-8
      								array($GLOBALS['pdf_left_margin'],$GLOBALS['pdf_top_margin'],$GLOBALS['pdf_right_margin'],$GLOBALS['pdf_bottom_margin']),
      								$_SESSION['language_direction'] == 'rtl' ? true : false
  								 );	
  			$pdf->setDefaultFont('dejavusans');
		    $pdf->writeHTML($_POST['textarea15'], false);
  
  			$sql="select title from  template_categories where id=".$_POST['view_temp_category'];
  			$res=sqlQuery($sql);
    		$fname=$res['title']."_".date('d-m-Y')."_".$id1."_report";
    		$repotname=$res['title']."_".date('d-m-Y')."_".$id1."_report";
    		$path=$OE_SITE_DIR."/documents/".$pid;
   			if(is_dir($path))
   			{

			
    			$pdf->Output( $path."/".$fname.".pdf", 'F');
    			$file_name=$path."/".$fname.".pdf";
   			}
   			else 
   			{
    			mkdir($path,'0755');
    			$pdf->Output( $path."/".$fname.".pdf", 'F');
     			$file_name=$path."/".$fname.".pdf";
   			 }
   
   			$file_name=$path."/".$fname.".pdf";
   			$size=filesize($file_name);
   			$mimetype="application/pdf";
   			$date=date('Y-m-d h:m:s');
   			$owner=$_SESSION['authUserID'];
   			$url="file://".$file_name;
   			$foreign_id=$pid;
   			$docdate=date('Y-m-d');
   			$hash=sha1_file($file_name);
   			$doc_data=add_escape_custom(htmlentities($_POST['textarea15']));
   			$notification_group=$GLOBALS['notification_group'];
   			$encounter=$_SESSION['encounter'];
		    $sql="insert into documents (id,type,size,date,url,mimetype,owner,revision,foreign_id,docdate,hash,list_id,encounter_id,document_data,notification_group)values ('$id1','file_url','$size','$date','$url','$mimetype',$owner,'$date','$foreign_id','$docdate','$hash','0',$encounter,'$doc_data','$notification_group')";
  
    		//new entry in documents  
    		sqlinsert($sql);
	   
		    //update seqeunces 
     		sqlQuery("update sequences set id=$id1"); 
     
		    //find category of template
     		$catid=sqlQuery("select id from categories where name='".$res['title']."'");
     
			//map new document entry with category
     		sqlInsert("insert into categories_to_documents (category_id,document_id) values('".$catid['id']."',".$id1.")");
     		$addsuccess=1;

     		$sqlg="SELECT ga.value as groupusers FROM `gacl_aro` ga join  gacl_groups_aro_map  ggam on ggam.aro_id=ga.id  WHERE ggam.group_id='".$GLOBALS['notification_group']."'";
     		$groupusers= sqlStatement($sqlg);
      		$j=array();
     		
     		while($i=sqlFetchArray($groupusers))
     		{
        		$j[]=$i['groupusers'];
     		}
     		
     		$groupusers=$j;
     		$patient_name=sqlQuery("select concat(title,' ',fname,' ',lname) as fullname from patient_data  where pid='".$pid."'");
     
	    	// Add a new message for a specific patient; the message is documented in Patient Notes.
	        // Add a new message; it's treated as a new note in Patient Notes.
        	$note = "".$_SESSION['authUser']." uploaded report of ".$res['title']." patient id:".$foreign_id." patient name:".$patient_name['fullname']." report id:".$id1."report name:".$repotname."report date:".$date." encounter:".$encounter;
        	$noteid = '';
        	$form_note_type = "New Document";
        	$form_message_status ="New";
        	$reply_to = $foreign_id;
        	$assigned_to_list = $groupusers;
        	foreach($assigned_to_list as $assigned_to)
        	{
        	 //  updatePnote($noteid, $note, $form_note_type, $assigned_to, $form_message_status);
             // When $assigned_to == '-patient-' we don't update the current note, but
            // There's no note ID, and/or it's assigned to the patient.
            // In these cases a new note is created.
            	addPnote($reply_to, $note, '1', '1', $form_note_type, $assigned_to, '', $form_message_status);
    		}

    }
  
    
}


$url="Location: custom_reports.php?addsuccess=".$addsuccess;

  if($_SESSION['usergroup']=='Administrators' || $_SESSION['usergroup']=='Physicians'||$_SESSION['usergroup'] == 'DataEntry User'|| $_SESSION['usergroup'] == 'Technician')
  {
        $url="Location: ../patient_file/summary/document_pending_list.php?addsuccess=".$addsuccess;
    }
    else
    {
        $url="Location: custom_reports.php?addsuccess=".$addsuccess;
     
    }
    
    header($url);
    exit;
?>
