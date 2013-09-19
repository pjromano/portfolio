<?php
	require_once("../include/start.php");
	require_once("privileges.php");
	
	if ($_GET['action'] === "login")
	{
		$opresult = authenticateUser($_POST['username'], $_POST['password']);
		if ($opresult === LOGIN_SUCCESS)
		{
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['privileges'] = PRIV_ADMIN;
		}
	}
	
	else if ($_GET['action'] === "logout")
	{
		$_SESSION['username'] = "Anonymous";
		$_SESSION['privileges'] = PRIV_NONE;
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<title>Philip Romano's Portfolio - Administration</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<h1>Portfolio Administration</h1>
<?php
	// If user has admin privileges
	if ($_SESSION['privileges'] === PRIV_ADMIN)
	{ ?>
	<div id="content">
<?php	// Print result of operation
		if (isset($_GET['action']))
		{ ?>
		Return to the <a href="/admin/index.php">admin index</a>.
<?php	}
		else
		{ ?>
		<a href="portfolio.php">Edit portfolios</a><br>
		<a href="images.php">Manage Images</a><br>
		<a href="files.php">Manage Documents and Files</a>
<?php	} ?>
	</div>
	<div id="footer">
		Welcome, <i><?php echo $_SESSION['username']; ?></i>. <a href="/admin/index.php?action=logout">Log Out</a>
	</div>
<?php
	}
	
	// User is not logged in
	else
	{ ?>
	<div id="content">
<?php	// User is in login stage but did not pass above credential test;
		// Display failed login message
		if ($_GET['action'] === "login")
		{ ?>
		<div class="message">
			Log in failed. Error code <?php echo $opresult; ?>.<br>
			Either try again or return to the <a href="/index.php">Portfolio Index</a>.
		</div>
<?php	}
		
		// User is logging out
		else if ($_GET['action'] === "logout")
		{
			if ($_SESSION['privileges'] === PRIV_NONE)
			{ ?>
		<div class="message">
			You have been successfully logged out.<br>
			Please return to the <a href="/index.php">Portfolio Index</a>.
		</div>
<?php		}
		}
	
		// User does not have privileges; either log in or point them to the front index
		else
		{ ?>
		<div class="message">
			You do not have privileges to access this section of the site.<br>
			Please return to the <a href="/index.php">Portfolio Index</a>.
		</div>
<?php } ?>
		<b>Log in:</b><br>
		<form method="post" action="index.php?action=login">
			Username: <input type="text" name="username"><br>
			Password: <input type="password" name="password"><br>
			<input type="submit" value="Log In">
		</form>
	</div>
<?php
	} ?>
</body>

</html>
