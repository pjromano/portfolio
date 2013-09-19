<?php
	require("include/start.php");
	
	$portfolio = 0;
	if ($_GET['portfolio'] != "")
	{
		$result = mysql_query("SELECT * FROM portfolios WHERE alias='". $_GET['portfolio'] . "'");
		if ($result && mysql_num_rows($result) > 0)
			$portfolio = mysql_fetch_array($result);
	}
	
	$currentpage = 0;
	if ($_GET['page'] != "")
	{
		$result = mysql_query("SELECT * FROM pages WHERE portfolio='" . $portfolio['id'] . "' AND alias='". $_GET['page'] . "'");
		if ($result && mysql_num_rows($result) > 0)
			$currentpage = mysql_fetch_array($result);
	}
	
	if (($_GET['portfolio'] != "" && $portfolio == 0) || ($_GET['page'] != "" && $currentpage == 0))
	{
		header("Location: " . SITE_BASEURL . "/notfound.php", true, 303);
		echo "Page not found. Redirecting...";
	}
	
	// If no page set, fetch page with lowest order
	if ($currentpage === 0)
	{
		$result = mysql_query("SELECT * FROM pages WHERE portfolio='" . $portfolio['id'] . "' ORDER BY ord");
		if ($result)
			$currentpage = mysql_fetch_array($result);
		else
		{
			header("Location: " . SITE_BASEURL . "/notfound.php", true, 303);
			echo "Page not found. Redirecting...";
		}
	}
	
	// Put all pages associated with this portfolio into an array
	$pageresult = mysql_query("SELECT * FROM pages WHERE portfolio='" . $portfolio['id'] . "' ORDER BY ord");
	if (!$pageresult)
	{
		header("Location: " . SITE_BASEURL . "/error.php", true, 303);
		echo "Database error. Redirecting...";
	}
	$i = 0;
	while ($row = mysql_fetch_array($pageresult))
	{
		$pages[$i] = $row;
		$i++;
	}
	
	// Put all entries associated with the current page into an array
	$entryresult = mysql_query("SELECT * FROM entries WHERE page='" . $currentpage['id'] . "' ORDER BY ord");
	if (!$entryresult)
	{
		header("Location: " . SITE_BASEURL . "/error.php", true, 303);
		echo "Database error. Redirecting...";
	}
	$i = 0;
	while ($row = mysql_fetch_array($entryresult))
	{
		$entries[$i] = $row;
		$i++;
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<title>Philip Romano's Portfolio - <?php echo $portfolio['name']; ?></title>
	<base href="http://portfolio.electoware.com">
	<link rel="stylesheet" href="style_portfolio.css">
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="script.js"></script>
</head>

<body>
	<div id="accent"></div>
	<div id="container">
		<div id="header">
			<h1>Philip Romano's</h1>
			<h2>Portfolio</h2>
		</div>
		<div id="content">
			<div id="title">
				<div class="portfolio_image"><?php if ($portfolio['thumb'] != "") { ?><img src="img/thumb/<?php echo $portfolio['thumb']; ?>"><?php } ?></div>
				<div id="title_text"><?php echo $portfolio['name']; ?></div>
			</div>
			<div id="page_container">
				<div id="menu_container">
					<ul id="portfolio_menu">
						<li class="first"></li>
<?php	$i = 0;
		while ($p = $pages[$i])
		{
			if ($p['id'] == $currentpage['id'])
			{ ?>
						<li class="current_l"></li>
<?php		} ?>
						<li<?php if ($p['id'] == $currentpage['id']) echo ' class="current"'; ?>><a id="menuitem<?php printf("%02d", $i); ?>" href="<?php echo $portfolio['alias'] . "/" . $p['alias']; ?>"><?php echo $p['title']; ?></a></li>
<?php 	if ($p['id'] == $currentpage['id'])
			{ ?>
						<li class="current_r"></li>
<?php		}
			$i++;
		} ?>
						<li>
							<a id="menuitem_return" href="/"><img id="return_img" src="img/return.png" title="Return to Listing"></a>
						</li>
						<li class="last"></li>
<?php	$i = 0;
		while ($p = $pages[$i])
		{
			if ($p['description'] != "")
			{ ?>
						<div id="menu_hint<?php printf("%02d", $i); ?>" class="menu_hint"><?php echo $p['description']; ?></div>
<?php 	}
			$i++;
		} ?>
						<div id="menu_hint_return" class="menu_hint">Return to Portfolio Listing</div>
					</ul>
				</div>
				<div id="text_container">
<?php $i = 0;
		while ($e = $entries[$i])
		{ ?>
					<div class="entry">
						<?php if ($e['showtitle']) { ?><div class="entryhead"><?php echo $e['title']; ?></div><?php } ?>
						<?php
							if ($e['type'] == CONTENT_TEXT)
							{ ?>
						<div class="entrycontent">
							<?php echo $e['content']; ?>
						</div>
<?php						}
							else if ($e['type'] == CONTENT_AUDIO)
							{
								if (!printAudioTags($e['id']))
								{ ?>
							The audio sample could not be loaded at this time.
<?php							}
							}
						?>
					</div>
<?php 	$i++;
		} ?>
				</div>
			</div>
		</div>
		<div id="footer">
			&copy; 2011 Philip Romano
		</div>
	</div>
</body>

</html>

