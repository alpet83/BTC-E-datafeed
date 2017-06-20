-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Июн 19 2017 г., 19:30
-- Версия сервера: 5.7.17
-- Версия PHP: 5.6.30-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `depth_history`
--

-- --------------------------------------------------------

--
-- Структура таблицы `pair_groups`
--

CREATE TABLE `pair_groups` (
  `pair` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `grp` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `pair_groups`
--

INSERT INTO `pair_groups` (`pair`, `grp`) VALUES
('btc_rur', 0),
('btc_usd', 0),
('dsh_btc', 0),
('dsh_usd', 0),
('eth_btc', 1),
('eth_rur', 1),
('eth_usd', 1),
('ltc_btc', 1),
('ltc_usd', 1),
('nmc_btc', 2),
('nmc_usd', 2),
('nvc_btc', 2),
('nvc_usd', 2),
('ppc_btc', 2),
('ppc_usd', 2),
('usd_rur', 2);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `pair_groups`
--
ALTER TABLE `pair_groups`
  ADD PRIMARY KEY (`pair`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
