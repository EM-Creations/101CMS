<?php
/*
 * Login Page
 */

if ($_currUser->type() == "guest") { // If they're not logged in
	// <editor-fold defaultstate="collapsed" desc="Login Form Code">
	if (isset($_POST['loginSubmitButton'])) {
		// <editor-fold defaultstate="collapsed" desc="Login Form Handling Code">
		$errors = array();

		if (!isset($_POST['loginUserNameField']) || empty($_POST['loginUserNameField'])) {
			$errors['userName'][] = "User name cannot be empty.";
		}

		if (!isset($_POST['loginPasswordField']) || empty($_POST['loginPasswordField'])) {
			$errors['password'][] = "Password cannot be empty.";
		}

		if (!Security::checkToken("loginForm", $_POST['101Token'])) { // If the CSRF token is invalid
			$errors['csrf'][] = "Invalid token.";
		}

		if (count($errors) == 0) { // If there were no errors, try to log the user in
			// Try to query the database for the user's information
			$userName = $_mysql->real_escape_string($_POST['loginUserNameField']);
			$password = $_mysql->real_escape_string($_POST['loginPasswordField']);

			$query = $_mysql->query("SELECT `id`, `password`, `salt` FROM `users` WHERE `name` = '" . $userName . "'");

			if ($query->num_rows) { // If the user this person is trying to log in as exists
				// Check if the password is correct
				$row = $query->fetch_assoc();

				$password = Security::sha2($row['salt'] . $password);
				if ($password == $row['password']) { // If the passwords match, log the user in
					$_currUser->logIn($row['id']); // Log the user in with the specific variables
					User::redirect("./?p=Home", "success", "Successfully logged in"); // Redirect the user
				} else {
					$errors['password'][] = "Incorrect password.";
				}
			} else {
				$errors['userName'][] = "User doesn't exist.";
			}
		}

		if (count($errors) > 0) {
			print("Error logging in.");
		}
		// </editor-fold>
	}
		
	// <editor-fold defaultstate="collapsed" desc="Login Form Display Code">
	?>

	<form id="loginForm" method="post">
		<?php Security::generateToken("loginForm"); ?>
		<table>
			<tr>
				<td class="right"><label for="loginUserNameField">Username:</label></td><td><input type="text" id="loginUserNameField" name="loginUserNameField" <?php if (isset($errors['userName'])) { print("class=\"error\""); } if (isset($_POST['loginUserNameField']) && !empty($_POST['loginUserNameField'])) { print("value=\"" . strip_tags($_POST['loginUserNameField']) . "\""); } ?> /></td>
				<?php 
				if (isset($errors['userName'])) {
					print("<td class=\"error\">\n");
					print("<img src=\"./themes/" . strip_tags($themeData['directory']) . "/images/error.gif\" alt=\"Error\" /> ");
					foreach ($errors['userName'] as $error) {
						print($error . "<br />\n");
					}
					print("</td>\n");
				} 
				?>
			</tr>
			<tr>
				<td class="right"><label for="loginPasswordField">Password:</label></td><td><input type="password" id="loginPasswordField" name="loginPasswordField" <?php if (isset($errors['password'])) { print("class=\"error\""); } ?> /></td>
				<?php 
				if (isset($errors['password'])) {
					print("<td class=\"error\">\n");
					print("<img src=\"./themes/" . strip_tags($themeData['directory']) . "/images/error.gif\" alt=\"Error\" /> ");
					foreach ($errors['password'] as $error) {
						print($error . "<br />\n");
					}
					print("</td>\n");
				} 
				?>
			</tr>
			<tr>
				<td></td><td>Remember me? <input type="checkbox" id="loginRememberMeCheckBox" name="loginRememberMeCheckBox" /></td>
			</tr>
			<tr>
				<td></td><td><input type="submit" id="loginSubmitButton" name="loginSubmitButton" value="Login" /> <a href="./?p=Register">Register</a></td>
									<td></td></tr><tr><td></td><Td><a href="./?p=resetpass">Forgot Password?</a></td></tr>
		</table>
	</form>
	<?php
	// </editor-fold>
		
	// </editor-fold>
} else {
	// <editor-fold defaultstate="collapsed" desc="Logout Form Code">
	if (isset($_POST['logoutButton'])) { // If the logout button has been clicked
		if (Security::checkToken("logoutForm", $_POST['101Token'])) { // If the CSRF token is valid
			$_currUser->logout();

			User::redirect("./?p=Login", "success", "Successfully logged out"); // Redirect the user
		}
	}
	?>
	<form id="logoutForm" method="post">
	<?php Security::generateToken("logoutForm"); ?>
		<button id="logoutButton" name="logoutButton" >Logout</button>
	</form>
	<?php
	// </editor-fold>
}
?>