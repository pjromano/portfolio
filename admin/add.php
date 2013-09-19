<?php
	require_once("../include/start.php");
	require_once("privileges.php");
	
	if (checkPrivileges(PRIV_ADMIN, $_SESSION['privileges']))
	{
		// Perform actions
		if ($_POST['action'] === "add")
		{
			$success = true;
			// Add entry to page
			if (isset($_GET['page']))
			{
				if ($_POST['type'] == CONTENT_TEXT)
					$content = $_POST['content_text'];
				else if ($_POST['type'] == CONTENT_AUDIO || $_POST['type'] == CONTENT_VIDEO)
					$content = "content_upload";
				
				if (addEntry($_POST['title'], $_GET['page'], $_POST['type'], $content . "|" . $_POST['content_description'], ($_POST['showtitle'] == "on")) < 0)
					$success = false;
			}
			
			// Add page to portfolio
			else if (isset($_GET['portfolio']))
			{
				if (addPage($_POST['title'], $_POST['alias'], $_POST['description'], $_GET['portfolio']) < 0)
					$success = false;
			}
			
			// Add new portfolio
			else
			{
				if ($_FILES['thumbfile']['error'] == UPLOAD_ERROR_OK)
				{
					if (addPortfolio($_POST['name'], $_POST['alias'], $_FILES['thumbfile']['tmp_name'], $_FILES['thumbfile']['name'], ($_POST['show'] == "on")) < 0)
						$success = false;
				}
				else
					$success = false;
			}
		}
		
		// Get breadcrumb information
		if (isset($_GET['page']))
		{
			$result = mysql_query("SELECT * FROM pages WHERE id='" . $_GET['page'] . "'");
			if ($result)
			{
				$page = mysql_fetch_array($result);
				$result = mysql_query("SELECT * FROM portfolios WHERE id='" . $page['portfolio'] . "'");
				if ($result)
					$portfolio = mysql_fetch_array($result);
			}
		}
		else if (isset($_GET['portfolio']))
		{
			$result = mysql_query("SELECT * FROM portfolios WHERE id='" . $_GET['portfolio'] . "'");
			if ($result)
				$portfolio = mysql_fetch_array($result);
		}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<title>Philip Romano's Portfolio - Administration</title>
	<link rel="stylesheet" href="style.css">
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="script.js"></script>
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
<?php if (isset($_GET['page']))
		{ ?>
	<div id="breadcrumb"><a href="index.php">Index</a> > <a href="portfolio.php">Portfolios</a> > <a href="portfolio.php?id=<?php echo $portfolio['id']; ?>"><?php echo $portfolio['name']; ?></a> > <a href="page.php?id=<?php echo $page['id']; ?>"><?php echo $page['title']; ?></a> > Add Entry</div>
<?php	}
		else if (isset($_GET['portfolio']))
		{ ?>
	<div id="breadcrumb"><a href="index.php">Index</a> > <a href="portfolio.php">Portfolios</a> > <a href="portfolio.php?id=<?php echo $portfolio['id']; ?>"><?php echo $portfolio['name']; ?></a> > Add Page</div>
<?php	}
		else
		{ ?>
	<div id="breadcrumb"><a href="index.php">Index</a> > <a href="portfolio.php">Portfolios</a> > Add Portfolio</div>
<?php	} ?>
	<div id="content">
<?php
		// Display result of action
		if ($_POST['action'] === "add")
		{
			if ($success)
			{
				if (isset($_GET['page']))
				{ ?>
		<div class="message">Entry <i><?php echo $_POST['title']; ?></i> successfully added.</div>
<?php			}
				else if (isset($_GET['portfolio']))
				{ ?>
		<div class="message">Page <i><?php echo $_POST['title']; ?></i> successfully added.</div>
<?php			}
				else
				{ ?>
		<div class="message">Portfolio <i><?php echo $_POST['name']; ?></i> successfully added.</div>
<?php			}
			}
			else
			{ ?>
		<div class="message_error">A database error occurred while updating the database.<br><?php echo mysql_error(); ?></div>
<?php		}
		}
		
		// Display form to add entry to page
		else if (isset($_GET['page']))
		{
			$result = mysql_query("SELECT title FROM pages WHERE id='" . $_GET['page'] . "'");
			if (!$result)
			{ ?>
		<div class="message_error">Failed to retrieve portfolio name from database.</div>
<?php		}
			else
			{
				$prow = mysql_fetch_array($result);
				$ptitle = $prow['title'];
			}
			?>
		<form method="post" action="add.php?page=<?php echo $_GET['page']; ?>" enctype="multipart/form-data">
			<strong>Add Entry to Page <i><?php echo $ptitle; ?></i></strong><br>
			<input type="hidden" name="action" value="add">
			<input type="hidden" name="MAX_FILE_SIZE" value="10000000"><?php // 10,000,000 B = 10 MB ?>
			Title: <input type="text" name="title"><br>
			Show entry title: <input type="checkbox" name="showtitle"><br>
			Type:
			<select id="typeselect" name="type">
				<option value="<?php echo CONTENT_UNDEFINED; ?>">
				<option value="<?php echo CONTENT_TEXT; ?>">Text
				<option value="<?php echo CONTENT_AUDIO; ?>">Audio
				<option value="<?php echo CONTENT_VIDEO; ?>">Video
			</select><br>
			Content:<br>
			<div id="content1" class="contenttoggle">
				<textarea name="content_text" class="texteditor"></textarea>
			</div>
			<div id="content2" class="contenttoggle">
				<input type="file" name="content_upload"><br>
				Description: <input type="text" name="content_description"><br>
			</div>
			<input type="submit" value="Add Entry">
		</form>
<?php	}
		
		// Display form to add page to portfolio
		else if (isset($_GET['portfolio']))
		{
			$result = mysql_query("SELECT name FROM portfolios WHERE id='" . $_GET['portfolio'] . "'");
			if (!$result)
			{ ?>
		<div class="message_error">Failed to retrieve portfolio name from database.</div>
<?php		}
			else
			{
				$prow = mysql_fetch_array($result);
				$pname = $prow['name'];
			}
			?>
		<form method="post" action="add.php?portfolio=<?php echo $_GET['portfolio']; ?>" enctype="multipart/form-data">
			<strong>Add Page to <i><?php echo $pname; ?></i></strong><br>
			<input type="hidden" name="action" value="add">
			Title: <input type="text" name="title"><br>
			Alias: <input type="text" name="alias"> (used to refer to this specific page by URL)<br>
			Description: <input type="text" name="description"><br>
			<input type="submit" value="Add Page">
		</form>
<?php	}
		
		// Display form to add new portfolio
		else
		{ ?>
		<form method="post" action="add.php" enctype="multipart/form-data">
			<strong>Add Portfolio</strong><br>
			<input type="hidden" name="action" value="add">
			<input type="hidden" name="MAX_FILE_SIZE" value="500000">
			Name: <input type="text" name="name"><br>
			Alias: <input type="text" name="alias"> (used to refer to this specific portfolio by URL)<br>
			Thumb: <input type="file" name="thumbfile"><br>
			Show: <input type="checkbox" name="show" checked><br>
			<input type="submit" value="Add Portfolio">
		</form>
<?php	} ?>
	</div>
	<div id="footer">
		Welcome, <i><?php echo $_SESSION['username']; ?></i>. <a href="/admin/index.php?action=logout">Log Out</a>
	</div>
</body>

</html>
<?php
	}
?>
