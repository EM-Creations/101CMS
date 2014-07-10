<?php
/* This file is used to print out header information */

print("<!DOCTYPE html>\n"); // HTML 5
print("\t<head>\n"); // Open the head tag
print("\t\t<title>" . $_site->title() . "</title>\n"); // Output the site's title

if (isset($_GET['p'])) { // If a page is set
	$page = $_mysql->real_escape_string(urldecode($_GET['p']));
} else { // If a page is not set, use "Home" as the default page
	$page = "Home";
}

print("\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n");
print("\t\t<meta name=\"generator\" content=\"101 CMS 0.1\" />\n"); // TODO: This will need changing if the name of the CMS changes
// Query the meta tags table to check for enabled meta tags, if there are any output them here
$metaTagsQuery = $_mysql->query("SELECT `name`, `content` FROM `meta_tags` WHERE (`page` = '" . $page . "' OR `page` = '') AND `enabled` = 1");

if ($metaTagsQuery->num_rows) { // There are meta tags for this page, output them
	while ($row = $metaTagsQuery->fetch_assoc()) { // For each meta tag
		print("\t\t<meta name=\"" . strip_tags($row['name']) . "\" content=\"" . strip_tags($row['content']) . "\" />\n");
	}
}

// Determine which theme we're using and include the relevant css file(s)
$themeDataQuery = $_mysql->query("SELECT `name`, `version`, `directory` FROM `themes` WHERE `enabled` = 1 LIMIT 1");
$themeData = $themeDataQuery->fetch_assoc();

print("\t\t<link rel=\"stylesheet\" id=\"" . strip_tags($themeData['name']) . "-" . strip_tags($themeData['version']) . "\" href=\"" . "./themes/" . strip_tags($themeData['directory']) . "/style.css" . "\" type=\"text/css\" media=\"screen\" />\n");

// jQuery UI CSS
print("\t\t<link rel=\"stylesheet\" id=\"" . strip_tags($themeData['name']) . "-jQuery-UI\" href=\"" . "./themes/" . strip_tags($themeData['directory']) . "/jqueryui/jquery-ui-1.10.3.custom.min.css" . "\" type=\"text/css\" media=\"screen\" />\n");

// Include the JavaScript files we need
$jsFiles = array();

$jsFiles[] = "jquery-1.8.2.min.js"; // JQuery

if ($_site->setting("AJAX Login")) { // If the AJAX Login setting is enabled, include the relevant JavaScript file
	$jsFiles[] = "ajaxLogin.js";
	$jsFiles[] = "jquery-ui-1.10.3.custom.min.js";
}

if (count($jsFiles) > 0) { // If there's at least one JavaScript file to include
	foreach ($jsFiles as $jsFile) { // For each JavaScript file
		print("\t\t<script type=\"text/javascript\" src=\"./cms/js/" . strip_tags($jsFile) . "\"></script>\n");
	}
}

print("\t</head>\n"); // Close the head tag

print("\t<body>\n"); // Open the body tag

// <editor-fold defaultstate="collapsed" desc="Error / Success / Info Message Handling">
if (isset($_GET['errormsg'])) {
	$message = urldecode($_GET['errormsg']);
	$message = htmlentities($message);?>

	<script type="text/javascript">
		$("body").prepend("<div class=\"errormsg\">" + <?php print("\"".$message."\""); ?> + "</div>");                     
			setTimeout(hideError,3000);

		function hideError() {
			$(".errormsg").fadeOut("slow", function() { $(this).remove(); });      
			}
	</script>

<?php
}

if (isset($_GET['successmsg'])) {
	$message = urldecode($_GET['successmsg']);
	$message = htmlentities($message);?>

	<script type="text/javascript">
		$("body").prepend("<div class=\"successmsg\">" + <?php print("\"".$message."\""); ?> + "</div>");                     
			setTimeout(hideSuccess,3000);

		function hideSuccess() {
			$(".successmsg").fadeOut("slow", function() { $(this).remove(); });      
			}
	</script>

<?php
}

if (isset($_GET['infomsg'])) {
	$message = urldecode($_GET['infomsg']);
	$message = htmlentities($message);?>

	<script type="text/javascript">
		$("body").prepend("<div class=\"infomsg\">" + <?php print("\"".$message."\""); ?> + "</div>");                     
			setTimeout(hideInfo,3000);

		function hideInfo() {
			$(".infomsg").fadeOut("slow", function() { $(this).remove(); });      
			}
	</script>

<?php
}
// </editor-fold>

print("\t\t<div id=\"mainContainer\">\n");

print("\t\t\t<div id=\"contentArea\">\n");

// Output header
CMS::outputHeader();
?>