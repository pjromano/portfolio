<?php
	// Adds a user, returning the id of the newly added entry
	function addUser($username, $password)
	{
		$passentry = encryptPassword($password);
		$result = mysql_query("INSERT INTO users (name, password)
			VALUES('$username', '$passentry')");
		if (!$result)
			return -1;
		return mysql_insert_id();
	}
	
	// Returns whether the UPDATE succeeded
	function changePassword($userid, $password)
	{
		if (!$password)
			return false;
		
		$passentry = encryptPassword($password);
		return mysql_query("UPDATE users SET password='$passentry' WHERE id='$userid'");
	}
	
	// Returns whether DELETE succeeded
	function removeUser($userid)
	{
		return mysql_query("DELETE FROM users WHERE id='$userid'");
	}
	
	// Returns true if the username is taken
	// If a database error occurs, returns true (assume the worst)
	function isUsernameTaken($username)
	{
		$result = mysql_query("SELECT * FROM users");
		if (!$result)
			return true;
		
		$found = false;
		while (($row = mysql_fetch_assoc($result)) && !$found)
		{
			if (strtolower($row['name']) == strtolower($username))
				$found = true;
		}
		return $found;
	}
	
	function authenticateUser($username, $password)
	{
		$result = mysql_query("SELECT * FROM users WHERE name='$username'");
		
		if (!$result || mysql_num_rows($result) == 0)
			return LOGIN_USERFAIL;
		
		$row = mysql_fetch_array($result);
		
		$salt = substr($row['password'], 0, 8);
		if ($salt . sha1($salt . $password) == $row['password'])
			return LOGIN_SUCCESS;
		return LOGIN_PASSFAIL;
	}
	
	function encryptPassword($pass)
	{
		$salt = generateSalt(8);
		$encrypted = $salt . sha1($salt . $pass);
		return $encrypted;
	}
	
	function generateSalt($length)
	{
		$salt = "";
		for ($i = 0; $i < $length; $i++)
		{
			$range = rand(0, 1);
			if ($range == 0)
				$salt .= chr(rand(48,57)); // 0 - 9
			else
				$salt .= chr(rand(97,102)); // a - f
		}
		return $salt;
	}
	
	/***********************
		Portfolio functions
	************************/
	
	// Add a portfolio to the database
	// 	$name	 - name to display
	//		$alias - the name used as part of the URL to refer to this specific portfolio
	//		$thumbtemp - filename of the temporary uploaded thumbnail file. The function will move it to a
	//			permanent location, stored in /img/thumb/
	//			The function assumes that the user checked that the file uploaded correctly, but the function
	//			will still return an error if the file does not exist.
	//				($_FILES['formfield']['error'] == UPLOAD_ERROR_OK)
	//		$thumbname - original filename from client's computer
	//		$show  - whether or not this portfolio is publicly displayed on the front page
	//	
	// Returns the newly added portfolio ID, or false on error
	
	function addPortfolio($name, $alias, $thumbtemp, $thumbname, $show)
	{
		if (!is_dir(SITE_THUMBPATH))
			mkdir(SITE_THUMBPATH, 0755);
		
		// Get the next auto_increment ID to use as the filename
		$result = mysql_query("SELECT auto_increment FROM information_schema.TABLES WHERE TABLE_NAME='portfolios'");
		if (!$result)
			$bname = basename($thumbname);
		else
		{
			$row = mysql_fetch_array($result);
			$nameparts = explode(".", basename($thumbname));
			$ext = $nameparts[count($nameparts) - 1];
			$bname = sprintf("%03d", $row['auto_increment'] + 1) . "." . $ext;
		}
		
		// Move uploaded file
		$filename = SITE_THUMBPATH . "/" . $bname;
		if (!move_uploaded_file($thumbtemp, $filename))
		{
			$success = false;
			echo "<div id=\"message\">Failed to move uploaded file - \"$filename\"</div>";
		}
		
		// Find greatest order value
		$result = mysql_query("SELECT * FROM portfolios ORDER BY ord DESC");
		if (!$result)
			return -1;
		
		if (mysql_num_rows($result) == 0)
			$order = 0;
		else
		{
			$greatest = mysql_fetch_array($result);
			$order = $greatest['order'] + 1;
		}
		
		// Insert into database
		$result = mysql_query("INSERT INTO portfolios (name, alias, thumb, ord, display) VALUES('$name', '$alias', '$bname', '$order', '$show')");
		if (!$result)
			return -1;
		return mysql_insert_id();
	}
	
	// Add a page to the database
	// 	$title - title of the page, which is displayed on the front
	//		$alias - the name used as part of the URL to refer to this specific portfolio
	//		$portfolio - the portfolio this page is associated with
	// The page is appended to the end of the portfolio's page list;
	//		its order value is one greater than the current greatest order
	// Returns the newly added page ID, or false on error
	
	function addPage($title, $alias, $description, $portfolio)
	{
		// Find greatest order value
		$result = mysql_query("SELECT * FROM pages WHERE portfolio='$portfolio' ORDER BY ord DESC");
		if (!$result)
			return -1;
		
		if (mysql_num_rows($result) == 0)
			$order = 0;
		else
		{
			$greatest = mysql_fetch_array($result);
			$order = $greatest['order'] + 1;
		}
		
		// Insert into database
		$result = mysql_query("INSERT INTO pages (title, alias, description, portfolio, ord) VALUES('$title', '$alias', '$description', '$portfolio', '$order')");
		if (!$result)
			return -1;
		return mysql_insert_id();
	}
	
	// Add an entry to the database
	// 	$title - title of the entry, which may or may not be displayed on the front (see $showtitle)
	//		$page - the page this entry is associated with
	//		$type - the type of content that this entry contains
	//			CONTENT_TEXT - content is displayed as rich text
	//			CONTENT_AUDIO - content is displayed as a flash audio player. 
	//			CONTENT_VIDEO - content is displayed as a flash video player.
	//				For both AUDIO and VIDEO, the $content variable is the name of the upload input field.
	//				This function will move the temporary file to a permanent location and refer to it in the database.
	//				For a description, add a | after the field name and append the description string
	//		$content - the content for this entry, as described above
	//		$showtitle - if true, the title of this entry will be displayed on the front, along with a visual separator
	//
	// The entry is appended to the end of the page's entry list;
	//		its order value is one greater than the current greatest order
	// Returns the newly added entry ID, or false on error
	
	function addEntry($title, $page, $type, $content, $showtitle)
	{
		// Find greatest order value
		$result = mysql_query("SELECT * FROM entries WHERE page='$page' ORDER BY ord DESC");
		if (!$result)
			return -1;
		
		if (mysql_num_rows($result) == 0)
			$order = 0;
		else
		{
			$greatest = mysql_fetch_array($result);
			$order = $greatest['order'] + 1;
		}
		
		// Determine value of content field
		$contententry = "";
		if ($type == CONTENT_TEXT)
			$contententry = $content;
		else if ($type == CONTENT_AUDIO)
		{
			if (!is_dir(SITE_AUDIOPATH))
				if (!mkdir(SITE_AUDIOPATH, 0755))
					return -1;
			
			$result = mysql_query("SELECT * FROM pages WHERE id='$page'");
			if (!$result || mysql_num_rows($result) == 0)
				return -1;
			$pagedata = mysql_fetch_array($result);
			$destpath = SITE_AUDIOPATH . "/" . sprintf("%03d", $pagedata['portfolio']);
			if (!is_dir($destpath))
				if (!mkdir($destpath, 0755))
					return -1;
			
			$split = explode("|", $content);
			$input = $split[0];
			$description = $split[1];
			
			if ($_FILES[$input]['error'] == UPLOAD_ERR_OK)
			{
				// Move uploaded file
				$destname = $_FILES[$input]['name'];
				if (file_exists($destpath . "/" . $destname))
				{
					echo "<div id=\"message_error\">Filename already exists - \"$destpath/$destname\"</div>\n";
					return -1;
				}
				if (!move_uploaded_file($_FILES[$input]['tmp_name'], $destpath . "/" . $destname))
				{
					echo "<div id=\"message_error\">Failed to move uploaded file - \"$destname\"</div>\n";
					return -1;
				}
				
				$contententry = SITE_BASEURL . "/media/audio/"
						. sprintf("%03d", $pagedata['portfolio']) . "/" . $destname . "|" . $description;
			}
			else if ($_FILES[$input]['error'] != UPLOAD_ERR_NO_FILE)
			{
				echo "Error during upload process: Error Code " . $_FILES[$input]['error'] . "<br>\n";
				return false;
			}
		}
		else if ($type == CONTENT_VIDEO)
		{
			echo "<div id=\"message\">Video not implemented.</div>\n";
			return -1;
		}
		
		// Insert into database
		$result = mysql_query("INSERT INTO entries (title, page, type, ord, content, showtitle)"
				. " VALUES('$title', '$page', '$type', '$order', '$contententry', '$showtitle')");
		if (!$result)
			return -1;
		return mysql_insert_id();
	}
	
	// Edit an existing portfolio
	//		$id	 - ID of the portfolio to update
	// 	$name	 - name to display
	//		$alias - the name used as part of the URL to refer to this specific portfolio
	//		$thumbtemp - filename of the temporary uploaded thumbnail file.
	//						 Set to 0 to keep the current thumbnail
	//		$thumbname - original filename from client's computer
	//		$show  - whether or not this portfolio is publicly displayed on the front page
	// Returns true on success, false on error
	
	function updatePortfolio($id, $name, $alias, $thumbtemp, $thumbname, $show)
	{
		// If the thumb is to be updated
		if ($thumbtemp !== 0)
		{
			if (!is_dir(SITE_THUMBPATH))
				mkdir(SITE_THUMBPATH, 0755);
			
			// Use the given portfolio ID to use as the thumb filename
			$nameparts = explode(".", basename($thumbname));
			$ext = $nameparts[count($nameparts) - 1];
			$bname = sprintf("%03d", $id) . "." . $ext;
			// Move uploaded file
			$filename = SITE_THUMBPATH . "/" . $bname;
			if (!move_uploaded_file($thumbtemp, $filename))
			{
				$success = false;
				echo "<div id=\"message\">Failed to move uploaded file - \"$filename\"</div>";
			}
		}
		
		// Update database
		$result = mysql_query("UPDATE portfolios SET name='$name', alias='$alias', display='$show' WHERE id='$id'");
		if (!$result)
			return false;
		if ($thumbtemp !== 0)
		{
			$result = mysql_query("UPDATE portfolios SET thumb='$bname' WHERE id='$id'");
			if (!$result)
				return false;
		}
		
		return true;
	}
	
	// Edit an existing page
	//		$id	 - ID of the page to update
	// 	$title - title of the page, which is displayed on the front
	//		$alias - the name used as part of the URL to refer to this specific portfolio
	// Returns true on success, false on error
	
	function updatePage($id, $title, $alias, $description)
	{
		$result = mysql_query("UPDATE pages SET title='$title', alias='$alias', description='$description' WHERE id='$id'");
		return $result;
	}
	
	// Edit an existing entry
	//		$id - ID of the entry to update
	// 	$title - title of the entry, which may or may not be displayed on the front (see $showtitle)
	//		$content - the content for this entry. Since the type cannot be changed, the content must correspond to the entry's type
	//				CONTENT_AUDIO and CONTENT_VIDEO always overwrite the current file on the server, like an update
	//		$showtitle - if true, the title of this entry will be displayed on the front, along with a visual separator
	//
	// The entry is appended to the end of the page's entry list;
	//		its order value is one greater than the current greatest order
	// Returns true on success, false on error
	
	function updateEntry($id, $title, $content, $showtitle)
	{
		$result = mysql_query("SELECT * FROM entries WHERE id='$id'");
		if (!$result || mysql_num_rows($result) == 0)
			return false;
		$entry = mysql_fetch_array($result);
		
		// Determine value of content field
		$contententry = "";
		if ($entry['type'] == CONTENT_TEXT)
			$contententry = $content;
		else if ($entry['type'] == CONTENT_AUDIO)
		{
			$split = explode("|", $content);
			$input = $split[0];
			$description = $split[1];
			
			$split = explode("|", $entry['content']);
			$currentfilename = $split[0];
			
			if ($_FILES[$input]['error'] == UPLOAD_ERR_OK)
			{
				$result = mysql_query("SELECT * FROM pages WHERE id='" . $entry['page'] . "'");
				if (!$result || mysql_num_rows($result) == 0)
					return false;
				$page = mysql_fetch_array($result);
				
				// Replace existing file on server with new uploaded file
				$basename = basename($currentfilename);
				if ($basename == "")
					$basename = $_FILES[$input]['name'];
				
				$fullpath = SITE_AUDIOPATH . "/" . sprintf("%03d", $page['portfolio']) . "/" . $basename;
				if (!move_uploaded_file($_FILES[$input]['tmp_name'], $fullpath))
					return false;
				
				$contententry = SITE_BASEURL . "/media/audio/"
						. sprintf("%03d", $page['portfolio']) . "/" . $basename . "|" . $description;
			}
			else if ($_FILES[$input]['error'] != UPLOAD_ERR_NO_FILE)
			{
				echo "Error during upload process: Error Code " . $_FILES[$input]['error'] . "<br>\n";
				return false;
			}
			else
				$contententry = $currentfilename . "|" . $description;
		}
		else if ($entry['type'] == CONTENT_VIDEO)
		{
			echo "<div id=\"message\">Video not implemented.</div>\n";
			return -1;
		}
		
		// Update database
		$query = "UPDATE entries SET title='$title', ";
		if (!($entry['type'] == CONTENT_AUDIO && $_FILES[$content]['error'] == UPLOAD_ERR_NO_FILE))
			$query .= "content='$contententry', ";
		$query .= "showtitle='$showtitle' WHERE id='$id'";
		$result = mysql_query($query);
		return $result;
	}
	
	// Move the order of an item in the database
	//		$table - the name of the table to modify; i.e. to move a portfolio, "portfolios"
	//					Preconditions : table has fields id and ord
	//		$id	 - ID of the item to move
	//		$dir	 - integer representing the direction to move the item
	//					if = 0 : keep the same
	//					if < 0 : move up by one in the order
	//					if > 0 : move down by one
	//		$limitfield - name of a field by which to limit the items which are selected
	//					i.e. to limit to the pages within a certain portfolio
	//					Set this to 0 to use no limit and select all rows in the table
	//		$limit - value of the limit; if the value of $limitfield matches this, the row is included
	//					in the selection. This is most likely the ID of the containing object
	//					(i.e. portfolio ID)
	//
	// Returns true on success, false on error
	
	function moveItem($table, $id, $dir, $limitfield, $limit)
	{
		// Do nothing and return true if direction is 0
		if ($dir == 0)
			return true;
		
		// Select rows within the group
		if ($limitfield === 0)
			// Select all items in the table
			$result = mysql_query("SELECT id, ord FROM $table WHERE $limitfield='$limit' ORDER BY ord");
		else
			// Select all items within the specified limit
			$result = mysql_query("SELECT id, ord FROM $table ORDER BY ord");
		if (!$result)
				return false;
		
		// Determine the highest order *needed* for this group of items
		// (to keep the ord field uniform)
		$order = 0;
		while ($row = mysql_fetch_array($result))
		{
			$list[$order] = $row['id'];
			$order++;
		}
		if (!isset($list))
			return false;
		
		// Switch in specified direction
		if ($dir < 0)
			$otherindex = -1;
		else
			$otherindex = 1;
		
		$switched = false;
		for ($i = 0; $i <= $order && !$switched; $i++)
		{
			// Switch with the previous index
			if ($list[$i] == $id)
			{
				// If we are moving the first item up or the last item down, do nothing
				if (($i == 0 && $otherindex == -1) || ($i == $order && $otherindex == 1))
					$switched = true;
				
				// Otherwise, move
				else
				{
					$temp = $list[$i + $otherindex];
					$list[$i + $otherindex] = $list[$i];
					$list[$i] = $temp;
					$switched = true;
				}
			}
		}
		
		// Update database
		for ($i = 0; $i <= $order; $i++)
		{
			$updateresult = mysql_query("UPDATE $table SET ord='$i' WHERE id='" . $list[$i] . "'");
			if (!$updateresult)
				return false;
		}
		
		return true;
	}
	
	// Delete the portfolio with given ID
	// Returns true on success, false on error
	function deletePortfolio($id)
	{
		// Delete associated thumnail image file
		$result = mysql_query("SELECT thumb FROM portfolios WHERE id='$id'");
		if (!$result)
			return false;
		$portfolio = mysql_fetch_array($result);
		unlink(SITE_THUMBPATH . "/" . $portfolio['thumb']);
		
		// Delete pages (which also deletes entries)
		$result = mysql_query("SELECT * FROM pages WHERE portfolio='$id'");
		if ($result)
			while ($page = mysql_fetch_array($result))
				deletePage($page['id']);
		
		// Delete images
		$result = mysql_query("SELECT * FROM images WHERE portfolio='$id'");
		if ($result)
			while ($image = mysql_fetch_array($result))
				deleteImage($image['id']);
		
		// Delete documents
		$result = mysql_query("SELECT * FROM files WHERE portfolio='$id'");
		if ($result)
			while ($file = mysql_fetch_array($result))
				deleteFile($file['id']);
		
		// Finally, delete this portfolio entry
		$result = mysql_query("DELETE FROM portfolios WHERE id='$id'");
		return $result;
	}
	
	// Delete the page with given ID
	// Returns true on success, false on error
	function deletePage($id)
	{
		// Delete entries
		$result = mysql_query("SELECT * FROM entries WHERE page='$id'");
		if ($result)
			while ($entry = mysql_fetch_array($result))
				deleteEntry($entry['id']);
		
		// Finally, delete this page entry
		$result = mysql_query("DELETE FROM pages WHERE id='$id'");
		return $result;
	}
	
	// Delete the entry with given ID
	// Returns true on success, false on error
	function deleteEntry($id)
	{
		$result = mysql_query("SELECT * FROM entries WHERE id='$id'");
		if (!$result || mysql_num_rows($result) == 0)
			return false;
		$entry = mysql_fetch_array($result);
		if ($entry['type'] == CONTENT_AUDIO)
		{
			// Delete audio file
			$result = mysql_query("SELECT * FROM pages WHERE id='" . $entry['id'] . "'");
			if (!$result || mysql_num_rows($result) == 0)
				return false;
			$page = mysql_fetch_array($result);
			
			$split = explode("|", $entry['content']);
			$filename = SITE_AUDIOPATH . "/" . sprintf("%03d", $page['portfolio']) . "/" . basename($split[0]);
			if (!unlink($filename))
				echo "Failed to remove associated audio file: \"$filename\"<br>\n";
		}
		
		$result = mysql_query("DELETE FROM entries WHERE id='$id'");
		return $result;
	}
	
	// Echoes the HTML tags to display the flash audio player
	//		$id - the ID of the entry that the audio player will play
	// Returns true on success;
	//		false on error, or if given entry ID is invalid or not CONTENT_AUDIO
	function printAudioTags($id)
	{
		$result = mysql_query("SELECT * FROM entries WHERE id='$id'");
		if (!$result || mysql_num_rows($result) == 0)
			return false;
		else
		{
			$entry = mysql_fetch_array($result);
			if ($entry['type'] == CONTENT_AUDIO)
			{
				$split = explode("|", $entry['content']);
				$url = $split[0];
				$description = $split[1];
				?>
		<div class="flashcontainer">
			<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="400" height="20">
				<param name="movie" value="/flash/audio.swf" />
				<param name="quality" value="high" />
				<param name="bgcolor" value="#ffffff" />
				<param name="play" value="true" />
				<param name="loop" value="true" />
				<param name="wmode" value="window" />
				<param name="scale" value="showall" />
				<param name="menu" value="true" />
				<param name="devicefont" value="false" />
				<param name="salign" value="" />
				<param name="allowScriptAccess" value="sameDomain" />
				<param name="flashvars" value="mp3=<?php echo $url; ?>" />
				<!--[if !IE]>-->
				<object type="application/x-shockwave-flash" data="/flash/audio.swf" width="400" height="20">
					<param name="movie" value="/flash/audio.swf" />
					<param name="quality" value="high" />
					<param name="bgcolor" value="#ffffff" />
					<param name="play" value="true" />
					<param name="loop" value="true" />
					<param name="wmode" value="window" />
					<param name="scale" value="showall" />
					<param name="menu" value="true" />
					<param name="devicefont" value="false" />
					<param name="salign" value="" />
					<param name="allowScriptAccess" value="sameDomain" />
					<param name="flashvars" value="mp3=<?php echo $url; ?>" />
				<!--<![endif]-->
					<a href="http://www.adobe.com/go/getflash">
						<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
					</a>
				<!--[if !IE]>-->
				</object>
				<!--<![endif]-->
			</object><br>
			<?php echo $description; ?>
		</div>
<?php		}
			else
				return false;
		}
		return true;
	}
	
	/*
		Image functions
	*/
	
	// Returns the path to the image file associated with the given ID, or false on error
	// Path is relative to the subdomain directory on the server
	// This would be used in the HTML to actually display the image
	function getImagePath($id)
	{
		$result = mysql_query("SELECT * FROM images WHERE id='$id'");
		if (!$result || mysql_num_rows($result) == 0)
			return false;
		$image = mysql_fetch_array($result);
		return "/media/images/" . sprintf("%03d", $image['portfolio']) . "/" . $image['filename'];
	}
	
	// Returns the full path to the image file associated with the given ID, or false on error
	// Path is the absolute path on the server itself
	// This is used internally within the PHP
	function getImageFullPath($id)
	{
		$result = mysql_query("SELECT * FROM images WHERE id='$id'");
		if (!$result || mysql_num_rows($result) == 0)
			return false;
		$image = mysql_fetch_array($result);
		return SITE_IMAGEPATH . "/" . sprintf("%03d", $image['portfolio']) . "/" . $image['filename'];
	}
	
	// Add an uploaded image to the database
	// 	$inputname - the HTML name of the file upload input field, such that the file information can
	//						 be obtained from the $_FILES variable
	//		$portfolio - the portfolio with which this image is associated, to keep things organized
	// Returns true on success, false on error
	function addImage($inputname, $portfolio)
	{
		$destpath = SITE_IMAGEPATH . "/" . sprintf("%03d", $portfolio);
		$destfile = $_FILES[$inputname]['name'];
		
		// Move uploaded file to folder for this portfolio's images
		if (!is_dir(SITE_BASEPATH . "/media"))
		{
			if (!mkdir(SITE_BASEPATH . "/media", 0755))
			{
				echo "Failed to make path '" . SITE_BASEPATH . "/media'<br>\n";
				return false;
			}
		}
		if (!is_dir(SITE_IMAGEPATH))
		{
			if (!mkdir(SITE_IMAGEPATH, 0755))
			{
				echo "Failed to make path '" . SITE_IMAGEPATH . "'<br>\n";
				return false;
			}
		}
		if (!is_dir($destpath))
		{
			if (!mkdir($destpath, 0755))
			{
				echo "Failed to make path '$destpath'<br>\n";
				return false;
			}
		}
		
		if (!move_uploaded_file($_FILES[$inputname]['tmp_name'], $destpath . "/" . $destfile))
			return false;
		
		// Determine if the filename already is used (within this portfolio)
		// This means the file was overwritten, and we should do nothing with the database entry
		$result = mysql_query("SELECT * FROM images WHERE portfolio='$portfolio' AND filename='$destfile'");
		if (!$result)
			return false;
		
		if (mysql_num_rows($result) == 1)
			return true;
		
		$result = mysql_query("INSERT INTO images (filename, portfolio) VALUES('$destfile', '$portfolio')");
		if (!$result)
			return false;
		return true;
	}
	
	// Delete the image with given ID, and unlink the associated file
	// Returns true on success, false on error
	function deleteImage($id)
	{
		$file = getImageFullPath($id);
		if (!$file)
			return false;
		
		if (!unlink($file))
			echo "Failed to delete file!<br>";
		
		$result = mysql_query("DELETE FROM images WHERE id='$id'");
		return $result;
	}
	
	/*
		Files/Documents functions
	*/
	
	// Add an uploaded document to the file structure
	//		$inputname - the HTML name of the file upload input field, such that the file information can
	//						 be obtained from the $_FILES variable
	//		$portfolio - the portfolio with which this document is associated
	//		$directory - the directory in which the file is contained.
	//						 This is relative to SITE_DOCUMENTPATH/[portfolio ID]/
	// 
	// Returns true on success, false on error
	function addFile($inputname, $portfolio, $directory)
	{
		$destpath = SITE_DOCUMENTPATH . "/" . sprintf("%03d", $portfolio) . "/" . $directory;
		$destfile = $_FILES[$inputname]['name'];
		
		// Move uploaded file to folder for this portfolio's documents
		if (!is_dir($destpath))
			mkdir($destpath, 0755);
		
		if (!move_uploaded_file($_FILES[$inputname]['tmp_name'], $destpath . "/" . $destfile))
			return false;
		
		return true;
	}
	
	// Delete the document with the given filename, under given portfolio ID.
	// The filename should include any subdirectories beneath SITE_DOCUMENTPATH/[portfolio ID]/
	// 		i.e. if a file named "test.pdf" is contained in the directory "container", then
	// 		$filename should be "container/test.pdf"
	// Returns true on success, false on error
	function deleteFile($portfolio, $filename)
	{
		return unlink(SITE_DOCUMENTPATH . "/" . sprintf("%03d", $portfolio) . "/" . $filename . "/" . $file['filename']);
	}
	
	// Creates a directory relative to SITE_DOCUMENTPATH/[portfolio ID]/
	// 	$portfolio - portfolio ID to create the directory under
	//		$directory - name of the new directory to create
	// Returns true on success or if directory already exists; false on error
	function addDocumentDirectory($portfolio, $directory)
	{
		$fullpath = SITE_DOCUMENTPATH . "/" . sprintf("%03d", $portfolio) . "/" . $directory;
		if (!is_dir($fullpath))
			return mkdir($fullpath, 0755);
		return true;
	}
	
	// Deletes the directory with the given name from the portfolio
	// Return true on success; false if directory doesn't exist or on error
	function deleteDocumentDirectory($portfolio, $directory)
	{
		$fullpath = SITE_DOCUMENTPATH . "/" . sprintf("%03d", $portfolio) . "/" . $directory;
		
		if (!is_dir($fullpath))
			return false;
		
		// First remove contained files
		$contents = scandir($fullpath);
		$success = true;
		foreach ($contents as $item);
			$success = $success && deleteFile($directory . "/" . $item);
		if (!$success)
			return false;
		
		// Now remove directory
		return rmdir($fullpath);
	}
?>