-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Сен 05 2010 г., 18:47
-- Версия сервера: 5.1.41
-- Версия PHP: 5.3.2-1ubuntu4.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `sam-o-bot`
--

-- --------------------------------------------------------

--
-- Структура таблицы `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `key` varchar(255) NOT NULL,
  `value` varchar(1024) NOT NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `config`
--

INSERT INTO `config` (`key`, `value`) VALUES
('zhurnal_news_timestamp', '1283697547'),
('zhurnal_news_interval', '1900'),
('zhurnal_news_last_check', '0');

-- --------------------------------------------------------

--
-- Структура таблицы `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `owner` varchar(255) CHARACTER SET utf8 NOT NULL,
  `text` text CHARACTER SET utf8 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `events`
--


-- --------------------------------------------------------

--
-- Структура таблицы `subscriptions`
--

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `owner` varchar(255) CHARACTER SET utf8 NOT NULL,
  `field` smallint(255) NOT NULL,
  `data` varchar(2048) CHARACTER SET utf8 NOT NULL,
  KEY `owner` (`owner`,`field`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `subscriptions`
--


-- --------------------------------------------------------

--
-- Структура таблицы `userdata`
--

CREATE TABLE IF NOT EXISTS `userdata` (
  `owner` varchar(255) NOT NULL,
  `motd_enable` tinyint(1) NOT NULL DEFAULT '1',
  `reserved1` tinyint(1) NOT NULL DEFAULT '0',
  `reserved2` tinyint(1) NOT NULL DEFAULT '1',
  UNIQUE KEY `owner` (`owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `userdata`
--

