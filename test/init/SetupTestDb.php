<?php
/**
* Copy development database to test database
*/
function SetupTestDb($server, $username, $password, $devDB, $testDB)
{
	// connect and create test database
	$conn = mysql_connect($server, $username, $password) or die(mysql_error());
	mysql_select_db($devDB) or die(mysql_error());
	mysql_query('DROP DATABASE `' . $testDB . '`');
	mysql_query('CREATE DATABASE `' . $testDB . '`') or die(mysql_error());
	
	// copy tables from development database to test database
	$tables = array();
	$res = mysql_list_tables($devDB);
	while ($line = mysql_fetch_array($res))
	{
		mysql_select_db($devDB) or die(mysql_error());
		$table = $line[0];
		$create = mysql_query('SHOW CREATE TABLE `' . $table . '`') or die(mysql_error());
		$cl = mysql_fetch_array($create);
		
		// create new table in test database
		mysql_select_db($testDB) or die(mysql_error());
		mysql_query($cl[1]) or die(mysql_error() . '<br/><br/>' . $cl[1]);
	
		// copy records as well
		mysql_query('INSERT INTO `' . $testDB . '`.`' . $table . '` SELECT * FROM `' . $devDB . '`.`'. $table . '`') or die(mysql_error() . __LINE__);		  
	}
}
?>