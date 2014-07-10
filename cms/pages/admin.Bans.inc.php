<?php

if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

if (!$_currUser->checkPermission("viewBans")) {
	header("Location: ./");
	exit;
}

print("<h1>Bans</h1>\n");

$query = $_mysql->query("SELECT `id`, `user`, `type`, `super_ban`, `expires`, `stamp` FROM `bans` WHERE `enabled` = 1 ORDER BY `stamp` DESC"); // Select all enabled bans

if ($query->num_rows) { // If there are bans
	print("<table>\n");

	print("<th>User</th>\n");
	print("<th>Type</th>\n");
	print("<th>Super Ban?</th>\n");
	print("<th>Expires</th>\n");
	print("<th>Stamp</th>\n");
	if ($_currUser->checkPermission("unban"))
		print("<th>Options</th>\n");

	while ($ban = $query->fetch_assoc()) {
		print("<tr id=\"ban_" . $ban['id'] . "\">\n");

		$user = new User($_mysql->real_escape_string($ban['user']));

		print("<td>" . $user->name() . "</td>
	    <td>" . $ban['type'] . "</td>
	    <td>" . (($ban['super_ban']) ? "Yes" : "No") . "</td>
	    <td>" . (($ban['expires'] > 0) ? date($_site->setting("dateFormat"), $ban['expires']) : "N/A") . "</td>
	    <td>" . date($_site->setting("dateFormat"), $ban['stamp']) . "</td>");

		if ($_currUser->checkPermission("unban"))
			print("<td><button id=\"unban_" . $ban['id'] . "\" class=\"unbanButton\" >Unban</button></td>\n");

		print("</tr>\n");
	}

	print("</table>\n");
} else { // If there are no bans
	print("There are no bans.");
}

print("");
?>