<?php
/*
 * Register Page
 */

if ($_currUser->type() == "guest") { // If they're not logged in
	if (isset($_POST['registerSubmitButton'])) {
		// <editor-fold defaultstate="collapsed" desc="Register Form Handling Code">
		$errors = array();
		
		if (!isset($_POST['registerUserNameField'])) {
			$errors['userName'][] = "Not set";
		} else {
			$userName = $_mysql->real_escape_string($_POST['registerUserNameField']);

			if (empty($userName)) {
				$errors['userName'][] = "User name cannot be empty.";
			} else {
				$userNameQuery = $_mysql->query("SELECT `id` FROM `users` WHERE `name` = '" . $userName . "'");

				if ($userNameQuery->num_rows) { // if this user name is already in use
					$errors['userName'][] = "This user name is already in use.";
				} else {
					if (strlen($userName) < 3 || strlen($userName) > 13) {
						$errors['userName'][] = "User name must be between 4 and 13 characters long.";
					} else {
						if (!preg_match("/^[a-z\d_]{2,20}$/i", $userName)) {
							$errors['userName'][] = "User name contains invalid characters.";
						}
					}
				}
			}
		}

		if (!isset($_POST['registerPasswordField'])) {
			$errors['password'][] = "Not set";
		} else {
			$password = $_mysql->real_escape_string($_POST['registerPasswordField']);

			if (empty($password)) {
				$errors['password'][] = "Password cannot be empty.";
			} else {
				
			}
		}

		if (!isset($_POST['registerPasswordField2'])) {
			$errors['confirmPassword'][] = "Not set";
		} else {
			$password2 = $_mysql->real_escape_string($_POST['registerPasswordField2']);

			if (empty($password2)) {
				$errors['confirmPassword'][] = "Password cannot be empty.";
			} else {
				if ($password != $password2) {
					$errors['confirmPassword'][] = "Passwords do not match.";
				}
			}
		}

		if (!isset($_POST['registerEmailField'])) {
			$errors['email'][] = "Not set";
		} else {
			$email = $_mysql->real_escape_string($_POST['registerEmailField']);
			if (empty($email)) {
				$errors['email'][] = "Email address cannot be empty.";
			} else {
				$emailQuery = $_mysql->query("SELECT `id` FROM `users` WHERE `email` = '" . $email . "'");

				if ($emailQuery->num_rows) { // if this email is already in use
					$errors['email'][] = "This email address is already in use.";
				} else {
					if (!preg_match("^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$^", $email)) {
						$errors['email'][] = "Invalid email address.";
					}
				}
			}
		}

		if (!isset($_POST['registerDOBField'])) {
			$errors['dob'][] = "Not set";
		} else {
			$dob = strtotime($_mysql->real_escape_string($_POST['registerDOBField']));
			
			if (empty($dob)) {
				$errors['dob'][] = "Date of birth cannot be empty.";
			} else {
				if ($dob == 0 || $dob > time()) { // If the DOB is 0 or is in the future, it's invalid
					$errors['dob'][] = "Invalid date of birth.";
				}
			}
		}

		if (!isset($_POST['registerCountryField'])) {
			$errors['country'][] = "Not set";
		} else {
			if (empty($_POST['registerCountryField'])) {
				$errors['country'][] = "Country cannot be empty.";
			} else {
				if (!array_key_exists($_POST['registerCountryField'], $_countries)) { // If the country provided isn't in the countries array
					$errors['country'][] = "Invalid country.";
				} else { // If the country provided is in the countries array
					$country = $_mysql->real_escape_string($_POST['registerCountryField']);
				}
			}
		}

		if ($_site->setting("captchaOnRegister")) { // If we're using captcha on the register page
			if ($_site->setting("recaptcha")) { // If the setting to turn on recaptcha is enabled
				if (!Security::validCaptcha("reCAPTCHA")) {
					$errors['captcha'][] = "Invalid captcha provided.";
				}
			}

			if ($_site->setting("EM-CreationsCaptcha")) { // If the setting to turn on EM-Creations captcha is enabled
				if (!Security::validCaptcha("emCaptcha")) {
					$errors['captcha'][] = "Invalid captcha provided.";
				}
			}

			if ($_site->setting("solveMediaCaptcha")) { // If the setting to turn on EM-Creations captcha is enabled
				if (!Security::validCaptcha("solveMedia")) {
					$errors['captcha'][] = "Invalid captcha provided.";
				}
			}
		}

		if (!Security::checkToken("registerForm", $_POST['101Token'])) { // If the CSRF token is invalid
			$errors['csrf'][] = "Invalid token";
		}

		if (count($errors)) { // If there are errors
			print("There were errors");
			//print_r($errors);
		} else {
			$salt = Security::generateSalt();
			$password = Security::sha2($salt . $password);

			$_mysql->query("INSERT INTO `users` 
		(`name`, 
		`salt`, 
		`password`, 
		`email`, 
		`dob`, 
		`country`, 
		`permissions`, 
		`rank`, 
		`register_ip`, 
		`last_ip`, 
		`registered`, 
		`last_login`) 
		VALUES 
		('" . $userName . "', 
		 '" . $salt . "', 
		 '" . $password . "', 
		 '" . $email . "', 
		 '" . $dob . "', 
		 '" . $country . "', 
		 '" . serialize(array()) . "', 
		 0, 
		 '" . $_mysql->real_escape_string($_SERVER['REMOTE_ADDR']) . "', 
		 '" . $_mysql->real_escape_string($_SERVER['REMOTE_ADDR']) . "', 
		 " . time() . ", 
		 " . time() . ")");
			
			User::redirect("./?p=Home", "success", "Account created"); // Redirect the user
		}
		// </editor-fold>
	}
	// <editor-fold defaultstate="collapsed" desc="Register Form Display Code">
	// JavaScript
	?>
	<script type="text/javascript">
		$(function() {
			$("#displayDOBField").datepicker(
				{
					defaultDate: "-18y",
					dateFormat: "<?php print(Site::convertPHPToJSDateFormat($_site->setting("dateFormat"))); ?>",
					altField: "#registerDOBField",
					altFormat: "yy-mm-dd",
					changeYear: true,
					yearRange: "-120:-12"
				}	
			);
		});
	</script>

	<form id="registerForm" method="post">
		<?php Security::generateToken("registerForm"); ?>
		<table>
			<tr>
				<td class="right"><label for="registerUserNameField">Username:</label></td><td><input type="text" id="registerUserNameField" name="registerUserNameField" <?php if (isset($errors['userName'])) { print("class=\"error\""); } ?> /></td>
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
				<td class="right"><label for="registerPasswordField">Password:</label></td><td><input type="password" id="registerPasswordField" name="registerPasswordField" <?php if (isset($errors['password'])) { print("class=\"error\""); } ?> /></td>
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
				<td class="right"><label for="registerPasswordField2">Confirm Password:</label></td><td><input type="password" id="registerPasswordField2" name="registerPasswordField2" <?php if (isset($errors['confirmPassword'])) { print("class=\"error\""); } ?> /></td>
				<?php 
					if (isset($errors['confirmPassword'])) {
						print("<td class=\"error\">\n");
						print("<img src=\"./themes/" . strip_tags($themeData['directory']) . "/images/error.gif\" alt=\"Error\" /> ");
						foreach ($errors['confirmPassword'] as $error) {
							print($error . "<br />\n");
						}
						print("</td>\n");
					} 
				?>
			</tr>
			<tr>
				<td class="right"><label for="registerEmailField">Email:</label></td><td><input type="text" id="registerEmailField" name="registerEmailField" <?php if (isset($errors['email'])) { print("class=\"error\""); } ?> /></td>
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
				<td class="right"><label for="displayDOBField">Date of birth:</label></td><td><input type="text" id="displayDOBField" name="displayDOBField" <?php if (isset($dob) && !empty($dob)) { print("value=\"" . date($_site->setting("dateFormat"), $dob) . "\""); } ?> <?php if (isset($errors['dob'])) { print("class=\"error\""); } ?> />
					<input type="hidden" id="registerDOBField" name="registerDOBField" <?php if (isset($dob) && !empty($dob)) { print("value=\"" . date("Y-m-d", $dob) . "\""); } ?> /></td>
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
				<td class="right"><label for="registerCountryField">Country:</label></td><td><select id="registerCountryField" name="registerCountryField">
				<?php 
				
				foreach ($_countries as $code=>$country) { // For each country
					// TODO: Add support for choosing a default country
					print("<option value=\"" . $code . "\" " . ($country == "United Kingdom" ? "selected=\"selected\"" : "") . ">" . $country . "</option>\n");
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

			<?php if ($_site->setting("captchaOnRegister")) { // If the setting to use recaptcha on the register page is enabled  ?>
				<tr>
					<td></td><td><?php 
					
					if ($_site->setting("EM-CreationsCaptcha")) { // If we're using EM-Creations captcha
						Security::outputCaptcha("emCreations");
					} else if ($_site->setting("recaptcha")) { // If we're using Google reCaptcha
						Security::outputCaptcha("reCAPTCHA");
					} else if ($_site->setting("solveMediaCaptcha")) { // If we're using SolveMedia captcha
						Security::outputCaptcha("solveMedia");
					}
					
					?></td>
					<?php 
						if (isset($errors['captcha'])) {
							print("<td class=\"error\">\n");
							print("<img src=\"./themes/" . strip_tags($themeData['directory']) . "/images/error.gif\" alt=\"Error\" /> ");
							foreach ($errors['captcha'] as $error) {
								print($error . "<br />\n");
							}
							print("</td>\n");
						} 
					?>
				</tr>
			<?php
			}
			?>
			<tr>
				<td></td><td><button type="submit" id="registerSubmitButton" name="registerSubmitButton">Register</button></td>
			</tr>
		</table>
	</form>
	<?php
	// </editor-fold>
}
?>