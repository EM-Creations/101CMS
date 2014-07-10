<?php
$errors = array();
if ($_currUser->type() != "guest") {
	$errors['currUserType'][] = "User is logged in!";
} else {


	if (!isset($_GET['email']) && !isset($_GET['token']) && !isset($_POST['resetEmailField']) && !isset($_POST['newPassField']) && !isset($_POST['newPassField2'])) {
		?>
		<form id="resetPassForm" method="post">
			<?php Security::generateToken("resetPassForm"); ?>
			<table>
				<tr>
					<td class="right"><label for="resetEmailField">Email:</label></td><td><input type="text" id="resetEmailField" name="resetEmailField" /></td>
				</tr>
				<tr><td></td></tr>
				<?php if ($_site->setting("recaptchaOnRegister")) { // If the setting to use recaptcha on the register page is enabled  ?>
					<tr>
						<td></td><td><?php Security::outputCaptcha("reCAPTCHA"); ?></td>
					</tr>
					<tr><td></td></tr>
					<?php
				}

				if ($_site->setting("EM-CreationsCaptchaOnRegister")) { // If the setting to use EM-Creations captcha on the register page is enabled 
					?>
					<tr>
						<td></td><td class="right"><?php Security::outputCaptcha("emCreations"); ?></td>
					</tr>
					<tr><td></td></tr>
				<?php }
				?>
				<tr>
					<td></td><td><input type="submit" id="passwordSubmitButton" name="passwordSubmitButton" value="Send" />
				</tr>
			</table>
		</form>
		<?php
	} elseif (isset($_POST['resetEmailField'])) {
		$email = $_mysql->real_escape_string($_POST['resetEmailField']);
		$emailQuery = $_mysql->query("SELECT `id` FROM `users` WHERE `email` = '" . $email . "'");
		if ($_site->setting("recaptchaOnRegister")) {
			if (!Security::validCaptcha("reCAPTCHA")) {
				$errors['captcha'][] = "Invalid";
			}
		}
		if ($_site->setting("EM-CreationsCaptchaOnRegister")) { // If the setting to turn on recaptcha is enabled
			if (!Security::validCaptcha("emCaptcha")) {
				$errors['captcha'][] = "Invalid";
			}
		}
		if (!Security::checkToken("resetPassForm", $_POST['101Token'])) { // If the CSRF token is invalid
			$errors['token'][] = "Bad token!";
		}
		if (!$emailQuery->num_rows) {
			$errors['email'][] = "Doesnt exist";
		} else {
			$emailQuery2 = $_mysql->query("SELECT * FROM resetpass_tokens WHERE email = '" . $email . "'");
		}
		if (!preg_match("^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$^", $email) && !preg_match("^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}.[a-zA-Z]{2,3}$^", $email)) {
			$errors['email'][] = "Bad Email!";
		}
		if (count($errors)) { // If there are errors
			print("There were errors");
			header("Location: ./?p=home");
			exit;
		} elseif ($emailQuery2->num_rows) {
			$_currUser->rePasswordReset($email);
			print("Request resent!");
			header("Location: ./?p=home");
			exit;
		} else {
			$_currUser->passwordReset($email);
			echo "Success!";
			header("Location: ./?p=home");
			exit;
		}
	} elseif (isset($_GET['email']) && isset($_GET['token'])) {

		$token = $_mysql->real_escape_string(trim($_GET['token'], "'"));
		$email = $_mysql->real_escape_string(trim($_GET['email'], "'"));
		$query = $_mysql->query("SELECT `id` FROM `resetpass_tokens` WHERE `email` = '" . $email . "'");
		$query2 = $_mysql->query("SELECT `id` FROM `resetpass_tokens` WHERE `token` = '" . $token . "'");
		if (!$query->num_rows || !$query2->num_rows) {
			print "Bad Email or Token!";
			header("Location: ./?p=home");
			exit;
		} else {
			?>


			<form id="newPassForm" method="post" action="./?p=resetpass">
				<?php Security::generateToken("newPassForm"); ?>
				<table>
					<tr>
						<td class="right"><label for="newPassField">New Password:</label></td><td><input type="password" id="newPassField" name="newPassField" /></td>
					</tr>
					<tr>
						<td class="right"><label for="newPassField2">Confirm Password:</label></td><td><input type="password" id="newPassField2" name="newPassField2" /></td>
					</tr>
					<tr>
						<?php echo "<input type=\"hidden\" id=\"token\" name=\"token\" value='" . $token . "' />" ?>
						<?php echo "<input type=\"hidden\" id=\"email\" name=\"email\" value='" . $email . "' />" ?>
						<td></td><td><input type="submit" id="newPasswordSubmitButton" name="newPasswordSubmitButton" value="Send" />
					</tr>
				</table>
			</form>


			<?php
		}
	} elseif (isset($_POST['newPassField']) && isset($_POST['newPassField2'])) {

		$token = $_mysql->real_escape_string(trim($_POST['token'], "'"));
		$email = $_mysql->real_escape_string(trim($_POST['email'], "'"));
		$pass1 = $_mysql->real_escape_string($_POST['newPassField']);
		$pass2 = $_mysql->real_escape_string($_POST['newPassField2']);
		if (!Security::checkToken("newPassForm", $_POST['101Token'])) { // If the CSRF token is invalid
			$errors['CSRFtoken'][] = "Bad CSRF token!";
		}
		if ($pass1 != $pass2) {
			$errors['password'][] = "passwords do not match!";
		}
		$query = $_mysql->query("SELECT `id` FROM `resetpass_tokens` WHERE `email` = '" . $email . "'");
		$query2 = $_mysql->query("SELECT `id` FROM `resetpass_tokens` WHERE `token` = '" . $token . "'");
		if (!$query->num_rows || !$query2->num_rows) {
			$errors['emailtoken'][] = "bad email or token!";
		}
		if (count($errors)) {
			print "There were errors.";
			header("Location: ./?p=home");
			exit;
		} else {
			$saltQuery = $_mysql->query("SELECT salt FROM users WHERE email = '" . $email . "'");
			$salt = $saltQuery->fetch_assoc();
			$newPassword = Security::sha2($salt['salt'] . $pass1);

			$_mysql->query("UPDATE `users` SET `password` = '" . $newPassword . "' WHERE `email` = '" . $email . "'") or die($_mysql->error);
			$_mysql->query("DELETE FROM resetpass_tokens WHERE token = '" . $token . "'");
			
			$sendEmailQuery = $_mysql->query("SELECT contact_email FROM `site_info` WHERE `id` = 1");
			$sendEmail = $sendEmailQuery->fetch_assoc();
			$emailTitleQuery = $_mysql->query("SELECT title FROM `site_info` WHERE `id` = 1");
			$emailTitle = $emailTitleQuery->fetch_assoc();
			require_once(__DIR__ . "/../lib/phpmailer/class.phpmailer.php");
			$mail = new PHPMailer();
			$mail->From = $sendEmail['contact_email'];
			$mail->FromName = $emailTitle['title'];
			$mail->AddAddress($email);
			$mail->IsHTML(true);
			$mail->Subject = "Password Reset! - '" . $emailTitle['title'] . "'";
			$mail->Body = "Your password has been changed at '" . $emailTitle['title'] . "'!";
			$mail->WordWrap = 50;
			$mail->Send();

			print "Success";
			header("Location: ./?p=home");
			exit;
		}
	}
}
?>
