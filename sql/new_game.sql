-- 
-- Table structure for table `GAMENAME_bilkos`
-- 

DROP TABLE IF EXISTS `GAMENAME_bilkos`;
CREATE TABLE `GAMENAME_bilkos` (
  `item_id` int(11) NOT NULL auto_increment,
  `item_name` varchar(30) NOT NULL default '',
  `item_code` varchar(30) NOT NULL default '',
  `item_type` int(11) NOT NULL default '0',
  `bidder_id` int(11) NOT NULL default '0',
  `going_price` int(11) NOT NULL default '0',
  `timestamp` int(11) NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '0',
  `descr` text NOT NULL,
  PRIMARY KEY  (`item_id`),
  UNIQUE KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_bmrkt`
-- 

DROP TABLE IF EXISTS `GAMENAME_bmrkt`;
CREATE TABLE `GAMENAME_bmrkt` (
  `bmrkt_id` int(11) NOT NULL auto_increment,
  `location` int(11) NOT NULL default '0',
  `bmrkt_type` tinyint(4) NOT NULL default '0',
  `bm_name` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`bmrkt_id`),
  KEY `planet_id` (`bmrkt_id`),
  KEY `location` (`location`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_clans`
-- 

DROP TABLE IF EXISTS `GAMENAME_clans`;
CREATE TABLE `GAMENAME_clans` (
  `clan_id` int(11) NOT NULL auto_increment,
  `clan_name` varchar(30) NOT NULL default '',
  `passwd` varchar(25) NOT NULL default '',
  `leader_id` int(11) NOT NULL default '0',
  `symbol` char(3) NOT NULL default '',
  `sym_color` varchar(6) NOT NULL default '',
  PRIMARY KEY  (`clan_id`),
  KEY `login_id` (`clan_id`,`clan_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_diary`
-- 

DROP TABLE IF EXISTS `GAMENAME_diary`;
CREATE TABLE `GAMENAME_diary` (
  `entry_id` int(11) NOT NULL auto_increment,
  `timestamp` int(11) NOT NULL default '0',
  `login_id` int(11) NOT NULL default '0',
  `entry` blob NOT NULL default '',
  `topic` blob NOT NULL default '',
  PRIMARY KEY  (`entry_id`),
  UNIQUE KEY `entry_id` (`entry_id`),
  KEY `entry_id_2` (`entry_id`,`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_messages`
-- 

DROP TABLE IF EXISTS `GAMENAME_messages`;
CREATE TABLE `GAMENAME_messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `sender_name` varchar(30) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  `login_id` int(11) NOT NULL default '0',
  `text` blob NOT NULL,
  `sender_id` int(11) NOT NULL default '1',
  `clan_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`message_id`),
  KEY `login_id` (`login_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_news`
-- 

DROP TABLE IF EXISTS `GAMENAME_news`;
CREATE TABLE `GAMENAME_news` (
  `timestamp` int(11) NOT NULL default '0',
  `headline` text NOT NULL,
  `topic_set` set('admin','attacking','bomb','clan','game_status','maint','other','player_status','planet','random_event','ship') NOT NULL,
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_planets`
-- 

DROP TABLE IF EXISTS `GAMENAME_planets`;
CREATE TABLE `GAMENAME_planets` (
  `planet_id` int(11) NOT NULL auto_increment,
  `planet_name` varchar(30) NOT NULL default '',
  `location` int(11) NOT NULL default '0',
  `login_id` int(11) NOT NULL default '0',
  `login_name` varchar(30) NOT NULL default 'Nobody',
  `fighters` int(11) NOT NULL default '20',
  `colon` int(11) NOT NULL default '1000',
  `cash` int(11) NOT NULL default '0',
  `clan_id` int(11) NOT NULL default '-1',
  `metal` int(11) NOT NULL default '0',
  `fuel` int(11) NOT NULL default '0',
  `elect` int(11) NOT NULL default '0',
  `alloc_fight` int(11) NOT NULL default '0',
  `alloc_elect` int(11) NOT NULL default '0',
  `pass` varchar(30) NOT NULL default '0',
  `planet_img` tinyint(4) default NULL,
  `shield_gen` tinyint(4) NOT NULL default '0',
  `shield_charge` int(11) NOT NULL default '0',
  `tech` int(11) NOT NULL default '0',
  `research_fac` tinyint(4) NOT NULL default '0',
  `allocated_to_fleet` int(11) NOT NULL default '0',
  `mining_drones` int(11) NOT NULL default '0',
  `drones_alloc_metal` int(11) NOT NULL default '0',
  `drones_alloc_fuel` int(11) NOT NULL default '0',
  `max_population` int(11) NOT NULL default '0',
  `planet_engaged` int(11) NOT NULL default '0',
  PRIMARY KEY  (`planet_id`),
  KEY `planet_id` (`planet_id`),
  KEY `location` (`location`),
  KEY `login_id` (`login_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_ports`
-- 

DROP TABLE IF EXISTS `GAMENAME_ports`;
CREATE TABLE `GAMENAME_ports` (
  `port_id` int(11) NOT NULL auto_increment,
  `location` int(11) NOT NULL default '0',
  PRIMARY KEY  (`port_id`),
  KEY `planet_id` (`port_id`),
  KEY `location` (`location`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_ships`
-- 

DROP TABLE IF EXISTS `GAMENAME_ships`;
CREATE TABLE `GAMENAME_ships` (
  `ship_id` int(11) NOT NULL auto_increment,
  `ship_name` varchar(20) NOT NULL default 'Un-named',
  `login_id` int(11) NOT NULL default '0',
  `clan_id` int(11) NOT NULL default '-1',
  `location` int(11) NOT NULL default '1',
  `shipclass` int(11) NOT NULL default '0',
  `class_name` varchar(30) NOT NULL default '0',
  `class_name_abbr` varchar(10) NOT NULL default '0',
  `shields` int(11) NOT NULL default '0',
  `max_shields` int(11) NOT NULL default '0',
  `fighters` int(11) NOT NULL default '0',
  `max_fighters` int(11) NOT NULL default '0',
  `armour` int(11) NOT NULL default '0',
  `max_armour` int(11) NOT NULL default '0',
  `cargo_bays` int(11) NOT NULL default '0',
  `fleet_id` tinyint(4) NOT NULL default '1',
  `clan_fleet_id` tinyint(4) NOT NULL default '0',
  `metal` int(11) NOT NULL default '0',
  `fuel` int(11) NOT NULL default '0',
  `elect` int(11) NOT NULL default '0',
  `colon` int(11) NOT NULL default '0',
  `mine_mode` int(11) NOT NULL default '0',
  `mine_rate_metal` int(11) NOT NULL default '0',
  `mine_rate_fuel` int(11) NOT NULL default '0',
  `move_turn_cost` int(11) NOT NULL default '1',
  `config` set('bs','sh','hs','ls','na','po','so','oo','sv','sw','er','sj','ws','e1','e2','fr','sc','bo') NOT NULL,
  `size` tinyint(4) NOT NULL default '1',
  `upgrade_slots` int(11) NOT NULL default '0',
  `num_ot` tinyint(4) NOT NULL default '0',
  `num_dt` tinyint(4) NOT NULL default '0',
  `num_pc` tinyint(4) NOT NULL default '0',
  `num_ew` tinyint(4) NOT NULL default '0',
  `point_value` int(11) NOT NULL default '0',
  `points_killed` int(11) NOT NULL default '0',
  `ship_engaged` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ship_id`),
  KEY `ship_id` (`ship_id`),
  KEY `location` (`location`),
  KEY `fighters` (`fighters`),
  KEY `fleet_id` (`fleet_id`),
  KEY `login_id` (`login_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_stars`
-- 

DROP TABLE IF EXISTS `GAMENAME_stars`;
CREATE TABLE `GAMENAME_stars` (
  `star_id` int(11) NOT NULL auto_increment,
  `star_name` varchar(30) NOT NULL default '',
  `x_loc` int(11) NOT NULL default '0',
  `y_loc` int(11) NOT NULL default '0',
  `z_loc` int(11) NOT NULL default '0',
  `link_1` int(11) NOT NULL default '0',
  `link_2` int(11) NOT NULL default '0',
  `link_3` int(11) NOT NULL default '0',
  `link_4` int(11) NOT NULL default '0',
  `link_5` int(11) NOT NULL default '0',
  `link_6` int(11) NOT NULL default '0',
  `metal` int(11) NOT NULL default '0',
  `fuel` int(11) NOT NULL default '0',
  `event_random` tinyint(4) NOT NULL default '0',
  `wormhole` int(11) NOT NULL default '0',
  `planetary_slots` int(11) NOT NULL default '0',
  PRIMARY KEY  (`star_id`),
  KEY `login_id` (`star_id`,`star_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `GAMENAME_stars`
-- 

INSERT INTO `GAMENAME_stars` VALUES (1, 'Sol', 250, 250, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_user_options`
-- 

DROP TABLE IF EXISTS `GAMENAME_user_options`;
CREATE TABLE `GAMENAME_user_options` (
  `login_id` int(11) NOT NULL default '0',
  `color_scheme` tinyint(4) NOT NULL default '1',
  `news_back` smallint(6) NOT NULL default '150',
  `forum_back` smallint(6) NOT NULL default '30',
  `show_sigs` tinyint(4) NOT NULL default '1',
  `show_pics` tinyint(4) NOT NULL default '1',
  `show_minimap` tinyint(4) NOT NULL default '1',
  `show_aim` tinyint(4) NOT NULL default '0',
  `show_icq` tinyint(4) NOT NULL default '0',
  `montrer_chat` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`login_id`),
  UNIQUE KEY `login_id` (`login_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `GAMENAME_user_options`
-- 

INSERT INTO `GAMENAME_user_options` VALUES (1, 1, 400, 5, 1, 1, 1, 1, 1, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `GAMENAME_users`
-- 

DROP TABLE IF EXISTS `GAMENAME_users`;
CREATE TABLE `GAMENAME_users` (
  `login_id` int(11) NOT NULL auto_increment,
  `login_name` varchar(30) NOT NULL default '',
  `banned_time` int(11) NOT NULL default '0',
  `banned_reason` text NOT NULL default '',
  `joined_game` int(11) NOT NULL default '0',
  `game_login_count` int(11) NOT NULL default '0',
  `turns` int(11) NOT NULL default '40',
  `turns_run` int(11) NOT NULL default '0',
  `location` int(11) NOT NULL default '1',
  `ship_id` int(11) NOT NULL default '1',
  `cash` int(11) NOT NULL default '1000',
  `tech` int(11) NOT NULL default '0',
  `on_planet` int(11) NOT NULL default '0',
  `last_attack` int(11) NOT NULL default '1',
  `last_attack_by` varchar(200) NOT NULL default '',
  `ships_killed` int(11) NOT NULL default '0',
  `ships_lost` int(11) NOT NULL default '0',
  `ships_killed_points` int(11) NOT NULL default '0',
  `ships_lost_points` int(11) NOT NULL default '0',
  `fighters_killed` int(11) NOT NULL default '0',
  `fighters_lost` int(11) NOT NULL default '0',
  `genesis` int(11) NOT NULL default '0',
  `terra_imploder` int(11) NOT NULL default '0',
  `gdt` smallint(6) NOT NULL default '0',
  `clan_id` int(11) NOT NULL default '0',
  `clan_sym` char(3) NOT NULL default '',
  `clan_sym_color` varchar(6) NOT NULL default '',
  `one_brob` tinyint(4) NOT NULL default '0',
  `score` int(11) NOT NULL default '0',
  `approx_value` bigint(20) NOT NULL default '0',
  `alpha` int(11) NOT NULL default '0',
  `gamma` int(11) NOT NULL default '0',
  `delta` tinyint(4) NOT NULL default '0',
  `sig` varchar(150) NOT NULL default '',
  `last_request` int(11) NOT NULL default '0',
  `last_access_msg` int(11) NOT NULL default '0',
  `last_access_forum` int(11) NOT NULL default '0',
  `last_access_clan_forum` int(11) NOT NULL default '0',
  `last_buy` int(11) NOT NULL,
  `last_code` varchar(10) NOT NULL,
  `second_scatter` tinyint(4) NOT NULL default '0',
  `show_user_ships` tinyint(4) NOT NULL default '1',
  `show_enemy_ships` tinyint(4) NOT NULL default '0',
  `explored_sys` text NOT NULL default '',
  PRIMARY KEY  (`login_id`),
  KEY `login_id` (`login_id`),
  KEY `login_name` (`login_name`),
  KEY `ships_killed` (`ships_killed`),
  KEY `turns_run` (`turns_run`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--  
--  Dumping data for table `GAMENAME_users`
--  

INSERT INTO `GAMENAME_users` VALUES (1, 'Admin', 0, '', 0, 1, 400, 0, 1, 1, 100000000, 10000, 0, 1, '', 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, '', '', 0, 0, 0, 1, 1, 0, '', 0, 0, 0, 0, 0, '', 0, 1, 0, '');
INSERT INTO GAMENAME_users (login_id, login_name, joined_game, turns_run) VALUES (4,'Un-Owned',1,2147483647);


-- --------------------------------------------------------
-- 
--  Update the other DB tables
-- 


-- --------------------------------------------------------
--
-- Alter structure for table `se_development_time`
--

ALTER TABLE `se_development_time` ADD `GAMENAME_available` TINYINT NOT NULL DEFAULT '1';



-- --------------------------------------------------------
--
-- Alter structure for table `se_admin_ships`
--

ALTER TABLE `se_admin_ships` ADD `GAMENAME_ship_status` TINYINT NOT NULL DEFAULT '1';


-- -------------------------------------------------------
--
-- Alter structure for table `se_db_vars`
--
ALTER TABLE `se_db_vars` ADD `GAMENAME_value` INT NOT NULL DEFAULT '-1';

-- --------------------------------------------------------
--
-- update se_db vars with default values
--

UPDATE se_db_vars set GAMENAME_value = default_value;
