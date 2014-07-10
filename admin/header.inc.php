<?php
/* This file is used to print out header information */

print("<!DOCTYPE html>\n"); // HTML 5
print("\t<head>\n"); // Open the head tag
print("\t\t<title>" . $_site->title() . " : Admin</title>\n"); // Output the site's title

if (isset($_GET['p'])) { // If a page is set
	$page = $_mysql->real_escape_string($_GET['p']);
} else { // If a page is not set, use "Home" as the default page
	$page = "Home";
}

//// Determine which theme we're using and include the relevant css file(s)
//$themeData = mysqlc_fetch_assoc(mysqlc_query("SELECT `name`, `version`, `directory` FROM `themes` WHERE `enabled` = 1 LIMIT 1"));
//
//print("\t\t<link rel=\"stylesheet\" id=\"" . $themeData['name']."-".$themeData['version'] . "\" href=\"" . "./themes/".$themeData['directory']."/style.css" . "\" type=\"text/css\" media=\"screen\" />\n");
// Include the CSS files we need
$cssFiles = array();

$cssFiles[] = "./admin.css"; // Admin area CSS file

if (count($cssFiles) > 0) { // If there's at least one CSS file to include
	foreach ($cssFiles as $cssFile) { // For each CSS file
		print("\t\t<link rel=\"stylesheet\" id=\"" . strip_tags($cssFile) . "\" href=\"" . strip_tags($cssFile) . "\" type=\"text/css\" media=\"screen\" />\n");
	}
}

// Include the JavaScript files we need
$jsFiles = array();

$jsFiles[] = "../cms/js/jquery-1.8.2.min.js";
$jsFiles[] = "../cms/lib/ckeditor/ckeditor.js";
$jsFiles[] = "../cms/js/ckeditorBrowserCheck.js";

if ($page == "MetaTags")
	$jsFiles[] = "../cms/js/admin.metaTags.js";
if ($page == "Ranks")
	$jsFiles[] = "../cms/js/admin.ranks.js";
if ($page == "Settings")
	$jsFiles[] = "../cms/js/admin.settings.js";
if ($page == "Bans")
	$jsFiles[] = "../cms/js/admin.bans.js";
if ($page == "EditUser")
	$jsFiles[] = "../cms/js/admin.editUser.js";
if ($page == "Rosters")
	$jsFiles[] = "../cms/js/admin.rosters.js";
if ($page == "Polls")
	$jsFiles[] = "../cms/js/admin.polls.js";
if ($page == "Pages")
	$jsFiles[] = "../cms/js/admin.editCMSPage.js";
if ($page == "Applications")
	$jsFiles[] = "../cms/js/admin.applications.js";

if (count($jsFiles) > 0) { // If there's at least one JavaScript file to include
	foreach ($jsFiles as $jsFile) { // For each JavaScript file
		print("\t\t<script type=\"text/javascript\" src=\"" . strip_tags($jsFile) . "\"></script>\n");
	}
}

print("\t</head>\n"); // Close the head tag

print("\t<body>\n"); // Open the body tag
?>

<!-- This <div> holds alert messages. -->
<div id="alerts">
	<noscript>
	<p>
		<strong>CKEditor requires JavaScript to run</strong>. In a browser with no JavaScript
		support, like yours, you should still see the contents (HTML data) and you should
		be able to edit it normally, without a rich editor interface.
	</p>
	</noscript>
</div>

<?php
print("\t\t<div id=\"mainContainer\">\n");

print("\t\t\t<div id=\"contentArea\">\n");

// Output header
//CMS::outputAdminHeader();
?>