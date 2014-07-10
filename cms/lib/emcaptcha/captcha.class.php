<?php
/**
 * EM Captcha Class
 * 
 * A simple and easy to implement visual captcha class by Edward McKnight (EM-Creations.co.uk)
 * 
 * PHP extension / library requirements: SQLite3 and GD (Version 2.0)
 *
 * @author Edward McKnight (EM-Creations.co.uk)
 * @version 1.1 BETA
 * @link http://www.em-creations.co.uk/?p=303
 * @copyright Copyright (c) 2013, Edward McKnight
 * @example http://www.em-creations.co.uk/?p=303
 * @license http://creativecommons.org/licenses/by-sa/3.0/ Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)
 */
class Captcha {
	// Set up private variables
	private $captcha = false;
	private $backgroundColour = false;
	private $border = false;
	private $storeType = "sqlite"; /* Currently the only store type option is sqlite */
	private $connection = false;
	private $expires = false;
	private $expiry = false;
	private $hash = false;
	private $id = false;
	private $questionText = false;

	/**
	 * Creates a new captcha object
	 * 
	 * @param int $id optional an ID must be provided when validating captcha input
	 */
	public function __construct($id = false) {
		// <editor-fold defaultstate="collapsed" desc="Construct Code">
		$this->id = $id;

		$this->hash = sha1($_SERVER['HTTP_USER_AGENT']); // Create a hash, helping to determine if this is the same user or not by hashing the user's user agent

		if ($this->storeType == "sqlite") { // If we're using SQLite
			$this->connection = new SQLite3(__DIR__ . "/db.sql"); // Try to open an SQLite connection
			// Create captcha table, if it doesn't already exist
			$this->connection->exec("CREATE TABLE IF NOT EXISTS `captcha` (
				    `captcha` varchar(255) NOT NULL,
				    `hash` char(40) NOT NULL,
				    `expires` int(11) NOT NULL
				  );");
		}
		// </editor-fold>
	}

	/**
	 * Set captcha options
	 * 
	 * @param string $backgroundColour optional, default "white"
	 * @param boolean $border optional, default false
	 * @param int $expires optional, default 5 minutes
	 * @param string $questionText optional, default "Type the characters:"
	 * @since Version 1.0
	 */
	public function setOpts($backgroundColour = "white", $border = false, $expires = 5, $questionText = "Type the characters:") {
		// <editor-fold defaultstate="collapsed" desc="Set Options Code">
		$this->backgroundColour = $backgroundColour;
		$this->border = $border;
		$this->expires = $expires; // We need to store the given expiry time also, so we can use it in the image tag, which calls captcha_show.php
		$this->expiry = time() + ($expires * 60); // Expiry time will be the time now, plus the given expiry time in seconds
		$this->questionText = $questionText; // Set the question text to be used on the captcha input field
		// </editor-fold>
	}

	/**
	 * Output captcha image and text field, call this method nested in a form
	 * 
	 * @param string $path Path to this directory, if none is provided __DIR__ will be used
	 * @since Version 1.0
	 */
	public function output($path = false) {
		// <editor-fold defaultstate="collapsed" desc="Output Captcha Input Fields Code">
		$this->captcha = $this->generateCaptcha(); // Generate a new captcha string

		if (!$path) { // If the path is not given use __DIR__ as the default path
			$path = __DIR__;
		}

		// Insert captcha row
		$this->connection->exec("INSERT INTO `captcha` ( 
				`captcha`, 
				`hash`, 
				`expires`
				) VALUES ( 
				'" . $this->captcha . "',
				'" . $this->hash . "',
				" . $this->expiry . " 
				)");

		$id = $this->connection->lastInsertRowID(); // Get this captcha's ID

		print("<input type=\"hidden\" name=\"emCaptchaID\" id=\"emCaptchaID\" value=\"" . $id . "\"/>\n"); // Output a hidden field, accessible as $_POST['emCaptchaID'], containing this captcha's ID
		print("<img src=\"" . $path . "/captcha_show.php?c=" . $id . "&bck=" . $this->backgroundColour . "&bor=" . (($this->border) ? "1" : "0") . "&exp=" . $this->expires . "&text=" . $this->questionText . "\" alt=\"Captcha Image\" /><br />\n"); // Output the captcha image
		print($this->questionText . "<br /><input type=\"text\" name=\"emCaptcha\" id=\"emCaptcha\" />\n"); // Output input field to typing in the characters
		// </editor-fold>
	}

	/**
	 * Generate new captcha string
	 * 
	 * @return string $captchaString
	 * @since Version 1.0
	 */
	public function generateCaptcha() {
		// <editor-fold defaultstate="collapsed" desc="Generate Captcha String Code">
		// Create an array of letters
		$letters = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		$length = 8; // Specify captcha length, a higher length may cause display issues
		$captcha = "";

		// Generate a captcha string
		for ($i = 0; $i < $length; $i++) {
			if (rand(0, 1) == 1) { // Select a letter
				$captcha .= $letters[rand(0, (count($letters) - 1))]; // Get a random letter from the letters array
			} else { // Select a number
				$captcha .= rand(0, 9); // Get a random number between 0 and 9
			}
		}
		
		return $captcha;
		// </editor-fold>
	}

	/**
	 * Return whether the captcha was valid or not
	 * 
	 * @return boolean $valid Whether the captcha was valid or not
	 * @since Version 1.0
	 */
	public function isValid() {
		// <editor-fold defaultstate="collapsed" desc="Validate Captcha Code">
		$captcha = $this->connection->escapeString($_POST['emCaptcha']);

		$result = $this->connection->query("SELECT `captcha` FROM `captcha` WHERE `hash` = '" . $this->hash . "' AND `expires` > " . time() . " AND `ROWID` = " . $this->id);

		if ($result) {
			if ($result->numColumns()) {
				while ($row = $result->fetchArray()) {
					if (strcasecmp($captcha, $row['captcha']) == 0) { // If the captchas match
						$this->connection->exec("DELETE FROM `captcha` WHERE `hash` = '" . $this->hash . "'"); // Delete captcha entries for this user
						return true;
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
		// </editor-fold>
	}

	/**
	 * Output the captcha image
	 * 
	 * @param int $width optional width of image
	 * @param int $height optional height of image
	 * @since Version 1.0
	 */
	public function show($width = 230, $height = 75) {
		// <editor-fold defaultstate="collapsed" desc="Captcha Image Code">
		if ($this->id) { // If a captcha ID is set
			// Get the captcha string for this id
			$query = $this->connection->query("SELECT `captcha` FROM `captcha` WHERE `ROWID` = " . $this->id);
			$result = $query->fetchArray();
			$this->captcha = $result['captcha'];

			header("Content-Type: image/png"); // Set the header content type

			$image = imagecreatetruecolor($width, $height); // Create a new image
			
			// Colours
			$colourWhite = imagecolorallocate($image, 255, 255, 255);
			$colourBlack = imagecolorallocate($image, 0, 0, 0);
			$colourRed = imagecolorallocate($image, 176, 23, 31);
			$colourBlue = imagecolorallocate($image, 0, 0, 255);
			$colourGreen = imagecolorallocate($image, 0, 139, 69);
			$colourOrange = imagecolorallocate($image, 205, 133, 0);
			$colourGrey = imagecolorallocate($image, 102, 102, 102);
			$colourPink = imagecolorallocate($image, 238, 0, 238);
			$colourPurple = imagecolorallocate($image, 85, 26, 139);
			$colours = array($colourRed, $colourBlue, $colourGreen, $colourOrange, $colourGrey, $colourPink, $colourPurple); // Set up an array of colours
			
			// Background colour
			switch ($this->backgroundColour) {
				case "#FFFFFF":
				case "white":
					$backgroundColour = $colourWhite;
					$borderColour = $colourBlack;
					$colours[] = $colourBlack;
					break;

				case "#000000":
				case "black":
					$backgroundColour = $colourBlack;
					$borderColour = $colourWhite;
					$colours[] = $colourWhite;
					break;

				default: // Default to white
					$backgroundColour = $colourWhite;
					$borderColour = $colourBlack;
					$colours[] = $colourBlack;
					break;
			}

			// Shape Colours
			$shapeBlue = imagecolorallocate($image, 135, 206, 250);
			$shapeGreen = imagecolorallocate($image, 0, 255, 127);
			$shapeBeige = imagecolorallocate($image, 205, 179, 139);
			$shapeGrey = imagecolorallocate($image, 205, 201, 201);
			$shapeColours = array($shapeBlue, $shapeGreen, $shapeBeige, $shapeGrey); // Set up an array of shape colours
			
			// Background
			imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColour);

			// <editor-fold defaultstate="collapsed" desc="Background Distortion Code">
			// Background lines
			for ($i = 0; $i < 100; $i++) {
				$colour = $shapeColours[rand(0, (count($shapeColours) - 1))]; // Choose a random colour from the shape colours array
				$sizeX = rand(20, ($width / 2) + (($width / 2) / 2)); // The maximum width can be three quaters of the image
				$sizeY = rand(20, ($height / 2) + (($height / 2) / 2)); // The maximum height can be three quaters of the image
				$posX = rand(1, ($width - 1)); // Starting x position
				$posY = rand(1, ($height - 1)); // Starting y position
				imageline($image, $posX, $posY, ($posX + $sizeX), ($posY + $sizeY), $colour); // Draw the line
			}

			$shapesNum = rand(3, 7); // Random number of shapes between 3 and 7

			for ($i = 0; $i < $shapesNum; $i++) {
				$shape = rand(0, 1); // Select random which shape we're going to draw
				$colour = $shapeColours[rand(0, (count($shapeColours) - 1))]; // Choose a random colour from the shape colours array

				switch ($shape) {
					case 0:
						$sizeX = rand(5, 40); // The maximum width can be 40 pixels
						$sizeY = rand(5, 40); // The maximum height can be 40 pixels
						$posX = rand(1, ($width - 1)); // Starting x position
						$posY = rand(1, ($height - 1)); // Starting y position
						imagefilledrectangle($image, $posX, $posY, ($posX + $sizeX), ($posY + $sizeY), $colour); // Draw the rectangle
						break;

					case 1:
						$sizeX = rand(5, 40); // The maximum width can be 40 pixels
						$sizeY = rand(5, 40); // The maximum height can be 40 pixels
						$posX = rand(1, ($width - 1)); // Starting x position
						$posY = rand(1, ($height - 1)); // Starting y position
						imagefilledarc($image, $posX, $posY, $sizeX, $sizeY, 0, 360, $colour, IMG_ARC_PIE); // Draw the arc / circle
						break;
				}
			}
			// </editor-fold>
			
			// Border
			if ($this->border) { // If the user wants the border to be displayed
				imagerectangle($image, 0, 0, ($width - 1), ($height - 1), $borderColour);
			}

			// Captcha characters
			$offsetX = 10;
			$offsetY = 30;
			$characters = str_split($this->captcha); // Get the captcha string as an array of characters
			$fonts = array("LimeLight.ttf", "SpecialElite.ttf", "3Dumb.ttf"); // Array of fonts, in the ./fonts directory, credits to the font creators :)

			foreach ($characters as $char) {
				// <editor-fold defaultstate="collapsed" desc="Output Characters Code">
				$diffX = rand(25, 30);
				$diffY = rand(-10, 10);

				$rand = rand(0, (count($colours) - 1));
				$colour = $colours[$rand]; // Get a random colour from the colours array
				// Output character, selecting a random font from the fonts array
				imagettftext($image, rand(18, 22), 0, $offsetX, $offsetY, $colour, "./fonts/" . $fonts[rand(0, (count($fonts) - 1))], $char);

				// If the character is close to the edge of the image, x axis
				if ($offsetX >= ($width - 2)) {
					$offsetX = ($width - 2);
				}

				// If the character is close to the edge of the image, y axis
				if ($offsetY >= ($height - 35)) {
					$offsetY = ($height - 35);
				} else if ($offsetY <= 30) {
					$offsetY = 30;
				}

				// Set position for the next character
				$offsetX += $diffX;
				$offsetY += $diffY;
				// </editor-fold>
			}

			imagepng($image); // Create the image, png format
			imagedestroy($image); // Destroy the image, at this point it has already been output
		} else { // If a captcha ID is not set
			return false;
		}
		// </editor-fold>
	}
}
?>