-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 06. Feb 2012 um 15:19
-- Server Version: 5.1.58
-- PHP-Version: 5.3.6-13ubuntu3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `piwk`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Authentication`
--

CREATE TABLE IF NOT EXISTS `Authentication` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `objectId` varchar(128) NOT NULL DEFAULT 'no objectId',
  `accessToken` varchar(128) DEFAULT 'no applicationSecret',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Campaign`
--

CREATE TABLE IF NOT EXISTS `Campaign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(64) NOT NULL,
  `startDate` varchar(64) NOT NULL,
  `endDate` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Data`
--

CREATE TABLE IF NOT EXISTS `Data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sourceId` int(11) unsigned NOT NULL DEFAULT '0',
  `metricId` int(11) unsigned NOT NULL DEFAULT '0',
  `value` varchar(264) NOT NULL DEFAULT 'no value',
  `timestamp` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Event`
--

CREATE TABLE IF NOT EXISTS `Event` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sourceTypeId` int(11) NOT NULL DEFAULT '0',
  `label` varchar(64) NOT NULL DEFAULT 'no label',
  `startDate` varchar(64) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `endDate` varchar(64) DEFAULT NULL,
  `campaignId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Metric`
--

CREATE TABLE IF NOT EXISTS `Metric` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `metricGroupId` int(11) NOT NULL DEFAULT '0',
  `metricKey` varchar(255) DEFAULT NULL,
  `label` varchar(64) NOT NULL DEFAULT 'no label',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=91 ;

--
-- Daten für Tabelle `Metric`
--

INSERT INTO `Metric` (`id`, `metricGroupId`, `metricKey`, `label`) VALUES
(1, 1, 'page_fan_adds', 'Daily Page Likes'),
(2, 2, 'page_discussions', 'Daily Page Discussions'),
(3, 3, 'de_DE', 'Deutsch'),
(4, 3, 'en_US', 'Amerikanisch'),
(5, 3, 'en_GB', 'Englisch'),
(6, 4, 'name', 'Twitter Name'),
(7, 5, 'screen_name', 'Twitter Screen Name'),
(8, 8, 'friends_count', 'Twitter Follower'),
(10, 14, 'page_fan_removes', 'Page Metric'),
(11, 15, 'page_comment_adds', 'Page Metric'),
(12, 16, 'page_like_adds', 'Page Metric'),
(13, 17, 'page_like_removes', 'Page Metric'),
(14, 18, 'page_wall_posts', 'Page Metric'),
(15, 19, 'page_views', 'Page Metric'),
(16, 20, 'page_views_internal_referrals', 'Page Metric'),
(17, 21, 'page_views_external_referrals', 'Page Metric'),
(18, 22, 'page_fans', 'Page Metric'),
(19, 23, 'F', 'Page Metric'),
(20, 23, 'M', 'Page Metric'),
(21, 23, 'U', 'Page Metric'),
(22, 32, 'AT', 'Page Metric'),
(23, 32, 'DE', 'Page Metric'),
(24, 32, 'BG', 'Page Metric'),
(26, 30, 'opens', 'Mailchimp Metric'),
(27, 32, 'IT', 'Page Metric'),
(28, 32, 'GB', 'Page Metric'),
(29, 3, 'bg_BG', 'Belgien'),
(30, 33, '13-17', 'Page Metric'),
(31, 33, '18-24', 'no label'),
(32, 33, '25-34', 'no label'),
(33, 33, '35-44', 'no label'),
(34, 33, '45-54', 'no label'),
(35, 33, '55+', 'no label'),
(36, 34, 'hard_bounces', 'Mailchimp Metric'),
(37, 35, 'soft_bounces', 'Mailchimp Metric'),
(38, 36, 'emails_sent', 'Mailchimp Metric'),
(39, 37, 'clicks', 'Mailchimp Metric'),
(40, 38, 'forwards', 'Mailchimp Metric'),
(41, 39, 'unsubscribes', 'Mailchimp Metric'),
(42, 40, 'users_who_clicked', 'Mailchimp Metric'),
(44, 9, 'page_active_users', 'Aktive Facebook Page User pro Tag'),
(45, 42, 'page_active_users_week', 'Aktive Facebook Page User pro Woche'),
(46, 43, 'page_active_users_month', 'Aktive Facebook Page User pro Monat'),
(47, 45, 'friends', 'Twitter friends '),
(48, 46, 'followers', 'Twitter Metric'),
(49, 47, 'updates', 'Twitter Metric'),
(50, 48, 'favorites', 'Twitter Metric'),
(51, 49, 'engagement', 'Calculated Metric'),
(52, 50, 'page_fan_adds_unique', 'Facebook Metric'),
(53, 51, 'page_fan_removes_unique', 'Facebook Metric'),
(54, 52, 'page_comment_adds_unique', 'Facebook Metric'),
(55, 53, 'page_like_adds_unique', 'Facebook Metric'),
(56, 54, 'page_like_removes_unique', 'Facebook Metric'),
(57, 55, 'page_wall_posts_unqiue', 'Facebook Metric'),
(58, 56, 'page_views_unique', 'Facebook Metric'),
(65, 63, 'unique_opens', 'Facebook Metric'),
(66, 64, 'unique_clicks', 'Facebook Metric'),
(67, 65, 'page_comment_removes', 'Facebook Metric'),
(68, 66, 'page_comment_removes_unique', 'Facebook Metric'),
(69, 2, 'page_discussions', 'Facebook Metric'),
(70, 68, 'page_discussions_unique', 'Facebook Metric'),
(71, 69, 'page_photos', 'Facebook Metric'),
(72, 70, 'page_photos_unique', 'Facebook Metric'),
(73, 71, 'page_videos', 'Facebook Metric'),
(74, 72, 'page_videos_unique', 'Facebook Metric'),
(76, 73, 'vienna', 'no label'),
(77, 73, 'munich', 'no label'),
(78, 73, 'nuremberg', 'no label'),
(79, 73, 'dusseldorf', 'no label'),
(81, 80, 'circulation', 'Feedburner circulation'),
(82, 81, 'hits', 'Feedburner hits'),
(83, 82, 'reach', 'Feedburner reach'),
(84, 83, 'page_wall_posts_unique', 'Facebook Page'),
(86, 85, 'feederAllPosts', 'Feeeder All Posts'),
(87, 86, 'feederNegativePosts', 'Feeeder Negative Posts'),
(88, 87, 'feederPositivePosts', 'Feeeder Positive Posts'),
(89, 90, 'subscriberGer', 'Mailchimp Subscriber Website German'),
(90, 91, 'subscriberEng', 'Mailchimp Subscriber Website English');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `MetricGroup`
--

CREATE TABLE IF NOT EXISTS `MetricGroup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sourceTypeId` int(11) NOT NULL DEFAULT '0',
  `metricGroupKey` varchar(255) DEFAULT NULL,
  `label` varchar(64) NOT NULL DEFAULT 'no label',
  `type` varchar(20) NOT NULL DEFAULT 'value',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=92 ;

--
-- Daten für Tabelle `MetricGroup`
--

INSERT INTO `MetricGroup` (`id`, `sourceTypeId`, `metricGroupKey`, `label`, `type`) VALUES
(1, 1, 'page_fan_adds', 'Daily Page Likes', 'value'),
(2, 1, 'page_discussions', 'Daily Page Discussions', 'value'),
(3, 1, 'page_fans_locale', 'Language of Facebook Likes', 'multivalue'),
(4, 3, 'name', 'Echter Name', 'value'),
(5, 3, 'screen_name', 'Twittername', 'value'),
(8, 3, 'friends_count', 'Twitter Follower', 'value'),
(9, 1, 'page_active_users', 'blabla', 'value'),
(14, 1, 'page_fan_removes', 'Page Metric', 'value'),
(15, 1, 'page_comment_adds', 'Page Metric', 'value'),
(16, 1, 'page_like_adds', 'Page Metric', 'value'),
(17, 1, 'page_like_removes', 'Page Metric', 'value'),
(18, 1, 'page_wall_posts', 'Page Metric', 'value'),
(19, 1, 'page_views', 'Page Metric', 'value'),
(20, 1, 'page_views_internal_referrals', 'Page Metric', 'value'),
(21, 1, 'page_views_external_referrals', 'Page Metric', 'value'),
(22, 1, 'page_fans', 'Page Metric', 'value'),
(23, 1, 'page_fans_gender', 'Page Metric', 'multivalue'),
(30, 4, 'opens', 'Page Metric', 'value'),
(32, 1, 'page_fans_country', 'Page Metric', 'multivalue'),
(33, 1, 'page_fans_age', 'Page Metric', 'multivalue'),
(34, 4, 'hard_bounces', 'Page Metric', 'value'),
(35, 4, 'soft_bounces', 'Page Metric', 'value'),
(36, 4, 'emails_sent', 'Page Metric', 'value'),
(37, 4, 'clicks', 'Page Metric', 'value'),
(38, 4, 'forwards', 'Page Metric', 'value'),
(39, 4, 'unsubscribes', 'Page Metric', 'value'),
(40, 4, 'users_who_clicked', 'Page Metric', 'value'),
(42, 1, 'page_active_users_week', 'Aktive Facebook Page User pro Woche', 'value'),
(43, 1, 'page_active_users_month', 'Aktive Facebook Page User pro Monat', 'value'),
(45, 3, 'friends', 'Twitter Friends', 'value'),
(46, 3, 'followers', 'Twitter Metric', 'value'),
(47, 3, 'updates', 'Twitter Metric', 'value'),
(48, 3, 'favorites', 'Twitter Metric', 'value'),
(49, 5, 'engagement', 'Calculated Metric', 'value'),
(50, 1, 'page_fan_adds_unique', 'Facebook Metric', 'value'),
(51, 1, 'page_fan_removes_unique', 'Facebook Metric', 'value'),
(52, 1, 'page_comment_adds_unique', 'Facebook Metric', 'value'),
(53, 1, 'page_like_adds_unique', 'Facebook Metric', 'value'),
(54, 1, 'page_like_removes_unique', 'Facebook Metric', 'value'),
(55, 1, 'page_wall_posts_unqiue', 'Facebook Metric', 'value'),
(56, 1, 'page_views_unique', 'Facebook Metric', 'value'),
(57, 1, 'page_fans_gender_age', 'Facebook Metric', 'multivalue'),
(58, 1, 'page_active_users_city', 'Facebook Metric', 'multivalue'),
(59, 1, 'page_active_users_country', 'Facebook Metric', 'multivalue'),
(60, 1, 'page_active_users_gender', 'Facebook Metric', 'multivalue'),
(61, 1, 'page_active_users_age', 'Facebook Metric', 'multivalue'),
(62, 1, 'page_active_users_gender_age', 'Facebook Metric', 'multivalue'),
(63, 4, 'unique_opens', 'Facebook Metric', 'value'),
(64, 4, 'unique_clicks', 'Facebook Metric', 'value'),
(65, 1, 'page_comment_removes', 'Facebook Metric', 'value'),
(66, 1, 'page_comment_removes_unique', 'Facebook Metric', 'value'),
(67, 1, 'page_discussions', 'Facebook Metric', 'value'),
(68, 1, 'page_discussions_unique', 'Facebook Metric', 'value'),
(69, 1, 'page_photos', 'Facebook Metric', 'value'),
(70, 1, 'page_photos_unique', 'Facebook Metric', 'value'),
(71, 1, 'page_videos', 'Facebook Metric', 'value'),
(72, 1, 'page_videos_unique', 'Facebook Metric', 'value'),
(73, 1, 'page_fans_city', 'Facebook Metric', 'multivalue'),
(80, 7, 'circulation', 'Feedburner circulation', 'value'),
(81, 7, 'hits', 'Feedburner hits', 'value'),
(82, 7, 'reach', 'Feedburner reach', 'value'),
(83, 1, 'page_wall_posts_unique', 'Facebook Page', 'value'),
(85, 8, 'feederAllPosts', 'Feeeder All Posts', 'value'),
(86, 8, 'feederNegativePosts', 'Feeeder Negative Posts', 'value'),
(87, 8, 'feederPositivePosts', 'Feeeder Positive Posts', 'value'),
(88, 4, 'urlClicks', 'Anzahl an Klicks auf URLs im Newsletter.', 'multivalue'),
(89, 4, 'urlUniqueClicks', 'Anzahl an Klicks auf URLs im Newsletter.', 'multivalue'),
(90, 4, 'subscriberGer', 'Mailchimp Subscriber Website German', 'value'),
(91, 4, 'subscriberEng', 'Mailchimp Subscriber Website Englisch', 'value');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Source`
--

CREATE TABLE IF NOT EXISTS `Source` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sourceTypeId` int(11) unsigned NOT NULL DEFAULT '0',
  `authId` int(11) NOT NULL DEFAULT '0',
  `label` varchar(64) NOT NULL DEFAULT 'no label',
  `intervall` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `lastDate` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Daten für Tabelle `Source`
--

INSERT INTO `Source` (`id`, `sourceTypeId`, `authId`, `label`, `intervall`, `active`, `lastDate`) VALUES
(3, 3, 7, 'Twittter', 2, 0, '0000-00-00'),
(5, 1, 1, 'Facebook Beispielunternehmen', 24, 1, '0000-00-00'),
(6, 4, 2, 'Mailchimp', 12, 0, '0000-00-00'),
(7, 5, 8, 'Calculated Metrics', 0, 0, '0000-00-00')

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `SourceType`
--

CREATE TABLE IF NOT EXISTS `SourceType` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL DEFAULT 'no key',
  `label` varchar(64) NOT NULL DEFAULT 'no label',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Daten für Tabelle `SourceType`
--

INSERT INTO `SourceType` (`id`, `key`, `label`) VALUES
(1, 'Facebook', 'Facebook'),
(2, 'Dummy', 'Dummy'),
(3, 'Twitter', 'Twitter'),
(4, 'Mailchimp', 'Mailchimp Newsletter'),
(5, 'Calculated', 'Calculated Metrics'),
(6, 'Tv', 'Television Spots'),
(7, 'Feedburner', 'Google Feedburner'),
(8, 'Feeder', 'Feeder Source');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
