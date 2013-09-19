<?php
	// This function returns true if the user's privileges are sufficient (>=) to 
	// the required privileges.
	// Call this function at the top of every page that requires privileges
	// 	$minlevel	- minimum level of access required to access the page
	//		$userlevel	- the level of the current user (usually stored in $_SESSION)
	// If the user's level is less than the minimum level, the page load is aborted
	// and the user is sent to the no access page.
	function checkPrivileges($minlevel, $userlevel)
	{
		if ($userlevel < $minlevel)
		{
			header("Location: " . SITE_BASEURL . "/admin/noaccess.php", true, 303);
			echo "Redirecting...";
			return false;
		}
		else
			return true;
	}
?>
