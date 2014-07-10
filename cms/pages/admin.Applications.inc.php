<?php
if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

if (!$_currUser->checkPermission("manageApplications") && !$_currUser->checkPermission("editApplication")) { // If the user doesn't have permission to look at clan applications or edit the application
	header("Location: ./");
	exit;
}

if (!isset($_GET['action'])) {
	// <editor-fold defaultstate="collapsed" desc="Clan Applications Landing Page Code">
	print("<h1>Clan Applications</h1>");

	if ($_currUser->checkPermission("manageApplications")) { // If the user has the permission to manage clan applications
		print("<a href=\"./?p=Applications&action=manage\">Manage Applications</a><br /><br />\n");
	}

	if ($_currUser->checkPermission("editApplication")) { // If the user has the permission to edit the clan application
		print("<a href=\"./?p=Applications&action=edit\">Edit Application Questions</a>\n");
	}
	// </editor-fold>
} else {
	if ($_GET['action'] == "manage") {
		// <editor-fold defaultstate="collapsed" desc="Manage Clan Applications Code">
		if (!$_currUser->checkPermission("manageApplications")) { // If the user doesn't have permission to manage applications
			header("Location: ./?p=Applications");
			exit;
		}
		
		print("<h1>Manage Clan Applications</h1>\n");
		
		
		// TODO: Select pending clan applications from the database, if there aren't any; don't output the table
		
		print("<table>\n");
		
		print("<th>User</th>\n");
		print("<th>Date Submitted</th>\n");
		print("<th>Options</th>\n");
		
		print("</table>\n");
		
		// </editor-fold>		
	} else if ($_GET['action'] == "edit") {
		// <editor-fold defaultstate="collapsed" desc="Edit Clan Application Questions Code">
		if (!$_currUser->checkPermission("editApplication")) { // If the user doesn't have permission to edit the clan application questions
			header("Location: ./?p=Applications");
			exit;
		}
		
		print("<h1>Application Questions</h1>\n");
		
		print("<form id=\"appQuestionsForm\" method=\"post\">\n");
		
		// TODO: Select questions from database
		
		print("</form>\n");
		
		print("<hr />\n");
		
		print("<h2>Add New Question</h2>\n");
		print("<table>\n");
		print("<form  method=\"post\">\n");
		
		print("<th>Label</th>\n");
		print("<th>Type</th>\n");
		print("<th>Value (Drop Down Menus: separate each option with a comma \",\")</th>\n");
		print("<th>Max Characters</th>\n");
		print("<th>Order</th>\n");
		
		print("<tr>\n");
		
		print("<td>\n");
		print("<input id=\"newQuestionLabel\" name=\"newQuestionLabel\" type=\"text\"/>\n");
		print("</td>\n");
		
		print("<td>\n");
		print("<select id=\"newQuestionType\" name=\"newQuestionType\">\n");
		print("<option value=\"text\">Checkbox</option>\n");
		print("<option value=\"select\">Drop Down Menu</option>\n");
		print("<option value=\"password\">Password Field</option>\n");
		print("<option value=\"radio\">Radio Button (Yes / No)</option>\n");
		print("<option value=\"textArea\">Text Area</option>\n");
		print("<option value=\"text\" selected=\"selected\">Text Field</option>\n");
		print("</select>\n");
		print("</td>\n");
		
		print("<td>\n");
		print("<input id=\"newQuestionPre\" name=\"newQuestionPre\" type=\"text\" />\n");
		print("</td>\n");
		
		print("<td>\n");
		print("<select id=\"newQuestionCharacters\" name=\"newQuestionCharacters\">\n");
		
		for ($i = 1; $i < 256; $i++) { // 1 - 255
			print("<option value=\"" . $i . "\">" . $i . "</option>\n");
		}
		
		print("</select>\n");
		print("</td>\n");
		
		print("<td>\n");
		print("<select id=\"newQuestionOrder\" name=\"newQuestionOrder\">\n");
		
		for ($i = 1; $i < 101; $i++) { // 1 - 100
			print("<option value=\"" . $i . "\">" . $i . "</option>\n");
		}
		
		print("</select>\n");
		print("</td>\n");
		
		print("<td>\n");
		print("<button id=\"newQuestionAdd\" name=\"newQuestionAdd\">Add Question</button>\n");
		print("</td>\n");
		print("</tr>\n");
		
		print("</form>\n");
		print("</table>\n");
		
		// </editor-fold>
	} else { // If the action is invalid
		die("<strong>Error:</strong> Invalid action.");
	}
}

?>