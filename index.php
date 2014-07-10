<?php
require(__DIR__ . "/cms/global.inc.php");

include(__DIR__ . "/cms/header.inc.php");

CMS::outputLeftBox();

print("<div id=\"mainContent\">\n");
print("<div id=\"content\">\n");

if (in_array($page, $_reservedPages)) { // If this is a reserved page
	include(__DIR__ . "/cms/pages/" . $page . ".inc.php");
} else { // If this isn't a reserved page print the CMS page
	CMS::outputCustomPage($page);
}

print("</div>\n");
print("<div class=\"bottomBoxBar\"></div>\n");
print("</div>\n");

CMS::outputFooter();
?>