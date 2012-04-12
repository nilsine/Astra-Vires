-- 
-- Table structure for table `admin_request`
-- 

DROP TABLE IF EXISTS `admin_request`;
CREATE TABLE `admin_request` (
  `login_id` int(11) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `time` varchar(30) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `game` varchar(20) NOT NULL default '',
  `time_playing` varchar(30) NOT NULL default '',
  `reason` text NOT NULL,
  `do_to_game` text NOT NULL,
  `other` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `daily_tips`
-- 

DROP TABLE IF EXISTS `daily_tips`;
CREATE TABLE `daily_tips` (
  `tip_id` int(11) NOT NULL auto_increment,
  `tip_content` text NOT NULL,
  PRIMARY KEY  (`tip_id`),
  UNIQUE KEY `tip_id` (`tip_id`),
  KEY `tip_id_2` (`tip_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=31 ;

-- 
-- Dumping data for table `daily_tips`
-- 

INSERT INTO `daily_tips` VALUES (1, 'To customise your SE experience, try playing with some of the options on the <b class=b1>Options</b> Page.');
INSERT INTO `daily_tips` VALUES (2, 'You can change your colour scheme at any time from the options page.<br />There are plenty to choose from.');
INSERT INTO `daily_tips` VALUES (3, 'Rule Number One: The Admin Is Always Right.\r\n<br />Rule Number Two: If The Admin Is Wrong, See Rule Number One.\r\n<br />{starfox25, Dec 06 2000 - 14:26 }');
INSERT INTO `daily_tips` VALUES (4, 'Don''t get mad.<br />Get Even!');
INSERT INTO `daily_tips` VALUES (5, 'Just because a ship is more expensive does not necassarily mean it is better.');
INSERT INTO `daily_tips` VALUES (6, 'The only source of knowledge is experience.\r\n<br />{Albert Einstein}');
INSERT INTO `daily_tips` VALUES (7, 'Do not repeat the tactics which have gained you one victory, but let your methods be regulated by the infinite variety of circumstances.\r\n<br />{Sun Tzu, The Art of War - 6:28, 300BC}');
INSERT INTO `daily_tips` VALUES (8, 'Nothing is foolproof to a sufficiently talented fool.\r\n<br />{Douglas Adams, Hitchhikers Guide to the Galaxy}');
INSERT INTO `daily_tips` VALUES (9, 'You can click on the Mini-map to get a complete picture of the universe.');
INSERT INTO `daily_tips` VALUES (10, 'Clicking a player''s name gives you information about that player.<br />This can also be done with your own name, and will reveal several new options.');
INSERT INTO `daily_tips` VALUES (11, 'The <b>Diary</b> is a useful place to store information (such as enemy locations, favourite messages, reminder to get a haircut).');
INSERT INTO `daily_tips` VALUES (12, 'Its only a game. Enjoy it.');
INSERT INTO `daily_tips` VALUES (13, 'If you find any bugs, report them to the admin, along with details as to what you where doing to get it.');
INSERT INTO `daily_tips` VALUES (14, 'Autowarp allows you to automatically find your way between A and B. It is not necassarily the shortest, or safest, route though.');
INSERT INTO `daily_tips` VALUES (15, 'Wormholes offer a great way to get across the universe in only 1 turn (provided there are any around).');
INSERT INTO `daily_tips` VALUES (16, 'Its generally possible to get things on the cheap at Bilkos Auction house. As well as lots of things you can''t get anywhere else in the game.<br />You can get to it from any star-port, or Earth.');
INSERT INTO `daily_tips` VALUES (17, 'You should change all your passwords every few months.<br />You should never give your password to other players. Ever!');
INSERT INTO `daily_tips` VALUES (18, 'Upgrades allow you to improve your star-ships, however they cannot be removed once installed.');
INSERT INTO `daily_tips` VALUES (19, 'Joining a Clan can get you new friends and allies, but also new foes.');
INSERT INTO `daily_tips` VALUES (20, 'Statistics about the game you are in can be found by clicking on the games name in the top left corner of the screen (below the date).');
INSERT INTO `daily_tips` VALUES (21, 'You may only own one flagship class ship at a time. If you loose it, the next one will cost double.');
INSERT INTO `daily_tips` VALUES (22, 'Transversers with the <b>Wormhole Stabiliser</b> upgrade are ideal for getting colonists onto your planets quickly and cheaply.');
INSERT INTO `daily_tips` VALUES (23, 'The <b class=b1>Ship Duplicator</b> at Earth, will allow you to create a whole fleet of ships that are identical to the one you are commanding, with the minimum of effort.');
INSERT INTO `daily_tips` VALUES (24, 'The hardest thing of all for a soldier is to retreat.<br />{Duke of Wellington}');
INSERT INTO `daily_tips` VALUES (25, 'Wise people learn when they can; fools learn when they must.<br />{Duke of Wellington}');
INSERT INTO `daily_tips` VALUES (26, 'Never interrupt your enemy when he is making a mistake.<br />{Napoleon Bonaparte}');
INSERT INTO `daily_tips` VALUES (27, 'You must not fight too often with one enemy, or you will teach him all your art of war.<br />{Napoleon Bonaparte}');
INSERT INTO `daily_tips` VALUES (28, 'Ships with the ''so'' (Ships Only) Config, are idealy suited to destroying planet-only ships.');
INSERT INTO `daily_tips` VALUES (29, 'Upgrades can play a pivotal role in combat. They allow a fleet to carry yet more firepower, whilst at the same time reducing damage taken.');
INSERT INTO `daily_tips` VALUES (30, 'You should not use one password for all applications (i.e. e-mail, online games etc). Instead you should have a different password for each account.');

-- --------------------------------------------------------

-- 
-- Table structure for table `option_list`
-- 

DROP TABLE IF EXISTS `option_list`;
CREATE TABLE `option_list` (
  `name` varchar(50) NOT NULL default '',
  `min` int(11) NOT NULL default '0',
  `max` int(11) NOT NULL default '0',
  `description` text NOT NULL,
  `option_type` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `option_list`
-- 

INSERT INTO `option_list` VALUES ('news_back', 10, 700, 'Allows you to set how many hours of news will be shown per screen.', 1);
INSERT INTO `option_list` VALUES ('forum_back', 1, 168, 'Allows you to choose how many hours the forum should list per screen.', 1);
INSERT INTO `option_list` VALUES ('show_pics', 0, 1, 'Pictures are loaded in numerous places throughout the game. They can be turned off here. (This will not effect the Minimap. That can be turned off elsewhere on this page) &&& Hide Pictures. &&& Show Pictures.', 1);
INSERT INTO `option_list` VALUES ('show_minimap', 0, 1, 'The Minimap is the map in the top right corner of the star System. When disbabled, a link to the full map will be shown in it''s place. &&& Minimap Disabled. &&& Minimap Enabled.', 1);
INSERT INTO `option_list` VALUES ('show_sigs', 0, 1, 'Signatures are are appended to the end of personal or forum messages sent by another player. <br />Turning them off can make the forums load significantly faster. &&& Signatures Hidden. &&& Signatures Shown.', 1);
INSERT INTO `option_list` VALUES ('show_aim', 0, 1, 'Allows you to show or hide other users AIM contact details. &&& Hide AIM details. &&& Show AIM details.', 1);
INSERT INTO `option_list` VALUES ('show_icq', 0, 1, 'Allows you to show or hide other users ICQ numbers. &&& Hide ICQ numbers. &&& Show ICQ numbers.', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `se_admin_ships`
-- 

DROP TABLE IF EXISTS `se_admin_ships`;
CREATE TABLE `se_admin_ships` (
  `ship_type_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ship_type_id`),
  UNIQUE KEY `ship_type_id` (`ship_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `se_admin_ships`
-- 

INSERT INTO `se_admin_ships` VALUES (1);
INSERT INTO `se_admin_ships` VALUES (2);
INSERT INTO `se_admin_ships` VALUES (3);
INSERT INTO `se_admin_ships` VALUES (4);
INSERT INTO `se_admin_ships` VALUES (5);
INSERT INTO `se_admin_ships` VALUES (6);
INSERT INTO `se_admin_ships` VALUES (7);
INSERT INTO `se_admin_ships` VALUES (8);
INSERT INTO `se_admin_ships` VALUES (9);
INSERT INTO `se_admin_ships` VALUES (10);
INSERT INTO `se_admin_ships` VALUES (11);
INSERT INTO `se_admin_ships` VALUES (12);
INSERT INTO `se_admin_ships` VALUES (13);
INSERT INTO `se_admin_ships` VALUES (14);
INSERT INTO `se_admin_ships` VALUES (15);
INSERT INTO `se_admin_ships` VALUES (301);
INSERT INTO `se_admin_ships` VALUES (302);
INSERT INTO `se_admin_ships` VALUES (303);
INSERT INTO `se_admin_ships` VALUES (304);
INSERT INTO `se_admin_ships` VALUES (399);

-- --------------------------------------------------------

-- 
-- Table structure for table `se_central_messages`
-- 

DROP TABLE IF EXISTS `se_central_messages`;
CREATE TABLE `se_central_messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `forum_id` tinyint(4) NOT NULL default '-50',
  `timestamp` int(11) NOT NULL default '0',
  `sender_id` int(11) NOT NULL default '0',
  `game_id` int(11) NOT NULL default '0',
  `sender_name` varchar(100) NOT NULL default '',
  `text` blob NOT NULL,
  PRIMARY KEY  (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `se_central_table`
-- 

DROP TABLE IF EXISTS `se_central_table`;
CREATE TABLE `se_central_table` (
  `todays_tip` tinyint(4) NOT NULL default '0',
  `last_ran_s_maint` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `se_central_table` ( `todays_tip` , `last_ran_s_maint` ) VALUES ('0', '0');

-- --------------------------------------------------------

-- 
-- Table structure for table `se_config_list`
-- 

DROP TABLE IF EXISTS `se_config_list`;
CREATE TABLE `se_config_list` (
  `config_id` char(2) NOT NULL default '',
  `short_for` varchar(30) NOT NULL default '',
  `type` varchar(20) NOT NULL default '',
  `description` text NOT NULL,
  `does_to_ship` text NOT NULL,
  `cost` int(11) NOT NULL default '0',
  `tech_cost` int(11) NOT NULL default '0',
  PRIMARY KEY  (`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `se_config_list`
-- 

INSERT INTO `se_config_list` VALUES ('bs', 'Battleship', 'Warfare', 'Registers the ship as a Battleship at the Sol Office for Offensive Actions (an expensive action requiring lots of [corrupt] bureaucrat [is there any other type?]).\r\n<br />Also installs improved computer systems that allow the ship to better handle the logistics of space combat.\r\n<br />On top of that, it also installs combat-grade shield generators to increase the shield generation rate.', 'The ship performs better in combat.\r\n<br />Allows the ship to hold more than 4,999 fighters.\r\n<br />Increases Shield recharge rate by 50%.', 25000, 0);
INSERT INTO `se_config_list` VALUES ('sh', 'Shield Charger', 'Warfare', 'An improved power to shield converter means the same amount of power restores more shields.', 'Increases shield recharge rate by 25% on top of any other upgrades.', 20000, 0);
INSERT INTO `se_config_list` VALUES ('bo', 'Bio-Organic Armour', 'Warfare', 'Part-living armour based on advanced Organic-Tissue Research, the ethics involved in creating such armour are still in-question, and so this armour can only be obtained from non-Sol sanctioned dealers (i.e. Blackmarkets).', 'The armour self-repairs slowly in a manner similar to shields.', 220, 6);
INSERT INTO `se_config_list` VALUES ('hs', 'High Stealth', 'Stealth', 'A complicated system of technologies are installed on the ship to prevent almost all heat/light/etc emissions, thus rendering it all but invisible.', 'Stops the enemy from being able to get any information about the ship without a scanner.\r\n<br />Even if the enemy has a scanner, the information divulged is still quite limited.\r\n<br />Bestows a slight bonus in combat.', 0, 0);
INSERT INTO `se_config_list` VALUES ('ls', 'Low Stealth', 'Stealth', 'Absorbative hull platings on the ship stop regular sensors from being able to aquire information about the ship.', 'Enemies without Scanners will not be given any real information about the ship.\r\n<br />Enemies with Scanners will see the ship as a normal ship.\r\n<br />Bestows a slight bonus in combat.', 40000, 0);
INSERT INTO `se_config_list` VALUES ('na', 'No Attack', 'Limiter', 'The ship is so flimsy and small that the pilots refuse to go into combat except to defend themselves where necessary.', 'The ship cannot attack, and will not particpate in offensive fleet actions.', 0, 0);
INSERT INTO `se_config_list` VALUES ('po', 'Planets Only', 'Limiter', 'Limited maneuverability stops this ship from being effective in space combat, however it is outfitted with numerous technologies that improve it''s anti-planet combat effectiveness.', 'Improved combat against planets.\r\n<br />Ship cannot participate in attacks against other ships.\r\n<br />If the ship is attacked by enemy ships it will take increased damage.', 0, 0);
INSERT INTO `se_config_list` VALUES ('so', 'Ships Only', 'Limiter', 'Unlike most ships that are generic and able to attack both ships and planets, this ship has only technology that allows it to attack ships. The crew are trained solely in anti-ship warfare and so become better at it than average.', 'The ship cannot participate in planetary assaults, unless there are enemy ships involved.\r\n<br />Improved combat effectiveness against other ships.\r\n<br />Excellent against Planet Only Ships.', 0, 0);
INSERT INTO `se_config_list` VALUES ('oo', 'Only One', 'Limiter', 'These ships are generally so powerful the Sol Government decided it was necessary to impose a limit on them to stop certain parties from getting too powerful.\r\n<br />Replacement of a ship that has this configuration requires the Captain to show substantial documentation and evidence proving the last one was destroyed.\r\n<br />The limit is seen as necessary even by blackmarketers who also enforce this particular law.', 'You may only own one ship that has this config at a time.\r\nCannot be transfered.', 0, 0);
INSERT INTO `se_config_list` VALUES ('sv', 'SW Mark 1 - Quark Disrupter', 'Super Weapon', 'The Quark disrupter does exactly what it claims to do. It disrupts quarks in the target area, thereby reducing the coherence of the target''s atoms.\r\n<br />Due to the significant chargeup time it''s generally impossible to target it at ships as said ship invariably notices the hostile ship slowly getting brighter and pointing at it in a menacing manner.\r\n<br />As such this weapon can only be used against planets.\r\n<br />This device comes with it''s own small powersource, which is used to increase shield-charge rate when the weapon is not in use.', 'For a set number of turns, this weapon will kill a semi-random number of fighters on a planet.\r\n<br />Shield recharge rate on the ship is 2.5 times more than normal.', 0, 0);
INSERT INTO `se_config_list` VALUES ('sw', 'SW Mark 2 - Terra Maelstrom', 'Super Weapon', 'A upgrade to the Mark 1 SuperWeapon (the Quark Disrupter), this improved version features superior targeting and a larger powersource.\r\n<br />These improvements combined with training in how to hold down the fire-button longer allow the weapon to do immense amounts of damage compared to its little brother.\r\n<br />The larger powersource also means that more shield-recharging can be done when the weapon is not in use.', 'This weapon can kill up to 75% or so of a planets fighters in a single blast if the attacker has sufficient turns.\r\n<br />Shield recharge rate is triple normal.', 1000000, 0);
INSERT INTO `se_config_list` VALUES ('er', 'Emergency Return', 'Propulsion', 'A result of early tests in wormhole stabilising technologies, the Emergency Return feature only works on small ships due to some rather fundamental limits in Physics.\r\n<br />It''s resultant small size, and one jump capability made it ideal for use on Escape-Pods, allowing them to get back to Sol with the aid of the transpace-beacon that was deployed in that system.', 'Allows the ship to make a single jump from anywhere in the galaxy back to Sol.', 0, 0);
INSERT INTO `se_config_list` VALUES ('sj', 'Subspace Jump', 'Propulsion', 'The ability to create temporary wormholes and travel through them has been a dream of humanity for decades.\r\n<br />However, with the advent of the Molsovian Jump Device, such dreams became reality.\r\n<br />Initially these devices could only be used to link star-systems using static warp-links.\r\n<br />However with further research it has become possible to create devices that can be installed on ships and push themselves through the holes they have created.\r\n<br />There are still limits, however with the aid of a Wormhole Stabiliser, most these can be circumvented.\r\n<br />One limit that cannot be circumvented however, is the lack of shields that this device forces its host ship to carry. Conflicts with the jump energies endanger the whole ship.', 'The ship can jump to any point in the universe. It can also tow up to 10 other ships with it.\r\n<br />The turn cost for the jump is based on the distance.\r\n<br />It is highly recommended that you install a Wormhole Stabiliser on any SJ capable ships you have.\r\n<br />Ships with the sj config cannot have shields.', 0, 0);
INSERT INTO `se_config_list` VALUES ('ws', 'Wormhole Stabiliser', 'Propulsion', 'Further research into the Molsovian Jump Devices allowed the reasearchers to eventually discover how to make the portable-devices wormholes'' stable for increased periods of time, thus enabling larger fleets to use them.', 'Allows the ship to perform "autowarping" (transfering stuff from 1 planet to another quickly and easily).\r\n<br />Also allows the ship to tow an unlimited number of ships through Subspace Jumps.', 65000, 0);
INSERT INTO `se_config_list` VALUES ('e1', 'Engine Upgrade - Mark 1', 'Propulsion', 'Engines with a greater thrust-to-weight ratio are installed on the ship, thus improving speed and acceleration.', 'Combat Movement is increased by 1.\r\n<br />Movement turn cost for this ship is reduced by 1 if the admin has set movement turn costs to be based on ship size/speed.', 19000, 0);
INSERT INTO `se_config_list` VALUES ('e2', 'Engine Upgrade - Mark 2', 'Propulsion', 'State-of-the-art engines are installed on the ship, and numerous components within the ship itself are replaced with super-lightweight alternatives, thus significantly improving acceleration and top-speed.', 'Combat Movement is increased by 2.\r\n<br />Movement turn cost for this ship is reduced by 2 if the admin has set movement turn costs to be based on ship size/speed.\r\n', 40000, 600);
INSERT INTO `se_config_list` VALUES ('fr', 'Freighter', 'Misc', 'As much a philosophy as a phsyical setup, the Freighter class ships have improved defensive technologies installed on them, allowing them to be able to better defend themselves when less than honest Captains come-a-knocking.', 'Ship has double usual shield recharge rate.\r\n<br />Ship has improved defence in combat.', 0, 0);
INSERT INTO `se_config_list` VALUES ('sc', 'Scanner', 'Misc', 'Improved scanning technologies are installed on the ship. These include Mark 3 Bounce-back-decto meters, Type 7.3 Determination Software, and a new cup-holder for the science officers'' station.', 'Allows the ship to see Low-stealth ships as normal, and get at least some information from High-Stealth ships.\r\n<br />Gives small combat bonus due to improved targeting.', 20000, 0);
INSERT INTO `se_config_list` VALUES ('pc', 'Plasma Cannon', 'Weapon', 'Firing nice big bolts of plasma at the enemy and with a good rate of fire these weapons can cause a considerable headache to the foe who happens to get one pointed their way.\r\n<br />Somewhat illegal because the competing company that makes the weaker and less capable Pea Shooters decided to `lobby` (read bribe) the Sol government into not allowing Plasma Cannons to be sold.', 'Does damage in combat in a similar way to fighters (destroy shields first, then fighters, then armour).\r\n<br />If the enemies armour is gone, these can destroy the enemy ship.', 55000, 700);
INSERT INTO `se_config_list` VALUES ('ot', 'Offensive Turret', 'Weapon', 'Otherwise known as the "Pea Shooter" due to the small green globules of trouble that it spurts, a Pea-Shooter Turret is an effect weapon for those little skirmishes.', 'Does damage in combat in a similar way to fighters (destroy shields first, then fighters, then armour).\r\n<br />If the enemies armour is gone, these can destroy the enemy ship.\r\n', 40000, 0);
INSERT INTO `se_config_list` VALUES ('dt', 'Defensive Turret', 'Weapon', 'A Special Turret that concentrates on damaging the enemy fighters BEFORE they can do damage to their target. Though they do less damage for about the same cost as the Pea-Shooters, the fact that anything they hit won''t actually damage the ship can be a life saver when the enemy outnumbers you.', 'Only Damages enemy fighters in combat.\r\nThey destroy enemy fighters BEFORE those fighters can do damage.', 45000, 0);
INSERT INTO `se_config_list` VALUES ('ew', 'Electronic Warfare Pod', 'Weapon', 'Electronic Warfare Pods use numerous different electronic devices to produce the desired effect.\r\n<br />They have a set `charge` which is recharged after every battle.\r\n<br />This means that like turrets they can only do a limited battle during a firefight.', 'In the following order, during combat:\r\n<br />Tries to disable enemy EWP''s. The player with the most EWP''s wins.\r\n<br />Remaining charge is used to try to stop Offensive turrets, followed by trying to stop Defensive turrets, and then if there is any charge left it will be used to destroy enemy fighters.\r\n<br />EWP''s do not function when a planet is involved in the combat', 60000, 300);
INSERT INTO `se_config_list` VALUES ('br', 'Bussard Ramjet', 'Misc', 'Using a very big scoop to pick up the detritus of the star systems (either fuel or metal) it travels through.', 'Allows the ship to gather either Metal or Fuel when jumping between systems. This happens automatically and any collected material is deposited in the ships hold.', 0, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `se_db_vars`
-- 

DROP TABLE IF EXISTS `se_db_vars`;
CREATE TABLE `se_db_vars` (
  `name` varchar(30) NOT NULL default '',
  `default_value` int(11) NOT NULL default '1',
  `min` int(11) NOT NULL default '1',
  `max` int(11) NOT NULL default '1',
  `description` text NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `se_db_vars`
-- 

INSERT INTO `se_db_vars` VALUES ('admin_var_show', 1, 0, 1, 'Determines if players can see what these admin vars are set to. &&& Hide Variables &&& Show Variables');
INSERT INTO `se_db_vars` VALUES ('allow_signatures', 0, 0, 1, 'Choose if player signatures are enabled of disabled &&& Disabled, they will whinge &&& Enabled, they will abuse it. :) ');
INSERT INTO `se_db_vars` VALUES ('alternate_play_1', 0, 0, 1, 'Determines if all ships can mine everything, or each ship mines either metal or fuel. <br />Players will not be able to buy metal or fuel, so will have to mine everything they plan to use. &&& Original play style. &&& Mine, Mine, Mine...');
INSERT INTO `se_db_vars` VALUES ('alternate_play_2', 0, 0, 1, 'A second alternate play style.<br />This one staggers the release of items (ships, upgrades everything). Players start with just the MF and SS available, but over time (dependent on how active the players are) more items will be made available. &&& Original play style. &&& R&D Enabled.<br />Note: If you have disabled an item, that item will remain disabled in this play style.');
INSERT INTO `se_db_vars` VALUES ('attack_planet_flag', 1, 0, 1, 'Sets whether planets can be attacked or not. &&& Planets are safe paradises &&& Planets can be victimised.');
INSERT INTO `se_db_vars` VALUES ('attack_sol_flag', 1, 0, 1, 'Chooose whether attacking in Sol is allowed or not &&& Sol is a safe haven. &&& Armaggedon in Sol.');
INSERT INTO `se_db_vars` VALUES ('attack_space_flag', 1, 0, 1, 'Sets whether ships can be attacked or not. &&& Ships are safe &&& Boom, Boom go the ships.');
INSERT INTO `se_db_vars` VALUES ('attack_turn_cost_planet', 10, 0, 1000, 'Number of turns it takes to attack a planet.');
INSERT INTO `se_db_vars` VALUES ('attack_turn_cost_space', 2, 0, 1000, 'Number of turns it takes to attack a ship.');
INSERT INTO `se_db_vars` VALUES ('armour_multiplier', 2, 1, 50, 'This specifies the amount of damage that must be done to kill a single unit of Armour.\r\n<br />1 is normal damage (it will die at the same speed as fighters or shields).\r\n<p />This will only effect the life expectancy of a players ships, as armour is the last line of defence for a ship.\r\n<br />Game is balanced for a setting of 2.');
INSERT INTO `se_db_vars` VALUES ('bomb_cost', 100000, 0, 10000000, 'Cost of the normal bombs. Other bombs will cost a multiple of this number.');
INSERT INTO `se_db_vars` VALUES ('bomb_flag', 0, 0, 2, 'Set the availability of bombs. &&& BIIIIGGGGG BOOOOOOM, all enabled &&& Bombs cannot be purchased from the Equip shop (but will appear in bilkos) &&& All bombs except the Delta Bomb are disabled.');
INSERT INTO `se_db_vars` VALUES ('buy_elect', 230, 1, 1000, 'Price Electronics can be brought for. Sell price is 20% less.');
INSERT INTO `se_db_vars` VALUES ('buy_fuel', 90, 1, 1000, 'Price Fuel can be brought for. Sell price is 20% less.');
INSERT INTO `se_db_vars` VALUES ('buy_metal', 80, 1, 1000, 'Price Metal can be brought for. Sell price is 20% less.');
INSERT INTO `se_db_vars` VALUES ('bilkos_time', 24, 6, 72, 'The amount of hours a player must hold a bid on an item at bilkos for, before it can be won.');
INSERT INTO `se_db_vars` VALUES ('clan_fleet_attacking', 0, 0, 1, 'Allow or disallow the ability for clan members to attack/Defend as a single fleet &&& Meek explosions, disabled &&& Huge great epic battle thingys - Advised only for clan games');
INSERT INTO `se_db_vars` VALUES ('clan_member_limit', 3, 0, 100, 'Max number of players able to join a single clan.');
INSERT INTO `se_db_vars` VALUES ('clans_max', 10000, 0, 10000, 'Max number of clans that can be created.');
INSERT INTO `se_db_vars` VALUES ('cost_colonist', 1, 0, 1000, 'Cost per colonist, as taken from Earth');
INSERT INTO `se_db_vars` VALUES ('cost_genesis_device', 50000, 0, 1000000, 'Cost of genesis devices.');
INSERT INTO `se_db_vars` VALUES ('enable_superweapons', 1, 0, 1, 'Availability of the Terra Maelstrom &&& Unavailable, the universe is safe &&& Available - Death Star wannabe');
INSERT INTO `se_db_vars` VALUES ('fighter_cost_earth', 100, 1, 10000, 'The cost to buy a fighter at earth.');
INSERT INTO `se_db_vars` VALUES ('game_length', 20, 1, 400, 'Planned length of the game, in days.');
INSERT INTO `se_db_vars` VALUES ('hourly_shields', 15, 0, 100, 'Number of shield points regenerated per ship each hour.');
INSERT INTO `se_db_vars` VALUES ('hourly_tech', 10, 0, 100, 'Number of Tech Units generated each hour by each planetary Research Facility. Incrementally increased based on the number of colonists on a planet. When a planet has max colonists, it will get this many tech units per hours.');
INSERT INTO `se_db_vars` VALUES ('hourly_turns', 20, 1, 1000, 'Number of turns gained each hour.');
INSERT INTO `se_db_vars` VALUES ('keep_sol_clear', 1, 0, 1, 'Choose if all non-newbie ships are to be cattered from Sol if they are in that system for two consecutive hourly maints. &&& Ships accumulate in Sol &&& Ships scattered and players frustrated :) ');
INSERT INTO `se_db_vars` VALUES ('max_players', 10000, 0, 10000, 'Max number of players that can be signed up in the game.');
INSERT INTO `se_db_vars` VALUES ('max_other_ships', 100, 0, 500, 'Max number of ships that are not warships, that a player can have.');
INSERT INTO `se_db_vars` VALUES ('max_turns', 600, 10, 50000, 'Max number of turns a player can have.');
INSERT INTO `se_db_vars` VALUES ('max_warships', 15, 0, 500, 'The maximum number of battleships a player may have.');
INSERT INTO `se_db_vars` VALUES ('min_before_transfer', 3, 0, 10000, 'Min number of days before players can transfer cash/ships.');
INSERT INTO `se_db_vars` VALUES ('new_logins', 1, 0, 1, 'Choose whether new players are allowed to join the game or not &&& Signups Disabled &&& Fresh Meat Enabled');
INSERT INTO `se_db_vars` VALUES ('random_events', 0, 0, 3, 'Random things happen in random places. There arn''t many random events at present. &&& No Random Events. It''s a veritable haven of sissies and nancies &&& Low level stuff. Players barely feel it. &&& Mid Level Stuff. Things are a little stronger. &&& We''re talking universal catacylsms.');
INSERT INTO `se_db_vars` VALUES ('retire_period', 24, 0, 72, 'The number of hours between when a user retires from a game, and when a user can join the game again. Set to 0 to turn off (No wait period).');
INSERT INTO `se_db_vars` VALUES ('rr_fuel_chance', 50, 0, 100, 'Chance that a star system will recieve random amount of fuel daily.');
INSERT INTO `se_db_vars` VALUES ('rr_fuel_chance_max', 19342, 0, 1000000, 'Maximum amount of fuel that a system will recieve.');
INSERT INTO `se_db_vars` VALUES ('rr_fuel_chance_min', 143, 0, 100000, 'Minimum amount of fuel that a system will recieve.');
INSERT INTO `se_db_vars` VALUES ('rr_metal_chance', 75, 0, 100, 'Chance that a star system will recieve random amount of metal daily.');
INSERT INTO `se_db_vars` VALUES ('rr_metal_chance_max', 15454, 0, 1000000, 'Maximum amount of metal that a system will recieve.');
INSERT INTO `se_db_vars` VALUES ('rr_metal_chance_min', 214, 0, 100000, 'Minimum amount of metal that a system will recieve.');
INSERT INTO `se_db_vars` VALUES ('score_method', 2, 0, 2, 'Decides method of scoring used. &&& Scores Disabled &&& Score is based on fighter kills and such like (i dunno, i just wrote the thing :) ). &&& Score is based on point value of ships killed and lost.');
INSERT INTO `se_db_vars` VALUES ('ship_warp_cost', -1, -1, 1000, 'This var determines how much it costs for players to warp between systems.<br /><br />Set it between 0 and 1000 to determine the number of turns,<br />OR<br />set it to -1, whereby a different system will be used, where different ship types take different numbers of turns to get to places. The bigger the ship the more turns it takes.');
INSERT INTO `se_db_vars` VALUES ('start_cash', 5000, 0, 1000000, 'Amount of cash a player starts out with.');
INSERT INTO `se_db_vars` VALUES ('start_late_multiplier', 50, 0, 200, 'The percent by which <b>start_cash</b> and <b>start_tech</b> will increase each day the game is un-paused.<br />Setting this gives late starters a better chance.<br />Set to 0 for no automatic increases.');
INSERT INTO `se_db_vars` VALUES ('start_ship', 6, 3, 6, 'Selects the ship the player starts in. &&& Scout Ship &&& Merchant Freighter &&& Stealth Trader &&& Harvestor Mammoth');
INSERT INTO `se_db_vars` VALUES ('start_tech', 0, 0, 10000, 'Number of tech support units a player starts out with. Recommended as zero, unless running an Extreme Game.');
INSERT INTO `se_db_vars` VALUES ('start_turns', 40, 0, 10000, 'Amount of turns a player starts out with.');
INSERT INTO `se_db_vars` VALUES ('sudden_death', 0, 0, 1, 'When in sudden death (SD), any player who dies is out of the game for however long is left. Also no new players will be able to join. &&& SD Disabled &&& Carnage central. With added chaos for good measure.');
INSERT INTO `se_db_vars` VALUES ('turns_before_planet_attack', 100, 0, 1000, 'Turns that a player has to use before they can attack/use planets.');
INSERT INTO `se_db_vars` VALUES ('turns_before_space_attack', 100, 0, 1000, 'Turns that have to be used before a new account can attack ships.');
INSERT INTO `se_db_vars` VALUES ('turns_safe', 100, 0, 1000, 'Turns that have to pass before a new player can be attacked.');
INSERT INTO `se_db_vars` VALUES ('uv_explored', 0, 0, 1, 'Determines if the universe is pre-explored or not. &&& Un-explored black morass. Players find stuff for themselves <br />Note2 - Do not give players so many turns they can explore the whole universe quickly, that defies the point of this. &&& Stellar Cartographers have mapped all the warp points, so players see a full map when they join the game');
INSERT INTO `se_db_vars` VALUES ('uv_fuel_max', 113205, 1, 1000000, 'Max amount of fuel in a star system when universe is generated.');
INSERT INTO `se_db_vars` VALUES ('uv_fuel_min', 695, 1, 100000, 'Min amount of fuel in a star system when universe is generated.');
INSERT INTO `se_db_vars` VALUES ('uv_fuel_percent', 30, 0, 100, 'Percent of star systems that will have fuel when universe is generated.');
INSERT INTO `se_db_vars` VALUES ('uv_map_layout', 0, 0, 5, 'Choose the layout of the universe map. &&& Random Star Distribution. &&& Grid of stars. &&& Galactic Core. &&& Clusters. &&& Circle filled with stars. &&& Ring of stars.)');
INSERT INTO `se_db_vars` VALUES ('uv_max_link_dist', -1, -1, 10000, 'Maximum distance a link between two star systems may go (in pixels).<br />Setting this low will result in most/all stars not being linked. Note that Sol is always linked, no matter this var.<br />Set to -1 to allow nature to take it''s course.<p />Try experimenting.');
INSERT INTO `se_db_vars` VALUES ('uv_metal_max', 99835, 1, 1000000, 'Max amount of metal in a star system when universe is generated.');
INSERT INTO `se_db_vars` VALUES ('uv_metal_min', 134, 1, 100000, 'Min amount of metal in a star system when universe is generated.');
INSERT INTO `se_db_vars` VALUES ('uv_metal_percent', 35, 0, 100, 'Percent of star systems that will have metal when universe is generated.');
INSERT INTO `se_db_vars` VALUES ('uv_min_star_dist', 10, 10, 20, 'Minimum distance between star systems (in pixels).');
INSERT INTO `se_db_vars` VALUES ('uv_num_bmrkt', 10, 0, 100, 'Sets number of blackmarkets created during Universe generation.');
INSERT INTO `se_db_vars` VALUES ('uv_num_ports', 10, 0, 1000, 'Number of star ports when universe is generated.');
INSERT INTO `se_db_vars` VALUES ('uv_num_stars', 150, 10, 500, 'Number of stars in the universe.');
INSERT INTO `se_db_vars` VALUES ('uv_planets', 50, 0, 5000, 'This sets the maximum number of pre-generated planets that may appear (randomly) in a system when the universe is generated. <br />This can be used in conjunction with <b>uv_planet_slots</b> to create a universe that has both planets, and planetary slots.');
INSERT INTO `se_db_vars` VALUES ('uv_planet_slots', 5, 0, 50, 'This sets the maximum number of planetary slots that may appear (randomly) per star-system when the universe is generated.');
INSERT INTO `se_db_vars` VALUES ('uv_show_warp_numbers', 1, 0, 1, 'Determine if numbers appear next to star systems on the univser map. &&& No numbers. Players start seeing stars. &&& Players get it easy cos there are numbers');
INSERT INTO `se_db_vars` VALUES ('uv_size_x_width', 500, 100, 2000, 'Width of the universe, in pixels.');
INSERT INTO `se_db_vars` VALUES ('uv_size_y_height', 500, 100, 2000, 'Height of the unvierse, in pixels.');
INSERT INTO `se_db_vars` VALUES ('uv_wormholes', 1, 0, 1, 'Choose whether wormholes are enabled or not &&& Disabled &&& Enabled');

-- --------------------------------------------------------

-- 
-- Table structure for table `se_development_time`
-- 

DROP TABLE IF EXISTS `se_development_time`;
CREATE TABLE `se_development_time` (
  `item_id` int(11) NOT NULL auto_increment,
  `item_name` varchar(50) NOT NULL default '',
  `year_set_1` int(11) NOT NULL default '0',
  PRIMARY KEY  (`item_id`),
  UNIQUE KEY `item_name` (`item_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=5004 ;

-- 
-- Dumping data for table `se_development_time`
-- 

INSERT INTO `se_development_time` VALUES (3, 'Scout Ship (Ship)', 0);
INSERT INTO `se_development_time` VALUES (4, 'Merchant Freighter (Ship)', 0);
INSERT INTO `se_development_time` VALUES (5, 'Stealth Trader (Ship)', 11);
INSERT INTO `se_development_time` VALUES (6, 'Harvester Mammoth (Ship)', 5);
INSERT INTO `se_development_time` VALUES (7, 'Attack Battleship (Ship)', 7);
INSERT INTO `se_development_time` VALUES (8, 'Warmonger (Ship)', 15);
INSERT INTO `se_development_time` VALUES (9, 'Skirmisher (Ship)', 85);
INSERT INTO `se_development_time` VALUES (10, 'Occultator (Ship)', 50);
INSERT INTO `se_development_time` VALUES (11, 'Transverser (Ship)', 17);
INSERT INTO `se_development_time` VALUES (12, 'Brobdingnagian (Ship)', 200);
INSERT INTO `se_development_time` VALUES (13, 'Flexi-Hull(tm) (Ship)', 30);
INSERT INTO `se_development_time` VALUES (14, 'Mega-Flex(tm) (Ship)', 60);
INSERT INTO `se_development_time` VALUES (15, 'Civilian Transport (Ship)', 20);
INSERT INTO `se_development_time` VALUES (16, 'Super Skirmisher (Bilkos Ship)', 95);
INSERT INTO `se_development_time` VALUES (17, 'Mega Miner/Cargo (Bilkos Ship)', 60);
INSERT INTO `se_development_time` VALUES (18, 'Adv. Transverser (Bilkos Ship)', 38);
INSERT INTO `se_development_time` VALUES (207, 'Explorer Mark I (Bilkos Ship)', 9);
INSERT INTO `se_development_time` VALUES (301, 'Mammoth Ram-Scoop (BM Ship)', 70);
INSERT INTO `se_development_time` VALUES (302, 'Mammoth Asteroid Processor (BM Ship)', 70);
INSERT INTO `se_development_time` VALUES (303, 'GunShip (BM Ship)', 45);
INSERT INTO `se_development_time` VALUES (304, 'Behemoth (BM Ship)', 170);
INSERT INTO `se_development_time` VALUES (399, 'Alien Battlestar (BM Ship)', 270);
INSERT INTO `se_development_time` VALUES (1000, 'Genesis Device (Equip)', 15);
INSERT INTO `se_development_time` VALUES (1001, 'Alpha Bomb', 17);
INSERT INTO `se_development_time` VALUES (1002, 'Gamma Bomb', 25);
INSERT INTO `se_development_time` VALUES (1003, 'Delta Bomb', 120);
INSERT INTO `se_development_time` VALUES (2000, 'Pea Shooter (Upgrade)', 7);
INSERT INTO `se_development_time` VALUES (2001, 'Defensive Turret (Upgrade)', 5);
INSERT INTO `se_development_time` VALUES (2003, 'Shrouding Unit (Upgrade)', 11);
INSERT INTO `se_development_time` VALUES (2004, 'Scanner (Upgrade)', 15);
INSERT INTO `se_development_time` VALUES (2005, 'Shield Charger  (Upgrade)', 30);
INSERT INTO `se_development_time` VALUES (2006, 'Wormhole Stabiliser (Upgrade)', 20);
INSERT INTO `se_development_time` VALUES (2007, 'Engine Upgrade', 70);
INSERT INTO `se_development_time` VALUES (3000, 'Shield Generator (Planet)', 85);
INSERT INTO `se_development_time` VALUES (3002, 'Warpack (Bilkos Bombs)', 30);
INSERT INTO `se_development_time` VALUES (3003, '1500 Fighter Bays (Bilkos Upgrade)', 25);
INSERT INTO `se_development_time` VALUES (3004, 'Battleship Conversion (Bilkos Upgrade)', 17);
INSERT INTO `se_development_time` VALUES (3005, 'Terra Maelstrom (Bilkos Upgrade)', 260);
INSERT INTO `se_development_time` VALUES (4000, 'Research labs (Planet)', 45);
INSERT INTO `se_development_time` VALUES (4001, 'Colonists (Earth, Planet)', 20);
INSERT INTO `se_development_time` VALUES (4002, 'Mining Drones (Planet)', 130);
INSERT INTO `se_development_time` VALUES (5000, 'Plasma Cannons (BM Upgrade)', 55);
INSERT INTO `se_development_time` VALUES (5001, 'Bio-Organic Armour (BM Upgrade)', 85);
INSERT INTO `se_development_time` VALUES (5002, 'EWP (BM Upgrade)', 55);
INSERT INTO `se_development_time` VALUES (5003, 'Advanced Engine Upgrade', 130);

-- --------------------------------------------------------

-- 
-- Table structure for table `se_games`
-- 

DROP TABLE IF EXISTS `se_games`;
CREATE TABLE `se_games` (
  `game_id` tinyint(4) NOT NULL auto_increment,
  `name` varchar(45) NOT NULL default '',
  `db_name` varchar(45) NOT NULL default '',
  `admin_name` varchar(200) NOT NULL default '',
  `admin_pw` varchar(33) NOT NULL default 'passwd',
  `admin_email` varchar(45) NOT NULL default '',
  `status` tinyint(4) NOT NULL default '1',
  `paused` tinyint(4) NOT NULL default '1',
  `description` text NOT NULL,
  `intro_message` text NOT NULL,
  `num_stars` int(11) NOT NULL default '150',
  `todays_tip` int(11) NOT NULL default '1',
  `hourly` smallint(6) NOT NULL default '0',
  `daily` tinyint(4) NOT NULL default '0',
  `last_hourly` int(100) NOT NULL default '0',
  `last_daily` int(11) NOT NULL default '0',
  `difficulty` int(11) NOT NULL default '3',
  `last_reset` int(11) NOT NULL default '0',
  `days_left` smallint(6) NOT NULL default '0',
  `session_id` varchar(40) NOT NULL default '',
  `session_exp` int(11) NOT NULL default '0',
  `user_agent` varchar(32) NOT NULL default '',
  `last_access_admin_forum` int(11) NOT NULL default '0',
  `random_filename` int(11) NOT NULL default '-1',
  PRIMARY KEY  (`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `se_ship_types`
-- 

DROP TABLE IF EXISTS `se_ship_types`;
CREATE TABLE `se_ship_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `type` varchar(30) NOT NULL default '',
  `class_abbr` varchar(10) NOT NULL default '',
  `cost` int(11) NOT NULL default '0',
  `tcost` int(11) NOT NULL default '0',
  `fighters` int(11) NOT NULL default '0',
  `max_fighters` int(11) NOT NULL default '0',
  `max_shields` int(11) NOT NULL default '0',
  `max_armour` int(11) NOT NULL default '0',
  `cargo_bays` int(11) NOT NULL default '0',
  `mine_rate_metal` int(11) NOT NULL default '0',
  `mine_rate_fuel` int(11) NOT NULL default '0',
  `descr` text NOT NULL,
  `size` tinyint(4) NOT NULL default '0',
  `config` set('bs','sh','hs','ls','na','po','so','oo','sv','sw','er','sj','ws','e1','e2','fr','sc','bo','br') NOT NULL,
  `upgrade_slots` int(11) NOT NULL default '0',
  `auction` tinyint(4) NOT NULL default '0',
  `move_turn_cost` int(11) NOT NULL default '1',
  `point_value` int(11) NOT NULL default '0',
  `num_pc` int(11) NOT NULL default '0',
  `num_ot` int(11) NOT NULL default '0',
  `num_dt` int(11) NOT NULL default '0',
  `num_ew` int(11) NOT NULL default '0',
  PRIMARY KEY  (`type_id`),
  KEY `type_id` (`type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=400 ;

-- 
-- Dumping data for table `se_ship_types`
-- 

INSERT INTO `se_ship_types` VALUES (1, 'Ship Destroyed', '', 'SDestroyed', 10000, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, '', 0, 0, 1, 0, 0, 0, 0, 0);
INSERT INTO `se_ship_types` VALUES (2, 'Escape Pod', 'Escape Pod', 'EP', 10000, 0, 0, 10, 10, 10, 10, 3, 0, 'If you''re in one of these, you''re pretty darn dead. So hurry up and get a proper ship.', 1, 'er', 0, 0, 1, 5, 0, 0, 0, 0);
INSERT INTO `se_ship_types` VALUES (3, 'Scout Ship', 'Scout Ship', 'SS', 1500, 0, 15, 70, 30, 15, 10, 1, 1, 'Though small, and cheap, this ship has a lot of potential. It''s invaluable for scouting in the random-event games, and games where it warp cost is based on ship size.<br />It also has a number of tactical uses.', 2, 'hs,na', 0, 0, 1, 5, 0, 0, 0, 0);
INSERT INTO `se_ship_types` VALUES (4, 'Merchant Freighter', 'Freighter', 'MF', 5000, 0, 50, 300, 150, 20, 150, 1, 5, 'Everyones favourite ship, and an old classic.<br />Good for mining, early attacking, scouting, and pretty much everything else.', 3, 'fr', 1, 0, 2, 10, 0, 0, 0, 0);
INSERT INTO `se_ship_types` VALUES (5, 'Stealth Trader', 'Freighter', 'ST', 28000, 0, 100, 300, 150, 20, 900, 9, 3, 'The strength of this ship is it''s cavernous cargo bays, as well as the fact it''s highly stealth.<br />It does however, have to get the cargo capacity from somewhere, and this is done by nearly eliminating the defences.', 4, 'hs,fr', 2, 0, 4, 15, 0, 0, 0, 0);
INSERT INTO `se_ship_types` VALUES (6, 'Harvester Mammoth', 'Freighter', 'HM', 40000, 0, 100, 500, 300, 30, 750, 5, 12, 'The fastest miner on the market, it also boasts 750 cargo bays and more than adequate defenses.<br />This is a well rounded ship with a multitude of purposes.', 4, 'fr', 4, 0, 4, 20, 0, 0, 1, 0);
INSERT INTO `se_ship_types` VALUES (7, 'Attack Battleship', 'Battleship', 'AB', 15000, 0, 250, 5000, 100, 50, 0, 0, 0, 'A general-purpose warship, and the lightest in the group. <br />Good early on in the game if you fancy picking on someone.<br />Also comes with 1 Pea Shooter Cluster, and 1 Defensive turret array.', 4, 'bs', 5, 0, 3, 30, 0, 1, 1, 0);
INSERT INTO `se_ship_types` VALUES (8, 'Warmonger', 'Battleship', 'WM', 35000, 0, 600, 9000, 195, 90, 0, 0, 0, 'Heavier than the AB when it comes to a fight, this ship is capable of holding its own.<br />High fighter capacity, as well as a scanner, 2 Pea Shooter Clusters and 2 Defensive Turret Arrays, make this a must buy for anyone anticipating a bad day at the office.', 5, 'bs,sc', 4, 0, 4, 40, 0, 2, 2, 0);
INSERT INTO `se_ship_types` VALUES (9, 'Skirmisher', 'Battleship', 'Skirm', 100000, 0, 1000, 17000, 500, 200, 0, 0, 0, 'If its all out war you want, then this is where you''ll get it.<br />This one has everything any warship ever needed. Lots of firepower (including 3 Pea Shooter Clusters, and 1 Defensive Turret Array), as well as some added extras such as scanner and light stealthing. <br />The neighbours will know when you bring one of these home.', 6, 'bs,ls,sc', 2, 0, 5, 50, 0, 1, 3, 0);
INSERT INTO `se_ship_types` VALUES (10, 'Occultator', 'Carrier', 'Occ', 520000, 0, 7, 120000, 1000, 5, 0, 0, 0, 'Welcome to the newest craze in the galaxy!<br />A hollowed out Asteroid with some very big engines attached to it. <br />The cost of this ship reflects the enourmous amount of effort required to engineer this monstrosity.<br />However, due to it''s obscene size, the ship is a sitting duck in a space based fire-fight, and will do significantly less damage than a normal ship would. So keep these ships well protected.\r\n<br />On the other hand they are ideal for planet leveling - where their size is an help rather than a hinderance, and moving large numbers of fighters, and gain a damage benefit in this field.', 7, 'bs,po', 3, 0, 7, 120, 0, 0, 0, 0);
INSERT INTO `se_ship_types` VALUES (11, 'Transverser', 'Warp-point Generator', 'TV', 210000, 0, 400, 500, 0, 300, 0, 0, 0, 'Using the latest Sub-space jump technology, this ship can move fleets anywhere in the Cosmos.  Very good ship for large-scale movements, but also uses alot of turns making the jumps.<br />Has 1 Defensive Turret Array to help protect your investment.<p />Due to technical limitations with Jump drives, ship including this technology cannot be fitted with shields. However they do have more than sufficient armour to make up for this inadequacy.', 5, 'na,sj', 3, 0, 4, 30, 0, 0, 1, 0);
INSERT INTO `se_ship_types` VALUES (12, 'Brobdingnagian', 'Flagship', 'Brob', 750000, 0, 2500, 32000, 1000, 750, 3000, 0, 0, 'The leviathan of space, and capable of making moons quake, this hulking mass of a ship is the best command ship you can legally buy.<br />Comes with built in Scanner and Quark Disrupter, and thats on top of the excellent offensive/defensive abilities it comes with too (including 5 Pea Shooter Clusters, 5 Deffensive Turret Arrays and a whole load of armour).<br />You''ll wonder what you ever did without it.', 8, 'oo,sv,sc', 0, 0, 7, 150, 0, 5, 5, 0);
INSERT INTO `se_ship_types` VALUES (13, 'Flexi-Hull(tm)', 'Modular', 'FH', 20000, 0, 100, 100, 100, 0, 100, 2, 6, 'Designed with the idea that users can do as they wish with this ship, it''s completely flexible, allowing for many applications in the hostile and changing universe.', 4, '', 30, 0, 5, 20, 0, 0, 0, 0);
INSERT INTO `se_ship_types` VALUES (14, 'Mega-Flex(tm)', 'Modular', 'M-Flex', 45000, 0, 100, 100, 100, 0, 100, 7, 7, 'Bigger, and with more upgradability than ever before, this ship is at the top in the tech tree for Modular Design.', 5, '', 70, 0, 6, 25, 0, 0, 0, 0);
INSERT INTO `se_ship_types` VALUES (15, 'Civilian Transport', 'Carrier', 'CT', 50000, 0, 100, 500, 200, 100, 4000, 0, 0, 'A ship dedicated to the pursuit of getting cargo around the galaxy. Comes with high stealth and twin Defensive Turret Arrays, but alas it cannot attack.', 4, 'hs,na', 3, 0, 3, 10, 0, 0, 2, 0);
INSERT INTO `se_ship_types` VALUES (16, 'Super Skirmisher', 'Battleship', 'SSkirm', 300000, 0, 3000, 20000, 1000, 250, 0, 0, 0, 'A Great ship for getting rid of those pesky enemies, as it incorporates 5 Plasma Cannon Clusters and 2 Electronic warfare Pods, as well as a high fighter capacity, and lots of shields.', 7, 'bs,sh,hs,sc', 5, 1, 6, 60, 5, 0, 0, 2);
INSERT INTO `se_ship_types` VALUES (17, 'Mega Miner/Cargo', 'Mega-Flex(tm)', 'MMC', 210000, 0, 1000, 1500, 500, 100, 5000, 10, 22, 'Home to vast cargo bays that could house an army of colonists, as well as an exeptional mining rate and two Arrays of Defensive Turrets, this ship is the best money can buy for mining and cargo carrying.<br />If only there were more of them.', 6, 'hs,na,fr', 5, 1, 6, 30, 0, 0, 2, 0);
INSERT INTO `se_ship_types` VALUES (18, 'Adv. Transverser', 'Transverser', 'Adv. TV', 320000, 0, 1500, 3000, 0, 600, 0, 0, 0, 'The 8th Wonder of Transport Tech. Excellent for autoshifting, as the wormhole stabiliser comes built in. However it cannot attack, but has 2 Defensive Turret Arrays to ward off enemy ships, and a load of armour to make up for its lack of shields.<p /><center><a href=./images/ships/ship_18.jpg/ target=_blank><img border=0 height=120 width=160 src=./images/ships/ship_18_tn.jpg></a></center>', 6, 'hs,na,sj,ws', 1, 1, 3, 40, 0, 0, 2, 0);
INSERT INTO `se_ship_types` VALUES (207, 'Explorer Mark I', 'Alien Scout', 'EM1', 40000, 0, 100, 300, 400, 20, 50, 2, 2, 'Fell off the back of an Alien Fleet. Includes 25$ increased Shield Regeneration, and self-repairing Bio-Organic Armour. <p /><center><a href=./images/ships/ship_207.jpg/ target=_blank><img border=0 height=120 width=160 src=./images/ships/ship_207_tn.jpg></a></center>', 1, 'sh,ls,sc,bo', 0, 1, 1, 10, 0, 0, 0, 0);
INSERT INTO `se_ship_types` VALUES (301, 'Mammoth Ram-Scoop', 'Adv. Freighter', 'HMR', 72000, 150, 0, 0, 120, 40, 1500, 3, 16, 'The new age of mining has dawned with the introduction of advanced Ram-Scoop Technology which allows this ship to collect fuel each time it moves between systems. The elimination of fighters and most shields allows for more cargo bays, while the Ram-Scoop allows for a much improved fuel mining rate.<br />The Electronic Warfare module makes up for most of its defensive shortcomings. <br />Comes with two upgrade pods for slight customisation.', 6, 'fr,br', 2, 0, 5, 20, 0, 0, 0, 1);
INSERT INTO `se_ship_types` VALUES (302, 'Mammoth Asteroid Processor', 'Adv. Freighter', 'HMA', 72000, 150, 0, 0, 120, 40, 1500, 17, 2, 'Spawned at the same time as the Mammoth Ram-Scoop, the advanced Asteriod Processing facilities allows for the ability to collect metal simply by moving between systems.<br />The increase in cargo bays has led to the elimination of fighters and most shields, but there was still space to put a Electronic Warfare module on to assist in defense.<br />Two upgrade pods allow for slight customisation.', 6, 'fr,br', 2, 0, 5, 20, 0, 0, 0, 1);
INSERT INTO `se_ship_types` VALUES (303, 'GunShip', 'Battleship', 'GB', 35000, 350, 300, 4000, 150, 50, 0, 0, 0, 'A fast, but lightly armed and armoured warship that uses 5 Plasma Cannons as its teeth, self repairing Bio-Organic Armour as it''s hide, and 2 Electronic Warfare modules for additional damage.<br />This ship is ideal for taking out light enemy vessels, with minimal fighter losses. It also has light stealth and a scanner to assist in the task.', 3, 'bs,ls,sc,bo', 1, 0, 2, 35, 5, 0, 0, 2);
INSERT INTO `se_ship_types` VALUES (304, 'Behemoth', 'Battleship', 'Bm', 350000, 1000, 1500, 24000, 600, 350, 0, 0, 0, 'Designed from scratch to be the best ship-to-ship combat machine ever concieved, this beast has an amalgamation of both Earth based, and Alien technologies.\r\n<p />7 Plasma Cannons, backed up by 2 Defensive turrets and 3 Electronic Warfare pods help support its large complement of fighters which get a bonus in ship-to-ship combat.\r\n<br />To round it all off, it has a scanner, light stealth and it\\''s armour is bio-organic allowing it to repair itself.\r\n<p />However because this ship was designed solely for space based operations, it does suffer a hefty dis-advantage in planetary assault operations.<br />Note: These ships are <b class=b1>Particularly</b> effective against Planet Only Ships.', 7, 'bs,ls,so,sc,bo', 1, 0, 7, 90, 7, 0, 2, 3);
INSERT INTO `se_ship_types` VALUES (399, 'Alien Battlestar', 'Flagship', 'BStar', 805000, 2000, 3000, 40000, 1000, 700, 500, 0, 0, 'If you thought the Brobdingnagian was the Emperor of Space, think again.\r\n<br />The alien vessel this ship is based on was found derelict in a remote star system. Much of it''s technology remains mystifying.\r\n<p />Armed to the teeth with an incredible 10 Plasma Cannons, 4 Electronic Warfare modules, self-repairing bio-organic armour and a scanner for good measure, this ship will lead your fleet to battle in true style.', 8, 'oo,sc,bo', 0, 0, 7, 200, 10, 0, 0, 4);

-- --------------------------------------------------------

-- 
-- Table structure for table `server_issuetracking`
-- 

DROP TABLE IF EXISTS `server_issuetracking`;
CREATE TABLE `server_issuetracking` (
  `id` int(14) NOT NULL auto_increment,
  `creation` int(11) NOT NULL default '0',
  `title` varchar(40) NOT NULL default '',
  `description` longtext NOT NULL,
  `STATUS` smallint(2) unsigned zerofill NOT NULL default '10',
  `login_id` int(11) NOT NULL default '0',
  `game` char(30) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `user_accounts`
-- 

DROP TABLE IF EXISTS `user_accounts`;
CREATE TABLE `user_accounts` (
  `login_id` int(11) NOT NULL auto_increment,
  `login_name` varchar(30) NOT NULL default '',
  `passwd` varchar(33) NOT NULL default '',
  `auth` int(11) NOT NULL default '0',
  `session_exp` int(11) NOT NULL default '0',
  `session_id` varchar(40) NOT NULL default '',
  `in_game` varchar(30) NOT NULL default '',
  `email_address` varchar(40) NOT NULL default '',
  `signed_up` int(11) NOT NULL default '0',
  `last_login` int(11) NOT NULL default '0',
  `login_count` int(11) NOT NULL default '0',
  `last_ip` varchar(16) NOT NULL default '',
  `num_games_joined` int(11) NOT NULL default '0',
  `page_views` int(11) NOT NULL default '0',
  `pass_change` varchar(200) NOT NULL default '0',
  `real_name` varchar(50) NOT NULL default '',
  `last_access_global_forum` int(11) NOT NULL default '0',
  `icq` int(11) NOT NULL default '0',
  `aim` varchar(50) NOT NULL default '',
  `msn` varchar(50) NOT NULL default '',
  `yim` varchar(50) NOT NULL default '',
  `con_speed` tinyint(4) NOT NULL default '2',
  `default_color_scheme` tinyint(4) NOT NULL default '1',
  `user_agent` varchar(32) NOT NULL,
  PRIMARY KEY  (`login_id`),
  UNIQUE KEY `login_name` (`login_name`),
  UNIQUE KEY `email_address` (`email_address`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- 
-- Dumping data for table `user_accounts`
-- 

INSERT INTO `user_accounts` VALUES (1, 'Admin', '', -1, 0, '', '', 'Tyrant of the Universe', 1, 1, 1, '', 0, 0, '0', 'Game Administrator', 0, 0, '', '', '', 3, 1, '');
INSERT INTO `user_accounts` VALUES (2, 'Pirates', '5504293.07987824', -1, 0, '', '', 'In-game AI', 1, 1, 1, '', 0, 0, '0', 'Created by Jonathan "Moriarty"', 0, 0, '', '', '', 1, 1, '');
INSERT INTO `user_accounts` VALUES (3, 'Aliens', '496444865.479083', -1, 0, '', '', 'In--Game AI', 1, 1, 1, '', 0, 0, '0', 'Created by Jonathan "Moriarty"', 0, 0, '', '', '', 1, 1, '');
INSERT INTO `user_accounts` VALUES (4, 'Standby', '2462151', -1, 0, '', '', 'Standby Account', 1, 1, 1, '', 0, 0, '0', 'Standby Account', 0, 0, '', '', '', 1, 1, '');
INSERT INTO `user_accounts` VALUES (5, '2nd Standby', '573947523', -1, 0, '', '', '2nd Standby Account', 1, 1, 1, '', 0, 0, '0', '2nd Standby Account', 0, 0, '', '', '', 1, 1, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `user_history`
-- 

DROP TABLE IF EXISTS `user_history`;
CREATE TABLE `user_history` (
  `login_id` int(11) NOT NULL default '0',
  `timestamp` int(11) NOT NULL default '0',
  `game_db` varchar(30) NOT NULL default '',
  `action` text NOT NULL,
  `user_IP` varchar(16) NOT NULL default '',
  `other_info` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
