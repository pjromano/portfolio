<?php
	require_once("../include/start.php");
	require_once("privileges.php");
	
	if (checkPrivileges(PRIV_ADMIN, $_SESSION['privileges']))
	{
		// Perform actions
		if ($_GET['action'] == "add")
			$success = addImage("imagefile", $_POST['portfolio']);
		else if ($_GET['action'] == "delete")
			$success = deleteImage($_GET['id']);
		
		// Get listing of portfolios
		$portfolios = 0;
		$result = mysql_query("SELECT * FROM portfolios");
		if ($result)
		{
			$portfolios = array();
			$i = 0;
			while ($row = mysql_fetch_array($result))
			{
				$portfolios[$i] = $row;
				$i++;
			}
		}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<title>Philip Romano's Portfolio - Administration</title>
	<link rel="stylesheet" href="style.css">
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="script.js"></script>
</head>

<body>
	<h1>Portfolio Administration</h1>
	<div id="breadcrumb"><a href="index.php">Index</a> > Manage Images</div>
	<div id="content">
<?php	// Display success/error message
		if ($_GET['action'] == "add" || $_GET['action'] == "delete")
		{
			if ($success)
			{ ?>
		<div class="message">Operation successful.</div>
<?php		}
			else
			{ ?>
		<div class="message_error">Operation failed.</div>
<?php		}
		} ?>
		<div id="imagelist">
<?php	$i = 0;
		while ($portfolios && $p = $portfolios[$i])
		{ ?>
			<div class="imagenode">
				<img id="imagenode<?php echo sprintf('%02d', $p['id']); ?>" class="expand" src="tree_expand.png">
				<a href="javascript:void()" id="imagenode_a<?php echo sprintf('%02d', $p['id']); ?>" class="expand_a"><?php echo $p['name']; ?></a>
			</div>
<?php		$imageresult = mysql_query("SELECT * FROM images WHERE portfolio='" . $p['id'] . "' ORDER BY filename");
			if (!$imageresult)
			{ ?>
			<div class="imagerow node<?php echo sprintf('%02d', $p['id']); ?>"><i>Failed to get images.</i></div>
<?php		}
			else if (mysql_num_rows($imageresult) == 0)
			{ ?>
			<div class="imagerow node<?php echo sprintf('%02d', $p['id']); ?>"><i>No images for this portfolio.</i></div>
<?php		}
			else
			{
				while ($image = mysql_fetch_array($imageresult))
				{ ?>
			<div class="imagerow node<?php echo sprintf('%02d', $p['id']); ?>">
				<a href="<?php echo getImagePath($image['id']); ?>"><?php echo $image['filename']; ?></a>
				<a href="images.php?action=delete&id=<?php echo $image['id']; ?>"><img src="delete.png"></a>
			</div>
<?php			}
			}
			$i++;
		} ?>
		</div>
		<form method="post" action="images.php?action=add" enctype="multipart/form-data">
			<strong>Upload Image <i><?php echo $entry['title']; ?></i></strong><br>
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
			Portfolio:
			<select name="portfolio">
<?php	$i = 0;
		while ($p = $portfolios[$i])
		{ ?>
				<option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?>
<?php 	$i++;
		} ?>
			</select><br>
			Image: <input type="file" name="imagefile"><br>
			<input type="submit" value="Upload">
		</form>
	</div>
	<div id="footer">
		Welcome, <i><?php echo $_SESSION['username']; ?></i>. <a href="/admin/index.php?action=logout">Log Out</a>
	</div>
</body>

</html>
<?php
	}
?>
