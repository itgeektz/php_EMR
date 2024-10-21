<?php

require_once("Tree.class.php");

/**
 * class CategoryTree
 * This is a class for storing document categories using the MPTT implementation
 */

class CategoryTree extends Tree {

	
	/*
	*	This just sits on top of the parent constructor, only a shell so that the _table var gets set
	*/
	function __construct($root,$root_type = ROOT_TYPE_ID) {
		$this->_table = "categories";
		parent::__construct($root,$root_type);
	}
	
	function _get_categories_array($patient_id) {
		$categories = array();
		$sql = "SELECT c.id, c.name, d.id AS document_id, d.type,d.document_data, d.url, d.docdate,d.authorize_status "
			. " FROM categories AS c, documents AS d, categories_to_documents AS c2d"
			. " WHERE c.id = c2d.category_id"
			. " AND c2d.document_id = d.id";

		if (is_numeric($patient_id)) {
                        if ($patient_id == "") {
                              // Collect documents that are not assigned to a patient
                              $sql .= " AND (d.foreign_id = 0 OR d.foreign_id IS NULL) ";
                        }
                        else {
                              // Collect documents for a specific patient
			      $sql .= " AND d.foreign_id = '" . $patient_id . "'";
                        }
		}
		$sql .= " ORDER BY c.id ASC, d.docdate DESC, d.url ASC";



		//echo $sql;

		$sqltempcat="SELECT `id`,`title` FROM `template_categories` WHERE `status`=1";
		$TempCategory = $this->_db->Execute($sqltempcat);
		$tempcat_array=array();
		while ($TempCategory && !$TempCategory->EOF) {
			$tempcat_array[]=$TempCategory->fields['title'];
			$TempCategory->MoveNext();
		}

		$result = $this->_db->Execute($sql);
		

	  while ($result && !$result->EOF) {
	  	
	  	if( in_array($result->fields['name'], $tempcat_array))
	  	{
	  		if($result->fields['authorize_status']==1 || $result->fields['document_data']== "")
	  		$categories[$result->fields['id']][$result->fields['document_id']] = $result->fields;
	  	}
	  	else
	  	{
	  		$categories[$result->fields['id']][$result->fields['document_id']] = $result->fields;

	  	}
	  	$result->MoveNext();
	  }
	  
	  return $categories;
		
	}
}
?>
