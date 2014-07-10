<?php
if ($_currUser->type() == "guest") { // If this user is not logged in
	header("Location: ./?p=Login");
	exit;
}

// TODO: Let users upload avatars and set their avatar etc..

if (isset($_POST['updateButton'])) { // If the personal settings form has been submitted
	// <editor-fold defaultstate="collapsed" desc="Update Personal Settings Form Handling Code">
	$errors = array(); // Array to store errors
	
	if (Security::checkToken("updateAccountForm", $_POST['101Token'])) { // If the CSRF token is valid
		if ($_site->setting("allowChangeUserName")) {
			if (isset($_POST['userNameField']) && !empty($_POST['userNameField'])) {
				$ret = $_currUser->setName($_POST['userNameField']);

				if ($ret !== true) { // If setting the name wasn't successful
					$errors['userName'][] = $ret;
				}
			}
		}

		if ((isset($_POST['passwordField']) && !empty($_POST['passwordField'])) && (isset($_POST['passwordField2']) && !empty($_POST['passwordField2']))) {
			$password = $_POST['passwordField'];
			$passwordConfirm = $_POST['passwordField2'];

			if ($password == $passwordConfirm) { // If the passwords match set the password
				$_currUser->setPassword($password);
			} else { // if the passwords don't match
				$errors['password'][] = "Passwords don't match.";
			}
		}

		if (isset($_POST['emailField']) && !empty($_POST['emailField'])) {
			$ret = $_currUser->setEmail($_POST['emailField']);
			
			if ($ret !== true) { // If setting the email wasn't successful
				$errors['email'][] = $ret;
			}
		}

		if (isset($_POST['dobField']) && !empty($_POST['dobField'])) {
			$ret = $_currUser->setDOB(strtotime($_POST['dobField']));
			
			if ($ret !== true) { // If setting the DOB wasn't successful
				$errors['dob'][] = $ret;
			}
		}

		if (isset($_POST['countryField']) && !empty($_POST['countryField'])) {
			$ret = $_currUser->setCountry($_POST['countryField']);
			
			if ($ret !== true) { // If setting the country wasn't successful
				$errors['country'][] = $ret;
			}
		}
	} else { // If the CSRF token isn't valid
		$errors['token'][] = "Invalid token.";
	}
	// </editor-fold>
}

if ($_site->setting("displayPageTitle")) { // If the setting to display page titles is enabled
	print("<span class=\"title\">Account</span>");
}

// <editor-fold defaultstate="collapsed" desc="Personal Settings Form Display Code">
// JavaScript
?>
<script type="text/javascript">
		$(function() {
			$("#displayDOBField").datepicker(
				{
					defaultDate: "-18y",
					dateFormat: "<?php print(Site::convertPHPToJSDateFormat($_site->setting("dateFormat"))); ?>",
					altField: "#dobField",
					altFormat: "yy-mm-dd",
					changeYear: true,
					yearRange: "-120:-12"
				}	
			);
		});
</script>

<form id="updateAccountForm" method="post">
<?php Security::generateToken("updateAccountForm"); ?>
	<table>
	<?php if ($_site->setting("allowChangeUserName")) { // If the setting to allow users to change their user name is enabled ?>
			<tr>
				<td class="right"><label for="userNameField">Username:</label></td><td><input type="text" id="userNameField" name="userNameField" <?php if (isset($errors['userName'])) { print("class=\"error\""); } ?> value="<?php print($_currUser->name()); ?>" /></td>
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
<?php } ?>
		<tr>
			<td class="right"><label for="passwordField">Password:</label></td><td><input type="password" id="passwordField" name="passwordField" <?php if (isset($errors['password'])) { print("class=\"error\""); } ?> /></td>
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
			<td class="right"><label for="passwordField2">Confirm Password:</label></td><td><input type="password" id="passwordField2" name="passwordField2" <?php if (isset($errors['password'])) { print("class=\"error\""); } ?> /></td>
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
			<td class="right"><label for="emailField">Email:</label></td><td><input type="text" id="emailField" name="emailField" <?php if (isset($errors['email'])) { print("class=\"error\""); } ?> value="<?php print($_currUser->email()); ?>" /></td>
			<?php 
			if (isset($errors['email'])) {
				print("<td class=\"error\">\n");
				print("<img src=\"./themes/" . strip_tags($themeData['directory']) . "/images/error.gif\" alt=\"Error\" /> ");
				foreach ($errors['email'] as $error) {
					print($error . "<br />\n");
				}
				print("</td>\n");
			} 
			?>
		</tr>
		<tr>
			<td class="right"><label for="displayDOBField">Date of birth:</label></td><td><input type="text" id="displayDOBField" name="displayDOBField" <?php if (isset($errors['dob'])) { print("class=\"error\""); } ?> value="<?php print($_currUser->dob($_site->setting("dateFormat"))); ?>" />
				<input type="hidden" id="dobField" name="dobField" value="<?php print($_currUser->dob("Y-m-d")); ?>" /></td>
			<?php 
			if (isset($errors['dob'])) {
				print("<td class=\"error\">\n");
				print("<img src=\"./themes/" . strip_tags($themeData['directory']) . "/images/error.gif\" alt=\"Error\" /> ");
				foreach ($errors['dob'] as $error) {
					print($error . "<br />\n");
				}
				print("</td>\n");
			} 
			?>
		</tr>
		<tr>
			<td class="right"><label for="countryField">Country:</label></td><td><select type="text" id="countryField" name="countryField">
			<?php
			
			foreach ($_countries as $code=>$country) { // For each country
				print("<option value=\"" . $code . "\" " . ($code == $_currUser->country(true) ? "selected=\"selected\"" : "") . ">" . $country . "</option>\n");
			}
			
			print("</select></td>\n");
			
			if (isset($errors['country'])) {
				print("<td class=\"error\">\n");
				print("<img src=\"./themes/" . strip_tags($themeData['directory']) . "/images/error.gif\" alt=\"Error\" /> ");
				foreach ($errors['country'] as $error) {
					print($error . "<br />\n");
				}
				print("</td>\n");
			} 
			?>
		</tr>

		<tr>
			<td></td><td><button type="submit" id="updateButton" name="updateButton">Update</button></td>
		</tr>
	</table>
</form>
<?php
// </editor-fold>
?>
