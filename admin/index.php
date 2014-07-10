<?php
require(__DIR__ . "/../cms/global.inc.php");

if (!$_currUser->checkPermission("basicAdmin")) { // If this user does not have the basic admin permission, redirect them
	header("Location: ../");
	exit;
}

include(__DIR__ . "/header.inc.php");

include(__DIR__ . "/../cms/pages/admin." . $page . ".inc.php");

include(__DIR__ . "/footer.inc.php");
?>
