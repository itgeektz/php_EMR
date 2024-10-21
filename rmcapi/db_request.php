<?php

require_once("../sites/default/sqlconf.php");

define ('HOSTNAME',$sqlconf['host']);
define ('USERNAME',$sqlconf['login']);
define ('PASSWORD',$sqlconf['pass']);
define ('DATABASE',$sqlconf['dbase']);

$conn = new mysqli(HOSTNAME,USERNAME,PASSWORD,DATABASE);
/* check connection */
if (mysqli_connect_errno()) {
    console.log("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

?>
