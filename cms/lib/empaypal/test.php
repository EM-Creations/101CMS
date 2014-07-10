<?php
ini_set("display_errors", "on"); error_reporting(E_ALL); // Turn on errror reporting
// PHP Class testing file

require(__DIR__ . "/PayPal.class.php");

//$paypal = new PayPal("eddy.mcknight@gmail.com", "R4bb0ts321", "123", "nvp", true);

$paypal = new PayPal("sdk-three_api1.sdk.com", "QFZCWN5HZM8VBG7Q", "A-IzJhZZjhg29XQ2qnhapuwxIDzyAZQ92FRP5dqBzVesOkzbdUONzmOU", "nvp", true); // Test credentials

$paypal->test();

?>
