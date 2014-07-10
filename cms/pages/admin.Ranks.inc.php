<?php
if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

if (!$_currUser->checkPermission("ranks")) { // If this user doesn't have the Super Admin permission, redirect them
	header("Location: ./");
	exit;
}

if (isset($_GET['rank'])) { // If we're editing / viewing a rank
	// <editor-fold defaultstate="collapsed" desc="Rank Editing Code">
	$rankID = $_mysql->real_escape_string($_GET['rank']);

	if (!is_numeric($rankID)) { // If the rank isn't a number
		die("<strong>Error:</strong> Rank isn't a number");
	}

	$rank = new Rank($rankID); // Instantiate the rank object

	if (isset($_POST['rankSubmitButton'])) { // If the personal settings form has been submitted
		if (isset($_POST['rankNameField']) && !empty($_POST['rankNameField'])) {
			$rank->setName($_POST['rankNameField']);
		}

		if ((isset($_POST['rankLevelField']) && !empty($_POST['rankLevelField']))) {
			$rank->setLevel($_POST['rankLevelField']);
		}
	} else if (isset($_POST['rankPermissionsSubmitButton'])) { // If the permissions form has been submitted
		$settings = array();

		foreach ($_POST as $key => $var) {
			if (substr($key, 0, 11) == "permission_") { // Check this is a setting variable
				$key = substr($key, 11);
				$key = str_replace(" ", "_", $key); // Replace spaces with underscores

				$settings[$key] = $var;
			}
		}

		$rank->savePermissions($settings);
	}

	print("<h1>Editing " . $rank->name() . "</h1>");

	print("<h2>Settings</h2>");
	?>
	<form id="registerForm" method="post">
		<table>
			<tr>
				<td class="right"><label for="rankNameField">Name:</label> <input type="text" id="rankNameField" name="rankNameField" value="<?php print($rank->name()); ?>" /></td>
			</tr>
			<tr>
				<td class="right"><label for="rankLevelField">Level:</label>
					<select id="rankLevelField" name="rankLevelField"><?php
	for ($i = 1; $i < 11; $i++) {
		print("<option " . (($i == $rank->level()) ? "selected=\"selected\"" : "") . " value=\"" . $i . "\">" . $i . "</option>\n");
	}
	?></select></td>
			</tr>
			<tr>
				<td style="padding-left: 150px;"><button type="submit" id="rankSubmitButton" name="rankSubmitButton">Update</button></td>
			</tr>
		</table>
	</form>
	<?php
	print("<h2>Permissions</h2>");

	print("<form id=\"settingsForm\" method=\"post\">\n");

	$permissions = array();
	$permissions['basicAdmin'] = "Basic admin area access.";
	$permissions['Super Admin'] = "Super Admin.";
	$permissions['managePages'] = "Manage CMS pages.";
	$permissions['manageUsers'] = "Manage users.";
	$permissions['viewBans'] = "View bans list.";
	$permissions['unban'] = "Unban users.";
	$permissions['ban'] = "Ban users.";
	$permissions['metaTags'] = "Edit meta tags.";
	$permissions['ranks'] = "Edit ranks.";
	$permissions['rosters'] = "Edit rosters.";
	$permissions['polls'] = "Edit polls.";
	$permissions['manageApplications'] = "Manage clan applications.";
	$permissions['editApplication'] = "Edit the clan application questions.";

	print("<table>\n");

	print("<th>Yes</th><th>No</th><th>Description</th>\n");

	foreach ($permissions as $permKey => $permDesc) {
		print("<tr><td><input type=\"radio\" id=\"perrmission_" . $permKey . "1\" name=\"permission_" . $permKey . "\" value=\"1\" " . (($rank->checkPermission($permKey)) ? "checked=\"checked\"" : "") . " /></td><td><input type=\"radio\" id=\"permission_" . $permKey . "1\" name=\"permission_" . $permKey . "\" value=\"0\" " . ((!$rank->checkPermission($permKey)) ? "checked=\"checked\"" : "") . " /></td><td>" . $permDesc . "</td></tr>\n");
	}

	print("</table>\n");


	print("<br /><button type=\"submit\" id=\"rankPermissionsSubmitButton\" name=\"rankPermissionsSubmitButton\">Save Permission</button>");

	print("</form>\n");
	// </editor-fold>
} else { // If we're on the rank list page
	// <editor-fold defaultstate="collapsed" desc="Rank List Code">
	if (isset($_POST['addRankButton'])) { // If the add rank form has been submitted
		$errors = array();

		if (!isset($_POST['rankName'])) {
			$errors['name'][] = "Not set";
		} else if (empty($_POST['rankName'])) {
			$errors['name'][] = "Empty";
		} else {
			$name = $_mysql->real_escape_string($_POST['rankName']);
		}

		if (!isset($_POST['rankLevel'])) {
			$errors['level'][] = "Not set";
		} else if (empty($_POST['rankLevel'])) {
			$errors['level'][] = "Empty";
		} else if (!is_numeric($_POST['rankLevel'])) {
			$errors['level'][] = "Not a number";
		} else {
			$level = $_mysql->real_escape_string($_POST['rankLevel']);
		}

		if (count($errors) > 0) { // If there were errors
			// TODO: Output errors in a nice way
			die("There were errors.");
		} else { // There weren't any errors, add the new rank
			$_mysql->query("INSERT INTO `ranks` (`name`, `level`, `permissions`) VALUES ('" . $name . "', " . $level . ", '" . serialize(array()) . "')");
		}
	}

	print("<h1>Ranks</h1>");

	$ranksQuery = $_mysql->query("SELECT * FROM `ranks`");

	if ($ranksQuery->num_rows) { // If there is at least one rank
		print("<table id=\"ranksTable\">\n");
		print("<th>Rank</th>\n");
		print("<th>Level</th>\n");
		print("<th>Options</th>\n");

		while ($rank = $ranksQuery->fetch_assoc()) {
			print("<tr id=\"rank_" . $rank['id'] . "\">\n");

			print("<td>" . $rank['name'] . "</td>\n");
			print("<td>" . $rank['level'] . "</td>\n");

			print("<td><button id=\"edit_" . $rank['id'] . "\" class=\"editRankButton\">Edit</button> <button id=\"delete_" . $rank['id'] . "\" class=\"deleteRankButton\">Delete</button></td>\n");

			print("</tr>\n");
		}

		print("</table>\n");
	} else {
		print("There are no ranks.");
	}

	print("<hr />\n");
	?>
	<h2>Add Rank</h2>
	<form id="addRankForm" method="post">
		<table>
			<th>Name</th>
			<th>Level</th>
			<tr>
				<td><input type="text" name="rankName" id="rankName"/></td>
				<td><select name="rankLevel" id="rankLevel"><?php
	for ($i = 1; $i < 11; $i++) {
		print("<option value=\"" . $i . "\">" . $i . "</option>\n");
	}
	?></select></td>
				<td><button type="submit" name="addRankButton" id="addRankButton">Add Rank</button></td>
			</tr>
		</table>
	</form>
<?php 
	// </editor-fold>
}
?>