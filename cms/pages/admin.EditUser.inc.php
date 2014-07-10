<?php
if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

if (!$_currUser->checkPermission("manageUsers")) {
	header("Location: ./");
	exit;
}

if (!isset($_GET['u'])) { // If no user is set, redirect them to the search users page
	header("Location: ./?p=Users");
	exit;
} else {
	$_editUser = new User($_mysql->real_escape_string($_GET['u'])); // Create a user object of the user we're editing

	if ($_editUser->type() == "guest") { // If the user didn't exist
		die("<strong>Error:</strong> ");
	} else { // If the user does exist
		if (isset($_POST['registerSubmitButton'])) { // If the personal settings form has been submitted
			// <editor-fold defaultstate="collapsed" desc="Settings Form Handling">
			if (isset($_POST['registerUserNameField']) && !empty($_POST['registerUserNameField'])) {
				$_editUser->setName($_POST['registerUserNameField']);
			}

			if (isset($_POST['registerRankField']) && is_numeric($_POST['registerRankField'])) {
				$_editUser->setRank($_POST['registerRankField']);
			}

			if ((isset($_POST['registerPasswordField']) && !empty($_POST['registerPasswordField'])) && (isset($_POST['registerPasswordField2']) && !empty($_POST['registerPasswordField2']))) {
				$password = $_POST['registerPasswordField'];
				$passwordConfirm = $_POST['registerPasswordField2'];

				if ($password == $passwordConfirm) { // If the passwords match set the password
					$_editUser->setPassword($password);
				}
			}

			if (isset($_POST['registerEmailField']) && !empty($_POST['registerEmailField'])) {
				$_editUser->setEmail($_POST['registerEmailField']);
			}

			if (isset($_POST['registerDOBField']) && !empty($_POST['registerDOBField'])) {
				$_editUser->setDOB($_POST['registerDOBField']);
			}

			if (isset($_POST['registerCountryField']) && !empty($_POST['registerCountryField'])) {
				$_editUser->setCountry($_POST['registerCountryField']);
			}
			// </editor-fold>
		} else if (isset($_POST['userPermissionsSubmitButton'])) { // If the permissions form has been submitted
			// <editor-fold defaultstate="collapsed" desc="Permission Form Handling">
			$settings = array();

			foreach ($_POST as $key => $var) {
				if (substr($key, 0, 11) == "permission_") { // Check this is a setting variable
					$key = substr($key, 11);
					$key = str_replace(" ", "_", $key); // Replace spaces with underscores

					$settings[$key] = $var;
				}
			}

			$_editUser->savePermissions($settings);
			// </editor-fold>
		} else if (isset($_POST['banSubmitButton'])) {
			// <editor-fold defaultstate="collapsed" desc="Ban Form Handling">
			$errors = array();

			if (!$_currUser->checkPermission("ban")) {
				$errors['unban'][] = "Access denied";
			}

			if (isset($_POST['superBan'])) { // If the superban checkbox was checked
				$superBan = true;
			} else { // If the superban checkbox wasn't checked
				$superBan = false;
			}

			if (isset($_POST['banType'])) {
				if ($_POST['banType'] == "temp" || $_POST['banType'] == "perm") {
					$banType = $_mysql->real_escape_string($_POST['banType']);

					if ($banType == "temp") { // If it's a temporary ban look for the expiry date
						if (isset($_POST['banExpiry'])) {
							if (!empty($_POST['banExpiry'])) {
								$expires = strtotime($_POST['banExpiry']);
							} else {
								$errors['banExpiry'][] = "Empty";
							}
						} else {
							$errors['banExpiry'][] = "Not set";
						}
					} else {
						$expires = 0;
					}
				} else {
					$errors['banType'][] = "Invalid";
				}
			} else {
				$errors['banType'][] = "Not set";
			}

			if (count($errors) > 0) {
				die("There were errors.");
			} else {
				$_editUser->ban($banType, $superBan, $expires);
			}
			// </editor-fold>
		} else if (isset($_POST['unbanSubmitButton'])) {
			// <editor-fold defaultstate="collapsed" desc="Unban Form Handling">
			$errors = array();

			if (!$_currUser->checkPermission("unban")) {
				$errors['unban'][] = "Access denied";
			}

			if (count($errors) > 0) {
				die("There were errors.");
			} else {
				$_editUser->unban();
			}
			// </editor-fold>
		}

		// <editor-fold defaultstate="collapsed" desc="Edit User Display Code">
		print("<h1>Editing " . $_editUser->name() . "</h1>");

		print("<h2>Personal Settings</h2>");
		?>
		<form id="registerForm" method="post">
			<table>
				<tr>
					<td class="right"><label for="registerUserNameField">Username:</label> <input type="text" id="registerUserNameField" name="registerUserNameField" value="<?php print($_editUser->name()); ?>" /></td>
				</tr>
				<tr>
					<td class="right"><label for="registerRankField">Rank:</label> <select id="registerRankField" name="registerRankField"><?php
		// Rank option of "None"
		print("<option value=\"0\" " . (($_editUser->rankID() == 0) ? "selected=\"selected\"" : "") . ">None</option>");

		$rankQuery = $_mysql->query("SELECT `id`, `name` FROM `ranks` ORDER BY `level` DESC");

		while ($rank = $rankQuery->fetch_assoc()) {
			print("<option " . (($_editUser->rankID() == $rank['id']) ? "selected=\"selected\"" : "") . " value=\"" . $rank['id'] . "\">" . $rank['name'] . "</option>\n");
		}
		?></select></td>
				</tr>
				<tr>
					<td class="right"><label for="registerPasswordField">Password:</label> <input type="password" id="registerPasswordField" name="registerPasswordField" /></td>
				</tr>
				<tr>
					<td class="right"><label for="registerPasswordField2">Confirm Password:</label> <input type="password" id="registerPasswordField2" name="registerPasswordField2" /></td>
				</tr>
				<tr>
					<td class="right"><label for="registerEmailField">Email:</label> <input type="text" id="registerEmailField" name="registerEmailField" value="<?php print($_editUser->email()); ?>" /></td>
				</tr>
				<tr>
					<td class="right"><label for="registerDOBField">Date of birth:</label> <input type="text" id="registerDOBField" name="registerDOBField" value="<?php print($_editUser->dob()); ?>" /></td>
				</tr>
				<tr>
					<td class="right"><label for="registerCountryField">Country of residence:</label> <input type="text" id="registerCountryField" name="registerCountryField" value="<?php print($_editUser->country()); ?>" /></td>
				</tr>

				<tr>
					<td style="padding-left: 150px;"><button type="submit" id="registerSubmitButton" name="registerSubmitButton">Update</button></td>
				</tr>
			</table>
		</form>
		<?php
		/* Options */
		print("<h2>Options</h2>");

		if ($_currUser->checkPermission("ban") || $_currUser->checkPermission("unban")) {
			print("<h3>Banning</h3>\n");
			print("<form id=\"banForm\" method=\"post\">\n");

			if (!$_editUser->isBanned()) {
				if ($_currUser->checkPermission("ban")) {
					print("<input type=\"radio\" name=\"banType\" id=\"banTypeTemp\" class=\"banType\" value=\"temp\" checked=\"checked\"/> Temporary<br />\n");

					print("<input type=\"radio\" name=\"banType\" id=\"banTypePerm\" class=\"banType\" value=\"perm\"/> Permanent<br />\n");

					// TODO: Attach JQuery UI calendar to the ban expiry
					print("<span id=\"banExpiry_opts\"><input type=\"text\" name=\"banExpiry\" id=\"banExpiry\" /> Ban Expiry<br />\n</span>");

					print("<input type=\"checkbox\" name=\"superBan\" id=\"superBan\" /> Superban?<br />\n");

					print("<button type=\"submit\" id=\"banSubmitButton\" name=\"banSubmitButton\">Ban</button>\n");
				}
			} else {
				if ($_currUser->checkPermission("unban"))
					print("<button type=\"submit\" id=\"unbanSubmitButton\" name=\"unbanSubmitButton\">Unban</button>\n");
			}

			print("</form>\n");
		}


		/* User Permissions */
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
			print("<tr><td><input type=\"radio\" id=\"perrmission_" . $permKey . "1\" name=\"permission_" . $permKey . "\" value=\"1\" " . (($_editUser->checkPermission($permKey, "user")) ? "checked=\"checked\"" : "") . " /></td><td><input type=\"radio\" id=\"permission_" . $permKey . "1\" name=\"permission_" . $permKey . "\" value=\"0\" " . ((!$_editUser->checkPermission($permKey, "user")) ? "checked=\"checked\"" : "") . " /></td><td>" . $permDesc . "</td></tr>\n");
		}

		print("</table>\n");


		print("<br /><button type=\"submit\" id=\"userPermissionsSubmitButton\" name=\"userPermissionsSubmitButton\">Save Permission</button>");

		print("</form>\n");
		// </editor-fold>
	}
}
?>