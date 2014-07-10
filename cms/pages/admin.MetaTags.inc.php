<?php
if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

if (!$_currUser->checkPermission("metaTags")) { // If this user does not have permission to edit meta tags, redirect them
	header("Location: ./");
	exit;
}

// Check to see whether the add new meta tag form has been submitted
if (isset($_POST['addMetaTagButton'])) {
	// <editor-fold defaultstate="collapsed" desc="Add Meta Tag Handling">
	// Check input
	$errors = array();

	if (!isset($_POST['name'])) {
		$errors['name'][] = "Not set";
	} else if (empty($_POST['name'])) {
		$errors['name'][] = "Empty";
	} else {
		$name = $_mysql->real_escape_string($_POST['name']);
	}

	if (!isset($_POST['content'])) {
		$errors['content'][] = "Not set";
	} else if (empty($_POST['name'])) {
		$errors['content'][] = "Empty";
	} else {
		$content = $_mysql->real_escape_string($_POST['content']);
	}

	if (!isset($_POST['page'])) {
		$errors['page'][] = "Not set";
	} else if (empty($_POST['name'])) {
		$errors['page'][] = "Empty";
	} else {
		$page = $_mysql->real_escape_string($_POST['page']);
	}

	if (isset($_POST['enabled'])) {
		$enabled = 1;
	} else {
		$enabled = 0;
	}

	if (count($errors) > 0) { // If there were errors
		// TODO: Output errors in a nice way
		print_r($errors);
		die("There were errors.");
	} else {
		// Add the new meta tag
		$_mysql->query("INSERT INTO `meta_tags` (`name`, `content`, `page`, `enabled`) VALUES ('" . $name . "', '" . $content . "', '" . $page . "', " . $enabled . ")");
	}
	// </editor-fold>
}

// <editor-fold defaultstate="collapsed" desc="Meta Tags Display">
print("<h1>Meta Tags</h1>");

// Global Meta Tags (Meta Tags that apply to every page)
print("<h2>Global Meta Tags</h2>\n");

$globalTags = $_mysql->query("SELECT `id`, `name`, `content`, `enabled` FROM `meta_tags` WHERE `page` = ''");

if ($globalTags->num_rows) { // If there's at least one global meta tag
	print("<table>");

	print("<th>Name</th>\n");
	print("<th>Content</th>\n");
	print("<th>Options</th>\n");

	while ($globalTag = $globalTags->fetch_assoc()) {
		print("<tr id=\"tag_" . htmlentities($globalTag['id']) . "\" " . (($globalTag['enabled']) ? "" : "style=\"color: grey;\"") . ">\n");

		print("<td>" . htmlentities($globalTag['name']) . "</td>\n");
		print("<td>" . htmlentities($globalTag['content']) . "</td>\n");
		print("<td><span class=\"deleteMetaTag\" rel=\"" . htmlentities($globalTag['id']) . "\">Delete</span></td>");

		print("</tr>\n");
	}
	print("</table>\n");
} else {
	print("There are currently no global meta tags.");
}

print("<hr />\n");


// Other Meta Tags
$otherPages = $_mysql->query("SELECT DISTINCT `page` FROM `meta_tags` WHERE `page` <> ''");

if ($otherPages->num_rows) {
	while ($otherPage = $otherPages->fetch_assoc()) {

		print("<h2>" . htmlentities($otherPage['page']) . " Meta Tags</h2>");

		$pageTags = $_mysql->query("SELECT `id`, `name`, `content`, `enabled` FROM `meta_tags` WHERE `page` = '" . $_mysql->real_escape_string($otherPage['page']) . "'");

		print("<table>");

		print("<th>Name</th>\n");
		print("<th>Content</th>\n");
		print("<th>Options</th>\n");

		while ($pageTag = $pageTags->fetch_assoc()) {
			print("<tr id=\"tag_" . htmlentities($pageTag['id']) . "\" " . (($pageTag['enabled']) ? "" : "style=\"color: grey;\"") . ">\n");

			print("<td>" . htmlentities($pageTag['name']) . "</td>\n");
			print("<td>" . htmlentities($pageTag['content']) . "</td>\n");
			print("<td><span class=\"deleteMetaTag\" rel=\"" . htmlentities($pageTag['id']) . "\">Delete</span></td>");

			print("</tr>\n");
		}
		print("</table>\n");

		print("<hr />\n");
	}
}


// Add new meta tag
?>
<h2>Add Meta Tag</h2>
<form method="post" id="addMetaTagForm">
    <table>
		<th>Name</th>
		<th>Content</th>
		<th>Page</th>
		<th>Enabled?</th>
		<tr>
			<td><input type="text" name="name" id="name" /></td>
			<td><input type="text" name="content" id="content" /></td>
			<td>
				<select name="page" id="page">
					<option selected="selected" value="">Global</option><?php
$pageQuery = $_mysql->query("SELECT `name` FROM `pages`");

while ($page = $pageQuery->fetch_assoc()) {
	print("<option value=\"" . htmlentities($page['name']) . "\">" . htmlentities($page['name']) . "</option>");
}
?></select>
			</td>
			<td><input type="checkbox" checked="checked" name="enabled" id="enabled" /></td>
			<td><button type="submit" name="addMetaTagButton" id="addMetaTagButton">Add Meta Tag</button></td>
		</tr>
    </table>
</form>
<?php
// </editor-fold>
?>