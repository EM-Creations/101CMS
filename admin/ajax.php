<?php
/*
 * Admin Area AJAX handling page
 */

require(__DIR__ . "/../cms/global.inc.php"); // Require the global file

if (isset($_GET['req']) || isset($_POST['req'])) { // All AJAX requests must provide $_GET['req']
	// Deleting of meta tags
	if (isset($_GET['req']) && $_GET['req'] == "deleteMetaTag") {
		// <editor-fold defaultstate="collapsed" desc="Delete Meta Tag Code">
		$json = array("status" => "failed", "errormsg" => false, "type" => "deleteMetaTag");

		if ($_currUser->type() == "member") { // If they're not logged in
			if (!isset($_GET['tagID'])) {
				$json['status'] = "error";
				$json['errormsg'] = "No meta tag id provided";
			} else if (!$_currUser->checkPermission("metaTags")) { // If this user does not have permission to edit meta tags, redirect them
				$json['status'] = "error";
				$json['errormsg'] = "Access denied";
			} else {
				$tag = $_mysql->real_escape_string($_GET['tagID']);

				$_mysql->query("DELETE FROM `meta_tags` WHERE `id` = '" . $tag . "'");

				if ($_mysql->affected_rows) { // If at least one row was affected
					$json['status'] = "success";
				} else {
					$json['status'] = "failed";
				}
			}

			print(json_encode($json));
		}
		// </editor-fold>
	} else if (isset($_GET['req']) && $_GET['req'] == "deleteRank") {
		// <editor-fold defaultstate="collapsed" desc="Delete Rank Code">
		$json = array("status" => "failed", "errormsg" => false, "type" => "deleteRank");

		if ($_currUser->type() == "member") { // If they're not logged in
			if (!isset($_GET['rankID'])) {
				$json['status'] = "error";
				$json['errormsg'] = "No rank id provided";
			} else if (!$_currUser->checkPermission("ranks")) { // If this user does not have permission to edit meta tags, redirect them
				$json['status'] = "error";
				$json['errormsg'] = "Access denied";
			} else {
				$rank = $_mysql->real_escape_string($_GET['rankID']);

				$_mysql->query("DELETE FROM `ranks` WHERE `id` = '" . $rank . "'");

				if ($_mysql->affected_rows) { // If at least one row was affected
					$json['status'] = "success";
				} else {
					$json['status'] = "failed";
				}
			}

			print(json_encode($json));
		}
		// </editor-fold>
	} else if (isset($_POST['req']) && $_POST['req'] == "unban") {
		// <editor-fold defaultstate="collapsed" desc="Unban Code">
		$json = array("status" => "failed", "errormsg" => false, "type" => "unban");

		if (isset($_POST['id']) && !empty($_POST['id'])) {

			if ($_currUser->checkPermission("unban")) { // Check that the user has the unban permission
				if (is_numeric($_POST['id'])) {
					$banID = $_mysql->real_escape_string($_POST['id']);

					$query = $_mysql->query("SELECT `user` FROM `bans` WHERE `id` = " . $banID);

					if ($_mysql->num_rows($query)) {
						$data = $query->fetch_assoc();

						$user = new User($_mysql->real_escape_string($data['user']));

						if ($user->unban()) { // Unban this user, removes all bans associated with it
							$json['status'] = "success";
						} else {
							$json['status'] = "failed";
						}
					} else {
						$json['status'] = "error";
						$json['errormsg'] = "Ban does not exist";
					}
				} else {
					$json['status'] = "error";
					$json['errormsg'] = "Ban id not a number";
				}
			} else {
				$json['status'] = "error";
				$json['errormsg'] = "Access denied";
			}
		} else {
			$json['status'] = "error";
			$json['errormsg'] = "No ban id provided";
		}

		print(json_encode($json));
		// </editor-fold>
	} else if (isset($_GET['req']) && $_GET['req'] == "deleteRoster") {
		// <editor-fold defaultstate="collapsed" desc="Delete Roster Code">
		$json = array("status" => "failed", "errormsg" => false, "type" => "deleteRoster");

		if (!$_currUser->checkPermission("rosters")) { // If this user doesn't have the roster permission
			$json['status'] = "error";
			$json['errormsg'] = "Access denied";
		} else if (!isset($_GET['rosterID'])) {
			$json['status'] = "error";
			$json['errormsg'] = "No roster id provided";
		} else if (empty($_GET['rosterID'])) {
			$json['status'] = "error";
			$json['errormsg'] = "No roster id provided";
		} else if (!is_numeric($_GET['rosterID'])) {
			$json['status'] = "error";
			$json['errormsg'] = "Invalid roster id";
		} else {
			$rosterID = $_mysql->real_escape_string($_GET['rosterID']);
			$_mysql->query("DELETE FROM `rosters` WHERE `id` = " . $rosterID); // Delete the roster
			$_mysql->query("DELETE FROM `roster_members` WHERE `roster` = " . $rosterID); // Delete all of this roster's members

			$json['status'] = "success";
			$json['errormsg'] = false;
		}

		print(json_encode($json)); // Output JSON
		// </editor-fold>
	} else if (isset($_GET['req']) && $_GET['req'] == "removeRosterMember") {
		// <editor-fold defaultstate="collapsed" desc="Remove Roster Member Code">
		$json = array("status" => "failed", "errormsg" => false, "type" => "removeRosterMember");

		if (!$_currUser->checkPermission("rosters")) { // If the user doesn't have the rosters permission
			$json['status'] = "error";
			$json['errormsg'] = "Access denied";
		} else if (!isset($_GET['memberID'])) {
			$json['status'] = "error";
			$json['errormsg'] = "No member id provided";
		} else if (empty($_GET['memberID'])) {
			$json['status'] = "error";
			$json['errormsg'] = "No member id provided";
		} else if (!is_numeric($_GET['memberID'])) {
			$json['status'] = "error";
			$json['errormsg'] = "Member id not a number";
		} else {
			$memberID = $_mysql->real_escape_string($_GET['memberID']);

			$_mysql->query("DELETE FROM `roster_members` WHERE `id` = " . (int) $memberID);

			$json['status'] = "success";
			$json['errormsg'] = false;
		}

		print(json_encode($json)); // Output JSON
		// </editor-fold>
	} else if (isset($_POST['req']) && $_POST['req'] == "deletePage") {
		// <editor-fold defaultstate="collapsed" desc="Delete CMS Page Code">
		$json = array("status" => "failed", "errormsg" => false, "type" => "deletePage");

		if (!$_currUser->checkPermission("managePages")) { // If the user doesn't have the manage pages permission
			$json['status'] = "error";
			$json['errormsg'] = "Access denied";
		} else if (!isset($_POST['id'])) { // If a page ID hasn't been set
			$json['status'] = "error";
			$json['errormsg'] = "No page id provided";
		} else { // If there were no errors
			$pageID = $_mysql->real_escape_string($_POST['id']);

			$_mysql->query("DELETE FROM `pages` WHERE `id` = " . (int) $pageID);

			$json['status'] = "success";
			$json['errormsg'] = false;
		}

		print(json_encode($json)); // Output JSON
		// </editor-fold>
	} else if (isset($_GET['req']) && $_GET['req'] == "deletePoll") {
		// <editor-fold defaultstate="collapsed" desc="Delete Poll Code">
		$json = array("status" => "failed", "errormsg" => false, "type" => "deletePoll");

		if (!$_currUser->checkPermission("polls")) { // If the user doesn't have the polls permission
			$json['status'] = "error";
			$json['errormsg'] = "Access denied";
		} else if (!isset($_GET['id'])) { // If a poll ID hasn't been set
			$json['status'] = "error";
			$json['errormsg'] = "No poll id provided";
		} else { // If there were no errors
			$pollID = $_mysql->real_escape_string($_GET['id']);

			$_mysql->query("DELETE FROM `polls` WHERE `id` = " . (int) $pollID);

			$json['status'] = "success";
			$json['errormsg'] = false;
		}

		print(json_encode($json)); // Output JSON
		// </editor-fold>
	}
}
?>