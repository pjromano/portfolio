<?php
	require_once("../include/start.php");
	require_once("privileges.php");
	
	if (checkPrivileges(PRIV_ADMIN, $_SESSION['privileges']))
	{
		// Perform actions
		if ($_POST['action'] == "edit")
		{
			if ($_FILES['thumbfile']['tmp_name'] != "")
				$thumb = $_FILES['thumbfile']['tmp_name'];
			else
				$thumb = 0;
			$success = updatePortfolio($_GET['id'], $_POST['name'], $_POST['alias'], $thumb, $_FILES['thumbfile']['name'], ($_POST['show'] == "on"));
		}
		else if ($_POST['action'] == "delete")
		{
			$success = deletePortfolio($_GET['id']);
		}
		
		if ($_GET['action'] == "up")
		{
			if (isset($_GET['page']))
				$success = moveItem("pages", $_GET['page'], -1, "portfolio", $_GET['portfolio']);
			else
				$success = moveItem("portfolios", $_GET['portfolio'], -1);
		}
		else if ($_GET['action'] == "dn")
		{
			if (isset($_GET['page']))
				$success = moveItem("pages", $_GET['page'], 1, "portfolio", $_GET['portfolio']);
			else
				$success = moveItem("portfolios", $_GET['portfolio'], 1);
		}
		
		// Get breadcrumb information
		$result = mysql_query("SELECT * FROM portfolios WHERE id='" . $_GET['id'] . "'");
		if ($result)
			$portfolio = mysql_fetch_array($result);
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
	// Display list of all portfolios (no portfolio selected)
	if (!isset($_GET['id']))
	{ ?>
	<div id="breadcrumb"><a href="index.php">Index</a> > <a href="portfolio.php">Portfolios</a></div>
<?php	if ($_GET['action'] == "up" || $_GET['action'] == "dn")
		{
			if ($success)
			{ ?>
		<div class="message">Operation successful.</div>
<?php		}
			else
			{ ?>
		<div class="message_error">Operation failed.</div>
<?php		}
		}
		
		$result = mysql_query("SELECT * FROM portfolios ORDER BY ord");
		?>
	<div id="content">
		<strong>Portfolios:</strong>
		<table class="itemlist">
<?php	if (!$result)
		{ ?>
			<tr><td colspan="4">Failed to retrieve portfolios from the database.</td></tr>
<?php }
		else if (mysql_num_rows($result) == 0)
		{ ?>
			<tr><td colspan="4">There are no portfolios.</td></tr>
<?php	}
		while ($row = mysql_fetch_array($result))
		{ ?>
			<tr>
				<td><a href="/admin/portfolio.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></td>
				<td><a href="/admin/portfolio.php?portfolio=<?php echo $row['id']; ?>&action=up"><img src="up.png" alt="^"></a></td>
				<td><a href="/admin/portfolio.php?portfolio=<?php echo $row['id']; ?>&action=dn"><img src="dn.png" alt="v"></a></td>
				<td><a href="/admin/portfolio.php?id=<?php echo $row['id']; ?>&action=delete"><img src="delete.png" alt="X"></a></td>
			</tr>
<?php } ?>
			<tr>
				<td><a href="/admin/add.php"><i>Add Portfolio</i></a></td>
				<td colspan="3"></td>
			</tr>
		</table>
	</div>
<?php
	}
	
	// Display delete confirmation message
	else if ($_POST['action'] == "deleteconfirm" || $_GET['action'] == "delete")
	{
		$result = mysql_query("SELECT * FROM portfolios WHERE id='" . $_GET['id'] . "'");
		if (!$result)
		{ ?>
	<div class="message_error">Failed to fetch portfolio name.</div>
<?php	}
		else
		{
			$portfolio = mysql_fetch_array($result);
			?>
	<div class="message">
		Are you sure you want to delete portfolio <i><?php echo $portfolio['name']; ?></i>?<br>
		<form method="post" action="portfolio.php?id=<?php echo $_GET['id']; ?>" class="inline_form">
			<input type="hidden" name="action" value="delete">
			<input type="submit" value="Yes">
		</form>
		<form method="post" action="portfolio.php?id=<?php echo $_GET['id']; ?>" class="inline_form">
			<input type="submit" value="No">
		</form>
	</div>
<?php	}
	}
	
	// Display message for success of action, or not
	else if ($_POST['action'] == "delete")
	{
		if ($success)
		{ ?>
		<div class="message">Operation successful.<br>Return to <a href="portfolio.php">portfolio list</a>.</div>
<?php	}
		else
		{ ?>
		<div class="message_error">Operation failed.<br>Return to <a href="portfolio.php?id=<?php echo $_GET['id']; ?>">portfolio</a>.</div>
<?php	}
	}
	
	// Display pages and information associated with selected portfolio
	else
	{ ?>
	<div id="breadcrumb"><a href="index.php">Index</a> > <a href="portfolio.php">Portfolios</a> > <a href="portfolio.php?id=<?php echo $_GET['id']; ?>"><?php echo $portfolio['name']; ?></a></div>
	<div id="content">
<?php	if ($_POST['action'] == "edit")
		{
			if ($success)
			{ ?>
		<div class="message">Operation successful.</div>
<?php		}
			else
			{ ?>
		<div class="message_error">Operation failed.</div>
<?php		}
		}
		
		$result = mysql_query("SELECT name FROM portfolios WHERE id='" . $_GET['id'] . "'");
		if (!$result)
		{ ?>
		<div class="message">Could not retrieve portfolio name.</div>
<?php	}
		else
		{
			$prow = mysql_fetch_array($result);
			$pname = $prow['name'];
		}
		?>
		<strong>Pages for <i><?php echo $pname; ?></i>:</strong>
<?php	$pageresult = mysql_query("SELECT * FROM pages WHERE portfolio='" . $_GET['id'] . "' ORDER BY ord");
		if (!$pageresult)
		{ ?>
		<div class="message">Failed to get portfolio pages.</div>
<?php }
		else
		{ ?>
		<table class="itemlist">
<?php		if (mysql_num_rows($pageresult) == 0)
			{ ?>
			<tr><td colspan="4">No pages for this portfolio.</td></tr>
<?php		}
			while ($page = mysql_fetch_array($pageresult))
			{ ?>
			<tr>
				<td><a href="/admin/page.php?id=<?php echo $page['id']; ?>"><?php echo $page['title']; ?></a></td>
				<td><a href="/admin/portfolio.php?portfolio=<?php echo $_GET['id']; ?>&action=up&page=<?php echo $page['id']; ?>"><img src="up.png" alt="^"></a></td>
				<td><a href="/admin/portfolio.php?portfolio=<?php echo $_GET['id']; ?>&action=dn&page=<?php echo $page['id']; ?>"><img src="dn.png" alt="v"></a></td>
				<td><a href="/admin/page.php?id=<?php echo $page['id']; ?>&action=delete"><img src="delete.png" alt="X"></a></td>
			</tr>
<?php		} ?>
			<tr>
				<td><a href="/admin/add.php?portfolio=<?php echo $_GET['id']; ?>"><i>Add Page</i></a></td>
				<td colspan="3"></td>
			</tr>
		</table>
<?php		$portfolioresult = mysql_query("SELECT * FROM portfolios WHERE id='" . $_GET['id'] . "'");
			if (!$portfolioresult || mysql_num_rows($portfolioresult) == 0)
			{ ?>
		<div class="message">Failed to get portfolio information.</div>
<?php		}
			else
				$portfolio = mysql_fetch_array($portfolioresult);
			?>
		<form method="post" action="portfolio.php?id=<?php echo $_GET['id']; ?>" enctype="multipart/form-data">
			<strong>Edit Portfolio</strong><br>
			<input type="hidden" name="action" value="edit">
			<input type="hidden" name="MAX_FILE_SIZE" value="500000">
			Name: <input type="text" name="name" value="<?php echo $portfolio['name']; ?>"><br>
			Alias: <input type="text" name="alias" value="<?php echo $portfolio['alias']; ?>"><br>
			Thumb:<br>
			<img src="/img/thumb/<?php echo $portfolio['thumb']; ?>" alt="(Thumbnail image)"><br>
			<input type="file" name="thumbfile"><br>
			Show: <input type="checkbox" name="show"<?php if ($portfolio['display']) echo " checked"; ?>><br>
			<input type="submit" value="Update">
		</form>
		<form method="post" action="portfolio.php?id=<?php echo $_GET['id']; ?>">
			<input type="hidden" name="action" value="deleteconfirm">
			<input type="submit" value="Delete Portfolio">
		</form>
	</div>
<?php	}
	} ?>
	<div id="footer">
		Welcome, <i><?php echo $_SESSION['username']; ?></i>. <a href="/admin/index.php?action=logout">Log Out</a>
	</div>
</body>

</html>
<?php
	}
?>
