<?php
/**
 * This page outputs the captcha image.
 * 
 * @author Edward McKnight (EM-Creations.co.uk)
 */
require(__DIR__ . "/captcha.class.php");

if (isset($_GET['c'])) {
	// Settings
	if (isset($_GET['bck'])) {
		$bck = $_GET['bck'];
	} else {
		$bck = "white";
	}

	if (isset($_GET['bor'])) {
		$border = (int) $_GET['bor'];
	} else {
		$border = false;
	}

	if (isset($_GET['exp'])) {
		$expires = (int) $_GET['exp'];
	} else {
		$expires = 5;
	}
	
	if (isset($_GET['text'])) {
		$questionText = strip_tags(htmlentities($_GET['text']));
	} else {
		$questionText = "Type the characters:";
	}

	$captcha = new Captcha($_GET['c']);
	$captcha->setOpts($bck, $border, $expires, $questionText);

	$captcha->show();
}
?>