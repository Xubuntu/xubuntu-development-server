<?php

/*  Modify this file to include your database credentials and details
 *  and rename to 'db.php'
 *
 */

global $db;

$db = new PDO(
	'mysql:host=DBHOST;dbname=DBNAME',
	'DBUSER',
	'DBPASS'
);

?>