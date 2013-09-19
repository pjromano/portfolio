<?php
	session_start();
	
	error_reporting(E_ALL);
	
	/* Constants */
	define("SITE_BASEURL", "http://portfolio.electoware.com");
	define("SITE_BASEPATH", "/hsphere/local/home/electo/portfolio.electoware.com");
	define("SITE_THUMBPATH", SITE_BASEPATH . "/img/thumb");
	define("SITE_IMAGEPATH", SITE_BASEPATH . "/media/images");
	define("SITE_AUDIOPATH", SITE_BASEPATH . "/media/audio");
	define("SITE_DOCUMENTPATH", SITE_BASEPATH . "/media/documents");
	
	// Privileges
	define("PRIV_NONE", 0);
	define("PRIV_ADMIN", 1);
	
	// Login flags
	define("LOGIN_SUCCESS", 0);
	define("LOGIN_PASSFAIL", 1);
	define("LOGIN_USERFAIL", 2);
	
	// Content type flags
	define("CONTENT_UNDEFINED", 0);
	define("CONTENT_TEXT", 1);
	define("CONTENT_AUDIO", 2);
	define("CONTENT_VIDEO", 3);
	
	require_once(SITE_BASEPATH . "/include/connect.php");
	require_once(SITE_BASEPATH . "/include/functions.php");
	
	if (!isset($_SESSION['username'])
			|| !isset($_SESSION['privileges']))
	{
		$_SESSION['username'] = "Anonymous";
		$_SESSION['privileges'] = PRIV_NONE;
	}
?>
