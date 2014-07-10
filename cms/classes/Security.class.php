<?php
/**
 * Security class
 *
 * @author Edward
 */
class Security {

	/**
	 * SHA 2 (512) a string
	 * 
	 * @param string $text
	 * @return string $hash
	 */
	public static function sha2($text) {
		return hash("sha512", $text);
	}

	/**
	 * Generate a CSRF token for a form
	 * 
	 * @param string $form The name of the form to generate a token for
	 * @param boolean $returnAsVar
	 * @global mysqli $_mysql
	 * @return string $tokenOutput
	 */
	public static function generateToken($form, $returnAsVar = false) {
		// <editor-fold defaultstate="collapsed" desc="Generate Token Code">
		global $_mysql;
		
		$ip = $_mysql->real_escape_string($_SERVER['REMOTE_ADDR']); // Get the user's IP
		$agent = $_mysql->real_escape_string($_SERVER['HTTP_USER_AGENT']); // Get the user's user agent (software and firmware data)

		$sesID = $_mysql->real_escape_string(session_id()); // Get the user's session id

		$salt = "it'stheendoftheworldasweknowit"; // Set the salt

		$token = self::sha2($salt . $agent . $sesID . time()); // Create the token, don't include the IP, as this can change from when a form is submitted to when it's handled, depending on the user's ISP

		$query = $_mysql->query("INSERT INTO `tokens` (`sesid`, `ip`, `agent`, `token`, `form`, `stamp`) VALUES ('" . $sesID . "', '" . $ip . "', '" . $agent . "', '" . $token . "', '" . $form . "', '" . time() . "')");

		if (!$query) { // If the query failed
			die("<strong>Error:</strong> A token could not be generated.");
		}

		if ($returnAsVar) { // If we're returning the token output as a variable
			return "<input type=\"hidden\" value=\"$token\" name=\"101Token\" id=\"101Token\"/>\n";
		} else { // If we're outputting the token
			print("<input type=\"hidden\" value=\"$token\" name=\"101Token\" id=\"101Token\"/>\n"); // Output the token as a hidden form input field
		}
		// </editor-fold>
	}

	/**
	 * Checks to see if a CSRF token is valid
	 * 
	 * @param string $form The name of the form to check the token against
	 * @param string $token The token to check
	 * @param int $timeout The amount of time the token is valid for, default of 5 minutes
	 * @global mysqli $_mysql
	 * @return boolean
	 */
	public static function checkToken($form, $token, $timeout = 300) {
		// <editor-fold defaultstate="collapsed" desc="Check Token Code">
		global $_mysql;
		
		$form = $_mysql->real_escape_string($form);
		$token = $_mysql->real_escape_string($token);
		$sesID = $_mysql->real_escape_string(session_id());

		$ip = $_mysql->real_escape_string($_SERVER['REMOTE_ADDR']);
		$agent = $_mysql->real_escape_string($_SERVER['HTTP_USER_AGENT']);

		// Don't query the database using the IP address, as it can change from when the form was submitted depending on the user's ISP
		$query = $_mysql->query("SELECT * FROM `tokens` WHERE `sesid` = '" . $sesID . "' AND `agent` = '" . $agent . "' AND `token` = '" . $token . "' AND `form` = '" . $form . "' LIMIT 1");

		if ($query->num_rows) { // If a token for this form and user exists
			$data = $query->fetch_assoc();
			$_mysql->query("DELETE FROM `tokens` WHERE `sesid` = '" . $sesID . "' AND `form` = '" . $form . "'"); // Delete the old token, and any other ones for this user and form that are hanging around

			$sum = ($data['stamp'] + $timeout);

			if (time() > $sum) { // If the token has expired
				return false;
			} else { // If the token has not expired
				return true;
			}
		} else { // If a token for this form and user doesn't exist, delete any that exist for this session and form, but don't have the correct user agent
			$_mysql->query("DELETE FROM `tokens` WHERE `sesid` = '" . $sesID . "' AND `form` = '" . $form . "'");
			return false;
		}
		// </editor-fold>
	}

	/**
	 * Generate a unique salt
	 * 
	 * @global mysqli $_mysql
	 * @return string $salt
	 */
	public static function generateSalt() {
		// <editor-fold defaultstate="collapsed" desc="Generate Salt Code">
		global $_mysql;
		
		$letters = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		$length = 12;
		$salt = "";
		$validSalt = false;

		// Generate a new salt until it's unique
		do {
			$salt = ""; // Reset the salt

			for ($i = 0; $i < $length; $i++) {
				if (rand(0, 1) == 1) { // Select a letter
					$salt .= $letters[rand(0, (count($letters) - 1))]; // Get a random letter from the letters array
				} else { // Select a number
					$salt .= rand(0, 9); // Get a random number between 0 and 9
				}
			}

			$query = $_mysql->query("SELECT `id` FROM `users` WHERE `salt` = '" . $salt . "'");

			if (!$query->num_rows) { // If there's no users with the same key
				$validSalt = true;
			}
		} while (!$validSalt);

		return $salt;
		// </editor-fold>
	}

	/**
	 * Output captcha
	 * 
	 */
	public static function outputCaptcha($type) {
		// <editor-fold defaultstate="collapsed" desc="Output Captcha Code">
		global $_site;

		if ($type == "reCAPTCHA") {
			// Google Recaptcha
			include_once("./cms/lib/recaptcha/recaptchalib.php"); // Include recaptcha library

			$key = $_site->setting("recaptchaPublicKey"); // Recaptcha public key

			if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) { // If we're using HTTPS
				print(recaptcha_get_html($key, null, true));
			} else { // If we're using HTTP
				print(recaptcha_get_html($key));
			}
		} else if ($type == "emCreations") {
			// EM-Creations Captcha
			include_once("./cms/lib/emcaptcha/captcha.class.php");

			$captcha = new Captcha();
			$captcha->setOpts("white", true, 5, "Answer:");

			$captcha->output("./cms/lib/emcaptcha");
		} else if ($type == "solveMedia") { // If we're using Solve Media
			// Solve Media
			include_once("./cms/lib/solvemedia/solvemedialib.php"); // Include the Solve Media library
			
			$key = $_site->setting("solveMediaCKey"); // Solve Media challenege key

			if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) { // If we're using HTTPS
				print(solvemedia_get_html($key, null, true));
			} else { // If we're using HTTP
				print(solvemedia_get_html($key));
			}
		}
		// </editor-fold>
	}

	/**
	 * Determine whether captcha was valid
	 * 
	 * @return boolean $validCaptcha 
	 */
	public static function validCaptcha($type) {
		// <editor-fold defaultstate="collapsed" desc="Valid Captcha Code">
		global $_site;

		if ($type == "reCAPTCHA") {
			// Google Recaptcha
			include_once("./cms/lib/recaptcha/recaptchalib.php"); // Include recaptcha library

			$key = $_site->setting("recaptchaPrivateKey"); // Recaptcha private key

			$resp = recaptcha_check_answer($key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

			return $resp->is_valid; // Return whether the captcha is valid or not
		} else if ($type == "emCaptcha") {
			// EM-Creations Captcha
			include_once("./cms/lib/emcaptcha/captcha.class.php");

			$captcha = new Captcha($_POST['emCaptchaID']);

			return $captcha->isValid();
		} else if ($type == "solveMedia") {
			// Solve Media Captcha
			include_once("./cms/lib/solvemedia/solvemedialib.php"); // Include the Solve Media library
			
			$vKey = $_site->setting("solveMediaVKey"); // Solve Media verification key
			$hKey = $_site->setting("solveMediaHKey"); // Solve Media authentication hash
			
			$resp = solvemedia_check_answer($vKey, 
											$_SERVER['REMOTE_ADDR'], 
											$_POST['adcopy_challenge'], 
											$_POST['adcopy_response'], 
											$hKey);
			
			return $resp->is_valid; // Return whether the captcha is valid or not
		}
		// </editor-fold>
	}
	
	/**
	 * Returns whether an email address is valid or not
	 * 
	 * @param string $emailAddress
	 * @return boolean $valid
	 */
	public static function isEmailValid($emailAddress) {
		// <editor-fold defaultstate="collapsed" desc="Validate Email Address Code">
		if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
			return true;
		} else {
			return false;
		}
		// </editor-fold>
	}
}
?>