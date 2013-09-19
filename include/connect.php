<?php
	if (!mysql_connect("mysql1301.ixwebhosting.com:3306", "electo_webmaster", "PhantomS94"))
		die("An error occurred while connecting to the MySQL database.");
	if (!mysql_select_db("electo_portfolio"))
		die("An error occurred while selecting the database.");
?>
