<?php
	require_once("../include/start.php");
	require_once("privileges.php");
	
	if (checkPrivileges(PRIV_ADMIN, $_SESSION['privileges']))
	{
		// Perform actions
		if ($_GET['action'] == "add")
			$success = addFile("document", $_POST['portfolio'], $_POST['directory']);
		else if ($_GET['action'] == "addfolder")
			$success = addDocumentDirectory($_POST['portfolio'], $_POST['parentdirectory'] . "/" . $_POST['directory']);
		else if ($_GET['action'] == "delete")
			$success = deleteFile($_GET['p'], $_GET['file']);
		else if ($_GET['action'] == "deletefolder")
			$success = deleteDocumentDirectory($_GET['dir']);
		
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
	<div id="breadcrumb"><a href="index.php">Index</a> > Manage Documents</div>
	<div id="content">
<?php	// Display success/error message
		if ($_GET['action'] == "add" || $_GET['action'] == "addfolder"
				|| $_GET['action'] == "delete" || $_GET['action'] == "deletefolder")
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
				<img id="imagenode<?php printf('%02d', $p['id']); ?>" class="expand" src="tree_expand.png">
				<a href="javascript:void()" id="imagenode_a<?php echo sprintf('%02d', $p['id']); ?>" class="expand_a"><?php echo $p['name']; ?></a>
				<a href="javascript:void()" class="addfolder"><img src="addfolder.png"></a>
			</div>
<?php		$documentroot = SITE_DOCUMENTDIRECTORY . "/" . sprintf("%03d", $p['id']);
			$directorycontents = scandir($documentroot);
			if (!$directorycontents || count($directorycontents) == 0)
			{ ?>
			<div class="imagerow node<?php echo sprintf('%02d', $p['id']); ?>"><i>No documents for this portfolio.</i></div>
<?php		}
			else
			{
				$n = 0;
				foreach ($directorycontents as $item)
				{
					if (is_dir($documentroot . "/" . $item))
					{ ?>
			<div id="imagerow<?php printf('%02d', $p['id']); ?>_<?php printf('%02d', $n); ?>" class="imagerow node<?php echo sprintf('%02d', $p['id']); ?>">
				<a href="<?php echo getImagePath($image['id']); ?>"><?php echo $image['filename']; ?></a>
				<a href="files.php?action=delete&id=<?php echo $image['id']; ?>"><img src="delete.png"></a>
			</div>
<?php					$subdircontents = scandir($documentroot . "/" . $item);
						foreach ($subdircontents as $subitem)
						{ ?>
			<div class="imagesubrow<?php printf('%02d', $p['id']); ?>_<?php printf('%02d', $n); ?> imagesubrow">
				<?php echo $subitem; ?>
			</div>
<?php					}
					}
					else
					{ ?>
			<div class="imagerow">
				<a href="<?php echo SITE_BASEURL . "/media/documents/" . $item; ?>"><?php echo $item; ?></a>
			</div>
<?php				}
				}
			}
			$i++;
		} ?>
		</div>
		<div id="uploaddocument">
			<form method="post" action="images.php?action=add" enctype="multipart/form-data">
				<strong>Upload Document</strong><br>
				Portfolio: <span class="modifiable" id="uploadportfolio"></span><br>
				Directory: <span class="modifiable" id="uploaddir"></span><br>
				<input type="hidden" name="portfolio" value="">
				<input type="hidden" name="directory" value="">
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
				Document: <input type="file" name="document"><br>
				<input type="submit" value="Upload">
			</form>
		</div>
		<div id="addfolder">
			<form method="post" action="images.php?action=addfolder" enctype="multipart/form-data">
				<strong>Add Directory</strong><br>
				Portfolio: <span class="modifiable" id="dirportfolio"></span><br>
				Parent Directory: <span class="modifiable" id="parentdir"></span><br>
				<input type="hidden" name="portfolio" value="">
				<input type="hidden" name="parentdirectory" value="">
				New Directory: <input type="text" name="directory"><br>
				<input type="submit" value="Add Directory">
			</form>
		</div>
	</div>
	<div id="footer">
		Welcome, <i><?php echo $_SESSION['username']; ?></i>. <a href="/admin/index.php?action=logout">Log Out</a>
	</div>
</body>

</html>
<?php
	}
?>
