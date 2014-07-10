<?php
require(__DIR__ . "/../cms/dbConfig.php");

print("<h3>Updating database..</h3><br />\n");


$queries = array();

// Add to queries array here
$queries[] = "CREATE TABLE IF NOT EXISTS `ranks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$queries[] = "ALTER TABLE  `users` CHANGE  `dob`  `dob` INT( 11 ) NOT NULL";

$queries[] = "CREATE TABLE IF NOT EXISTS `bans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) NOT NULL,
  `type` enum('temp','perm') NOT NULL,
  `super_ban` tinyint(1) NOT NULL,
  `expires` int(11) NOT NULL,
  `stamp` int(11) NOT NULL,
  `enabled` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB;";

// Roster Tables
$queries[] = "CREATE TABLE IF NOT EXISTS `rosters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";

$queries[] = "CREATE TABLE IF NOT EXISTS `roster_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) NOT NULL,
  `roster` bigint(20) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";

$queries[] = "CREATE TABLE IF NOT EXISTS `admin_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin` bigint(20) NOT NULL,
  `system` varchar(255) NOT NULL,
  `action` text NOT NULL,
  `stamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

$queries[] = "CREATE TABLE IF NOT EXISTS `tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sesid` varchar(255) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `agent` text NOT NULL,
  `token` varchar(128) NOT NULL,
  `form` varchar(255) NOT NULL,
  `stamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

$queries[] = "CREATE TABLE IF NOT EXISTS `polls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `lock_type` enum('rank','permission','none') NOT NULL,
  `lock` varchar(255) NOT NULL,
  `expires` int(11) NOT NULL,
  `answers` text NOT NULL,
  `multiple_answers` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `stamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$queries[] = "CREATE TABLE IF NOT EXISTS `polls_votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `poll` bigint(20) NOT NULL,
  `user` bigint(20) NOT NULL,
  `answer` varchar(255) NOT NULL,
  `stamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$queries[] = "CREATE TABLE IF NOT EXISTS `resetpass_tokens` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `token` varchar(255) NOT NULL,
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;
";

$queries[] = "ALTER TABLE  `pages` CHANGE  `object`  `object` BIGINT NOT NULL";

$queries[] = "ALTER TABLE  `pages` ADD  `link` VARCHAR( 255 ) NOT NULL AFTER  `content` ,
ADD  `object_type` ENUM(  'poll',  'roster',  'news',  'none' ) NOT NULL AFTER  `link`";

$queries[] = "ALTER TABLE  `users` CHANGE  `country`  `country` VARCHAR( 2 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";

$i = 1;
foreach ($queries as $query) {
	print("<strong>Executing Query $i of " . count($queries) . ":</strong> " . $query . "<br />\n");

	$_mysql->query($query);

	if ($_mysql->error) { // If there was a mysql error
		print("<strong>Error:</strong> " . $_mysql->error . "<br />\n");
	}
	print("<br /><br />");
	$i++;
}
?>
