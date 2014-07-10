<?php
if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

if (!$_currUser->checkPermission("manageUsers")) {
	header("Location: ./");
	exit;
}

print("<h1>Users</h1>");

if (isset($_POST['userSearchButton'])) {
	$userSearch = $_mysql->real_escape_string($_POST['userSearchField']);

	$query = $_mysql->query("SELECT `id`, `name` FROM `users` WHERE `name` LIKE '%" . $userSearch . "%'");

	if ($query->num_rows) { // If there are results from the search
		while ($row = $query->fetch_assoc()) {
			print("<a href=\"./?p=EditUser&u=" . $row['id'] . "\">" . $row['name'] . "</a><br />\n");
		}
	} else {
		print("<strong>No results.</strong><br />");
	}

	print("<br /><a href=\"./?p=Users\">Back to search</a>");
} else {
	?>
	<form method="post" id="searchUsersForm">
		<input type="text" name="userSearchField" id="userSearchField" />
		<button type="submit" name="userSearchButton" id="userSearchButton">Search</button>
	</form>
	<?php
}
?>