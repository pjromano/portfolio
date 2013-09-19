<?php
	include("../include/start.php");
	
	$output = "var tinyMCEImageList = new Array(";
	
	$result = mysql_query("SELECT * FROM images WHERE portfolio='" . $_GET['portfolio'] . "' ORDER BY filename");
	if ($result && mysql_num_rows($result) > 0)
	{
		$count = 0;
		while ($row = mysql_fetch_array($result))
		{
			$output .= '["' . $row['filename'] . '", "' . getImagePath($row['id']) . '"], ';
			$count++;
		}
		
		if ($count > 0)
			$output = substr($output, 0, -2);
	}
	
	$output .= ");";
	echo $output;
?>
