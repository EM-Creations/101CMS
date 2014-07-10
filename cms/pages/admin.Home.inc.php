<?php

if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

print("<h1>Admin Home Page</h1>");

// Temporary Links
// Core
print("<h2>Core</h2><hr /><br />\n");
print("<a href=\"./?p=Applications\">Applications</a> - Working on<br />\n");
print("<a href=\"./?p=Settings\">Settings</a><br />\n");
print("<a href=\"./?p=MetaTags\">Meta Tags</a><br />\n");
print("<a href=\"./?p=Pages\">Pages</a><br />\n");
print("<a href=\"./?p=Users\">Users</a><br />\n");
print("<a href=\"./?p=Bans\">Bans</a><br />\n");
print("<a href=\"./?p=Ranks\">Ranks</a><br />\n");
print("<a href=\"./?p=Widgets\">Widgets</a> - Working on<br />\n");

// Modules
print("<h2>Modules</h2><hr /><br />\n");
print("<a href=\"./?p=News\">News</a> - TODO<br />\n");
print("<a href=\"./?p=Polls\">Polls</a><br />\n");
print("<a href=\"./?p=Rosters\">Rosters</a><br />\n");

// Plugins
print("<h2>Plugins</h2><hr /><br />\n");
// TOOD: Plugins will need to be listed by looking in the plugins table
?>
