<?php
/**
 * CMS class for outputting page elements
 *
 * @author Edward
 */
class CMS {

	/**
	 * Output header
	 * 
	 * @global Site $_site
	 * @global User $_currUser
	 * @param boolean $returnAsVar
	 * @return string $returnAsVar
	 */
	public static function outputHeader($returnAsVar = false) {
		// <editor-fold defaultstate="collapsed" desc="Output Header Code">
		global $_site;
		global $_currUser;

		$header = "\t\t\t\t<div id=\"header\">\n<a href=\"./\">\n";
		// TODO: Check if a logo image is set, if it is use it, otherwise use the site info name
		$header .= "<img src=\"./themes/default/images/logo.png\" />";
		//	$header .= "\t\t\t\t\t<h1>" . $_site->title() . "</h1>\n";
		$header .= "</a>\n";

		if ($_site->setting("AJAX Login")) {
			$header .= "<div id=\"ajaxLoginBox\">\n";
			$header .= "<div style=\"padding-left: 20px; padding-top: 10px;\">\n";

			if ($_currUser->type() == "guest") { // If they're not logged in
				$header .= "<table id=\"ajaxLoginTable\">\n";
				$header .= "<tr><td class=\"right\">Username <input type=\"text\" id=\"ajaxLoginUser\" name=\"ajaxLoginUser\" size=\"7\" /></td></tr>\n";
				$header .= "<tr><td class=\"right\">Password <input type=\"password\" id=\"ajaxLoginPassword\" name=\"ajaxLoginUser\" size=\"7\" /></td></tr>\n";
				$header .= "<tr><td style=\"padding-left: 10px;\"><button id=\"ajaxLoginButton\">Login</button> <a href=\"./?p=Register\">Register</a></td></tr>";
                                $header .= "<tr><td><a href=\"./?p=resetpass\">Forgot Password?</a></td></tr>";
				$header .= "</table>\n";
			} else if ($_currUser->type() == "member") {

				$header .= "<table id=\"ajaxLoginTable\">\n";

				$header .= "<tr>\n";
				$header .= "<td rowspawn=\"3\">" . $_currUser->avatar("s") . "</td>";

				$header .= "<td>Welcome <a href=\"./?p=Profile&u=" . urlencode($_currUser->name()) . "\">" . $_currUser->name() . "</a><br />\n";

				$header .= "<a href=\"./?p=Account\">My Account</a><br />\n";
				$header .= "<a href=\"#\" id=\"ajaxLogout\">Logout</a><br />\n";

				$header .= "</td>\n</tr>\n";

				$header .= "</table>";
			}
			$header .= "</div>\n";
			$header .= "</div>\n";
		}

		// Check to see if at least one of the social buttons are enabled
		if ($_site->setting("googlePlusButton") || $_site->setting("facebookButton") || $_site->setting("twitterFollowButton")) {
			$header .= "<div id=\"headerSocialButtons\">\n";

			if ($_site->setting("googlePlusButton")) {
				$header .= self::outputSocialButton("Google+", true);
			}

			if ($_site->setting("facebookButton")) {
				$header .= self::outputSocialButton("FacebookLike", true);
			}

			if ($_site->setting("twitterFollowButton")) {
				$header .= self::outputSocialButton("TwitterFollow", true);
			}

			$header .= "</div>\n";
		}

		// Output Menu
		$header .= CMS::outputMenu(true);

		$header .= "\t\t\t\t</div>\n";

		if ($returnAsVar) { // If we're returning the header as a variable
			return $header;
		} else { // If we're not returning the header as a variable
			print($header);
		}
		// </editor-fold>
	}

	/**
	 * Output the main menu of the page
	 * 
	 * @param optional $returnAsVar Return menu as array, rather than printing
	 * @return mixed $menu
	 */
	public static function outputMenu($returnAsVar = false, $returnType = "string") {
		// <editor-fold defaultstate="collapsed" desc="Output Menu Code">
		global $_currUser, $_mysql; // We may need to reference the current user object

		$query = $_mysql->query("SELECT `name`, `link`, `lock_type`, `lock` FROM `pages` WHERE `menu` = 1 AND `enabled` = 1 ORDER BY `menu_order`");

		if (!$query->num_rows) { // If there aren't any pages set to appear in the menu return out of the function
			return;
		}

		if ($returnType == "string") {
			$menu = "";
		} else if ($returnType == "array") {
			$menu = array();
		}

		if ($returnType == "string") {
			$menu .= "\t\t\t\t\t<div id=\"mainMenu\">\n";
			$menu .= "\t\t\t\t\t<ul>\n";
		}

		while ($row = $query->fetch_assoc()) {
			/*
			 * Check if there's a lock on this page,
			 * if there is check to see whether this user has permission to view it,
			 * if they don't skip this page.
			 */

			if ($row['lock_type'] == "rank") {
				$thisRank = new Rank($_currUser->rankID());
				if ($row['lock'] > $thisRank->level()) {
					continue;
				}
			} else if ($row['lock_type'] == "permission") {
				if (!$_currUser->checkPermission($row['lock'])) {
					continue;
				}
			}

			if ($returnType == "string") {
				$menu .= "\t\t\t\t\t\t<li><a href=\"" . (($row['link'] == "null" || empty($row['link'])) ? "./?p=" . urlencode($row['name']) . "" : $row['link']) . "\">" . (($row['name'] == "Login" && $_currUser->type() == "member") ? "Logout" : $row['name']) . "</a></li>\n";
			} else if ($returnType == "array") {
				$menu[] = $row['name'];
			}
		}

		if ($returnType == "string") {
			$menu .= "\t\t\t\t\t</ul>\n</div>\n";
		}

		if ($returnAsVar) { // If we're returning the menu as a variable
			return $menu;
		} else { // If we're not returning the menu as a variable
			print($menu);
		}
		// </editor-fold>
	}

	/**
	 * Output footer
	 * 
	 * @global Site $_site
	 * @global float $_generateStart
	 * @param boolean $returnAsVar
	 * @return string $footer
	 */
	public static function outputFooter($returnAsVar = false) {
		// <editor-fold defaultstate="collapsed" desc="Output Footer Code">
		global $_site;
		global $_generateStart;
		global $_currUser;

		$footer = "\t\t\t\t<div id=\"footer\">\n";

		// TODO: Check for custom footer content and add it to $footer if available

		$footer .= "\t\t\t\t\t&copy; Copyright " . date("Y") . " 101st Division Clan CMS<br />Credits: <a href=\"http://www.em-creations.co.uk\" target=\"_blank\">EM-Creations</a>, Paul, Falco\n<br>";

		$_generateEnd = microtime(true);

		if ($_site->setting("Generation Stats")) {
			$footer .= "\n\n" . "Generated on: " . date($_site->setting("dateFormat")) . " in " . round($_generateEnd - $_generateStart, 5) . " seconds.";
		}
		if ($_currUser->type() != "guest") {
			if ($_currUser->checkPermission("basicAdmin")) { 		
				$footer .= "<br>"."\n\n"."<a href=\"./admin/\">Administrator Control Panel</a>";
			}
		}
		$footer .= "\t\t\t\t</div>\n";
		$footer .= "\t\t\t</div>\n";
		$footer .= "\t\t</div>\n";
		$footer .= "\t</body>\n";
		$footer .= "</html>";

		if ($returnAsVar) { // If we're returning the footer as a variable
			return $footer;
		} else { // If we're not returning the footer as a variable
			print($footer);
		}
		// </editor-fold>
	}

	/**
	 * Output a custom CMS page
	 * 
	 * @global Site $_site
	 * @global User $_currUser
	 * @global mysqli $_mysql
	 * @param string $pageName
	 * @param boolean $returnAsVar
	 * @return string $page
	 */
	public static function outputCustomPage($pageName, $returnAsVar = false) {
		// <editor-fold defaultstate="collapsed" desc="Output Custom Page Code">
		global $_site, $_currUser, $_mysql;

		$pageDataQuery = $_mysql->query("SELECT `name`, `content`, `object_type`, `object`, `last_updated`, `lock_type`, `lock` FROM `pages` WHERE `name` = '" . $pageName . "' LIMIT 1");
		$pageData = $pageDataQuery->fetch_assoc();
		
		if ($pageData['lock_type'] == "rank") {
			$thisRank = new Rank($_currUser->rankID());
			if ($pageData['lock'] > $thisRank->level()) {
				// Redirect user
				header("Location: ./");
				exit;
			}
		} else if ($pageData['lock_type'] == "permission") {
			if (!$_currUser->checkPermission($pageData['lock'])) {
				// Redirect user
				header("Location: ./");
				exit;
			}
		}

		$toAdd = "";

		if ($_site->setting("ShowPageLastUpdate")) { // If the setting to show when the page was last updated is enabled
			$toAdd .= " <span class=\"pageUpdatedTime\">Last Updated " . date($_site->setting("dateFormat"), $pageData['last_updated']) . "</span>";
		}

		if ($_site->setting("displayPageTitle")) { // If the setting to display the page title is enabled
			$toAdd .= "<span class=\"title\">" . $pageData['name'] . "</span>";
		}
		
		if ($pageData['object_type'] != "none") { // If this page has an object attached to it
			switch ($pageData['object_type']) {
				case "poll": // If a poll is attached to this page
					$poll = new Poll((int) $pageData['object']); // Instantiate a new poll object
					if ($poll->type() != "new") { // If we instantiated the poll successfully
						$toAdd .= $poll->display(true);
					}
					break;
					
				case "roster": // If a roster is attached to this page
					$roster = new Roster((int) $pageData['object']); // Instantiate a new roster object
					if ($roster->getType() != "new") { // If we instantiated the poll successfully
						$toAdd .= $roster->display(true);
					}
					break;
			}
		}

		$pageData['content'] = $toAdd . $pageData['content']; // Prepend any $toAdd content

		if ($returnAsVar) {
			return $pageData['content'];
		} else {
			print($pageData['content']);
		}
		// </editor-fold>
	}

	/**
	 * Output left box
	 * 
	 * @param int $width
	 * @param boolean $returnAsVar
	 * @return string $leftBox
	 */
	public static function outputLeftBox($width = 175, $returnAsVar = false) {
		// <editor-fold defaultstate="collapsed" desc="Output Left Box Code">
		$leftBox = "<div id=\"leftBox\" style=\"width: " . (int) $width . "px;\">\n";
		// TODO: Get which widgets are enabled and output them here

		/* Test Widget */
		$leftBox .= "<div class=\"widget\">\n<div class=\"content\">\n";
		$leftBox .= "<span class=\"title\">TeamSpeak 3</span>\n";
		$leftBox .= "<iframe src=\"http://cache.www.gametracker.com/components/html0/?host=85.236.100.27:30047&bgColor=333333&fontColor=CCCCCC&titleBgColor=222222&titleColor=FF9900&borderColor=555555&linkColor=FFCC00&borderLinkColor=222222&showMap=0&currentPlayersHeight=160&showCurrPlayers=1&showTopPlayers=0&showBlogs=0&width=155\" frameborder=\"0\" scrolling=\"no\" width=\"155\" height=\"348\"></iframe>";
		$leftBox .= "</div>\n<div class=\"bottomBoxBar\"></div>\n</div>\n";

		/* Advertisement Test Widget */
		$leftBox .= "<div class=\"widget\">\n<div class=\"content\">\n";
		$leftBox .= "<span class=\"title\">ADVERTISEMENT</span>\n";

		$leftBox .= "Advert to go here";

		$leftBox .= "</div>\n<div class=\"bottomBoxBar\"></div>\n</div>\n";

		$leftBox .= "</div>\n";

		if ($returnAsVar) { // If we're returning the left box content as a variable
			return $leftBox;
		} else { // If we're not returning the left box content as a variable
			print($leftBox);
		}
		// </editor-fold>
	}

	/**
	 * Output social button
	 * 
	 * @global Site $_site
	 * @param string $button
	 * @param boolean $returnAsVar
	 * @return string $buttonHTML
	 */
	public static function outputSocialButton($button, $returnAsVar = false) {
		// <editor-fold defaultstate="collapsed" desc="Output Social Button Code">
		global $_site;
		$output = false;

		switch ($button) {
			case "google+":
			case "Google+":
				$output = "<div class=\"socialButton\">\n
			    <div class=\"g-plusone\" ";

				// If a URL is set for this button, use it here
				$output .= "data-href=\"" . $_site->setting("googlePlusButton_URL") . "\"";

				$output .= "></div>
			    <script type=\"text/javascript\">
			      window.___gcfg = {lang: 'en-GB'};

			      (function() {
				var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
				po.src = 'https://apis.google.com/js/plusone.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
			      })();
			    </script>
		    </div>\n";
				break;

			case "facebooklike":
			case "FacebookLike":
				$output = "<div class=\"socialButton\">\n
			    <div id=\"fb-root\"></div>
			    <script>(function(d, s, id) {
			      var js, fjs = d.getElementsByTagName(s)[0];
			      if (d.getElementById(id)) return;
			      js = d.createElement(s); js.id = id;
			      js.src = \"//connect.facebook.net/en_GB/all.js#xfbml=1\";
			      fjs.parentNode.insertBefore(js, fjs);
			    }(document, 'script', 'facebook-jssdk'));</script>";
				$output .= "<div class=\"fb-like\"";

				$output .= "data-href=\"" . $_site->setting("facebookButton_URL") . "\"";

				$output .= "data-send=\"false\"
			    data-layout=\"button_count\"
			    data-width=\"450\"
			    data-height=\"60\"
			    data-show-faces=\"true\"></div></div>\n";
				break;

			case "twitterfollow":
			case "TwitterFollow":
				$output = "<div class=\"socialButton\">\n";

				$output .= "<a href=\"https://twitter.com/" . $_site->setting("twitterFollowButton_URL") . "\" class=\"twitter-follow-button\" data-show-count=\"false\" data-size=\"large\">Follow @" . $_site->setting("twitterFollowButton_URL") . "</a>";

				$output .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=\"//platform.twitter.com/widgets.js\";fjs.parentNode.insertBefore(js,fjs);}}(document,\"script\",\"twitter-wjs\");</script>";
				$output .= "</div>\n";
				break;

			default:
				$output = false;
				break;
		}

		if ($returnAsVar) {
			return $output;
		} else {
			print($output);
		}
		// </editor-fold>
	}

}
?>