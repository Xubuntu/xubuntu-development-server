-- DATABASE FOR THE XUBUNTU STATUS TRACKER

-- Table events
CREATE TABLE `events` (
  `series` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `event` text
);

-- Table history (for the burndown chart)
CREATE TABLE `history` (
  `series` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `items_total` int(11) DEFAULT NULL,
  `items_inprogress` int(11) DEFAULT NULL,
  `items_done` int(11) DEFAULT NULL,
  UNIQUE KEY `series` (`series`,`date`)
);

-- Table meta (cache data)
CREATE TABLE `meta` (
  `meta_key` varchar(50) DEFAULT NULL,
  `meta_value` text,
  UNIQUE KEY `meta_key` (`meta_key`)
);

-- Table series
CREATE TABLE `series` (
  `series` varchar(50) DEFAULT NULL,
  `name` text,
  `blueprint` text,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `active_series` tinyint(1) DEFAULT NULL,
  `default_series` tinyint(1) DEFAULT NULL,
  UNIQUE KEY `series` (`series`)
);

-- Table specs (subspec data)
CREATE TABLE `specs` (
  `spec` varchar(50) DEFAULT NULL,
  `series` varchar(50) DEFAULT NULL,
  `name` text,
  `url` text,
  `whiteboard` text,
  `item_count` int(11) DEFAULT NULL,
  UNIQUE KEY `spec` (`spec`)
);

-- Table status (work item data)
CREATE TABLE `status` (
  `series` varchar(50) DEFAULT NULL,
  `spec` text,
  `description` varchar(255) DEFAULT NULL,
  `nick` varchar(255) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `date_done` date DEFAULT NULL,
  `last_update` int(11) DEFAULT NULL,
  UNIQUE KEY `series` (`series`,`nick`,`description`)
);

-- Table users
CREATE TABLE `users` (
  `nick` varchar(50) DEFAULT NULL,
  `name` text,
  `memberships` text,
  UNIQUE KEY `nick` (`nick`)
);
