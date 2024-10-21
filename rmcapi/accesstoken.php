<?php
require_once("db_request.php");

/**
 * Get the random string with alphanumeric characters
 *
 * @param  String  $length    Length of the random string
 */
function generate_accesstoken($length=16)
{
    global $conn;

    $characters = "abcdefghijklmnopqrstuvwxyzABCDERFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $randomString = "";
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[mt_rand(0, strlen($characters)-1)];
    }

    $qry = "SELECT id FROM session WHERE accesstoken=$randomString";
    $res = $conn->query($qry);

    if($res === false){
	return $randomString;
    }

   if($res->num_rows > 0){
    while($result = $res->fetch_assoc()){
	     $id = $result['id'];
    }
   }else{
	    generate_accesstoken();
   }

   return $randomString;

}

/**
 * Validate the access token
 *
 * @param  String  $accesstoken Accesstoken for the session
 */
function validate_accesstoken($accesstoken){

    global $conn;
    $id = "";
    $qry = "SELECT id FROM session WHERE accesstoken='$accesstoken' AND (NOW() BETWEEN createdtime AND expirytime)";

    $res = $conn->query($qry);
    while($result = $res->fetch_assoc()){
        $id = $result['id'];
    }
    if($id != ""){
	     return true;
    } else {
	     return false;
    }
}

/**
 * Delete the accesstoken from the session table
 *
 * @param  String  $uid 	User ID for which we need to delete the access token
 * @param  String  $accesstoken Accesstoken for the session
 */
function delete_accesstoken($uid,$accesstoken){

    global $conn;

    $delQry = "DELETE FROM session WHERE userid='$uid' AND accesstoken='$accesstoken'";
    if ($conn->query($delQry) === TRUE ){
   	  return true;
    }else {
	     return false;
    }
}
?>
