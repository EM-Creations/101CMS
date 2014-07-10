<?php
if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

if (!$_currUser->checkPermission("rosters")) {
	header("Location: ./");
	exit;
}

if (isset($_GET['roster'])) { // If we're existing a pre-existing roster
	// <editor-fold defaultstate="collapsed" desc="Edit Roster Code">
	$rosterID = $_mysql->real_escape_string($_GET['roster']);

	if (!is_numeric($rosterID)) { // If the roster id isn't a number
		header("Location: ./?p=Rosters");
		exit;
	}

	$rosterQuery = $_mysql->query("SELECT `name` FROM `rosters` WHERE `id` = " . $rosterID . " LIMIT 1");
	$roster = $rosterQuery->fetch_assoc();
	$roster['id'] = $rosterID;

	if (isset($_POST['addUserButton'])) { // If the add user form has been submitted
		// <editor-fold defaultstate="collapsed" desc="Add User Form Handling Code">
		$errors = array();

		// Validate the user name
		if (!isset($_POST['userName'])) {
			$errors['user'][] = "Not set";
		} else {
			if (empty($_POST['userName'])) {
				$errors['user'][] = "Empty";
			} else {
				$user = new User($_mysql->real_escape_string($_POST['userName'])); // Create a new user object by username

				if ($user->type() == "guest") { // If the type is guest the user object failed to create properly
					$errors['user'][] = "Doesn't exist";
				}
			}
		}

		// Validate the order
		if (!isset($_POST['order'])) {
			$errors['order'][] = "Not set";
		} else {
			if (empty($_POST['order'])) {
				$errors['order'][] = "Empty";
			} else {
				if (!is_numeric($_POST['order'])) {
					$errors['order'][] = "Not a number";
				} else {
					$order = $_mysql->real_escape_string($_POST['order']);
				}
			}
		}

		// Check that this user doesn't already exist in the roster
		if (count($errors > 0)) { // We won't have the $user variable to work with unless there were no errors so far
			$query = $_mysql->query("SELECT `id` FROM `roster_members` WHERE `roster` = " . $roster['id'] . " AND `user` = " . $user->userID());

			if ($query && $query->num_rows) { // If this user is already in this roster
				$errors['user'][] = "Already in roster";
			}
		}

		if (count($errors) > 0) { // If there was at least one error
			die("There were errors. " . print_r($errors, true));
		} else { // If there were no errors
			$_mysql->query("INSERT INTO `roster_members` (`user`, `roster`, `order`) VALUES (
			" . $user->userID() . ", 
			" . $roster['id'] . ", 
			" . $order . ")
			") or die($_mysql->error);
		}
		// </editor-fold>
	}

	print("<h1>" . $roster['name'] . " Roster</h1>\n");
	// Get this roster's members
	$rosterMembersQuery = $_mysql->query("SELECT `id`, `user`, `order` FROM `roster_members` WHERE `roster` = " . $roster['id'] . " ORDER BY `order` ASC");

	if ($rosterMembersQuery->num_rows) { // If there's at least one user in this roster
		print("<table>\n");

		print("<th>Member</th>\n");
		print("<th>Order</th>\n");
		print("<th>Options</th>\n");

		while ($member = $rosterMembersQuery->fetch_assoc()) {
			$user = new User($member['user']); // Create a new user object for this roster member
			print("<tr id=\"member_" . $member['id'] . "\">\n");
			print("<td>" . $user->name() . "</td>\n");
			print("<td>" . $member['order'] . "</td>\n");
			print("<td><button id=\"remove_" . $member['id'] . "\" class=\"removeRosterMemberButton\">Remove</button></td>\n");
			print("</tr>\n");
		}

		print("</table>\n");
	} else {
		print("This roster has no members.");
	}
	?>
	<h2>Add User</h2>
	<form method="post" id="">
		<table>
			<th>
				User
			</th>
			<th>
				Order
			</th>
			<tr>
				<td>
	<?php // TODO: Auto suggest JQuery-UI for user names  ?>
					<input type="text" name="userName" id="userName" />
				</td>
				<td>
					<select name="order" id="order">
	<?php
	for ($i = 1; $i < 101; $i++) {
		print("<option value=\"" . $i . "\">" . $i . "</option>");
	}
	?>
					</select>
				</td>
				<td>
					<button type="submit" name="addUserButton" id="addUserButton">Add User</button>
				</td>
			</tr>
		</table>
	</form>
	<?php
	// </editor-fold>
} else { // If we're viewing the list of rosters
	// <editor-fold defaultstate="collapsed" desc="Roster List Code">
	if (isset($_POST['rosterSubmitButton'])) { // The new roster form has been submitted
		$errors = array();

		if (!isset($_POST['rosterName'])) { // If the roster name was not set
			$errors['name'][] = "Not set";
		} else {
			if (empty($_POST['rosterName'])) {
				$errors['name'][] = "Empty";
			} else {
				$rosterName = $_mysql->real_escape_string($_POST['rosterName']);
			}
		}

		if (count($errors) > 0) { // If there were errors
			// TODO: Output roster errors in a nice way
			die("There were errors");
		} else {
			$_mysql->query("INSERT INTO `rosters` (`name`) VALUES ('" . $rosterName . "')"); // Insert roster row
		}
	}

	print("<h1>Rosters</h1>\n");

	$query = $_mysql->query("SELECT * FROM `rosters` ORDER BY `id` DESC"); // Query the database, selecting all custom rosters

	if ($query->num_rows) { // If there is at least one custom roster
		print("<table>\n");
		print("<th>Roster</th>\n");
		print("<th>Options</th>\n");

		while ($row = $query->fetch_assoc()) {
			print("<tr id=\"roster_" . $row['id'] . "\" class=\"roster\">\n");

			print("<td>" . $row['name'] . "</td>\n");
			print("<td><button class=\"editRosterButton\" id=\"edit_" . $row['id'] . "\">Edit</button> <button class=\"deleteRosterButton\" id=\"delete_" . $row['id'] . "\">Delete</button></td>\n");

			print("</tr>\n");
		}

		print("</table>\n");
	} else {
		print("There are no custom rosters.");
	}
	?>
	<h2>New Roster</h2>
	<form method="post" id="newRosterForm">
		<table>
			<tr>
				<th>
					Roster Name
				</th>
			</tr>
			<tr>
				<td>
					<input type="text" name="rosterName" id="rosterName" />
				</td>
				<td>
					<button type="submit" name="rosterSubmitButton" id="rosterSubmitButton">Create</button>
				</td>
			</tr>
		</table>
	</form>
	<?php
	// </editor-fold>
}
?>