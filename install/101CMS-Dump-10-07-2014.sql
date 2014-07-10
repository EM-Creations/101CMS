-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 10, 2014 at 01:13 PM
-- Server version: 5.5.25a
-- PHP Version: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `101cms`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_users`
--

CREATE TABLE IF NOT EXISTS `active_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `expires` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_log`
--

CREATE TABLE IF NOT EXISTS `admin_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin` bigint(20) NOT NULL,
  `system` varchar(255) NOT NULL,
  `action` text NOT NULL,
  `stamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE IF NOT EXISTS `bans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) NOT NULL,
  `type` enum('temp','perm') NOT NULL,
  `super_ban` tinyint(1) NOT NULL,
  `expires` int(11) NOT NULL,
  `stamp` int(11) NOT NULL,
  `enabled` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `captcha`
--

CREATE TABLE IF NOT EXISTS `captcha` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `captcha` varchar(255) NOT NULL,
  `hash` char(40) NOT NULL,
  `expires` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `meta_tags`
--

CREATE TABLE IF NOT EXISTS `meta_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `page` varchar(255) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `meta_tags`
--

INSERT INTO `meta_tags` (`id`, `name`, `content`, `page`, `enabled`) VALUES
(1, 'description', '101st Clan CMS System - Demo', '', 1),
(3, 'keywords', 'awesome, fun, cms', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `link` varchar(255) NOT NULL,
  `object_type` enum('poll','roster','news','none') NOT NULL,
  `object` bigint(20) NOT NULL,
  `lock_type` enum('rank','permission','none') NOT NULL,
  `lock` varchar(255) NOT NULL,
  `menu` tinyint(1) NOT NULL,
  `menu_order` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `created_on` int(11) NOT NULL,
  `last_updated` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `name`, `content`, `link`, `object_type`, `object`, `lock_type`, `lock`, `menu`, `menu_order`, `enabled`, `created_on`, `last_updated`) VALUES
(1, 'Home', '<h2 style="text-align: center; ">\r\n	Welcome to the 101st Division Clan Website!</h2>\r\n<p style="text-align: center; ">\r\n	&nbsp;</p>\r\n<p style="text-align: center; ">\r\n	To find out more about us please visit our <a href="./?p=About Us">About Us</a> page.</p>\r\n', '', 'poll', 0, 'none', '', 1, 1, 1, 1351430783, 1371286494),
(4, 'About Us', '<p style="text-align: center; ">\r\n	<span style="font-size:14px;"><span style="font-family:trebuchet ms,helvetica,sans-serif;"><span style="color:#ff0000;">101st Division</span> is a <strong>multinational gaming clan</strong> that plays a variety of games as well as running and developing game servers and scripts.</span></span></p>\r\n<p style="text-align: center; ">\r\n	<span style="font-size:14px;"><span style="font-family: ''trebuchet ms'', helvetica, sans-serif; ">101st Division was founded on the <strong>17th of January 2011</strong> by co-leaders StatusRed and NINTHTJ, who had a history of playing within other gaming communities together. Since then 101st Division has grown in strength and now has members from various countries including Britain (UK), The Republic of Ireland, the U.S.A, the Netherlands and many more.</span></span></p>\r\n<p style="text-align: center; ">\r\n	<span style="font-size:14px;"><span style="font-family:trebuchet ms,helvetica,sans-serif;">101st Division strives to be an <strong>open clan that welcomes practically anyone</strong> that wants to come along and join us, without strict rules such as compulsory events, eg training sessions; although we do do organised events we don&rsquo;t punish those who cannot, or do not wish to attend.</span></span></p>\r\n<p style="text-align: center; ">\r\n	&nbsp;</p>\r\n<p style="text-align: center; ">\r\n	<span style="font-size:14px;"><span style="font-family:trebuchet ms,helvetica,sans-serif;">Joining 101st Division is an easy and simple process, simply register an account on our website, which you&rsquo;re viewing now and then head over to our forums and register an account there before getting in contact with a Clan Colonel to ask to be added as a member of the clan.</span></span></p>\r\n', '', 'none', 0, 'none', '', 1, 10, 1, 1351430783, 1371402243),
(8, 'Poll', '<style type="text/css">\r\n#qp_main5198 .qp_btna:hover input {background-color:rgb(99,185,255)!important}</style>\r\n<div id="qp_main5198" style="border-radius:6px;border:1px solid black;margin:10px;padding:10px;padding-bottom:12px;background-color:rgb(0,0,0);background-image:URL(http://i91.photobucket.com/albums/k286/pulselayouts/poll/poll_bg005.jpg);background-position:top left;background-repeat:no-repeat;background-image:url(http://i91.photobucket.com/albums/k286/pulselayouts/poll/poll_bg001.jpg);background-size:100%;background-image:none">\r\n	<div style="border-radius:6px;font-family:Arial;font-size:12px;font-weight:bold;background-color:rgb(194,193,193);color:black;width:100%;filter:alpha(opacity:80);-moz-opacity:0.8;opacity:0.8;-khtml-opacity:0.8;margin-bottom:10px">\r\n		<div style="padding:10px">\r\n			Your Opinions on Jack Cruz</div>\r\n	</div>\r\n	<form action="http://www.learnmyself.com/results5198x140AEeF6-1" id="qp_form5198" method="post" style="display:inline;margin:0px;padding:0px" target="_blank">\r\n		<div style="border-radius:6px;background-color:transparent">\r\n			<div style="border-radius:6px;display:block;font-family:Arial;font-size:12px;color:white;padding-top:5px;padding-bottom:5px;clear:both;width:80%">\r\n				<span onclick="var c=this.childNodes[0];c.checked=(c.type==''radio''?true:!c.checked);" style="display:block;padding-left:30px"><input name="qp_v5198" style="float:left;width:25px;margin-left:-25px;margin-top:-1px;padding:0px;height:18px" type="radio" value="1" />Hate him</span></div>\r\n			<div style="border-radius:6px;display:block;font-family:Arial;font-size:12px;color:white;padding-top:5px;padding-bottom:5px;clear:both;width:80%">\r\n				<span onclick="var c=this.childNodes[0];c.checked=(c.type==''radio''?true:!c.checked);" style="display:block;padding-left:30px"><input name="qp_v5198" style="float:left;width:25px;margin-left:-25px;margin-top:-1px;padding:0px;height:18px" type="radio" value="2" />Dont mind him</span></div>\r\n			<div style="border-radius:6px;display:block;font-family:Arial;font-size:12px;color:white;padding-top:5px;padding-bottom:5px;clear:both;width:80%">\r\n				<span onclick="var c=this.childNodes[0];c.checked=(c.type==''radio''?true:!c.checked);" style="display:block;padding-left:30px"><input name="qp_v5198" style="float:left;width:25px;margin-left:-25px;margin-top:-1px;padding:0px;height:18px" type="radio" value="3" />Like Him</span></div>\r\n		</div>\r\n		<div style="padding-top:10px;clear:both">\r\n			<a class="qp_btna" href="#"><input name="qp_b5198" style="width:80px;height:30px;margin-right:5px;border-radius:10px;border:3px solid rgb(16,56,89);background-color:rgb(179,213,242);font-family:Arial;font-size:12px;font-weight:bold;color:rgb(0,0,0);cursor:pointer;cursor:hand" type="submit" value="Vote" /></a></div>\r\n	</form>\r\n</div>\r\n<script src="http://scripts.learnmyself.com/3001/scpolls.js" language="javascript"></script>', '', 'poll', 0, 'rank', '1', 1, 4, 1, 1351430783, 1352239076),
(10, 'EM-Creations', '', 'http://www.EM-Creations.co.uk', 'none', 0, 'none', '', 1, 10, 1, 1351446983, 1371332363),
(12, 'Login', '', './?p=Login', 'none', 0, 'none', '', 1, 1, 1, 1371401979, 1371401979);

-- --------------------------------------------------------

--
-- Table structure for table `polls`
--

CREATE TABLE IF NOT EXISTS `polls` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `polls`
--

INSERT INTO `polls` (`id`, `name`, `lock_type`, `lock`, `expires`, `answers`, `multiple_answers`, `enabled`, `stamp`) VALUES
(6, 'How cool is Eddy?', 'rank', '4', 1340402400, 'a:8:{i:0;s:17:"Super duper cool\r";i:1;s:11:"Super cool\r";i:2;s:5:"Okay\r";i:3;s:9:"Not cool\r";i:4;s:17:"Seriously uncool\r";i:5;s:1:"\r";i:6;s:1:"\r";i:7;s:0:"";}', 1, 1, 1355054183);

-- --------------------------------------------------------

--
-- Table structure for table `polls_votes`
--

CREATE TABLE IF NOT EXISTS `polls_votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `poll` bigint(20) NOT NULL,
  `user` bigint(20) NOT NULL,
  `answer` varchar(255) NOT NULL,
  `stamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ranks`
--

CREATE TABLE IF NOT EXISTS `ranks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `ranks`
--

INSERT INTO `ranks` (`id`, `name`, `level`, `permissions`) VALUES
(3, 'Colonel', 10, 'a:11:{s:10:"basicAdmin";s:1:"0";s:11:"Super_Admin";s:1:"0";s:11:"managePages";s:1:"1";s:11:"manageUsers";s:1:"0";s:8:"viewBans";s:1:"0";s:5:"unban";s:1:"0";s:3:"ban";s:1:"0";s:8:"metaTags";s:1:"1";s:5:"ranks";s:1:"0";s:7:"rosters";s:1:"1";s:5:"polls";s:1:"1";}'),
(4, 'Private', 1, 'a:0:{}');

-- --------------------------------------------------------

--
-- Table structure for table `rosters`
--

CREATE TABLE IF NOT EXISTS `rosters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `rosters`
--

INSERT INTO `rosters` (`id`, `name`) VALUES
(3, 'Founders'),
(5, 'test');

-- --------------------------------------------------------

--
-- Table structure for table `roster_members`
--

CREATE TABLE IF NOT EXISTS `roster_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) NOT NULL,
  `roster` bigint(20) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `roster_members`
--

INSERT INTO `roster_members` (`id`, `user`, `roster`, `order`) VALUES
(5, 5, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `site_info`
--

CREATE TABLE IF NOT EXISTS `site_info` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `secure_url` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `clan_tag` char(10) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `site_info`
--

INSERT INTO `site_info` (`id`, `secure_url`, `url`, `title`, `clan_tag`, `contact_email`, `settings`) VALUES
(1, 'localhost/SVN/101CMS', 'localhost/SVN/101CMS', '101 CMS Demo', '[101]', 'admin@101stdivision.net', 'a:18:{s:10:"AJAX_Login";s:1:"1";s:16:"Generation_Stats";s:1:"1";s:16:"displayPageTitle";s:1:"1";s:19:"allowChangeUserName";s:1:"1";s:12:"roundAvatars";s:1:"1";s:18:"ShowPageLastUpdate";s:1:"1";s:16:"useGZCompression";s:1:"0";s:10:"dateFormat";s:5:"d/m/Y";s:29:"EM-CreationsCaptchaOnRegister";s:1:"1";s:19:"recaptchaOnRegister";s:1:"0";s:18:"recaptchaPublicKey";s:40:"6Le9UsESAAAAAPgy3RYRhWYkCmaKq6iWwQ7x-8xv";s:19:"recaptchaPrivateKey";s:40:"6Le9UsESAAAAACvzP2gokxvIZK6kRjd0a8oTdRBd";s:16:"googlePlusButton";s:1:"1";s:20:"googlePlusButton_URL";s:16:"http://google.ca";s:14:"facebookButton";s:1:"1";s:18:"facebookButton_URL";s:29:"http://www.em-creations.co.uk";s:19:"twitterFollowButton";s:1:"1";s:23:"twitterFollowButton_URL";s:13:"101stDivision";}');

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE IF NOT EXISTS `themes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `version` float NOT NULL,
  `directory` varchar(255) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `name`, `version`, `directory`, `enabled`) VALUES
(1, 'Default', 1, 'default', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE IF NOT EXISTS `tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sesid` varchar(255) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `agent` text NOT NULL,
  `token` varchar(128) NOT NULL,
  `form` varchar(255) NOT NULL,
  `stamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dob` int(11) NOT NULL,
  `country` varchar(2) NOT NULL,
  `permissions` text NOT NULL,
  `rank` int(11) NOT NULL,
  `register_ip` char(15) NOT NULL,
  `last_ip` char(15) NOT NULL,
  `registered` int(11) NOT NULL,
  `last_login` int(11) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `salt`, `password`, `email`, `dob`, `country`, `permissions`, `rank`, `register_ip`, `last_ip`, `registered`, `last_login`, `avatar`) VALUES
(2, 'Falco', 'scotland', '9176e4b8f6d141380a668348370f14715eceee3dda9ad6a7adec1f485f7f1b8143acc03f318fad44bb67f925aed0f0def77c042c01814645576be303459f8d57', 'liam@lol.com', 0, 'Sc', 'a:3:{s:11:"Super_Admin";s:1:"1";s:11:"managePages";s:1:"1";s:11:"manageUsers";s:1:"1";}', 0, '', '82.71.2.63', 1351432138, 1352829658, ''),
(5, 'Eddy', '2D4kY6G5XgUh', '7c437f02ca0f070c1d06c3f6d7c99e4f9cbc7153c282fea153674903e185d7ba9fa740446d8399322ce14edc27a3450cde527160c4c220bce764d0d2e2be41d0', 'eddy@em-creations.co.uk', 735692400, 'GB', 'a:9:{s:10:"basicAdmin";s:1:"1";s:11:"Super_Admin";s:1:"1";s:11:"managePages";s:1:"0";s:11:"manageUsers";s:1:"1";s:8:"viewBans";s:1:"1";s:5:"unban";s:1:"1";s:3:"ban";s:1:"1";s:8:"metaTags";s:1:"0";s:5:"ranks";s:1:"1";}', 3, '127.0.0.1', '127.0.0.1', 1351432138, 1379863880, ''),
(6, 'admin', 'test', 'a48826003e3e161b92538c432a7b33433fac2d0fe262c9fddab42b1a71451747e45167aa0d08012407b9a6842d47a074facc1ba56e8954ed245951c39f07302a', 'admin@admin.com', 0, 'GB', 'a:9:{s:10:"basicAdmin";s:1:"1";s:11:"Super_Admin";s:1:"1";s:11:"managePages";s:1:"0";s:11:"manageUsers";s:1:"1";s:8:"viewBans";s:1:"1";s:5:"unban";s:1:"1";s:3:"ban";s:1:"1";s:8:"metaTags";s:1:"0";s:5:"ranks";s:1:"1";}', 3, '', '127.0.0.1', 0, 1404990658, '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
