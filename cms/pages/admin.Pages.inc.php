<?php
if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

if (!$_currUser->checkPermission("managePages")) { // If this user doesn't have permission to manage pages
	header("Location: ./");
	exit;
}

$objectTypes = array("none", "poll", "roster", "news"); // Array to store object types

if (isset($_GET['a'])) { // If an action is specified
	if ($_GET['a'] == "newPage" || $_GET['a'] == "editPage") { // If the user is editing a CMS page or creating a new one
		if ($_GET['a'] == "editPage" && !isset($_GET['id'])) { // If the user wants to edit the page but no id for the page they want to edit is given
			die("<strong>Error:</strong> No page ID provided.");
		}

		if (isset($_POST['pageSaveButton'])) { // If the form has been submitted
			// <editor-fold defaultstate="collapsed" desc="Page Form Handling Code">
			$errors = array();

			if (!isset($_POST['pageName'])) {
				$errors['name'][] = "Not set";
			} else if (empty($_POST['pageName'])) {
				$errors['name'][] = "Empty page name";
			} else {
				$pageName = $_mysql->real_escape_string($_POST['pageName']);
			}

			if (!isset($_POST['pageEnabled'])) {
				$errors['enabled'][] = "Not set";
			} else {
				$pageEnabled = $_mysql->real_escape_string($_POST['pageEnabled']);
			}

			if (!isset($_POST['pageLink'])) {
				$errors['link'][] = "Not set";
			} else {
				$link = $_mysql->real_escape_string($_POST['pageLink']);
			}
			
			// Page content check
			if (isset($_POST['pageLink']) && empty($_POST['pageLink'])) { // Only bother checking for content if this page isn't a link
				if (!isset($_POST['editor1'])) {
					$errors['content'][] = "Not set";
				} else {
					if (empty($_POST['editor1'])) {
						$errors['content'][] = "Empty content";
					} else {
						$content = $_mysql->real_escape_string($_POST['editor1']);
					}
				}
			} else {
				$content = "";
			}
			
			if (!isset($_POST['objectType'])) {
				$errors['objectType'][] = "Not set";
			} else {
				if (empty($_POST['objectType'])) {
					$errors['objectType'][] = "Empty object type.";
				} else {
					if (!in_array($_POST['objectType'], $objectTypes)) { // If the object type is invalid
						$errors['objectType'][] = "Invalid object type.";
					} else { // If the object type is valid
						$objectType = $_mysql->real_escape_string($_POST['objectType']);
					}
				}
			}
			
			if (isset($objectType)) {
				if ($objectType == "poll") { // If the object type is a poll
					if (!isset($_POST['pollObject']) || empty($_POST['pollObject'])) { // If the poll object is empty
						$errors['pollObject'] = "Empty poll object.";
					} else { // If the poll object is not empty
						$object = (int) $_mysql->real_escape_string($_POST['pollObject']);
						
						$poll = new Poll($object);
						
						if ($poll->type() == "new") { // If we couldn't instantiate the poll
							$errors['pollObject'] = "Invalid poll.";
						}
					}
				} else if ($objectType == "roster") { // If the object type is a roster
					if (!isset($_POST['rosterObject']) || empty($_POST['rosterObject'])) { // If the roster object is empty
						$errors['rosterObject'] = "Empty roster object.";
					} else { // If the roster object is not empty
						$object = (int) $_mysql->real_escape_string($_POST['rosterObject']);
						
						$roster = new Roster($object);
						
						if ($roster->getType() == "new") { // If we couldn't instantiate the roster
							$errors['rosterObject'] = "Invalid roster.";
						}
					}
				}
			}
			
			if (!isset($object)) { // If the object variable hasn't been set yet
				$object = 0; // Set it to 0
			}

			if (!isset($_POST['pageDisplayInMenu'])) {
				$errors['displayInMenu'][] = "Not set";
			} else {
				if ($_POST['pageDisplayInMenu'] && !isset($_POST['pageMenuOrder'])) {
					$errors['displayInMenu'][] = "No menu order";
				} else if ($_POST['pageDisplayInMenu'] && isset($_POST['pageMenuOrder'])) { // If 
					$displayInMenu = $_mysql->real_escape_string($_POST['pageDisplayInMenu']);
					$menuOrder = $_mysql->real_escape_string($_POST['pageMenuOrder']);

					if (!is_numeric($menuOrder)) {
						$errors['displayInMenu'][] = "Menu order not a number";
					}
				} else {
					$displayInMenu = $_mysql->real_escape_string($_POST['pageDisplayInMenu']);
				}
			}

			if (!isset($_POST['pageLockType'])) {
				$errors['lockType'][] = "Not set";
			} else {
				if ($_POST['pageLockType'] == "rank" && !isset($_POST['pageLockTypeRank'])) {
					$errors['lockType'][] = "Rank not set";
				} else if ($_POST['pageLockType'] == "permission" && !isset($_POST['pageLockTypePermission'])) {
					$errors['lockType'][] = "Permission not set";
				} else if ($_POST['pageLockType'] == "rank") {
					$lockType = $_mysql->real_escape_string($_POST['pageLockType']);
					$lock = $_mysql->real_escape_string($_POST['pageLockTypeRank']);

					if (!is_numeric($lock)) {
						$errors['lockType'][] = "Rank ID not a number";
					}
				} else if ($_POST['pageLockType'] == "permission") {
					$lockType = $_mysql->real_escape_string($_POST['pageLockType']);
					$lock = $_mysql->real_escape_string($_POST['pageLockTypePermission']);
				} else if ($_POST['pageLockType'] == "none") {
					$lockType = $_mysql->real_escape_string($_POST['pageLockType']);
					$lock = "";
				}
			}


			if (count($errors)) { // If there were errors
				print_r($errors); // Temporary
				die("<strong>There were errors.</strong>");
			} else { // If there weren't any errors, 
				if ($_GET['a'] == "editPage") { // Editing a page
					$_mysql->query("UPDATE `pages` 
			SET `name` = '" . $pageName . "', 
			 `content` = '" . $content . "', 
			 `link` = '" . $link . "', 
			 `object_type` = '" . $objectType . "', 
			 `object` = " . $object . ", 
			 `lock_type` = '" . $lockType . "', 
			 `lock` = '" . $lock . "', 
			 `menu` = '" . $displayInMenu . "', 
			 `menu_order` = '" . $menuOrder . "', 
			 `enabled` = '" . $pageEnabled . "', 
			 `last_updated` = " . time() . " 
			 WHERE `id` = " . $_mysql->real_escape_string($_GET['id']));
				} else { // New page
					$_mysql->query("INSERT INTO `pages` 
			(`name`, 
			`content`, 
			`link`, 
			`object_type`, 
			`object`, 
			`lock_type`, 
			`lock`, 
			`menu`, 
			`menu_order`, 
			`enabled`,
			`created_on`, 
			`last_updated`) 
			VALUES 
			('" . $pageName . "', 
			 '" . $content . "', 
			 '" . $link . "', 
			 '" . $objectType . "', 
			 " . $object . ", 
			 '" . $lockType . "', 
			 '" . $lock . "', 
			 '" . $displayInMenu . "', 
			 '" . $menuOrder . "', 
			 '" . $pageEnabled . "', 
			 " . time() . ", 
			 " . time() . ")");
					
					// Redirect the user
					header("Location: ./?p=Pages");
					exit;
				}
			}
			// </editor-fold>
		}

		// <editor-fold defaultstate="collapsed" desc="Edit Page Code">
		if ($_GET['a'] == "editPage") { // If this is to edit a page load in the page data
			$pageDataQuery = $_mysql->query("SELECT * FROM `pages` WHERE `id` = " . $_mysql->real_escape_string($_GET['id']) . " LIMIT 1"); // Load in the page data
			$pageData = $pageDataQuery->fetch_assoc();
			
		} else {
			$pageData = array(); // If this is for a new page, set $pageData to a blank array
		}

		print("<h1>" . (($_GET['a'] == "newPage") ? "New CMS Page" : "Editing " . $pageData['name'] . " page") . "</h1>");

		print("<form method=\"post\">\n");

		print("<table id=\"editPageTable\">\n");

		print("<tr>\n<td>Page Name: </td><td><input type=\"text\" name=\"pageName\" id=\"pageName\" value=\"" . (($_GET['a'] == "newPage") ? "" : $pageData['name']) . "\"/></td>\n</tr>\n");

		print("<tr>\n<td>Enabled? </td><td><input type=\"radio\" name=\"pageEnabled\" id=\"pageEnabledYes\" value=\"1\" " . (($_GET['a'] == "editPage" && $pageData['enabled'] == 1) ? "checked=\"checked\"" : "") . " /> Yes <input type=\"radio\" name=\"pageEnabled\" id=\"pageEnabledNo\" value=\"0\" " . (($_GET['a'] == "newPage" || $pageData['enabled'] == 0) ? "checked=\"checked\"" : "") . " /> No</td></tr>\n");

		print("<tr>\n<td>Link: </td><td><input type=\"text\" name=\"pageLink\" id=\"pageLink\" value=\"" . (($_GET['a'] == "newPage") ? "" : $pageData['link']) . "\"/></td>\n</tr>\n");
		
		print("<tr>\n<td>Object Type: </td><td><select name=\"objectType\" id=\"objectType\">\n");
		
		foreach ($objectTypes as $objectType) { // For each object type
					print("<option value=\"" . $objectType . "\" " . (($_GET['a'] == "editPage" && $pageData['object_type'] == $objectType) ? "selected=\"selected\"" : "") . " >" . ucfirst($objectType) . "</option>\n");
		}
		
		print("</select>\n</td>\n</tr>\n");

		// Poll Object
		print("<tr class=\"objectOpts\" id=\"pollObjectOpts\">\n<td>Poll: </td><td><select name=\"pollObject\" id=\"pollObject\">");
		
		if ($_currUser->checkPermission("polls")) { // If this user has access to polls
			$polls = Poll::getPolls();
			
			foreach ($polls as $pollID=>$poll) { // For each poll
				print("<option value=\"" . $pollID . "\" " . (($_GET['a'] == "editPage" && $pageData['object_type'] == "poll" && $pageData['object'] == $pollID) ? "selected=\"selected\"" : "") . ">" . $poll . "</option>\n");
			}
		}
		
		print("</select></td>\n</tr>\n");
		
		// Roster Object
		print("<tr class=\"objectOpts\" id=\"rosterObjectOpts\">\n<td>Roster: </td><td><select name=\"rosterObject\" id=\"rosterObject\">");
		
		if ($_currUser->checkPermission("rosters")) { // If this user has access to polls
			$rosters = Roster::getRosters();
			
			foreach ($rosters as $rosterID=>$roster) { // For each roster
				print("<option value=\"" . $rosterID . "\" " . (($_GET['a'] == "editPage" && $pageData['object_type'] == "roster" && $pageData['object'] == $rosterID) ? "selected=\"selected\"" : "") . ">" . $roster . "</option>\n");
			}
		}
		
		print("</select></td>\n</tr>\n");

		print("<tr>\n<td>Display in menu?</td><td><input type=\"radio\" name=\"pageDisplayInMenu\" id=\"pageDisplayInMenuYes\" value=\"1\" " . (($_GET['a'] == "editPage" && $pageData['menu']) ? "checked=\"checked\"" : "") . " /> Yes <input type=\"radio\" name=\"pageDisplayInMenu\" id=\"pageDisplayInMenuNo\" " . (($_GET['a'] == "newPage" || ($_GET['a'] == "editPage" && !$pageData['menu'])) ? "checked=\"checked\"" : "") . " /> No</td>\n</tr>\n");

		print("<tr id=\"pageMenuOrderOpts\">\n<td>Menu order: </td><td><select name=\"pageMenuOrder\" id=\"pageMenuOrder\" >\n");

		for ($i = 1; $i < 11; $i++) {
			print("<option value=\"$i\" " . (($_GET['a'] == "editPage" && $pageData['menu'] && $pageData['menu_order'] == $i) ? "selected=\"selected\"" : "") . " >$i</option>\n");
		}

		print("</select>\n</td>\n</tr>\n");

		print("<tr>\n<td>Lock type: </td><td><select name=\"pageLockType\" id=\"pageLockType\">\n");
		print("<option value=\"none\" " . (($_GET['a'] == "newPage" || ($_GET['a'] == "editPage" && $pageData['lock_type'] == "none")) ? "selected=\"selected\"" : "") . " >None</option>\n");
		print("<option value=\"rank\" " . (($_GET['a'] == "editPage" && $pageData['lock_type'] == "rank") ? "selected=\"selected\"" : "") . " >Minimum Rank Level</option>\n");
		print("<option value=\"permission\" " . (($_GET['a'] == "editPage" && $pageData['lock_type'] == "permission") ? "selected=\"selected\"" : "") . " >Permission</option>\n");
		print("</select>\n</td>\n</tr>\n");

		print("<tr id=\"pageLockTypeRankOpts\">\n<td>Rank: </td><td><select name=\"pageLockTypeRank\" id=\"pageLockTypeRank\">");

		// Get the ranks out of the database and put them into this select box ordered by the highest level descending
		$ranksQuery = $_mysql->query("SELECT `name`, `level` FROM `ranks` ORDER BY `level` DESC");

		while ($rankData = $ranksQuery->fetch_assoc()) {
			print("<option " . (($pageData['lock_type'] == "rank" && $pageData['lock'] == $rankData['level']) ? "selected=\"selected\"" : "") . " value=\"" . $rankData['level'] . "\">" . $rankData['name'] . "</option>\n");
		}

		print("</select>\n</td>\n</tr>\n");

		print("<tr id=\"pageLockTypePermissionOpts\">\n<td>Permission: </td><td><input type=\"text\" name=\"pageLockTypePermission\" id=\"pageLockTypePermission\" /></td>\n</tr>\n");

		print("<tr>\n<td colspan=\"2\">Content:</td>\n</tr>\n");

		// Set the class as ckeditor to make an editor box
		print("<tr>\n<td></td><td><textarea class=\"ckeditor\" id=\"editor1\" name=\"editor1\">\n");
		if ($_GET['a'] == "editPage") { // If we're editing a page put the page's content in the editor
			print($pageData['content']);
		}
		print("</textarea></td>\n</tr>\n");

		print("<tr>\n<td></td><td><button type=\"submit\" name=\"pageSaveButton\" id=\"pageSaveButton\">Save Page</button></td>\n</tr>\n");

		print("</table>\n");

		print("</form>\n");
		// </editor-fold>
	} else { // If the action is invalid
		die("<strong>Error:</strong> Invalid action provided."); // TODO: Make this a nice looking error message
	}
} else { // If the user is not creating / editing a page
	// <editor-fold defaultstate="collapsed" desc="CMS Pages List">
	print("<h1>CMS Pages</h1>");

	// TODO: Query database and list the CMS pages, don't let users delete reserved pages, use the $_reservedPages array
	$pagesQuery = $_mysql->query("SELECT * FROM `pages`");

	if ($pagesQuery->num_rows) { // If there are pages
		print("<table>\n");

		print("<th>Name</th><th>Link</th><th>Object</th><th>Lock Type</th><th>Lock</th><th>Display in menu?</th><th>Menu Order</th><th>Enabled?</th><th>Options</th>");

		while ($pageRow = $pagesQuery->fetch_assoc()) {
			print("<tr id=\"page_" . (int) $pageRow['id'] . "\">\n");

			print("<td><a href=\"./?p=Pages&a=editPage&id=" . (int) $pageRow['id'] . "\">" . $pageRow['name'] . "</a></td>");
			print("<td>" . $pageRow['link']  . "</td>");
			print("<td>" . ((!empty($pageRow['object'])) ? ucfirst($pageRow['object_type']) . (($pageRow['object_type'] != "none") ? " - " . $pageRow['object'] : "")  : "") . "</td>");
			print("<td>" . ucfirst($pageRow['lock_type']) . "</td>");
			print("<td>" . $pageRow['lock'] . "</td>");
			print("<td>" . $pageRow['menu'] . "</td>");
			print("<td>" . $pageRow['menu_order'] . "</td>");
			print("<td>" . $pageRow['enabled'] . "</td>");
			print("<td><button id=\"edit_" . (int) $pageRow['id'] . "\" class=\"editPageButton\">Edit</button> ");
			print("<button id=\"delete_" . (int) $pageRow['id'] . "\" class=\"deletePageButton\">Delete</button></td>\n");
			
			print("</tr>\n");
		}

		print("</table>\n");
	} else {
		print("<strong>There are no pages.</strong>");
	}


	print("<br /><br /><a href=\"./?p=Pages&a=newPage\">Create New Page</a>\n"); // Temporary new page link
// </editor-fold>
}
?>