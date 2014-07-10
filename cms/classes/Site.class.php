<?php
/**
 * Site data class
 *
 * @author Edward
 */
class Site {
	// Declare class properties
	private $url = false;
	private $secureURL = false;
	private $title = false;
	private $tag = false;
	private $email = false;
	private $settings = array();

	/**
	 * Site constructor
	 * 
	 * @global mysqli $_mysql
	 */
	public function __construct() {
		// <editor-fold defaultstate="collapsed" desc="Site Construct Code">
		global $_mysql;
		
		$siteDataQuery = $_mysql->query("SELECT * FROM `site_info` WHERE `id` = 1");
		$siteData = $siteDataQuery->fetch_assoc();

		$this->url = $siteData['url'];
		$this->secureURL = $siteData['secure_url'];
		$this->title = $siteData['title'];
		$this->tag = $siteData['clan_tag'];
		$this->email = $siteData['contact_email'];

		if (!isset($siteData['settings']) || empty($siteData['settings']) || $siteData['settings'] == "") { // Make sure there are some site settings
			$this->settings = array();
		} else {
			$this->settings = unserialize($siteData['settings']);
		}
		// </editor-fold>
	}

	/**
	 * Retrieve site's non secure url
	 * 
	 * @return string $url
	 */
	public function url() {
		return $this->url;
	}

	/**
	 * Set the site's URL
	 * 
	 * @param string $url
	 * @global mysqli $_mysql
	 */
	public function setURL($url) {
		// <editor-fold defaultstate="collapsed" desc="Set URL Code">
		global $_mysql;
		
		$url = $_mysql->real_escape_string($url);

		if ($url != $this->url) {
			$this->url = $url;
			$_mysql->query("UPDATE `site_info` SET `url` = '" . $url . "' WHERE `id` = 1");
		}
		// </editor-fold>
	}

	/**
	 * Retrieve site's secure url
	 * 
	 * @return string $url
	 */
	public function secureURL() {
		return $this->secureURL;
	}

	/**
	 * Set the site's secure URL
	 * 
	 * @param string $url
	 * @global mysqli $_mysql
	 */
	public function setSecureURL($url) {
		// <editor-fold defaultstate="collapsed" desc="Set Secure URL Code">
		global $_mysql;
		
		$url = $_mysql->real_escape_string($url);

		if ($url != $this->secureURL) {
			$this->secureURL = $url;
			$_mysql->query("UPDATE `site_info` SET `secure_url` = '" . $url . "' WHERE `id` = 1");
		}
		// </editor-fold>
	}

	/**
	 * Retrieve site's title
	 * 
	 * @return string $title
	 */
	public function title() {
		return $this->title;
	}

	/**
	 * Set the site's title
	 * 
	 * @param string $title
	 * @global mysqli $_mysql
	 */
	public function setTitle($title) {
		// <editor-fold defaultstate="collapsed" desc="Set Title Code">
		global $_mysql;
		
		$title = $_mysql->real_escape_string($title);

		if ($title != $this->title) {
			$this->title = $title;
			$_mysql->query("UPDATE `site_info` SET `title` = '" . $title . "' WHERE `id` = 1");
		}
		// </editor-fold>
	}

	/**
	 * Get the clan's tag, with formatting if wanted
	 * 
	 * @param mixed $formatting
	 * @return string $tag
	 */
	public function tag($formatting = false) {
		// <editor-fold defaultstate="collapsed" desc="Tag Code">
		switch ($formatting) {
			case "[":
			case "]":
				return "[" . $this->tag . "]";
				break;

			case "(":
			case ")":
				return "(" . $this->tag . ")";
				break;

			case "=":
				return "=" . $this->tag . "=";
				break;

			case "{":
			case "}":
				return "{" . $this->tag . "}";
				break;

			case false:
				return $this->tag;
				break;

			default:
				return $this->tag;
				break;
		}
		// </editor-fold>
	}

	/**
	 * Set the site's clan tag
	 * 
	 * @param string $tag
	 * @global mysqli $_mysql
	 */
	public function setTag($tag) {
		// <editor-fold defaultstate="collapsed" desc="Set Tag Code">
		global $_mysql;
		
		$tag = $_mysql->real_escape_string($tag);

		if ($tag != $this->tag) {
			$this->tag = $tag;
			$_mysql->query("UPDATE `site_info` SET `clan_tag` = '" . $tag . "' WHERE `id` = 1");
		}
		// </editor-fold>
	}

	/**
	 * Retrieve site contact email
	 * 
	 * @return string $email
	 */
	public function email() {
		return $this->email;
	}

	/**
	 * Set the site's contact email
	 * 
	 * @param string $email
	 * @global mysqli $_mysql
	 */
	public function setEmail($email) {
		// <editor-fold defaultstate="collapsed" desc="Set Email Code">
		global $_mysql;
		
		$email = $_mysql->real_escape_string($email);

		if ($email != $this->email) {
			$this->email = $email;
			$_mysql->query("UPDATE `site_info` SET `contact_email` = '" . $email . "' WHERE `id` = 1");
		}
		// </editor-fold>
	}

	/**
	 * Retrieve site settings array
	 * 
	 * @return array $settings
	 */
	public function settings() {
		return $this->settings;
	}

	/**
	 * Save site settings
	 * 
	 * @param array $settings
	 * @global mysqli $_mysql
	 */
	public function saveSettings($settings = array()) {
		// <editor-fold defaultstate="collapsed" desc="Save Settings Code">
		global $_mysql;
		
		$dbSettings = serialize($settings); // Serialize the settings array, ready to store in the database

		$_mysql->query("UPDATE `site_info` SET `settings` = '" . $_mysql->real_escape_string($dbSettings) . "' WHERE `id` = 1");

		$this->settings = $settings;
		// </editor-fold>
	}

	/**
	 * Retrieve the value of a specific site setting
	 * 
	 * @return mixed $settingValue 
	 */
	public function setting($setting) {
		// <editor-fold defaultstate="collapsed" desc="Setting Code">
		$setting = str_replace(" ", "_", $setting); // Replace any spaces with under scores

		if (array_key_exists($setting, $this->settings)) {
			if ($this->settings[$setting]) { // Only return the setting if it evaluates to true
				return $this->settings[$setting];
			}
		} else {
			/* Check if there's a fall back, if the setting doesn't exist; eg a default value */
			switch ($setting) {
				case "dateFormat":
					return "d/m/Y";
					break;

				default:
					return false;
			}

			return false;
		}
		// </editor-fold>
	}
	
	/**
	 * Convert PHP to JavaScript Date Format
	 * 
	 * @param string $php_format
	 * @return string
	 */
	public static function convertPHPToJSDateFormat($php_format) {
		// <editor-fold defaultstate="collapsed" desc="Convert PHP to JS Date Format">
		$PHP_matching_JS = array(
				// Day
				'd' => 'dd',
				'D' => 'D',
				'j' => 'd',
				'l' => 'DD',
				'N' => '',
				'S' => '',
				'w' => '',
				'z' => 'o',
				// Week
				'W' => '',
				// Month
				'F' => 'MM',
				'm' => 'mm',
				'M' => 'M',
				'n' => 'm',
				't' => '',
				// Year
				'L' => '',
				'o' => '',
				'Y' => 'yy',
				'y' => 'y',
				// Time
				'a' => '',
				'A' => '',
				'B' => '',
				'g' => '',
				'G' => '',
				'h' => '',
				'H' => '',
				'i' => '',
				's' => '',
				'u' => ''
		);

		$js_format = "";
		$escaping = false;

		for($i = 0; $i < strlen($php_format); $i++)
		{
			$char = $php_format[$i];
			if($char === '\\') // PHP date format escaping character
			{
				$i++;
				if($escaping) $js_format .= $php_format[$i];
				else $js_format .= '\'' . $php_format[$i];
				$escaping = true;
			}
			else
			{
				if($escaping) { $js_format .= "'"; $escaping = false; }
				if(isset($PHP_matching_JS[$char]))
					$js_format .= $PHP_matching_JS[$char];
				else
				{
					$js_format .= $char;
				}
			}
		}

		return $js_format;
		// </editor-fold>
	}
}
?>