<?php

if ($_currUser->type() == "guest") { // If the user isn't logged in, redirect them
	header("Location: ../");
	exit;
}

if (!$_currUser->checkPermission("Super Admin")) { // If this user doesn't have the Super Admin permission, redirect them
	header("Location: ./");
	exit;
}

if (isset($_POST['settingsSubmitButton'])) {
	// <editor-fold defaultstate="collasped" desc="Update Settings Handling">
	$settings = array();

	foreach ($_POST as $key => $var) {
		if (substr($key, 0, 8) == "setting_") { // Check this is a setting variable
			$key = substr($key, 8);
			$key = str_replace(" ", "_", $key); // Replace spaces with underscores
			// <editor-fold defaultstate="collapsed" desc="Non-standard settings">
			if ($key == "dateFormat" && $var == "null") {
				if (isset($_POST['customDateFormat']) && !empty($_POST['customDateFormat'])) {
					$var = $_mysql->real_escape_string($_POST['customDateFormat']);
				} else {
					$var = "d/m/Y"; // Default date format
				}
			} else if ($key == "twitterFollowButton_URL") { // If this key is the twitterFollowButton_URL setting
				$var = str_replace("@", "", $var); // If the user submitted setting contains an at sign (@), remove it
			}
			// </editor-fold>

			$settings[$key] = $var;
		}
	}

	$_site->saveSettings($settings);
	// </editor-fold>
}

// <editor-fold defaultstate="collapsed" desc="Settings Display">
print("<h1>Site Settings</h1>");

print("<h2>Features</h2>");

print("<form id=\"settingsForm\" method=\"post\">\n");

$features = array();
$features['AJAX Login'] = "Display AJAX login in header.";
$features['Generation Stats'] = "Display page generation statistics in footer.";
$features['displayPageTitle'] = "Display page name as title in content area.";
$features['allowChangeUserName'] = "Allow users to change their user name.";
$features['roundAvatars'] = "Round the corners of users' avatars.";
$features['ShowPageLastUpdate'] = "Show when CMS pages were last updated.";
$features['useGZCompression'] = "Use GZIP compression, if enabled.";

print("<table>\n");

print("<th>Yes</th><th>No</th><th>Description</th>\n");

foreach ($features as $featureKey => $featureDesc) {
	print("<tr><td><input type=\"radio\" id=\"setting_" . $featureKey . "1\" name=\"setting_" . $featureKey . "\" value=\"1\" " . (($_site->setting($featureKey)) ? "checked=\"checked\"" : "") . " /></td><td><input type=\"radio\" id=\"setting_" . $featureKey . "1\" name=\"setting_" . $featureKey . "\" value=\"0\" " . ((!$_site->setting($featureKey)) ? "checked=\"checked\"" : "") . " /></td><td>" . $featureDesc . "</td></tr>\n");
}

print("</table>\n");

print("<h2>Misc</h2>");

$misc = array();
$misc['dateFormat'] = "Date Format";

print("<table>\n");

print("<th>Yes</th><th>No</th><th>Description</th>\n");

foreach ($misc as $miscKey => $miscDesc) {

	if ($miscKey == "dateFormat") { // Different code for these setting as we need text fields
		print("<tr><td colspan=\"2\"><select type=\"text\" id=\"setting_" . $miscKey . "\" name=\"setting_" . $miscKey . "\">");

		print("<option " . (($_site->setting($miscKey) == "d/m/Y") ? "selected=\"selected\"" : "") . " value=\"d/m/Y\">British Short Date Format</option>\n");
		print("<option " . (($_site->setting($miscKey) == "jS F Y") ? "selected=\"selected\"" : "") . " value=\"jS F Y\">British Long Date Format</option>\n");

		print("<option " . (($_site->setting($miscKey) == "m/d/Y") ? "selected=\"selected\"" : "") . " value=\"m/d/Y\">American Short Date Format</option>\n");
		print("<option " . (($_site->setting($miscKey) == "F jS Y") ? "selected=\"selected\"" : "") . " value=\"F jS Y\">American Long Date Format</option>\n");

		print("<option " . (($_site->setting($miscKey) != "d/m/Y" && $_site->setting($miscKey) != "jS F Y" && $_site->setting($miscKey) != "m/d/Y" && $_site->setting($miscKey) != "F jS Y") ? "selected=\"selected\"" : "") . " value=\"null\">Custom Date Format</option>\n");

		print("</select></td>");

		// This cell will be hidden unless the "Custom Date Format" option is selected
		print("<td id=\"customDateFormatOpts\"><input type=\"text\" name=\"customDateFormat\" id=\"customDateFormat\" value=\"" . $_site->setting($miscKey) . "\" /></td>");

		print("<td>" . $miscDesc . "</td></tr>\n");
	} else {
		print("<tr><td><input type=\"radio\" id=\"setting_" . $miscKey . "1\" name=\"setting_" . $miscKey . "\" value=\"1\" " . (($_site->setting($miscKey)) ? "checked=\"checked\"" : "") . " /></td><td><input type=\"radio\" id=\"setting_" . $miscKey . "1\" name=\"setting_" . $miscKey . "\" value=\"0\" " . ((!$_site->setting($miscKey)) ? "checked=\"checked\"" : "") . " /></td><td>" . $miscDesc . "</td></tr>\n");
	}
}

print("</table>\n");

print("<h2>Security</h2>");

$security = array();
$security['captchaOnRegister'] = "Use captcha on the register page.";
$security['EM-CreationsCaptcha'] = "Use EM-Creations.co.uk captcha.";
$security['recaptcha'] = "Use Google reCAPTCHA.";
$security['recaptchaPublicKey'] = "Google reCAPTCHA public key.";
$security['recaptchaPrivateKey'] = "Google reCAPTCHA private key.";
$security['solveMediaCaptcha'] = "Use SolveMedia captcha.";
$security['solveMediaCKey'] = "SolveMedia challenge key.";
$security['solveMediaVKey'] = "SolveMedia verification key.";
$security['solveMediaHKey'] = "SolveMedia authentication hash key.";

print("<table>\n");

print("<th>Yes</th><th>No</th><th>Description</th>\n");

foreach ($security as $securityKey => $securityDesc) {
	if ($securityKey == "recaptchaPublicKey" || $securityKey == "recaptchaPrivateKey") { // Different code for these settings as we need text fields
		print("<tr class=\"recaptcha_opts\"><td colspan=\"2\"></td><td><input type=\"text\" id=\"setting_" . $securityKey . "\" name=\"setting_" . $securityKey . "\" value=\"" . (($_site->setting($securityKey)) ? $_site->setting($securityKey) : "") . "\" /></td><td>" . $securityDesc . "</td></tr>\n");
	} else if ($securityKey == "solveMediaCKey" || $securityKey == "solveMediaVKey" || $securityKey == "solveMediaHKey") { // Different code for these settings as we need text fields
		print("<tr class=\"solveMedia_opts\"><td colspan=\"2\"></td><td><input type=\"text\" id=\"setting_" . $securityKey . "\" name=\"setting_" . $securityKey . "\" value=\"" . (($_site->setting($securityKey)) ? $_site->setting($securityKey) : "") . "\" /></td><td>" . $securityDesc . "</td></tr>\n");
	} else {
		print("<tr><td><input type=\"radio\" id=\"setting_" . $securityKey . "Yes\" class=\"setting_" . $securityKey . "_radio\" name=\"setting_" . $securityKey . "\" value=\"1\" " . (($_site->setting($securityKey)) ? "checked=\"checked\"" : "") . " /></td><td><input type=\"radio\" id=\"setting_" . $securityKey . "No\" class=\"setting_" . $securityKey . "_radio\" name=\"setting_" . $securityKey . "\" value=\"0\" " . ((!$_site->setting($securityKey)) ? "checked=\"checked\"" : "") . " /></td><td>" . $securityDesc . "</td></tr>\n");
	}
}

print("</table>\n");

print("<h2>Social</h2>");

$social = array();
$social['googlePlusButton'] = "Google Plus Button";
$social['facebookButton'] = "Facebook Button";
$social['twitterFollowButton'] = "Twitter Follow Button";

print("<table>\n");

print("<th>Yes</th><th>No</th><th>Description</th>\n");

foreach ($social as $socialKey => $socialDesc) {

	if ($socialKey == "googlePlusButton" || $socialKey == "facebookButton" || $socialKey == "twitterFollowButton") { // Different code for these setting as we need text fields
		print("<tr><td><input type=\"radio\" id=\"setting_" . $socialKey . "Yes\" class=\"setting_" . $socialKey . "_radio\" name=\"setting_" . $socialKey . "\" value=\"1\" " . (($_site->setting($socialKey)) ? "checked=\"checked\"" : "") . " /></td><td><input type=\"radio\" id=\"setting_" . $socialKey . "No\" class=\"setting_" . $socialKey . "_radio\" name=\"setting_" . $socialKey . "\" value=\"0\" " . ((!$_site->setting($socialKey)) ? "checked=\"checked\"" : "") . " /></td><td>" . $socialDesc . "</td></tr>\n");

		print("<tr id=\"setting_" . $socialKey . "_opts\"><td colspan=\"2\"><input type=\"text\" id=\"setting_" . $socialKey . "_URL\" name=\"setting_" . $socialKey . "_URL\" value=\"" . (($_site->setting($socialKey . "_URL")) ? $_site->setting($socialKey . "_URL") : "") . "\" /></td><td>URL</td></tr>\n");
	} else {
		print("<tr><td><input type=\"radio\" id=\"setting_" . $socialKey . "1\" name=\"setting_" . $socialKey . "\" value=\"1\" " . (($_site->setting($socialKey)) ? "checked=\"checked\"" : "") . " /></td><td><input type=\"radio\" id=\"setting_" . $socialKey . "1\" name=\"setting_" . $socialKey . "\" value=\"0\" " . ((!$_site->setting($socialKey)) ? "checked=\"checked\"" : "") . " /></td><td>" . $socialDesc . "</td></tr>\n");
	}
}

print("</table>\n");

print("<br /><button type=\"submit\" id=\"settingsSubmitButton\" name=\"settingsSubmitButton\">Save Settings</button>");

print("</form>\n");
// </editor-fold>
?>