<?php

if (!isset($_GET['u'])) { // If no user is specified redirect the user
	header("Location: ./");
	exit;
}

$user = $_mysql->real_escape_string($_GET['u']);

$query = $_mysql->query("SELECT `id` FROM `users` WHERE `name` = '" . $user . "' LIMIT 1");

if ($query->num_rows) { // If the user was found
	$data = $query->fetch_assoc();

	$_profileUser = new User($data['id']); // Create a new user object for the user we're viewing

	print("<span class=\"title\">Viewing " . $_profileUser->name() . "</span>");

	print($_profileUser->avatar());

	print("<table id=\"viewProfileTable\">\n");

	print("<tr>\n<td class=\"label\">Join Date:</td><td>" . $_profileUser->registered("d", $_site->setting("dateFormat")) . "</td>\n</tr>\n");

	print("<tr>\n<td class=\"label\">Last Login:</td><td>" . $_profileUser->lastLogin("d", $_site->setting("dateFormat")) . "</td>\n</tr>\n");

	print("<tr>\n<td class=\"label\">Date of Birth:</td><td>" . $_profileUser->dob($_site->setting("dateFormat")) . "</td>\n</tr>\n");

	print("<tr>\n<td class=\"label\">Country:</td><td>" . $_profileUser->country() . "</td>\n</tr>\n");

	print("</table>\n");
} else {
	die("<strong>Error:</strong> User not found");
}
?>
