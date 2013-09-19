<?php
	require_once("../include/start.php");
	require_once("privileges.php");
	
	if (checkPrivileges(PRIV_ADMIN, $_SESSION['privileges']))
	{
		// Get breadcrumb information
		$result = mysql_query("SELECT * FROM pages WHERE id='" . $_GET['id'] . "'");
		if ($result)
		{
			$page = mysql_fetch_array($result);
			$result = mysql_query("SELECT * FROM portfolios WHERE id='" . $page['portfolio'] . "'");
			if ($result)
				$portfolio = mysql_fetch_array($result);
		}
		
		// Perform actions
		if ($_POST['action'] == "edit")
		{
			$success = updatePage($_GET['id'], $_POST['title'], $_POST['alias'],  $_POST['description']);
		}
		else if ($_POST['action'] == "delete")
		{
			$success = deletePage($_GET['id']);
		}
		
		if ($_GET['action'] == "up")
			$success = moveItem("entries", $_GET['entry'], -1, "page", $_GET['page']);
		else if ($_GET['action'] == "dn")
			$success = moveItem("entries", $_GET['entry'], 1, "page", $_GET['page']);
		
		// Get any changes to current page
		$result = mysql_query("SELECT * FROM pages WHERE id='" . $_GET['id'] . "'");
		if ($result)
			$page = mysql_fetch_array($result);
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
	// Display delete confirmation message
	if ($_POST['action'] == "deleteconfirm" || $_GET['action'] == "delete")
	{
		$result = mysql_query("SELECT * FROM pages WHERE id='" . $_GET['id'] . "'");
		if (!$result)
		{ ?>
	<div class="message_error">Failed to fetch page name.</div>
<?php	}
		else
		{
			$page = mysql_fetch_array($result);
			?>
	<div class="message">
		Are you sure you want to delete page <i><?php echo $page['title']; ?></i>?<br>
		<form method="post" action="page.php?id=<?php echo $_GET['id']; ?>" class="inline_form">
			<input type="hidden" name="action" value="delete">
			<input type="submit" value="Yes">
		</form>
		<form method="post" action="page.php?id=<?php echo $_GET['id']; ?>" class="inline_form">
			<input type="submit" value="No">
		</form>
	</div>
<?php	}
	}
	
	// Display message for success of delete, or not
	else if ($_POST['action'] == "delete")
	{
		if ($success)
		{ ?>
		<div class="message">Operation successful.<br>Return to <a href="portfolio.php?id=<?php echo $portfolio['id']; ?>">portfolio</a>.</div>
<?php	}
		else
		{ ?>
		<div class="message_error">Operation failed.<br>Return to <a href="page.php?id=<?php echo $_GET['id']; ?>">page</a>.</div>
<?php	}
	}
	
	// Display entries and information associated with selected page
	else
	{ ?>
	<div id="breadcrumb"><a href="index.php">Index</a> > <a href="portfolio.php">Portfolios</a> > <a href="portfolio.php?id=<?php echo $portfolio['id']; ?>"><?php echo $portfolio['name']; ?></a> > <a href="page.php?id=<?php echo $page['id']; ?>"><?php echo $page['title']; ?></a></div>
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
		?>
		<strong>Entries for <i><?php echo $page['title']; ?></i>:</strong>
<?php	$entryresult = mysql_query("SELECT * FROM entries WHERE page='" . $_GET['id'] . "' ORDER BY ord");
		if (!$entryresult)
		{ ?>
		<div class="message">Failed to get page entries.</div>
<?php }
		else
		{ ?>
		<table class="itemlist">
<?php		if (mysql_num_rows($entryresult) == 0)
			{ ?>
			<tr><td colspan="4">No entries for this page.</td></tr>
<?php		}
			while ($entry = mysql_fetch_array($entryresult))
			{ ?>
			<tr>
				<td><a href="entry.php?id=<?php echo $entry['id']; ?>"><?php echo $entry['title']; ?></a></td>
				<td><a href="/admin/page.php?id=<?php echo $_GET['id']; ?>&action=up&entry=<?php echo $entry['id']; ?>"><img src="up.png" alt="^"></a></td>
				<td><a href="/admin/page.php?id=<?php echo $_GET['id']; ?>&action=dn&entry=<?php echo $entry['id']; ?>"><img src="dn.png" alt="v"></a></td>
				<td><a href="/admin/entry.php?id=<?php echo $entry['id']; ?>&action=delete"><img src="delete.png" alt="X"></a></td>
			</tr>
<?php		} ?>
			<tr>
				<td><a href="/admin/add.php?page=<?php echo $_GET['id']; ?>"><i>Add Entry</i></a></td>
				<td colspan="3"></td>
			</tr>
		</table>
		<form method="post" action="page.php?id=<?php echo $_GET['id']; ?>" enctype="multipart/form-data">
			<strong>Edit Page</strong><br>
			<input type="hidden" name="action" value="edit">
			Title: <input type="text" name="title" value="<?php echo $page['title']; ?>"><br>
			Alias: <input type="text" name="alias" value="<?php echo $page['alias']; ?>"><br>
			Description: <input type="text" name="description" value="<?php echo $page['description']; ?>"><br>
			<input type="submit" value="Update">
		</form>
		<form method="post" action="page.php?id=<?php echo $_GET['id']; ?>">
			<input type="hidden" name="action" value="deleteconfirm">
			<input type="submit" value="Delete Page">
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
