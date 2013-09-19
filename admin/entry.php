<?php
	require_once("../include/start.php");
	require_once("privileges.php");
	
	if (checkPrivileges(PRIV_ADMIN, $_SESSION['privileges']))
	{
		// Get breadcrumb information
		$result = mysql_query("SELECT * FROM entries WHERE id='" . $_GET['id'] . "'");
		if ($result)
		{
			$entry = mysql_fetch_array($result);
			$result = mysql_query("SELECT * FROM pages WHERE id='" . $entry['page'] . "'");
			if ($result)
			{
				$page = mysql_fetch_array($result);
				$result = mysql_query("SELECT * FROM portfolios WHERE id='" . $page['portfolio'] . "'");
				if ($result)
					$portfolio = mysql_fetch_array($result);
			}
		}
		
		// Perform actions
		if ($_POST['action'] == "edit")
		{
			if ($entry['type'] == CONTENT_TEXT)
				$success = updateEntry($_GET['id'], $_POST['title'], $_POST['content'], ($_POST['showtitle'] == "on"));
			else if ($entry['type'] == CONTENT_AUDIO)
				$success = updateEntry($_GET['id'], $_POST['title'], "content" . "|" . $_POST['content_description'], ($_POST['showtitle'] == "on"));
			else
				$success = false;
		}
		else if ($_POST['action'] == "delete")
			$success = deleteEntry($_GET['id']);
		
		// Get any changes to current entry
		$result = mysql_query("SELECT * FROM entries WHERE id='" . $_GET['id'] . "'");
		if ($result)
			$entry = mysql_fetch_array($result);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<title>Philip Romano's Portfolio - Administration</title>
	<link rel="stylesheet" href="style.css">
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript">
		tinyMCE.init({
				mode : "textareas",
				theme : "advanced",
				plugins : "inlinepopups,media",
				
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,fontselect,fontsizeselect,forecolor",
				theme_advanced_buttons2 : "justifyleft,justifycenter,justifyright,justifyfull,|,indent,outdent,|,undo,redo,|,cleanup,removeformat,|,bullist,numlist,|,link,unlink,image,media",
				theme_advanced_buttons3 : "",
				theme_advanced_toolbar_location : "bottom",
				theme_advanced_toolbar_align : "center",
				theme_advanced_statusbar_location : "none",
				theme_advanced_resizing : false,
				
				width : "550",
				height : "400",
				
				external_image_list_url : "image_list.php?portfolio=<?php echo $portfolio['id']; ?>"
		});
	</script>
</head>

<body>
	<h1>Portfolio Administration</h1>
<?php
	// Display delete confirmation message
	if ($_POST['action'] == "deleteconfirm" || $_GET['action'] == "delete")
	{
		$result = mysql_query("SELECT * FROM entries WHERE id='" . $_GET['id'] . "'");
		if (!$result)
		{ ?>
	<div class="message_error">Failed to fetch entry name.</div>
<?php	}
		else
		{
			$entry = mysql_fetch_array($result);
			?>
	<div class="message">
		Are you sure you want to delete entry <i><?php echo $entry['title']; ?></i>, from page <i><?php echo $page['title']; ?>?<br>
		<form method="post" action="entry.php?id=<?php echo $_GET['id']; ?>" class="inline_form">
			<input type="hidden" name="action" value="delete">
			<input type="submit" value="Yes">
		</form>
		<form method="post" action="entry.php?id=<?php echo $_GET['id']; ?>" class="inline_form">
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
		<div class="message">Operation successful.<br>Return to <a href="page.php?id=<?php echo $page['id']; ?>">page</a>.</div>
<?php	}
		else
		{ ?>
		<div class="message_error">Operation failed.<br>Return to <a href="entry.php?id=<?php echo $_GET['id']; ?>">entry</a>.</div>
<?php	}
	}
	
	// Display information associated with selected entry
	else
	{ ?>
	<div id="breadcrumb"><a href="index.php">Index</a> > <a href="portfolio.php">Portfolios</a> > <a href="portfolio.php?id=<?php echo $portfolio['id']; ?>"><?php echo $portfolio['name']; ?></a> > <a href="page.php?id=<?php echo $page['id']; ?>"><?php echo $page['title']; ?></a> > <a href="entry.php?id=<?php echo $entry['id']; ?>"><?php echo $entry['title']; ?></a></div>
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
		<form method="post" action="entry.php?id=<?php echo $_GET['id']; ?>" enctype="multipart/form-data">
			<strong>Edit Entry <i><?php echo $entry['title']; ?></i></strong><br>
			<input type="hidden" name="action" value="edit">
			<input type="hidden" name="MAX_FILE_SIZE" value="10000000"><?php // 10,000,000 B = 10 MB ?>
			Title: <input type="text" name="title" value="<?php echo $entry['title']; ?>"><br>
			Show entry title: <input type="checkbox" name="showtitle"<?php if ($entry['showtitle']) echo " checked"; ?>><br>
			Type: <i><?php
				if ($entry['type'] == CONTENT_TEXT) echo "Text";
				else if ($entry['type'] == CONTENT_AUDIO) echo "Audio";
				else if ($entry['type'] == CONTENT_VIDEO) echo "Video";
			?> (Cannot change)</i><br>
			Content:<br>
<?php			if ($entry['type'] == CONTENT_TEXT)
				{ ?>
			<textarea name="content" class="texteditor"><?php echo $entry['content']; ?></textarea><br>
<?php			}
				else
				{
					if (!printAudioTags($entry['id']))
						echo "Failed to get audio from database.<br>\n";
					?><br>
			<input type="file" name="content"><br>
			Description: <input type="text" name="content_description" value="<?php $desc = explode("|",$entry['content']); echo $desc[1]; ?>"><br>
<?php			} ?>
			<input type="submit" value="Update">
		</form>
		<form method="post" action="entry.php?id=<?php echo $_GET['id']; ?>">
			<input type="hidden" name="action" value="deleteconfirm">
			<input type="submit" value="Delete Entry">
		</form>
	</div>
<?php
	} ?>
	<div id="footer">
		Welcome, <i><?php echo $_SESSION['username']; ?></i>. <a href="/admin/index.php?action=logout">Log Out</a>
	</div>
</body>

</html>
<?php
	}
?>
