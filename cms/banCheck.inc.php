<?php
// Get this user's IP address
$ip = $_SERVER['REMOTE_ADDR'];

// Select all enabled permanent super bans
$query = $_mysql->query("SELECT `user` FROM `bans` WHERE `enabled` = 1 AND `super_ban` = 1 AND `type` = 'perm'");

if ($query->num_rows) { // If there's at least one permanent super ban
	while ($ban = $query->fetch_assoc()) {
		$user = new User($ban['user']);

		if ($ip == $user->registerIP() || $ip == $user->ip()) { // If this user's IP is the same as a super banned user's register IP or last IP address
			die("Access denied");
		}
	}
}

// Select all enabled temporary super bans
$query = $_mysql->query("SELECT `user`, `expires` FROM `bans` WHERE `enabled` = 1 AND `super_ban` = 1 AND `type` = 'temp'");

if ($query->num_rows) { // If there's at least one temporary super ban
	while ($ban = $query->fetch_assoc()) {
		if ($ban['expires'] > time()) { // If the expiry date is in the future
			$user = new User($ban['user']);

			if ($ip == $user->registerIP() || $ip == $user->ip()) { // If this user's IP is the same as a super banned user's registration IP or last IP address
				die("Access denied");
			}
		}
	}
}
?>
