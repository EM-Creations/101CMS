<?php
/*
 * AJAX handling page
 */

require(__DIR__ . "/global.inc.php");

if (isset($_GET['req']) || isset($_POST['req'])) { // All AJAX requests must provide $_GET['req']
	if ($_POST['req'] == "login") {
		// <editor-fold defaultstate="collapsed" desc="Login Code">
		$json = array("status" => "failed", "errormsg" => false, "type" => "login");

		if ($_currUser->type() == "guest") { // If they're not logged in
			$json['type'] = "login";

			if (!isset($_POST['user']) || empty($_POST['user'])) {
				$json['status'] = "error";
				$json['errormsg'] = "Empty username";
			} else if (!isset($_POST['pass']) || empty($_POST['pass'])) {
				$json['status'] = "error";
				$json['errormsg'] = "Empty password";
			} else {
				// Try to query the database for the user's information
				$userName = $_mysql->real_escape_string($_POST['user']);
				$password = $_mysql->real_escape_string($_POST['pass']);

				$query = $_mysql->query("SELECT `id`, `password`, `salt` FROM `users` WHERE `name` = '" . $userName . "'");

				if ($query->num_rows) { // If the user this person is trying to log in as exists
					// Check if the password is correct
					$row = $query->fetch_assoc();

					$password = Security::sha2($row['salt'] . $password);
					if ($password == $row['password']) { // If the passwords match, log the user in
						$_currUser->logIn($row['id']); // Log the user in with the specific variables

						if ($_currUser->isBanned()) { // If this user is banned, log them back out
							$_currUser->logout();
							$json['status'] = "error";
							$json['errormsg'] = "Banned";
							$json['user'] = false;
						} else {
							$json['status'] = "success";
							$json['errormsg'] = false;
							$json['user'] = array("id" => $_currUser->userID(), "name" => $_currUser->name(), "avatar" => $_currUser->avatar("s"));
						}
					} else {
						$json['status'] = "error";
						$json['errormsg'] = "Incorrect Password";
					}
				} else {
					$json['status'] = "error";
					$json['errormsg'] = "User does not exist";
				}
			}
			print(json_encode($json));
		}
		// </editor-fold>
	} else if ($_POST['req'] == "logout") {
		// <editor-fold defaultstate="collasped" desc="Logout Code">
		$json = array("status" => "failed", "errormsg" => false, "type" => "logout");

		if ($_currUser->logout()) { // If logging out the user succeeded
			// TODO: Success logout code
			$json['status'] = "success";
		} else { // If we were not able to log out the user
			// TODO: Fail logout code
			$json['status'] = "failed";
			$json['errormsg'] = "Failed to logout";
		}
		print(json_encode($json));
		// </editor-fold>
	}
}
?>
