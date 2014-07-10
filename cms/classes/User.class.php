<?php

/**
 * User class
 * @author Edward
 */
class User {
	// Declare class properties here
	private $userID = false;
	private $name = false;
	private $email = false;
	private $dob = false;
	private $country = false;
	private $permissions = array();
	private $rank = false;
	private $rankName = false;
	private $rankPermissions = array();
	private $registerIP = false;
	private $ip = false;
	private $type = "guest";
	private $salt = false;
	private $registered = false;
	private $lastLogin = false;
	private $avatar = false;

	/**
	 * Create a new user object
	 * 
	 * @param int $userID The ID of the user to create an object of
	 * @global mysqli $_mysql
	 */
	public function __construct($user = null) {
		// <editor-fold defaultstate="collapsed" desc="User Constructor Code">
		global $_mysql; 
		
		// If the user is null this is a guest account

		if ($user != null) {
			if (is_numeric($user)) { // If the user is numeric, it's probably an ID
				$userQuery = $_mysql->query("SELECT * FROM `users` WHERE `id` = " . $user);
			} else { // If the user is a string, it's probably a name
				$userQuery = $_mysql->query("SELECT * FROM `users` WHERE `name` = '" . $user . "'");
			}
		}

		if ($user == null || !@$userQuery->num_rows) { // If this is a guest user or if we tried to find the user and they weren't found
			$this->userID = false;
			$this->name = false;
			$this->email = false;
			$this->dob = false;
			$this->country = false;
			$this->rank = 0;
			$this->permissions = false;
			$this->rankName = false;
			$this->rankPermissions = array();
			$this->registerIP = false;
			$this->ip = false;
			$this->type = "guest";
			$this->salt = false;
			$this->registered = false;
			$this->lastLogin = false;
			$this->avatar = false;
		} else {
			$userData = $userQuery->fetch_assoc();

			$this->userID = $userData['id'];
			$this->name = $userData['name'];
			$this->email = $userData['email'];
			$this->dob = $userData['dob'];
			$this->country = $userData['country'];

			if (isset($userData['permissions']) && !empty($userData['permissions']) && $userData['permissions'] != '') {
				$this->permissions = unserialize($userData['permissions']);
			} else {
				$this->permissions = array();
			}

			// Set rank information
			$this->rank = $userData['rank'];

			$rank = new Rank($this->rank);

			$this->rankName = $rank->name();
			$this->rankPermissions = $rank->permissions();
			unset($rank);

			$this->registerIP = $userData['register_ip'];
			$this->ip = $userData['last_ip'];
			$this->type = "member";
			$this->salt = $userData['salt'];
			$this->registered = $userData['registered'];
			$this->lastLogin = $userData['last_login'];

			// Rank data
			$this->rank = $userData['rank'];


			if (!isset($userData['avatar']) || empty($userData['avatar'])) { // If this user hasn't set up an avatar, use the default
				$this->avatar = "101-default-avatar.jpg";
			}
		}
		// </editor-fold>
	}

	/**
	 * Get this user's ID
	 * 
	 * @return int $userID
	 */
	public function userID() {
		return $this->userID;
	}

	/**
	 * Get this user's name
	 * 
	 * @return string $name
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * Set this user's name
	 * 
	 * @param string $name
	 * @global mysqli $_mysql
	 * @return mixed
	 */
	public function setName($name) {
		// <editor-fold defaultstate="collapsed" desc="Set Name Code">
		global $_mysql;
		
		$name = $_mysql->real_escape_string($name);

		if ($name != $this->name) {
			if (preg_match("/^[a-z\d_]{2,20}$/i", $_POST['userNameField'])) {
				$this->name = $name;

				$userNameQuery = $_mysql->query("SELECT `id` FROM `users` WHERE `name` = '" . $name . "'");

				if (!$userNameQuery->num_rows) { // If this user name isn't in use
					$_mysql->query("UPDATE `users` SET `name` = '" . $name . "' WHERE `id` = " . $this->userID . "");
					return true;
				} else { // If this user name is in use, return false
					return "User name in use.";
				}
			} else {
				return "Invalid user name.";
			}
		} else {
			return true;
		}
		// </editor-fold>
	}

	/**
	 * Return the user's rank ID
	 * 
	 * @return int $rankID
	 */
	public function rankID() {
		return $this->rank;
	}

	/**
	 * Set this user's rank
	 * 
	 * @param int $rank
	 * @global mysqli $_mysql
	 */
	public function setRank($rank) {
		// <editor-fold defaultstate="collapsed" desc="Set Rank Code">
		global $_mysql;
		
		$rank = $_mysql->real_escape_string($rank);

		if ($rank != $this->rank) {
			$this->rank = $rank;

			$rank = new Rank($this->rank);

			$this->rankName = $rank->name();
			$this->rankPermissions = $rank->permissions();

			unset($rank);

			$_mysql->query("UPDATE `users` SET `rank` = " . $this->rank . " WHERE `id` = " . $this->userID . "");
		}
		// </editor-fold>
	}

	/**
	 * Returns this user's most recent IP
	 * 
	 * @return string $ip
	 */
	public function ip() {
		return $this->ip;
	}

	/**
	 * Returns the IP with which this user registered
	 * 
	 * @return string $ip
	 */
	public function registerIP() {
		return $this->registerIP;
	}

	/**
	 * Returns the type of account this user has
	 * 
	 * @return string $type
	 */
	public function type() {
		return $this->type;
	}

	/**
	 * Returns the email for this user
	 * 
	 * @return string $email
	 */
	public function email() {
		return $this->email;
	}

	/**
	 * Set this user's email
	 * 
	 * @param string $email
	 * @global mysqli $_mysql
	 * @return mixed
	 */
	public function setEmail($email) {
		// <editor-fold defaultstate="collapsed" desc="Set Email Code">
		global $_mysql;
		
		$email = $_mysql->real_escape_string($email);

		if ($email != $this->email) { // If the email is different from the existing email
			if (Security::isEmailValid($email)) { // If the email address is valid
				$this->email = $email;

				$_mysql->query("UPDATE `users` SET `email` = '" . $email . "' WHERE `id` = " . $this->userID);
				return true;
			} else { // If the email address is not valid
				return "Invalid email address.";
			}
		} else { // If the email is the same as the existing email
			return true;
		}
		// </editor-fold>
	}

	/**
	 * Returns the date of birth for this user
	 * 
	 * @param string $format
	 * @return string $dob
	 */
	public function dob($format = false) {
		if (!$format) { // If we're not using a format, return the raw date of birth
			return $this->dob;
		} else { // If we are using a format
			return date($format, $this->dob);
		}
	}

	/**
	 * Set this user's date of birth
	 * 
	 * @param int $dob
	 * @global mysqli $_mysql
	 * @return mixed
	 */
	public function setDOB($dob) {
		// <editor-fold defaultstate="collapsed" desc="Set DOB Code">
		global $_mysql;
		
		$dob = $_mysql->real_escape_string($dob);

		if ($dob != $this->dob) {
			if ($dob < time()) { // If the DOB isn't in the future
				$this->dob = $dob;
				$_mysql->query("UPDATE `users` SET `dob` = '" . $dob . "' WHERE `id` = " . $this->userID . "");
				return true;
			} else { // If the DOB is in the future
				return "Invalid date of birth.";
			}
		}
		// </editor-fold>
	}

	/**
	 * Returns the country for this user
	 * 
	 * @param boolean $code
	 * @global array $_countries
	 * @return string $country
	 */
	public function country($code = false) {
		// <editor-fold defaultstate="collapsed" desc="Country">
		global $_countries;
		
		if ($code) { // If we're returning the code
			return $this->country;
		} else { // If we're returning the country
			return $_countries[$this->country];
		}
		// </editor-fold>
	}

	/**
	 * Set this user's country
	 * 
	 * @param string $country
	 * @global mysqli $_mysql
	 * @global array $_countries
	 * @return mixed
	 */
	public function setCountry($country) {
		// <editor-fold defaultstate="collapsed" desc="Set Country Code">
		global $_mysql, $_countries;
		
		$country = $_mysql->real_escape_string($country);

		if ($country != $this->country) { // If the country is different from the existing country
			if (!array_key_exists($country, $_countries)) { // If the country is invalid
				return "Invalid country.";
			} else { // If the country is valid
				$this->country = $country;

				$_mysql->query("UPDATE `users` SET `country` = '" . $country . "' WHERE `id` = " . $this->userID . "");
				return true;
			}
		} else { // If the country is the same as the existing country
			return true;
		}
		// </editor-fold>
	}

	/**
	 * Set this user's password
	 * 
	 * @param string $password
	 * @global mysqli $_mysql
	 */
	public function setPassword($password) {
		global $_mysql;
		
		$password = $_mysql->real_escape_string(Security::sha2($this->salt . $password));
		$_mysql->query("UPDATE `users` SET `password` = '" . $password . "' WHERE `id` = " . $this->userID . "");
	}

	/**
	 * Return the time this user registered
	 * 
	 * @param string $mode = "r"
	 * @param string $format = null
	 * @return mixed $registered
	 */
	public function registered($mode = "r", $format = null) {
		// <editor-fold defaultstate="collapsed" desc="Registered Code">
		if ($mode == "r") { // Raw mode
			return $this->registered;
		} else if ($mode == "d") {
			return date($format, $this->registered);
		}
		// </editor-fold>
	}

	/**
	 * Return the time this user last logged in
	 * 
	 * @param string $mode = "r"
	 * @param string $format = null
	 * @return mixed $lastLogin
	 */
	public function lastLogin($mode = "r", $format = null) {
		// <editor-fold defaultstate="collapsed" desc="Last Login Code">
		if ($mode == "r") { // Raw mode
			return $this->lastLogin;
		} else if ($mode == "d") {
			return date($format, $this->lastLogin);
		}
		// </editor-fold>
	}

	/**
	 * Return the HTML markup to output this user's avatar
	 * 
	 * @return string $avatarHTML
	 */
	public function avatar($size = "m") {
		// <editor-fold defaultstate="collapsed" desc="Avatar Code">
		global $_site;

		if ($size == "s") {
			$height = 70;
			$width = 70;
		} else if ($size == "m") {
			$height = 150;
			$width = 150;
		} else if ($size == "l") {
			$height = 200;
			$width = 200;
		}

		return "<img src=\"./uploads/avatars/" . $this->avatar . "\" alt=\"" . $this->name . " Avatar\" height=\"" . $height . "\" width=\"" . $width . "\" style=\"border: 1px solid black; " . (($_site->setting("roundAvatars") ? "border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px;" : "")) . "\" />";
		// </editor-fold>
	}

	/**
	 * Check a specific permission
	 * 
	 * @param string $permission The permission to check for
	 * @return boolean
	 */
	public function checkPermission($permission, $type = false) {
		// <editor-fold defaultstate="collapsed" desc="Check Permission Code">
		// First check for user permissions, then check rank permissions
		$permission = str_replace(" ", "_", $permission); // Replace any spaces with under scores
		// Check in the user's permissions
		if (array_key_exists($permission, $this->permissions)) {
			// Only return the permission if it isn't false; this enables the check to also look at the rank permissions before returning
			if ($this->permissions[$permission]) {
				return $this->permissions[$permission];
			}
		}

		if (!$type) { // If a type is not specified go ahead and also check rank permissions
			// Check for rank's permissions
			if (array_key_exists($permission, $this->rankPermissions)) {
				return $this->rankPermissions[$permission];
			}
		}

		return false;
		// </editor-fold>
	}

	/**
	 * Log in the user
	 * 
	 * @param int $user
	 * @global mysqli $_mysql
	 */
	public function logIn($user) {
		// <editor-fold defaultstate="collapsed" desc="Login Code">
		global $_mysql;
		
		$userAgent = $_mysql->real_escape_string($_SERVER['HTTP_USER_AGENT']);
		$hash = Security::sha2("o'canadaourhomeandnativeland" . session_id() . $userAgent);
		$expires = time() + (15 * 60); // Set the expiry time to 15 minutes in the future
		$ip = $_mysql->real_escape_string($_SERVER['REMOTE_ADDR']);

		$_mysql->query("INSERT INTO `active_users` (`user`, `session_id`, `hash`, `expires`) VALUES (" . $user . ", '" . session_id() . "', '" . $hash . "', " . $expires . ")");

		$_mysql->query("UPDATE `users` SET `last_ip` = '" . $ip . "', `last_login` = " . time() . " WHERE `id` = " . $user . ""); // Set the most recent IP
		// Change variables
		$userQuery = $_mysql->query("SELECT * FROM `users` WHERE `id` = " . $user);
		$userData = $userQuery->fetch_assoc();

		$this->userID = $user;
		$this->name = $userData['name'];

		if (isset($userData['permissions']) && !empty($userData['permissions']) && $userData['permissions'] != '') {
			$this->permissions = unserialize($userData['permissions']);
		} else {
			$this->permissions = array();
		}


		// Set rank information
		$this->rank = $userData['rank'];

		$rank = new Rank($this->rank);

		$this->rankName = $rank->name();
		$this->rankPermissions = $rank->permissions();
		unset($rank);

		$this->registerIP = $userData['register_ip'];
		$this->ip = $userData['last_ip'];
		$this->type = "member";
		$this->salt = $userData['salt'];
		$this->registered = $userData['registered'];
		$this->lastLogin = $userData['last_login'];

		if (!isset($userData['avatar']) || empty($userData['avatar'])) { // If this user hasn't set up an avatar, use the default
			$this->avatar = "101-default-avatar.jpg";
		}
		// </editor-fold>
	}
    /**
	 * Send password reset request.
	 * 
	 * @global mysqli $_mysql
	 */
	public function passwordReset($email) {
		// <editor-fold defaultstate="collapsed" desc="password reset stuff">
		global $_mysql;
		
		$ip = $_mysql->real_escape_string($_SERVER['REMOTE_ADDR']); // Get the user's IP
		$agent = $_mysql->real_escape_string($_SERVER['HTTP_USER_AGENT']); // Get the user's user agent (software and firmware data)
		$sesID = $_mysql->real_escape_string(session_id()); // Get the user's session id
		$salt = "AMERICAFUCKYEAH!"; // Set the salt
		$token = Security::sha2($salt . $agent . $sesID . time()); // Create the token
		$query = $_mysql->query("INSERT INTO `resetpass_tokens` (`email`, `ip`,  `token`) VALUES ('" . $email . "', '" . $ip . "', '" . $token . "')");
		$sendEmailQuery = $_mysql->query("SELECT contact_email FROM `site_info` WHERE `id` = 1");
		$sendEmail = $sendEmailQuery->fetch_assoc();
		$emailTitleQuery = $_mysql->query("SELECT title FROM `site_info` WHERE `id` = 1");
		$emailTitle = $emailTitleQuery->fetch_assoc();
		$siteURLQuery = $_mysql->query("SELECT url FROM `site_info` WHERE `id` = 1");
		$siteURL = $siteURLQuery->fetch_assoc();
		require(__DIR__ . "/../lib/phpmailer/class.phpmailer.php");
		$mail = new PHPMailer();
		$mail->From = $sendEmail['contact_email'];
		$mail->FromName = $emailTitle['title'];
		$mail->AddAddress($email);
		$mail->IsHTML(true);
		$mail->Subject = "Reset Password Request - '" . $emailTitle['title'] . "'";
		$mail->Body = "A password change request has been made for the account registered with this email. Please use the link below to change your password.<br><br>
                        <a href=''" . $siteURL['url'] . "'/?p=resetpass&email='" . $email . "'&token='" . $token . "'>Click Here!</a><br><br>
                                   or if that link doesn't work, go here:<br><br>
                                   " . $siteURL['url'] . "/?p=resetpass&email='" . $email . "'&token='" . $token . "'";
		$mail->WordWrap = 50;
		$mail->Send();

		// </editor-fold>
	}

	/**
	 * Re Password Reset
	 * 
	 * @param string $email
	 * @global mysqli $_mysql
	 */
	public function rePasswordReset($email) {
		// <editor-fold defaultstate="collapsed" desc="password reset stuff2">
		global $_mysql;
		
		$tokenQuery = $_mysql->query("SELECT token FROM resetpass_tokens WHERE email = '" . $email . "'");
		$token = $tokenQuery->fetch_assoc();
		$sendEmailQuery = $_mysql->query("SELECT contact_email FROM `site_info` WHERE `id` = 1");
		$sendEmail = $sendEmailQuery->fetch_assoc();
		$emailTitleQuery = $_mysql->query("SELECT title FROM `site_info` WHERE `id` = 1");
		$emailTitle = $emailTitleQuery->fetch_assoc();
		$siteURLQuery = $_mysql->query("SELECT url FROM `site_info` WHERE `id` = 1");
		$siteURL = $siteURLQuery->fetch_assoc();
		require(__DIR__ . "/../lib/phpmailer/class.phpmailer.php");
		$mail = new PHPMailer();
		$mail->From = $sendEmail['contact_email'];
		$mail->FromName = $emailTitle['title'];
		$mail->AddAddress($email);
		$mail->IsHTML(true);
		$mail->Subject = "Reset Password Request - '" . $emailTitle['title'] . "'";
		$mail->Body = "A password change request has been made for the account registered with this email. Please use the link below to change your password.<br><br>
                        <a href=''" . $siteURL['url'] . "'/?p=resetpass&email='" . $email . "'&token='" . $token['token'] . "'>Click Here!</a><br><br>
                                   or if that link doesn't work, go here:<br><br>
                                   " . $siteURL['url'] . "/?p=resetpass&email='" . $email . "'&token='" . $token['token'] . "'";
		$mail->WordWrap = 50;
		$mail->Send();

		// </editor-fold>
	}
	/**
	 * Return this user's permissions
	 * 
	 * @return array $permissions
	 */
	public function permissions() {
		return $this->permissions;
	}

	/**
	 * Save this user's permissions
	 * 
	 * @param array $permissions
	 * @global mysqli $_mysql
	 */
	public function savePermissions($permissions) {
		// <editor-fold defaultstate="collapsed" desc="Save Permissions Code">
		global $_mysql;
		
		$this->permissions = $permissions;
		$permissions = serialize($permissions);

		$_mysql->query("UPDATE `users` SET `permissions` = '" . $permissions . "' WHERE `id` = " . $this->userID);
		// </editor-fold>
	}

	/**
	 * Log out the user
	 * 
	 * @global mysqli $_mysql
	 */
	public function logout() {
		// <editor-fold defaultstate="collapsed" desc="Logout Code">
		global $_mysql;
		
		if ($this->type == "guest") { // If this user is a guest, we can't log them out
			return false;
		}

		$query = $_mysql->query("DELETE FROM `active_users` WHERE `user` = " . $this->userID);

		if (!$query || !$_mysql->affected_rows) { // If either the query failed or no rows were affected
			return false;
		} else {
			return true;
		}
		// </editor-fold>
	}

	/**
	 * Unban the user
	 * Remove all bans associated with this user
	 * 
	 * @global mysqli $_mysql
	 * @return boolean $successful If the unban succeeded or not
	 */
	public function unban() {
		// <editor-fold defaultstate="collapsed" desc="Unban Code">
		global $_mysql;
		
		$_mysql->query("UPDATE `bans` SET `enabled` = 0 WHERE `user` = " . $this->userID); // Disable all of this user's bans

		if ($_mysql->affected_rows) { // If at least one ban was deleted
			return true;
		} else { // If no bans were deleted
			return false;
		}
		// </editor-fold>
	}

	/**
	 * Ban the user
	 * 
	 * @param string $type Ban type
	 * @param int $expires Ban expiry date
	 * @global mysqli $_mysql
	 */
	public function ban($type, $superBan, $expires) {
		// <editor-fold defaultstate="collapsed" desc="Ban Code">
		global $_mysql;
		
		if ($superBan) {
			$superBan = 1;
		} else {
			$superBan = 0;
		}

		$expires = $_mysql->real_escape_string($expires);

		if ($type == "temp") {
			$_mysql->query("INSERT INTO `bans` (`user`, `type`, `super_ban`, `expires`, `stamp`, `enabled`) 
			    VALUES (
			    " . $this->userID . ", 
			    '" . $type . "', 
			    " . $superBan . ", 
			    " . $expires . ", 
			    " . time() . ", 
			    1
			    )");
		} else if ($type == "perm") {
			$_mysql->query("INSERT INTO `bans` (`user`, `type`, `super_ban`, `expires`, `stamp`, `enabled`) 
			    VALUES (
			    " . $this->userID . ", 
			    '" . $type . "', 
			    " . $superBan . ", 
			    0, 
			    " . time() . ", 
			    1
			    )");
		}
		// </editor-fold>
	}

	/**
	 * Return if the user is banned or not
	 * 
	 * @global mysqli $_mysql
	 * @return boolean $isBanned Whether this user is banned or not
	 */
	public function isBanned() {
		// <editor-fold defaultstate="collapsed" desc="Is Banned Code">
		global $_mysql; 
		
		// First query the database for permanent bans
		$query = $_mysql->query("SELECT `id` FROM `bans` WHERE `enabled` = 1 AND `type` = 'perm' AND `user` = " . $this->userID);

		if ($query->num_rows) { // If there's at leat one permanent ban for this user
			return true;
		} else {
			$query = $_mysql->query("SELECT `expires` FROM `bans` WHERE `enabled` = 1 AND `type` = 'temp' AND `user` = " . $this->userID);

			if ($query->num_rows) {
				while ($ban = $query->fetch_assoc()) {
					if ((int) $ban['expires'] > time()) { // If the ban expiry time is greater than the current time, the user is banned
						return true;
					}
				}

				return false; // Return false if all of the temporary bans have expired
			} else { // If there were no temporary bans either, then the user isn't banned
				return false;
			}
		}
		// </editor-fold>
	}

	/**
	 * Log an admin action by this user
	 * 
	 * @param string $action
	 * @param string $system
	 * @global mysqli $_mysql
	 */
	public function logAction($action, $system = false) {
		// <editor-fold defaultstate="collapsed" desc="Log Action Code">
		global $_mysql;
		
		if (!$system) { // If the system variable isn't set, set it to "Generic"
			$system = "Generic";
		}

		$_mysql->query("INSERT INTO `admin_log` (`admin`, `system`, `action`, `stamp`) VALUES (" . $this->userID . ", '" . $_mysql->real_escape_string($system) . "', '" . $_mysql->real_escape_string($action) . "', " . time() . ")");
		// </editor-fold>
	}
	
	/**
	 * Redirect the user
	 * 
	 * @param string $URL
	 * @param mixed $msgType
	 * @param string $msgText
	 */
	public static function redirect($URL, $msgType = false, $msgText = "") {
		// <editor-fold defaultstate="collapsed" desc="Redirect">
		if ($msgType) { // If we're also displaying a message
			$URL .= "&" . $msgType . "msg=" . urlencode($msgText); // Add the message to the URL
		}
		
		// Redirect the user
		header("Location: " . $URL);
		exit;
		// </editor-fold>
	}
}
?>