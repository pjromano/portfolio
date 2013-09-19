<?php
	require("include/start.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<title>Philip Romano's Portfolio</title>
	<base href="http://portfolio.electoware.com">
	<link rel="stylesheet" href="style_home.css">
</head>

<body>
	<div id="accent"></div>
	<div id="container">
		<div id="header">
			<h1>Philip Romano's</h1>
			<h2>Portfolio</h2>
		</div>
		<div id="content">
			<ul id="portfolios">
				<li class="first"></li>
<?php	$result = mysql_query("SELECT * FROM portfolios WHERE display='1' ORDER BY ord");
		if (!$result)
		{ ?>
		
<?php	}
		else
		{
			while ($portfolio = mysql_fetch_array($result))
			{ ?>
				<li>
					<a href="<?php echo $portfolio['alias']; ?>">
						<div class="list_left"><div class="list_image"><?php if ($portfolio['thumb'] != "") { ?><img src="img/thumb/<?php echo $portfolio['thumb']; ?>"><?php } ?></div></div>
						<div class="list_right"><?php echo $portfolio['name']; ?></div>
					</a>
				</li>
<?php		}
		} ?>
				<li class="last"></li>
			</ul>
		</div>
		<div id="footer">
			&copy; 2011 Philip Romano
		</div>
	</div>
</body>

</html>

