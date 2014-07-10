<?php
if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

if (!$_currUser->checkPermission("polls")) { // If the doesn't have the poll permission
	header("Location: ./");
	exit;
}

if (isset($_GET['poll'])) {
	// <editor-fold defaultstate="collapsed" desc="Poll Edit Code">
	$pollID = $_mysql->real_escape_string($_GET['poll']);
	
	$poll = new Poll($pollID);
	
	if ($poll->type() == "existing") { // If this poll exists

		if (isset($_POST['pollUpdateButton'])) { // If the update button has been clicked
			if (isset($_POST['pollName']) && $_POST['pollName'] != $poll->name()) { // If the poll's name has changed
				$poll->setName($_POST['pollName']);
			}
			
			if (isset($_POST['pollLockType'])) {
				if ($_POST['pollLockType'] != $poll->lockType()) { // If the poll's lock type has changed
					$poll->setLockType($_POST['pollLockType']);
				}
			}
			
			if ($poll->lockType() == "rank" && isset($_POST['pollLockTypeRank']) && $poll->lock() != $_POST['pollLockTypeRank']) { // If this poll's lock has been changed
				$poll->setLock($_POST['pollLockTypeRank']);
			}
			
			if ($poll->lockType() == "permission" && isset($_POST['pollLockTypePermission']) && $poll->lock() != $_POST['pollLockTypePermission']) { // If this poll's lock has been changed
				$poll->setLock($_POST['pollLockTypePermission']);
			}
			
			if (isset($_POST['pollAnswers'])) {
				if ($poll->answers("l", null, true) != $_POST['pollAnswers']) { // If this poll's answers have changed
					$answers = explode("\n", $_POST['pollAnswers']);
					
					foreach ($answers as $key=>$answer) { // For each answer, strip tags
						$answer = strip_tags($answer);
						$answer = str_replace("\n", "", $answer);
						$answers[$key] = $answer;
					}
					
					$poll->setAnswers($answers);
				}
			}
			
			if (isset($_POST['pollMultipleAnswers'])) {
				if (!$poll->multipleAnswers()) { // If this poll's multiple answers boolean has changed
					$poll->setMultipleAnswers(true);
				}
			} else {
				if ($poll->multipleAnswers()) { // If this poll's multiple answers boolean has changed
					$poll->setMultipleAnswers(false);
				}
			}
			
			if (isset($_POST['pollExpiryType'])) {
				if ($_POST['pollExpiryType'] == "never" && $poll->expires() != "Never") { // The poll expiry type has changed
					$poll->setExpires(0);
				} else if ($_POST['pollExpiryType'] == "date") {
					if (isset($_POST['pollExpiry'])) {
						// Update the poll's expiry to what's been input
						$_POST['pollExpiry'] = str_replace("/", "-", $_POST['pollExpiry']);
						$expires = strtotime($_POST['pollExpiry']);
						$poll->setExpires($expires);
					}
				}
			}
			
			if (isset($_POST['pollEnabled'])) {
				if (!$poll->enabled()) { // If this poll's enabled boolean has changed
					$poll->setEnabled(true);
				}
			} else {
				if ($poll->enabled()) { // If this poll's enabled boolean has changed
					$poll->setEnabled(false);
				}
			}
		}
		
		// <editor-fold defaultstate="collapsed" desc="Poll Update Form Display Code">
		print("<h2>Editing Poll: " . $poll->name() . "</h2>");
		
		?>
		<form id="newPollForm" method="post">
			<table>
				<tr><td>Name</td><td><input type="text" id="pollName" name="pollName" value="<?php print($poll->name()); ?>" /></td></tr>
				<tr>
					<td>Lock type:</td><td><select name="pollLockType" id="pollLockType">	
							<option value="none" <?php if ($poll->lockType() == "none") print("selected=\"selected\""); ?>>None</option>
							<option value="rank" <?php if ($poll->lockType() == "rank") print("selected=\"selected\""); ?>>Minimum Rank Level</option>
							<option value="permission" <?php if ($poll->lockType() == "permission") print("selected=\"selected\""); ?>>Permission</option>
						</select>
					</td>
				</tr>

				<tr id="pollLockTypeRankOpts">
					<td>Rank:</td><td><select name="pollLockTypeRank" id="pollLockTypeRank">
		<?php
		// Get the ranks out of the database and put them into this select box ordered by the highest level descending
		$ranksQuery = $_mysql->query("SELECT `id` FROM `ranks` ORDER BY `level` DESC");

		while ($rankData = $ranksQuery->fetch_assoc()) {
			$thisRank = new Rank($rankData['id']);
			print("<option value=\"" . $thisRank->rankID() . "\" " . (($poll->lockType() == "rank" && $poll->lock() == $thisRank->rankID()) ? "selected=\"selected\"" : "") . ">" . $thisRank->name() . "</option>\n");
		}
		?>
							</select>
						</td>
					</tr>

					<tr id="pollLockTypePermissionOpts">
						<td>Permission: </td><td><input type="text" name="pollLockTypePermission" id="pollLockTypePermission" value="<?php if ($poll->lockType() == "permission") print($poll->lock()); ?>" /></td>
					</tr>
					<tr>
						<td>Answers</td>
						<td><textarea id="pollAnswers" name="pollAnswers" rows="5"><?php print($poll->answers("l")); ?></textarea></td>
						<td>Separate each answer with a new line</td>
					</tr>
					<tr>
						<td>Allow Multiple Answers?</td>
						<td><input type="checkbox" name="pollMultipleAnswers" id="pollMultipleAnswers" <?php if ($poll->multipleAnswers()) print("checked=\"checked\""); ?> /></td>
					</tr>
					<tr><td>Expiry</td><td><select id="pollExpiryType" name="pollExpiryType">
								<option value="never" <?php if ($poll->expires() == "Never") print("selected=\"selected\""); ?>>Never</option>
								<option value="date" <?php if ($poll->expires() != "Never") print("selected=\"selected\""); ?>>Date</option>
							</select></td></tr>
			<?php // TODO: Attach JQuery-UI calendar to this element  ?>
					<tr id="pollExpiryOpts"><td></td><td><input type="text" id="pollExpiry" name="pollExpiry" value="<?php if ($poll->expires() != "Never") print($poll->expires("d")); ?>" /></td></tr>
					<tr><td>Enabled?</td><td><input type="checkbox" id="pollEnabled" name="pollEnabled" <?php if ($poll->enabled()) print("checked=\"checked\""); ?> /></td></tr>
					<tr><td></td><td><button type="submit" id="pollUpdateButton" name="pollUpdateButton">Update Poll</button></td></tr>
				</table>
			</form>
		<?php
		// </editor-fold>
		} else { // If this poll does not exist
			// TODO: Nicer error message when trying to edit a non-existant poll
			die("<strong>Error:</strong> Poll does not exist");
		}
	// </editor-fold>
} else {
	// <editor-fold defaultstate="collapsed" desc="Poll List Code">
	if (isset($_POST['pollSubmitButton'])) { // If the new poll form has been submitted
		// <editor-fold defaultstate="collapsed" desc="New Poll Handling Code">
		$errors = array();

		if (isset($_POST['pollEnabled'])) { // If the poll enable checkbox has been checked
			$enabled = 1;
		} else {
			$enabled = 0;
		}
		
		if (isset($_POST['pollMultipleAnswers'])) { // If the poll multiple answers checkbox has been checked
			$multipleAnswers = 1;
		} else {
			$multipleAnswers = 0;
		}

		if (!isset($_POST['pollName'])) { // If the poll name is not set
			$errors['name'][] = "Not set";
		} else {
			if (empty($_POST['pollName'])) { // If the poll name is empty
				$errors['name'][] = "Empty";
			} else {
				$name = strip_tags($_POST['pollName']);
			}
		}
		
		if (!isset($_POST['pollLockType'])) { // If the poll lock type is not set
			$errors['lockType'][] = "Not set";
		} else {
			if (empty($_POST['pollLockType'])) {
				$errors['lockType'][] = "Empty";
			} else {
				if (!in_array($_POST['pollLockType'], array("none", "rank", "permission"))) { // If hte poll lock type is invalid
					$errors['lockType'][] = "Invalid";
				} else {
					$lockType = $_POST['pollLockType']; // If we've got this far then the poll lock type doesn't need to be escaped, as it's valid input already
					
					if ($lockType == "none") { // If this poll isn't going to have a lock, set the lock to null
						$lock = "null";
					} else { // If this poll is going to have a lock, check the lock POST variable
						if ($lockType == "rank") {
							if (!isset($_POST['pollLockTypeRank'])) {
								$errors['lock'][] = "Not set";
							} else {
								if (empty($_POST['pollLockTypeRank'])) {
									$errors['lock'][] = "Empty";
								} else {
									$lock = $_POST['pollLockTypeRank'];
								}
							}
						} else if ($lockType == "permission") {
							if (!isset($_POST['pollLockTypePermission'])) {
								$errors['lock'][] = "Not set";
							} else {
								if (empty($_POST['pollLockTypePermission'])) {
									$errors['lock'][] = "Empty";
								} else {
									$lock = $_POST['pollLockTypePermission'];
								}
							}
						}
					}
				}
			}
		}
		
		if (!isset($_POST['pollAnswers'])) {
			$errors['answers'][] = "Not set";
		} else {
			if (empty($_POST['pollAnswers'])) {
				$errors['answers'][] = "Empty";
			} else { // If the answers are set and contain data
				$answers = strip_tags($_POST['pollAnswers']); // Strip tags out of the answers
				$answers = explode("\n", $_POST['pollAnswers']); // Explode the answers by a new line character "\n"
				
				$answers = serialize($answers); // Escape and serialise the answers
			}
		}
		
		if (!isset($_POST['pollExpiryType'])) {
			$errors['expiryType'][] = "Not set";
		} else {
			if (empty($_POST['pollExpiryType'])) {
				$errors['expiryType'][] = "Empty";
			} else {
				if ($_POST['pollExpiryType'] == "never") {
					$expiry = 0;
				} else if ($_POST['pollExpiryType'] == "date") {
					if (!isset($_POST['pollExpiry'])) {
						$errors['expiry'][] = "Not set";
					} else {
						if (empty($_POST['pollExpiry'])) {
							$errors['expiry'][] = "Empty";
						} else {
							$expiry = strtotime($_POST['pollExpiry']);
						}
					}
				} else {
					$errors['expiryType'][] = "Invalid";
				}
			}
		}

		if (count($errors) > 0) { // If there was at least one error
			die("There were errors." . print_r($errors, true));
		} else { // If there were no errors
			$poll = new Poll(); // Create a new poll object
			$poll->create($name, $lockType, $lock, $expiry, $answers, $multipleAnswers, $enabled);
		}
		// </editor-fold>
	}

	print("<h1>Polls</h1>");

	$query = $_mysql->query("SELECT `id`, `name`, `lock_type`, `lock`, `expires`, `answers`, `enabled`  FROM `polls`");

	if ($query->num_rows) { // If there's at least one poll
		print("<table>\n");

		print("<th>Poll</th>\n");
		print("<th>Lock Type</th>\n");
		print("<th>Lock</th>\n");
		print("<th>Expires</th>\n");
		print("<th>Answers</th>\n");
		print("<th>Multiple Answers?</th>\n");
		print("<th>Options</th>\n");

		while ($poll = $query->fetch_assoc()) { // For each polls
			$thisPoll = new Poll($poll['id']); // Create a new poll object for this poll
			print("<tr id=\"poll_" . $thisPoll->id() . "\" style=\"" . (($thisPoll->enabled()) ? "" : "color: grey;") . "\">\n"); // If the poll is disabled set the colour to grey

			if ($thisPoll->lockType() == "rank") {
				$rank = new Rank((int) $thisPoll->lock());
				$lock = $rank->name();
			} else {
				$lock = $thisPoll->lock();
			}
			
			print("<td>" . $thisPoll->name() . "</td>\n");
			print("<td>" . $thisPoll->lockType() . "</td>\n");
			print("<td>" . $lock . "</td>\n");
			print("<td>" . $thisPoll->expires("d") . "</td>\n");

			print("<td>\n");

			$thisPoll->answers("l", "<br />"); // Print this poll's answers, separated by a new line tag

			print("</td>");
			
			print("<td>" . ($thisPoll->multipleAnswers() ? "Yes" : "No") . "</td>\n");

			print("<td><button id=\"edit_" . $thisPoll->id() . "\" class=\"editPollButton\">Edit</button> <button id=\"delete_" . $thisPoll->id() . "\" class=\"deletePollButton\">Delete</button></td>\n");

			print("</tr>\n");
		}

		print("</table>\n");
	} else { // If there's no polls
		print("There are currently no polls.");
	}

	// <editor-fold defaultstate="collapsed" desc="New Poll Form Code">
	?>
	<h2>New Poll</h2>
	<form id="newPollForm" method="post">
		<table>
			<tr><td>Name</td><td><input type="text" id="pollName" name="pollName" /></td></tr>
			<tr>
				<td>Lock type:</td><td><select name="pollLockType" id="pollLockType">	
						<option value="none">None</option>
						<option value="rank">Minimum Rank Level</option>
						<option value="permission">Permission</option>
					</select>
				</td>
			</tr>

			<tr id="pollLockTypeRankOpts">
				<td>Rank:</td><td><select name="pollLockTypeRank" id="pollLockTypeRank">
	<?php
	// Get the ranks out of the database and put them into this select box ordered by the highest level descending
	$ranksQuery = $_mysql->query("SELECT `id` FROM `ranks` ORDER BY `level` DESC");

	while ($rankData = $ranksQuery->fetch_assoc()) {
		$thisRank = new Rank($rankData['id']);
		print("<option value=\"" . $thisRank->rankID() . "\">" . $thisRank->name() . "</option>\n");
	}
	?>
					</select>
				</td>
			</tr>

			<tr id="pollLockTypePermissionOpts">
				<td>Permission: </td><td><input type="text" name="pollLockTypePermission" id="pollLockTypePermission" /></td>
			</tr>
			<tr>
				<td>Answers</td>
				<td><textarea id="pollAnswers" name="pollAnswers" rows="5"></textarea></td>
				<td>Separate each answer with a new line</td>
			</tr>
			<tr>
				<td>Allow Multiple Answers?</td>
				<td><input type="checkbox" name="pollMultipleAnswers" id="pollMultipleAnswers" /></td>
			</tr>
			<tr><td>Expiry</td><td><select id="pollExpiryType" name="pollExpiryType">
						<option value="never">Never</option>
						<option value="date">Date</option>
					</select></td></tr>
	<?php // TODO: Attach JQuery-UI calendar to this element  ?>
			<tr id="pollExpiryOpts"><td></td><td><input type="text" id="pollExpiry" name="pollExpiry" /></td></tr>
			<tr><td>Enabled?</td><td><input type="checkbox" id="pollEnabled" name="pollEnabled" checked="checked" /></td></tr>
			<tr><td></td><td><button type="submit" id="pollSubmitButton" name="pollSubmitButton">Create Poll</button></td></tr>
		</table>
	</form>
	<?php
	// </editor-fold>
	// </editor-fold>
}
?>