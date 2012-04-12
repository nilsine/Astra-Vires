<?php
require_once("common.inc.php");

print_header("Archive Solar Empire News");


?>

<blockquote>
<center>
<br /><br /><p />Back to <a href='index.php'>Login</a>
</center>



<b>05:15 - Sep 08, 2003</b>
<br />I've created a nice little 'forgot your password' script for forgetful players who forget their password. It won't give you your old password, but it will let you create a new-one.
<p /> - Moriarty


<p /><br /><b>09:53 - Aug 24, 2003</b>
<br />Been quiet for a while, but fixed a few bugs today.
<p />Also re-added autowarping of minerals between planets (left the links out by accident from when i implemented the improved planet screen)
<p /> - Moriarty


<p /><br /><b>07:37 - Jul 16, 2003</b>
<br />Morning folks.
<p />The present admin of the slow game (Crector) is now stepping down, and will be replaced by 'Azzkikka'.
<br />Thanks to Crector keeping it running. :)

<p /><br /><b>I have made a change today too.
<br />The planets screen has been improved. It is now possible to load and unload your ships with much more control (at least thats the aim :) ) than before.
<br />You can even load and unload groups of ships that you select. You can even load/unload ships individually, <b class='b1'>Without</b> having to be in command of them!.
<p /> - Enjoy.
<p /> - Moriarty


<p /><br /><b>08:59 - Jul 12, 2003</b>
<br />I've added a new admin variable to the game (<b class='b1'>uv_explored</b>.
<p />When set, this will mean that players can't see the universe map, and so will have to explore the universe themselves (to see where the links go etc).
<br />It's not possible to transwarp/subspace jump to a system that you have not explored!
<p />I've also fixed a number of bugs.
<p /> - Moriarty


<p /><br /><b>04:19 - Jun 19, 2003</b>
<br /><b>Major Update</b>
<p />Stage 2 of my 'fleet attack system' has now been uploaded.
<p />The <b class='b1'>major improvements</b> of this stage are:
<br /> - You can now <b class='b1'>attack planets with your entire fleet</b> (similar to attacking an enemy fleet).
<br /> - Planets can now come to the assistance of fleets. This sort of replaces the old 'hostile planets' system.
<br /> - It is now possible to <b class='b1'>SIMULATE</b> combat. It costs 5 turns to do, and gives a general idea of what the outcome will be.
<br /> - Improved reporting of the combat.
<p />Those are the major highlights. There are numerous other improvements with the system.
<p />I <b>AM</b> Anticipating there are bugs in the system. If you find any, please report them to me.
<p /> - Moriarty


<p /><br /><b>05:53 - Jun 16, 2003</b>
<br />- When a player logs out, they will no longer be listed as an 'active player'.
<br /> - Most images in the game now have 'alt' comments. These allow a user to get an idea of what the image is, if the image doesn't load for some reason (i.e. the browser doesn't accept images).
<br /> - Fixed some bugs.


<p /><br /><b>07:29 - Jun 12, 2003</b>
<br />Some Minor <b>Changes</b>
<p /> - It is no longer possible to use your account password as a planet password (believe it or not people did do this!!!).
<br /> - Passwords that are vaguely similar to your account password cannot be used for either planets or clans.
<br /> - Fixed a few other minor issues.
<p /> - Moriarty


<p /><b>05:51 - Jun 11, 2003</b>
<br />As some of you may have noticed by now, I've implemented a automatic logging system into the game.
<br />If the game enconterers an error relating to the database, it will be logged automatically. This saves you folks having to report it.
<br />However, you should continue to report any errors that don't tell you not to bother reporting them. It's not a foolproof system yet. :)
<p /> - I've added 7 new 'tips of the day'. They will appear randomly in the future.
<br /> - Ship values for scoring are now 100 times higher. So a ship that used to be worth 5 points, is now worth 500. This should make scores reflect ship kills more, and fighter kills less.
<br /> - In respect to the above point value increase, the number of points required for a ship to gain a 'level' of 'experience' is now 4500.
<br /> - A few admin related fixes.
<p /> - Moriarty


<p /><b>06:38 - May 29, 2003</b>
<br />Quite a while since my last update.
<br />Much of the stuff i've uploaded today was actually done a week or two ago... Just never got around to uploading it till now.
<p />Changes.
<br /> - Maximum population is now fully and properly implemented on planets.
<br /> - Colonists shouldn't go all screwy and into the negative any more. Same goes for fighters and fighter allocation.
<p />I've made a load of other changes, but those are either 'under the hood', or admin only type things....
<p />The good news is that i've been doing quite a bit more work on the fleet attacking... The ability to fleet attack planets and use planets for fleet defence is comming along nicely. But that won't be uploaded till it's completed. ;)
<p /> - Moriarty


<p /><br /><b>05:56 - May 06, 2003</b>
<br />Major update time. 
<p />I have just uploaded a whole load of fixes, as well as a bunch of pretty hefty balance <b>changes</b> to the game.
<br />Fixes:
<br /> - Some 'behind the scenes' stuff.
<br /> - Now impossible for other planets in system 1 to show up (even if they excist).
<br /> - Admin can now view player histories properly again.
<br /> - No longer possible to collect turns from bilkos if they will take your turns over your turn limit.
<br /> - Attack link will no longer show if ship is in Sol, and attacking at Sol is disabled.
<br /> - Improved error reporting (though it still replies on you players... so maybe not.. :)
<br /> - And a few other minor fixes.
<p />Balance changes are listed <a href='http://se.purely.info/forums/viewtopic.php?t=45' target='_blank' >Here</a> at the purely forums.
<br />Please post any comments about the balance changes to that thread. But try to give them at least a couple of days before you start moaning... :)
<p /> - Moriarty

<p /><b>10:34 - Apr 15, 2003</b>
<p />Me again.
<br />I've been doing some stuff that most of you won't be able to notice.
<p />BUT, i have changed the universe generator. It's now in a different language, and runs significantly faster. But that's not what you folks are interested in.
<br />It also has a few extra features:
<br /> - Admins have a new variable <b>uv_max_link_dist</b> that allows them to dictate the maximum distance a link between star systems may be.
<br /> - New 'Filled in Circle' universe layout. All the stars are placed within a invisible circle.
<br /> - New 'Ring' layout. This places stars in a ring, with the Sol system in the middle.
<br /> - New 'layered ring' layout. Two or even three rings of stars with Sol all on it's own the middle.
<br /> - The ability for admins to 'preview' universes that use their the present variables.
<p />All in all, these changes should mean some new and interesting universe types lying around out there in future games.
<p /> - Moriarty


<p /><b>05:20 - Apr 13, 2003</b>
<p />It's admin seeking time again.
<p />Seeing as the admin proper for the slow game hasn't logged in for some time i've taken over the slow game and am going to bring it to a fairly swift conclusion.
<p />I am therefore going to have to seek out a new admin for the slow game.
<p />Also note that the slow game now has maints running once per <b class='b1'>two</b> hours. It used to be once per three, however that seemed to be a little too slow.
<p /> - Moriarty


<p /><b>02:31 - Mar 31, 2003</b>
<p />Morning Folks.
<p />Been quiet for a while.
<p />A week or two back i removed omega missiles. Though if you still had one you could fire it. I forgot to announce i though. :)
<p />The blitz admin is presently being changed. Many thanx to hunter for his long tenure as it's previous admin.
<br />The admin for the next game will be LockJaw, followed by a second, 'to be announced', admin. Then out of the two of them i'll pick the one permanent admin.
<p />A changes list follows:
<br /> - It is now possible to transfer MANY ships to another player.
<br /> - Transversers can no longer attack. (Adv. Tvs already has this ability). This will allow for simpler attacking of the enemy whilst all your ships are in one fleet.
<br /> - It is now possible to buy the maximum number of defensive weapons at the upgrade Black Markets (same as is possible in the normal upgrade market).
<br /> - Cost to blow up ships is now 100 credits. Used to be 1 turn.
<p /> - Moriarty


<p /><b>11:24 - Mar 24, 2003</b>
<p />New ship added!
<br />Yes, thought that'd get your attention. :)
<p />I've just added what amounts to the 23rd ship into the game.
<br />It's a anti-ship ship, that does rubbish against planets, but kills off other ships with ease.
<br />It can only be brought from BM's.
<p />The Occultator has been removed from BM's and placed into Sol.
<p />There's a story that covers both of these changes in the stories section of the help file.
<p /> - Moriarty


<p /><b>06:28 - Mar 18, 2003</b>
<p />The test for fleet attacking are still going strong. It's not fallen apart yet. :)
<p />I've made a few <b>Changes</b> today:
<br /> - It is now possible to buy armour for your ships (equipment shop).
<br /> - It is possible to fill your fleet full of shields and armour now (equipment shop).
<br /> - Added some popup help for shields, armour and fighters in the equipment shop. Hopefully more is to come.
<br /> - I have deleted the 'allow_popups' user option, as there is little need for it any more.
<br /> - The cost of shields at earth have been lowered.
<br /> - And a few bug fixes and other 'under the scenes' changes too.
<p /> - Moriarty


<p /><b>20:42 (GMT) - Mar 17, 2003</b>
<p />The testing of the fleet attack system is going well. A few minor bugs fixed in it, but other than that, it's still working. :)
<p />I am now taking requests for a new admin for Blitz, as Hunter (the present admin) would like to step down. Admin request form is above this news post.
<p />I have just uploaded a boat full of <b>changes</b>.
<br /> - Bio-Organic Armour is fully implemented in the game. It can only be brought from black-markets, and will reduce your maximum armour capacity by 10%. However it will also make your armour auto-regenerate like shields.
<br /> - Silicon armour is now removed from the game. The new attack system makes it obsolete.
<br /> - It is now possible to purchase more armour capacity for a ship at the Upgrade store in Sol. You can use standard and mass upgrade options.
<br /> - Fixed a few minor bugs. Also made quite a lot of 'under the bonnet' changes to numerous areas of the game.

<p /> - Moriarty


<p /><b>22:39 GMT - Feb 23, 2003</b>
<p />A few more <b>changes</b>.
<br /> - There are now <b class='b1'>two different ship limits</b>. One of them limits the number of warships you may have (ships with the battleship upgrade), the other limits all the rest of the ships. This should allow the admin more control over just how agressive the players can become.
<br /> - Due to the above change, it is now NOT possible to put more than 4999 fighter capacity onto one ship unless the ship is classed as a battleship (bilkos upgrade).
<br /> - The attack pack (as brought from bilkos) now increase fig capacity by 900 - up from 700).
<br /> - Fixed a lot of the bugs that have been reported.
<br /> - ships are no longer listed on the player info screen. They can now only be found under fleet command.
<p /> - Moriarty


<p /><b>02:14 - Feb 22, 2003</b>
<p />I have made some rather dramatic changes to the game and have just uploaded them.
<br />There are now fleets in the game. All ships form part of one fleet.
<br />I have <b class='b1'>Got rid of Towing!</b>. Instead of towing now all that happens is that every ship in a fleet will follow every other ship in the same fleet. So there is no real 'command ship' any more. You can command any ship in a fleet, and all the rest in the same fleet will follow.
<p /> - Each player can have up to 120 seperate fleets!
<br /> - At present the Fleets only purpose is to allow for more efficient towing. However when fleet attacking is complete they will have many more uses.
<br /> - Any new ships you buy are in the same fleet as the ship you are commanding.
<br /> - A ship can only be assiged to 1 of your fleets at a time.
<p />There are also Clan Fleets. These will server simply to afford clan members mutual protection. At present they don't do anything. Again this will change with fleet attacking completed.
<p />I have added <b class='b1'>Armour</b> to the game. At present no ships have any as it doesn't do anything.
<p />There is now a new page called <b class='b1'>Fleet Command</b> this will allow you to do lots of stuff with fleets. It is from this screen that you must now self-destruct ships from.
<p /> That lot shouldn't be too much to take in. Enjoy. :)
<p /> - Moriarty


<p /><b>07:15 - Feb 21, 2003</b>
<br />This will probably be my last update for a few days.
<br />I'm about to start work on the fleet-attack system. And as well as that i'm going to be making a few drastic changes to planets, and probably bombs too.
You won't see any of these changes for a few days though. But they will all come at once when they do come.
<p />Recent <b>Changes</b>
<br /> - More bug fixes and code improvements.
<br /> - It is now possible to fill a fleet with goods from a port.
<p /> - Moriarty


<p /><b>01:56 (GMT) - Feb 18, 2003</b>
<br />Seems i forgot to post my changes yesterday. Oh well.
<br />Join the <b class='b1'>Advanced Empires</b> game if you want to play a version of SE where you don't start with all the tech. Should prove for a very interesting game.
<p /><b>Changes</b>
<br /> - Fixed one heck of a lot of minor bugs. :-)
<br /> - <b>Engine upgrades</b> are now available. A cheaper one at Sol, and a more expensive one at the Blackmarkets. They increase your speed in combat, and in games where the cost warp cost is based on the ships in the fleet, they can lower the warp cost between systems!
<br /> - Added a new story to the story collection (which can be found in the help pages). The story relates to the new engine upgrades.
<br /> - Admin can now give all players a cash injection.
<br /> - Fixed the bug that stopped planet passwords from being turned off.
<p />Anyone with any suggestions on how the interface for a new <b class='b1'>Fleet Attacking</b> function i'm working on should look, feel free to message them to me in any of the games.
<br />When i say fleet attacking, i mean like: "Your 5 Skirms and 2 Warmongers are attacking the enemies 1 Brob and 9 AB's".
<p /> - Moriarty



<p /><b>04:06 - Feb 16, 2003</b>
<p />Weeeeee....
<p />Well i've been hard at work playing with the code, and have some significant improvements to report:
<p /> - There is now a <b class='b1'>Ship Duplicator</b> at sol that will allow you to make an exact structural copy (everything except the contents of your hold) of the ship you are commanding.
<br />However Seatogu doesn't like alien tech, so only ships with tech you can get from Earth can be used.
<br /> - It is now possible to mass purchase any ship in the game (with the obvious, and not very small exceptions of the two flagships). This includes Black Market ships!
<br /> - There is now an much improved clan-ranking screen where you can easily compare clans statistics.
<br /> - Due to an astute observation by a player in the new Chaotic Empires game, when ship prices don't increase even if fighter prices do. This has now been rectified, so that the cost of a ship is based on it's hull price + the value of it's fighters.
<p /> - That's enough for today. It's 9am here. Bed time. :)
<p /> - Moriarty


<p /><b>12:53 - Feb 15, 2003</b>
<p />Booo...
<br />Yes i'm still here, and still playing with stuff.
<br />Today a few medium sized changes:

<br /> - The most obvious change is the system information that appears at the top of the star system screen. This information is now presented in a nice table based layout. It holds all the same information, but has an extra little feature pertaining to autowarp that shows what jumps are left to jump. If you don't like the new system there is a user option to change it to the old one.
<br /> - I have performed major surgery on the user options page. Gone is the day of complicated option change. Now it's nice and simple with radio buttons for most things, so you can easily see what you're getting yourself into. Take a look. You might just understand this version. :)
<br /> - Autowarp now runs from the star-system. So when you've entered a destination, you'll go straight back to the star system, rather than to a page saying it's all set.
<br /> - Autowarp now says how many jumps are left to a system (in both new and old).
<br /> - And one last thing: the user option to specify if you want planet reports all the time or some of the time, or none of the time is now removed. Instead you can now choose on a per-planet basis (so you can have your production planets on, and your defence planets off). Hope it makes life a little easier.
<p /> - Moriarty


<p /><b>01:32 - Feb 14, 2003</b>
<p />Guess who.
<br />Yep been playing with the code again, and this time we have a rather larger change than the other recent one's i've been making.
<p />I have put something so wonderfully marvelously genously awesome into the game that there's already a word for it:
<br />"Time"! :-)
<p />Well it's not THAT good, but it's better than nothing (note: other servers :) ).
<br />Basically it's an alternate play style, and can be turned on or off by the admin (hehe, it's better than real life. U can't turn time off here :) ). What it does is stagger the release of technology/planets/ships/everything. <br /> The game will generally start with only MF's and SS's available. Then once time is passing and players are using their turns, more items will become available. Players don't have to research anything, it just happens.
<p />So no more starting with access to a whole armarda, this is more realistic. Though eventually you will have access to you precious ABStars and Brobs, but not at the start.
<p /> - Enjoy. Advanced Empires will be restarting tomorow, and when it does, it will be the first game to have it turned on.
<br />Word of warning though: It's still in the test phase. The amount of time it takes for things to appear will probably need balancing. Afterall, you don't just invent time overnight... ;-)

<p /> - Moriarty


<p /><b>12:25 - Feb 13, 2003</b>
<p />Me again.
<br />Been playing with a few minor fixes and improvements:
<p /> - I have added 'planetary slots', similar to those that can be seen on the Tradelair server (the idea was originally proposed in the globals). It is only possible to create planets in a system with empty slots. For the 3 running games i have set all slots in all systems (bar sol) to 2. So that means that only 2 <b>more</b> planets can be built per system. This will have no effect on the present number of planets in a system.
<br /> -- From now on, when a game resets there will always be planet slots in a system. These slots are randomly placed (The admin can specify the most possible slots a system can be given) at universe creation.
<br /> -- They can ONLY be turned off by creating a universe with pre-created planets (a admin var i programmed a long time ago). So either way, from now on the maximum number of planets in a system will be limited by the admin. That means no more - 200 planets in 1 system... Otherwise known as 'turn burners'.
<br /> - Fixed the bug that let ramscoops load up more than their capacity of cargo.
<br /> - Clan members can now click on the 'new' link next to their clan forum to see new messages for the clan forum (same to the standard forum system).

<p />As you've probably noticed by now, there's now a fourth game on this server, called <b class='b1'>Chaotic Empires</b>, this game will (like all the others) be different from the norm, in that every game will have a different admin.
<br />This should allow more people to get to know what the admin can and cannot do, as well as present some very new game types (one of the conditions of becoming an admin for the game is that you be original with the game vars).
<br />There are after all about <b class='b1'>1.86463338260134107707700092928e+142</b> possible combinations for all the admin game vars (that's written as: 1,864,633,382,601,341,077,077,000, 929,280,000,000,000,000,000, 000,000,000,000,000,000,000,000,000, 000,000,000,000,000, 000,000,000,000,000,000,000, 000,000,000,000,000,000,000,000 ). (That's more than there are theoretically atoms in our universe (only 78-81 0's on the end, rather than our 142!!!)
<br />True, you can't get that many different game types out of them, but I'd hazard a guess you can get at least a few hundred out of them. If not thousands.

<p /> - Either way it should keep people busy formulating new strategies and formulating games to beat those strategies. :)
<p /> - Moriarty


<p /><b>07:27 - Feb 11, 2003</b>
<p />Morning Peeps.
<p />Well i've been playing with the code again, the first time in a long time, and performing some long overdue work.
<p />Todays changes:
<br /> - It is no longer possible to use Omega Missiles, Quarks or Terras when the admin has planet attacking turned off.
<br /> - The admin now has the ability to make it so as when a player retires from a game they can't rejoin the game for however many hours. This should get rid of a few of the less ethical strategies (including cheating). :)
<br /> -- I've set the vars for the games as follows: Blitz: 12 hour wait. AE: 24 hour wait. Slow: 48 hours.
<p /> - There are now percentages shown on your player information page, similar to those shown on the clan page for your clan. This gives you one less reason to be in a clan now... ;-)
<br /> - I've also made a few other less significant changes and maintence fixes.
<p /> More will come soon, and purely will be back up there as the most advanced of the servers. :)
<p /> - Moriarty


<p /><b>08:45 - Feb 09, 2003</b>
<p />I have implemented some code that will stop omega missiles from setting planet colonists to idle, whilst still killing as many colonists as before.

<p />This will have some implications for those of your who used this bug as a strategy.
<p /> - Moriarty




<p /><b>02:56 - Jun 27, 2002</b>
<p />Morning Folks.
<p />Courtesy of <b class='b1'>Admiral V Pier</b> (<a href='http://fedintel.org' target='_blank'>http://fedintel.org</a>) there are now graphics for every ship that can be brought at Seatogus, and the black-markets. A couple of blackmarket ships also have images to their name.
<br />All ship images can be clicked to bring up a bigger image of the ship.
<p />Also, theres a nice new SE logo, also courtesy of the Admiral.
<p /> - Moriarty


<p /><b>12:40(BST) - Jun 21, 2002</b>
<p />Happy Summer Solstice All.
<p />I have resolved a number of bugs pertaining to my recent enhancements.
<p />I have also been playing with the universe generator after my recent improvements to it. I have generated a 3 universes, along with their universe maps.
<br />A relatively minor 1,500 star universe using the clusters star layout. The map can be found Here. 2050 Pixels wide/high
<br />A much larger 7,500 star universe using the clusters star layout also. The map can be found Here. 7550 Pixels wide/High.
<br />I also generatd a hefty 10,000 star universe (took about 2 hours!). The map is 1.7Mb, unfortunatly it has too many wormholes showing to be of any use. 9000 Pixels Wide/High.
<p />Feel free to take a look at them. They should help to bring home the problems involved with having Massive universes. Especially if there is only 1 Sol System.
<p /> - Moriarty


<p /><b>09:28- May 10, 2002</b>
<p />Well, courtesy of Maugrim the Reaper, there is now a "player relations" section in the game.
<p />From there you can set any player as: Enemy, Neutral (default), Ally, Or NAPed.
<p />At present they allow you track who you want to kill and who's your buddy, thats all. At a later date they will work in sync with Maugrims AI bombs. But they are a few weeks off or so i'm told.
<p />The idea is that the clan leader can set clans and players as allies or enemies or whatever. Then whenever someone in you see that player, (s)he will have a symbol next to their name which indicates the attitude your clan has towards them. You can then kill them or say hi depending on the symbol.
<p />Enjoy the update. At this moment in time, this is the only server with them installed!!! :)
<br />And thanks to Maugrim for implementing it (even though i am anti-clan! :) ).
<p /> - Moriarty


<p /><b>09:10- Mar 20, 2002</b>
<br />Righto. Where to begin.
<br /><br />As You are all probably aware the maints are now fixed again.
<br /><br />The new summary system for ships is courtesy of <b class='b1'>TheRumour</b>, however I decided to spruce it up a little to make it more user friendly (and efficient).
<br /><br />It is now possible to tow entire groups of ships - Like all of your merchants, or all of your skirms - at once, using either of the two tow methods. If a whole group is being towed, it will also tell you it is.
<br /><br />Provided you meet the requisite criteria (have run enough turns, the enemy is out of safe turns etc), an "attack" link will appear next to a group of enemy ships (that are displayed using the summary). This attack link points to the ship with the most fighters, out of the bunch in that collection. No attack link will appear next to highly cloaked ships, even if you do have a scanner, whilst using the summary.
<br /><br />Mass ship purcharse now use an improved numbering system: 01, 02,03...09,10,11
<br />Etc. This will result in them all being listed properly (unless u buy 100 or more at a time!).
<br /><br />That should make all your conquering days so much easier.
<br /><br /> - Moriarty


<p /><b>06:38- Mar 15, 2002<b>
<br />Morning Folks.
<p />The Random Events test game is taking more players. About 15 more to be precise. Vets only need apply, and though it is mid-game your starting cash should make up for some of the difference.
<p />Also, i got a bit bored a couple of days ago and so decided to play with the universe generation. Specifically the deployment of stars. At present there are three systems working:
<br />Random layout (stars are layed out randomly).
<br />Grid layout: Stars are layed out in the grid (but links between them are random).
<br />Galactic Core: My personal favourite. Most stars are clustered around the centre with sparse ones around the edges.
<p />They are a new admin var: uv_map_layout, and i have set the vets game to use the galactic core layout. Once the maps are generated u'll see what i mean.
<p />Anyone can think up other star layouts, forward em to the admin who'll get them to me. (already working on spiral and clusters! (they are mathmatically harder to do).
<p />Enjoy.
<p /> - Moriarty


<p /><b>08:28- Mar 06, 2002
<br />Admins: The first 3 scoring systems are (fully?) working. The 4th one hasn't been programmed yet, because I havn't figured out how its going to be figured out.
<p />Newbies game will reset tomorow it would seem.
<p />Oh, and jettisoning colonists now results in rather humorous posts to the news.
<p /> - Moriarty

<p /><b>Feb 19, 2002</b>
<br />Testing the new developer/server admin post server news script.
<br /><br />Players should note that the in-game news and diary search functions have recieved MAJOR overhauls, and, even though i do say so myself, they can rival all but the likes of google in their ability to find what you're after. <br />Give em a try. Enter say: "Admin Bomb" for a news search, and all entries where the admin has set off a bomb will be found, and ordered by relevence, and then time.
<br /><br />The victims of any recent bomb attacks (in the past couple of days) will have noticed that the message you recieve from the attack is now just one message telling you everything that was hit and destroyed. This should save the server some effort, as well as meaning if 100 of your ships get hit, you don't get 100 messages.
<br /><br />Couple of bug fixes too.
<br /><br /> - Moriarty


<p /><b>Feb 19, 2002</b>
<br />Admins: The setting of your admin variables should now work completly. Does in my game.
<br />It is now possible to sort the order that posts are displayed in a players history in a number of different ways.
<br />Fixed bug that made the nightly maint not run last night, so I hope it should run tonight.
<br /><br /> - Moriarty

<p /><b>Jan 09 2002</b>
<br />Morning all. Though I shouldn't, have I played with the code again. It really is quite addictive playing with this games code.
<br />Anyhow, fixed a few things up. Should be some bugs gone now. And a LOT of small things done too:
<br /><b>Changes</b>
<br /> - Now possible to change the order that planets and ships are displayed in both the clan page, and the player info page.
<br /> - Admin now has a list of all the planets in the universe.
<br /> - Admin can now see players auth code (already implemented in WFs game, but i couldn't be bothered to nab his code :) ).
<br /> - Now a link to game vars from login page.
<br /> - Now States admins name on login page (below game name).
<br /> - By popular demand now possible to bid for ships at bilkos even if you are at the ship limit.
<br /> - Planets are now listed properly in the star system. By planet name, then by fighter count. Was previously more of a random order.
<br /> - When entering a hostile system, the planet with the most fighters is the one that is used to attack the incoming fleet (that also was random before).
<br /> - And a dozen other little things that you may or may not notice. :)
<br />Enjoy<br /><br />- Moriarty

<p /><b>Dec 24, 2001</b>
<br />Happy Christmas Eve!  I decided to do a little work, so I fixed the map thingy.  Your game can start now, Norb.
<br /> - <b>TheMadWeaz</b>

<p /><b>Dec 17, 2001</b>
<br />Right mining should now be fixed in its entirety.
<br />Yes nice thing you're working on KC. Does it work yet? (i ain't tested it, but i will tonight :) ).
<br />Is now possible for the admin to determine the cost of warping between systems. From 0 to 1000. Is Also possible for admin to make it so as different ships require different amounts of turns to move between systems. From 1 turn to shift the scout, up to 7 turns to move the Brob 1 system. If this method is used, i'd advise admin to give players some more turns per system.
<br />Also fixed up a number of other bugs, and small things.
<br />Oh, and if you reply to a players message now, you'll be able to see a copy of the original message at the top of the screen. Should help you work out what expletives you're replying to. :)
<br /> - <b>Moriarty</b>

<p /><b>Dec 15 , 2001</b>
<br />Play nice girls.
<br />
<br />BTW Weaz..that thing is done, I need a game to test the thing on with a thing that the thing can be tested on. It will probably have to be an artificial thing unless you want to spend awhlie trying to get a natural thing for the thing to be tested on.
<br />- <b>KilerCris</b>

<p /><b>Dec 14 (12:16 am), 2001</b>
<br />Alright, mining should be somewhat normal now... mori- we gonna balence these mining rates soon?
<br />- <b>TheMadWeaz</b>

<p /><b>Dec 14, 2001</b>
<br />Kk.  Fixed mining and the sudden death bugs.  I'm gonna do some more work before I retire for the night.
<br />- <b>TheMadWeaz</b>

<p /><b>Dec 13 2001</b>
<br />Morning Again folks. Well i did say no big changes planned, but hey... this is me, and i generally can't resist them. Spent a while today creating an alternate play style of SE.
<br />This style is different in that different ships have different mining rates of metal and fuel. Some ships mine metal well, some mine fuel well (the same old ships, i've just messed with them a little :) ). This means that ships mine a little faster these days than they used to, and so the price of metal and fuel has dropped a little.
<br />The new play style is, of course, an admin variable, and hopefully at least one admin will get around to making a game with it set.
<br /><b>Changes</b>
<br />As well as that i've fixed the obligatory bugs, and made small improvments to little things.
<br />New play style. (as mentioned above).
<br />Players have the option to get varying amounts of reports from planets. Ranging from a production report from all planets, to a report from no planets, with the option to get them only from producing planets somewhere in the middle. Thats in the options sectiond.
<br />Oh and Scattering of ships shouldn't catch you off guard any more. Ships only scatter on the second hour someone has stayed in sol now. So if you happen to be in sol accidently during one main you're fine, but be sure to stay away on the second hourly maint.
<br />Any suggestions post em in a forum where i make an appearance, and i'll see what i can do. :)
<br />- <b>Moriarty</b>

<p /><b>Dec 12, 2001</b>
<br />Added a bunch of new owner stuff (mostly .php scripts that run the perl ones).
<br />Admins can now build universes w/ the click of a button (better than waiting a few hrs).
<br /><b>MORI</b>- don't overwrite any changes!  DL a new copy of the entire code, then add in your changes be4 you update.
<br />- <b>Weaz</b>

<p /><b>Dec 11, 2001</b>
<br />Little more info. Just about half the time i used "weaz" it was in a condecending manner. :)
<br />Anyhow fixed a few more bugs last night, but nothing new.
<br />- <b>Moriarty</b>

<p /><b>Dec 11, 2001</b>
<br />Little knowlage- Mori used my name (weaz) 5 times in the last post.
<br />- <b>TheMadWeaz</b>

<p /><b>Dec 11, 2001</b>
<br />Morning Folks. Months and months since last I posted to one of these. Anyhow I spent a few hours last night playing with the SE code and fixing the mess that Weaz created (he'll deny it I know :) ).
<br />Most of what i did has to do with small changes/fixes, but a few new options too.
<br /><b>Changes</b>
<br />- Fixes for inumerable bugs.
<br />- Optimized some of the code. i.e. the server should have an easier time of it.
<br />- There are a few new options, these include: show_icq, show_aim, allow_popups, show_config, tow_method.
<br />- The last one is my favorite, as it allows you to choose between Weazs method of towing ships (the old way), and my way (you know he actually DELETED the code for my way, not just commented it out). My way being using check boxes. So now both of us can be happy. :)
<br />- Most of the options allow you to undo things that weaz did (and that i don't like). So now users get the option rather than just weazs way on his server, and my way on my server.
<br />- No other big changes you folks need to know about, other than maybe the signups changed. But you'll all see that soon enough.
<br />My advice would simply be to go and look at the options page.
<br /><br />Oh, and version 1.4 will most likely be release soon. Seeing as there are lots of bug fixes and the like. (weazs changes included in the next release).
<br />- <b>Moriarty</b>

<p /><b>31-May-01</b>
<br />Nothing else to do today, so been playing with SE code. Number of bugs fixed. Most importantly, selling of metal and fuel is now different.
<br /><b>Changes</b>
<br /> - Dynamic Prices for metal and fuel. The more you sell at once the more money you for each unit sold. So if you sell 5 units you will probably only get 80 credits per unit, but if you sold 150, then you would get about 96 Credits per unit. This will help the peeps who don't login hourly. Its done on a per ship scale, so selling from your whole fleet and will be the same as selling from each ship individually.
<br /> - Fixed bug where colonist transporting was a bit grouchy. Should all be fixed again.
<br /> - Now states whether your planet(s) are set to hostile or passive in the star system. Should mean less of this annoying "but i left the planet on hostile last night, i'm sure i did" moments. :)
<br /> - Help has been updated a load. Theres some information about the new system for selling at ports, as well as some updated info on random events.
<br /> - If the sol scattering is on and you meet the criteria for being scattered you will get a warning come up when you are in Sol saying how long until the next scattering.
<br /> - A few other bug fixes.
<p /> - Moriarty

<p /><b>21-May-01</b>
<br />Been Quiet for the week, but not been compeltely inactive.
<br /><b>Changes</b>
<br /> - Ability for admin to choose which ships s/he wants available in the game.
<br /> - Bug Fixes
<p /> Have now released another complete version of the code which is uptodate (but doesn't include this message). Its numbered 1.001 (as in first proper release), and is available <a href='http://sourceforge.net/projects/solar-empire/'>here</a>.
<p /> - Moriarty

<p /><b>14-May-01</b>
<br />Lots of goodies for you all.
<br /><b>Changes</b>
<br /> - Invert selected things. This will allow you to automatically tick all the check boxes for towing ships or deleting messages or blowing up ships. Play around with it. It should be useful.
<br /> - Load of bug fixes. Amazed at how many of these little varmits i've got rid off.
<br /> - The anticipated and promised planet-to-planet autoshift. This'll let u move all those nice charming colonists quickly and easily.
<br /> - Shows when a clan-mate is online. This can be seen in your clans info page. This function only exists for clan-mates, not for all players in general (and it won't either).
<br /> - When a clan leader retires they MUST choose to either disband the clan, or assing a new leader, otherwise they won't get retired.
<p /> - Moriarty

<p /><b>10-May-01</b>
<p />Yay. All assignments for this year done, and so for the next month or so i can play with SE code.
<p />A goods days work:
<br /><b>Changes</b>
<br /> - The long overdue "load all" makes an appearance. This allows players to load a fleet with colonists from a planet (that is theirs).
<br /> - A number of bug fixes.
<br /> - Cloaked ships should now be displayed properly.
<br /> - Admin is able to disable the placing of gamma bombs in bilkos.
<br /> - Changed the logging method of messages to the diary. This should result in cutting the size of the forum by about 40%.
<p /> - Moriarty

<p /><b>6-Apr-01</b>
<br />At last i bothered to get around to putting in the "tow/release selected" function for ships. Seems to be working. Also put in a "select all" thingy so as u can select all your ships at once (and thus the link for tow all, release all in the sidebar is no longer there).
<br />As you'd expect numerous bug fixes too, along with other minor changes.
<p /> - Moriarty

<p /><b>2-Apr-01</b>
<br />Well lots of new stuff to add in today.
<br /><b>Changes</b>
<br /> - Auction house. Comes complete with:
<br /> -- Unique ship types.
<br /> -- Lots of special upgrades.
<br /> -- The amazingly powerful "terra maelstrom" (minimum price 1mil!);
<br /> -- Some other stuff.
<br /> - Also, missiles. These can be built from planets, and do percentage based damage against the target planet. This should remove the problem of boring slow end games, as people will have to keep planet fighters low to survive against missiles, but keep them high to survive against enemies. A balancing act.
<br /> - 3 new player options. 2 relating to images, and the last lets players list enemy ships in a quicker way (good for slower connections).
<br /> - The usual bug fixes.
<p /> Moriarty

<p /><b>1-Apr-01</b>
<br />Have been working on SE a bit, and some things u can expect to get uploaded monday:
<br /> - Some more player options.
<br /> - Some more bug fixes.
<br /> - Bilkos Auction House. (this is going to be good i hope :) ).
<p /> - Moriarty

<p /><b>26-Mar-01</b>
<br />Seems i'm doing this weekly now. Anyhow more bugs fixed, and a few changes too:
<br /><b>Changes</b>
<br /> - Clan forums. Only fellow clan members and the admin can access a clans forum. Obviously you only get a link to the forum if you are in a clan.
<br /> - Pause game. Admin now has the ability to pause a game. All this does is stop the maints from running. This is usefull to allow everyone to start the game at the same time.
<br /> - Placing fighters on a planet now uses turns. This is going to be rather contreversial, however from what i can tell, now taht there is a turn based protection system, it should use turns to place fighters onto a planet. Otherwise extremely potent planets can be generated and the player could well remain in the safe period.
<br /> - Is now possible to load all fighters from a planet onto all of a players ships in a system. A much demanded feature.
<br /> - Players are now able to make it so as the forum only displays the last messages printed to it since last the player accessed the forum. This aliviates the need for players to use "markers".
<br /> - Admin can send players and "initial intro" message. This message is sent when an players account is created. useful for telling players the rules.
<br /> - And of course lots of bug fixes.
<p /> - Moriarty

<p /><b>20-Mar-01</b>
<br />Well yet again been fairly quiet. Not done much other than fix a few bugs recently.
<p /> - Moriarty

<p /><b>12-Mar-01</b>
<br />Been relatively quiet over the past week, primarily as no major changes have taken place, though a number of bugs and balancing issues have been resolved. Also optimised (i.e. made better/faster) code in a fair few pages.
<br />Seems wolfden is dying on us again.
<br /><b>Changes</b>
<br /> - Is now possible to select messages you want deleting and then delete them (private messages).
<br /> - Can do the same thing for the diary too.
<br /> - Signature maximum length is now 150 char(acter)s.
<br /> - Added a couple of player vars to the options. Players can now set the news posts shown at a time, as well as the number of hours a forum shows at a time.
<p /> Have come accross a few other issues though. These include:
<br /> - Cookies being handled wrong by IE (least they don't seem to be working). This sort of messes with some of my more recent changes.
<br /> - The maints being processed wrong when done automatically (but they run just fine when done manually).
<p />Neither of those two is easy to fix, and so no solution will be forcoming for a while.
<p /> - Moriarty

<p /><b>4-Mar-01</b>
<br />Large amount of changes over the weekend:
<br /><b>Changes</b>
<br /> - Converted SE from .php3 to plain .php. Please update relevant bookmarks.
<br /> - Added "AutoShift" (automatic colonist mover).
<br /> - Changed autowarp a bit more to make it a bit more functional, and idiot proof :).
<br /> - Added "fill all" for colonists (i.e can buy lots of them from earth now all at once).
<br /> - Aliens now use bounties.
<br /> - Improved Aliens in some other ways.
<br /> - Fixed some bugs.
<br /> - Planet attack now uses more efficient code. It's also been fixed up a bit (ie can now set a planet to hostile even if a clanmate is in the system).
<br /> - Fixed up resource regen a bit.
<p /> - Moriarty

<p /><b>2-Mar-01</b>
<br />Woflden is happily coping with everything now. Wonder how long till it breaks down again? :).
<br />Anyhow, have fixed a few bugs, and changed a few things again. Mostly small stuff:
<br /><b>Changes</b>
<br /> -Messed with the admins "active users" page.
<br /> - Admin can now change the admin e-mail.
<br /> - Admin diary has 200 entries. Players entries stays at 50.
<br /> - Diary improved cosmetically.
<br /> - SNE's now use the bomb_cost var. They are always 5times more than GB's and Aplhas.
<br /> - Fixed it so as the one_user_one_comp var now works fully, even with resets. This is a potent anti-multi defense if set to 0 (or 1).
<br /> - Did some other stuff too.
<p /> Update... about 9 hours later:
<br />Have fixed a vast number of bugs in a number of different areas.
<br />Also found a autowarp which was coded by "semicolon". He uploaded it to CVS 4 days ago, and this is the first anyone has heard of it I think.
<br />I have merged the autowarp in with the game, and made a couple of improvements to it (primarily error checking, but also fixed one critical "bug").
<br /> Also updated the code in CVS, so if anyone has access, tomorow some time they can nab the "nightly CVS tarball".
<p /> - Moriarty

<p /><b>1-Mar-01</b>
<br />Well i've fixed up the DB a bit so as the ports are working, and peeps should be able to log-in again.
<br />The vars are the same as my game (cos its my DB that i had to nab), so I doubt many of you lot will like it. The vars will no-doubt be changed in due course by KC/Weaz.
<br />Oh, and my server seems to be working again.
<p /> - Moriarty

<p /><b>28-Feb-01</b>
<br /><h2>GRRRRRRrrrrrrrRRRRRAAAAAAAAAAAGGGGG!!!!!!!!!!!!!!!!!!!!</h2>
<br />MYSQL = GAY..WHY MUST THE DATABASE KEEP GETTING ^*%&$@ UP!!!!!! AGGGGGGGGG!!!!!!!!!!!!!!1
<br />*kicks computer* YEAH YOU BETTER WORK!!!!
<br />Ima start emailing them till it stops...
<p /> - KilerCris

<p /><b>27-Feb-01</b>
<br />A few big things. Have made an admin var, where only one player per computer may be allowed. Also planets now produce a lovely report over each maint.
<br /><b>Changes</b>
<br /> - Made it so as the admin can decide if more than one player per computer is allowed. THIS IS <b class='b1'>NOT</b> Done with IP's, so don't worry about that.
<br /> - A message is sent for each planet that is run through the nightly maint. It reports a lot of statistics about what the planet did and stuff.
<br /> - Cost of Minerals is now an admin var. This can allow for a much more dynamic game-set.
<br /> - Aliens and Pirates use the set mineral prices too.
<br /> - Fixed yet more bugs.
<br /> - Admin can now make a description of the game. This will help players decide which game they like the look of before joining.
<br /> - Messed with active user count thing.
<p /> - Moriarty

<p /><b>26-Feb-01</b>
<br /><b class='b1'>A MULTI-CHECKER!!!!</b>Thats right. Admin now has a page that finds multi's.
<br />Lots of small things done today, with a couple of big ones. Also Wolfden has been having some probs. I suspect its due to a lack of RAM. Have sent Bryan some info and am trying to get him to fix it.
<br /><b>Changes</b>
<br /> - Lots of small changes to clan stuff. Just display issues.
<br /> - Passwords and password verification had the once over.
<br /> - Fixed that darn bug with the apostrophe's on planets. It's no longer possible to use apostrophes, or quotation marks in a planet name.
<br /> - Fixed a few other minor bugs.
<br /> - Fixed the Darn annoying bug where it wasn't updated the database vars file, when the admin did somthing.
<br /> - Players can now destroy many ships at once (from the same place as before).
<br /> - basic e-mail verification now added.
<br /> - A few other small things.
<br /> - A multi-checker... You create a multi, and if its an obvious one, the Admin WILL find out about it. At present this is only basic, but it'll get much more advanced.
<br /> - Moriarty

<p /><b>23-Feb-01</b><br />
<br /> - Gamma bombs no longer hurt newbies.
<br /> - Alpha Bombs should no longer hurt newbies either.
<p /> - Moriarty

<p /><b>22-Feb-01</b><br />
<br /><b><font color=lime>YAY</b> We now have a database at sourceforge. This means that as soon as Bryan gets around to it the main server will be back up.</font> Note that it rests on Bryan coming online, which he seems to be abstaining from these days.
<br /><b>Changes</b>
<br /> - Moving costs turns again.
<p /> - Moriarty

<p /><b>21-Feb-01</b><br />
<br />Added a history section. This contains login times and IP's for each account (seperate one for each account).
<br />Also added Mass ship buying. Can only buy merchants in this manner.
<br /><b>Changes</b>
<br /> - Mass ship buying.
<br /> - History section added.
<br /> - Only idle colonists are calculated now.
<br /> - Fixed some bugs in the universe generation bit.
<br /> - Added wormholes (they have been in a while, but the database wasn't updated (silly me :) ).
<br /> - Wormholes are now shown on the universe map (if the var is set for them to be shown).
<br /> - Fixed a few other bugs.
<p /> Moriarty

<p /><b>19-Feb-01</b><br />
<br />Well a few changes.
<br /><b>Changes</b>
<br /> - The attack link does not appear next to a ship if certain criteria are not met.
<br /> - More bug fixes (thats no surprise :) ).
<br /> - Planet allocation of colonists is now much improved.
<br /> - There is now a shield generator for planets. It can only be used for charging shields on ships. NOT for defending a planet.
<br /> - A few other things.
<br /> - Moriarty

<p /><b>15-Feb-01</b><br />
<br />Unsurprisingly Weaz didn't even get the basics of the raiding ship working, no matter his claims. As such I've got it working to a good level (i.e. no bugs that I know of) and have tested most of the functions. It's got a nice random element in it (its 50/50 whether the raid succeeds or not).
<br /><b>Changes</b> - I've made over the past 2 days:
<br /> - got raiding ship working.
<br /> - Fixed a few bugs.
<br /> - Hid the position a player holds in the politics section. (due to popular demand)
<br /> - Added a few more ministers.
<br /> - Bounty now shown on player info page.
<br /> - Increased the size of text boxes.
<br /> - Can now edit diary entries.
<br /> - Lots of small changes too.
<br /> - Have changed the Brob a bit. Can now have more than one per game, however may only have one at a time, and the cost rises exponentially (i.e. 1mil, 2 mil, 4 mil, 8 mil etc) for each consecutive one.
<br /> - Moving ships around is now free (turn wise), and transwarp and subspace jump now cost less turns to implement.
<p /> - Moriarty

<p /><b>14-Feb-01</b><br />
Me and chris designed a new feature: Raiding Ships.  One is already done, and a few more are on the way.  Stop by segotu's ships to check them out.  BTW- The whole raiding crap is still under construction... the basics work, but new things will be added.
<p /> - Weaz

<p /><b>13/14-Feb-01</b><br />
Oooops..slight bug in the polling system..there had been a bug in it before and I found it but forgot to take it out. I also cleared the votes, so please go re-vote for which logo you think is the best.
<p /> - KilerCris

<p /><b>13-Feb-01</b><br />
New logo to chose from..by me...bunch of variations..remember you can always change yur vote :).
<p /> - KilerCris

<p /><b>13-Feb-01</b>
<br />Its good to see I've got Kilercris & Weaz using the right date format for these posts to the front-page now :) .
<br />Anyhow I now have access to kilercris's server again, and after a few days of having a rest from SE I feel like messing some code up again :), so there are going to be lots of bug fixes and stuff added later today. First up i have to merge my changes with kilercris's for the past few days.
<p /> - Moriarty

<p /><b>12/13-Feb-01</b>
<br />New Active Users system...shows how many people clicked links within the last 5 minutes. Admins can see who they are.
<p /> - KilerCris


<p /><b>12-Feb-01</b>
<br />Chris made a new logo but he has too much ::gasp:: homework to tell you himself.  Hehe... sucks for him (I hated school).  Anyway, looks pretty cool, huh?  I made the text, chris made the earth-like thingy.  Anyway, sure beats the old one :).
<p /> - Weaz

<p /><b>10/11-Feb-01</b>
<br />YAY Weekend...working on some things.
<br /><b>Changes</b>
<br /> - When you kill a ship it's fuel/metal goes to the system..well..a random precentage of it.
<br /> - Where you get hit by a gamma bomb or alpha bomb you only get 1 message instead of 1 for each ship
<br /> - When you have a transwarp drive you can now 'Transwarp Burst'..it costs 15 turns and sends you and all towed ships to a random system
<p /> - KilerCris

<p /><b>10-Feb-01</b>
<br />The -X- vs =V= (or *R*) game is over... =V= dominating -x- big time.  Sadly, this wasnt that great of a game, since =V= called on its big-brother clan *R* to whip -X-.  Anyway, good job all you players.  I hope to see you all around in our other games.
<p /> - WEAZ

<p /><b>8-Jan-01</b>
<br />Made some changes today.
<br /><b>Changes</b>
<br /> - Pirates now use Bounties ;-)
<br /> - Messed with pirates in other areas.
<br /> - Shield rates on different ships now generate at different speeds.
<br /> - Bug fixes
<br /> - Changed price of ramjet scoop to 20k
<br /> - Changed the find system thing. Is now easier to understand results.
<br /> - Some other stuff i probably forgot :)

<p /> - Moriarty

<p /><b>7-Jan-01</b>
<br />No real changes today folks. However i have made a whole host of bug fixes, as well as some minor (cosmetic) changes (such as the kick link apearing next to the admin in the clan control).
<br />Don't forget. If you find a bug (or even just think somethings a bug) and it really is a bug, then u'll get paid minimum of 25000. So please get reporting them.
<p /> - Moriarty

<p /><b>6-Feb-01</b>
<br />Biggest change of the day so far. A news search facility. Lets u find things in the news. Its extremely basic, nothing advanced in it. Its even case-senstative. Have also addopted the search thingy to the diary.
<br /><b>Changes</b>
<br /> - Added Search to News.
<br /> - Added Search to Diary.
<br /> - Small warning now appears if a player tries to mass-mine with a ship that can't mine. (some people where getting confused by hitting the link, and not seeing their Brob Mining).
<br /> - Changed some ships a bit. The Brob now has a ramjet scoop.
<br /> - Added a mini-modular ship.
<br /> - Ramjet Scoops now collect even if you aren't commanding it.
<br /> - Added ability to leave all colonists, metal, fuel, oranics, or electronics, that are on your ship onto a planet, all at once.
<br /> - Messed with attacking a bit. different ship types may now do different amounts of damage.
<br /> - Can now re-name a planet.
<p /> - Moriarty

<p /><b>5-Feb-01</b>
<br />Due to a floppy disk corruption, i can't upload my changes from last night... but no matter, i'll upload them later today. Till then, i'll add other things that (i hope) won't use the same files.
<br />Right, got a disk that isn't messed up. And have uploaded my changes.
<br /><b>Changes</b>
<br /> - Two new ship types. The skirmisher (the heaviest warship on the market), and the flexi-hull(tm) (a modular ship, with 65 upgrade pods).
<br /> - Attacking is now more based around the type of ship you are in. For example, stealth ships may take less damage and do more (element of surprise), whilst battleships do more damage, and freighters do more counter damage.
<br /> - Added a politics section. At present it doesn't really do anything, but its a start.
<br /> - Bounties now gain 2% interest per day.
<br /> - First player to find aliens gets money.
<br /> - Fixed a few bugs.
<br /> - Mass buying of upgrades
<p /> - Moriarty

<p /><b>4-Jan-01</b>
<br />Well i couldn't see many bug fixes in the maint. I also made some changes too it, listed below:
<br /><b>Changes</b>
<br /> - The log function now works. Courtest of kilercris it also includes the send of the message.
<br /> - Wormhole generation included in the universe generation (admin var). Not a random event.
<br /> - Added Solar Storms.
<br /> - News listning for random events now available through observatory.
<br /> - Aliens now sell excess electronics and organics that they have on their planets.
<br /> - Ports have dynamic prices (with next universe generation).
<br /> - Fixed that idiot weaz's colonist carrier. It no longer has a price of 25k and a value of 100k.
<br /> - Also added the "alpha" bomb, which takes out all shields on all ships in the system (cept admin).
<br /> - Lotsof other minor fixes.
<p /> - Moriarty

<p /><b>2-3/4-01</b>
<br />YAAAWWWWWNNNN
<br /> Many many bugs in daily maint..me fix..me make taxes..taxes work now..so do building fighters..organics will be in by tomarrow night
<p /> - KilerCris

<p /><b>2-3-01</b>
<br />Reprogrammed system search.
<br /><b>Changes</b>
<br /> - Better search engine for the map.
<br /> - Can click games name under time to see game stats.
<br /> - New color scheme editor. You guys can't use it yet. Will make it a public tool soon.
<br /> - A few new color schemes check them out.
<br /> - Please visit <a href='http://kilercris.cjb.net/se_submit/'>http://kilercris.cjb.net/se_submit/</a> to submit star names. We nede more.
<p /> - KilerCris

<p /><b>2-Feb-01</b>
<br />There is a now the ability to search the map to find a system. Idea suggested by AOD, and the results are made possible, ourtesy of <b class='b1'>AOD's</b> Layering code. It looks totally mint.
<br /><b>Changes</b>
<br /> - Working search engine for the map.
<br /> - LOADS of major bug fixes.
<br /> Other than that, nothing much else done today (though that lot took loads of time).
<p /> - Moriarty


<p /><b>1-Feb-01</b>
<br />There is now a <b class='b1'>"fleet diary"</b> which you can use to hold any information you like. No-one can see or access the information in it, other than you. It has a number of different topics to help find information.
<br /><b>Changes</b>
<br /> - Made the player information page more accessable, by putting a link in the left side-bar.
<br /> - Moved the tow-fleet and release fleet links to just under the mini-map.
<br /> - Supernova won't always turn into black-holes now. Instead, they have a higher chance of turning into <b class='b1'>"safe" SuperNova Remnants</b> which have lots of minerals in them.
<br /> - Wormholes now work. Only problem is i havn't coded anything in to create them when the univese is built yet.
<br /> - Fixed a few more bugs (darn those critters get everywhere) :)
<br /> - Can now post apostrophes and the like to the forums now (not that many of you ever post using grammer symbols anyway). :)
<br /> - Cleaned up logging in for the first time. Also cleaned up retirement.
<p /> - Moriarty

<p /><b>31-Jan-01</b>
<br />Have comprehensivly updated the player-rankings. Lots of information in there now, and can be ordered by a dozen things or so.
<br /><b>Changes</b>
<br /> - Number of bug fixs.
<br /> - Major over-haul of the player rankings.
<br /> - Clans with no members are deleted hourly.
<br /> - Have created the largest Game in SE history (has 530 systems, on a 1500 pixel map!) which is being used to test lots of random events (even though the players don't know it :) ).
<br /> - Aliens will have a bigger system set in bigger games.
<br /> - Messed with other asspects of alien deployment, to try and get them away from earth, yet not too near the edge of the map.
<br /> - Created a random events list thing in the observatory of sol that lists all the Random Events.
<p /> - Moriarty

<p /><b>30-Jan-01</b>
<br />Well folks. Kilercris has got the maps working. No small feat from what i can tell. Theres also some more back-end stuff that's been made by him too. 
<br /><b>Changes</b>
<br /> - Some bug fixes
<br /> - There are now <b>Six</b> colour schemes to choose from. Accessable via the options.
<br /> - Aliens now Declare war on a clan, if a clan member annoys them enough.
<br /> - Pirates Now Raid Planets. (both planets that are near them, and they find the planets of their enemies.
<br /> - Pirates raid ships that are in their system.
<br /> - Pirate Fleets are now generally Bigger.
<br /> - Hidden observatory, as its one use for the moment (galatic library) is now obsolete.
<br /> - The first player to run into the aliens gets it reported.
<br /> - Is now possible to pay a bribe to the Pirates to get them to leave you alone.
<br /> - Transfering a ship of a certain type will get the Aliens to leave you alone.
<br /> - Fixed up the send-ship page a lot.
<br /> - The <b class='b1'>SuperNova Effector</b> is now added. It'll destroy a whole system, within 48 hours (two maints) of being detonated. Warning is given. Cost if a low 500,000. Its avaliable form the equip shop.
<p /> - Moriarty

<p /><b>1-25/26-01</b>
<br />YAAAWWWNN...put in a couple new features before I got to be...
<br /><b>Changes</b>
<br /> - Pretty sure planet passwords are working 100%..gonna add a little more before I'm through with them.
<br /> - 'Leave all' option on planets...puts all fighters on all yur ships in system on the planet.
<br /> - You can now self destruct your ships the link is next to the remote command link on your info page.
<br /> - If you go to someone elses info there is a 'Transfer Ship Registration' link that lets you send a ship to that person..of coarse there is a small 500 credit transaction fee and it is posted on the news...48 hour limit on new accounts...
<br /> - fixed mori's FTP access:)
<p /><b>Other Changes</b> - Made by Moriarty
<br /> Well i was up even later, and got lots of stuff working too:
<br /> - Pirates are now only too happy to raid any ship in their system, and then sell the booty on. At present they don't don anything with the money. That will change.
<br /> - Finishing of a Pirate Fleet now gets you a sum of money.
<br /> - Killing the last pirate ship gets u the bounty, amoung other things :).
<br /> - Pirates now send lots more messages. All suitably spine chilling :).
<br /> - During a raid against a ship, the player will loose 5% of the fighters on the ship.
<p /> - Moriarty

<p /><b>25-Jan-01</b>
<br />Seems like my changes are only going to be on wolfden server, as kilercris still has to point my ftp in the right direction.
<br /><b>Changes</b>
<br /> - Pirates attack using the same system as the aliens do.
<br /> - You can now see your alien and pirate levels in your player information.
<br /> - Have created a options page. from here you can change colour scheme, retire you account, or change password. More options are likely over time.
<br /> - When someone joins or leaves a clan, the clan leader now gets messaged.
<br /> - Aliens now pay money for ships they get.
<br /> - Aliens pay money for fighters that get added to their ships.
<br /> - It is no longer possible to put a bounty on a clan-mates head.
<br /> - It is now possible to pay of a bounty on your head, or a bounty that is on a clan-members head. The bounty must be paid all at once.
<br /> - Pirate fleets now move every few days.
<br /> - Aliens now have scout ships. About one per 10 systems. These move each day.
<br /> - Have created an "obseratoy of Sol". This now houses the library, and has more to come i hope.
<p /> - Moriarty

<p />
<br />Somehow the ships table got currupted. I had to delete it and make a new one so you all don't have any ships right now....hehe just kidding lucky you I was able to recover it.
<br /> - Kilercris

<p /><b>24-Jan-01</b>
<br />Well not as much done over the past four days as could have been, due to multiple server problems. However what has been done:
<br /><b>Changes</b>
<br /> - I created a server on spaceports. No meant feat, but i still managed it in 24hrs, with some time to spare too :).
<br /> - Aliens will now attack you if you have previously attacked them.
<br /> - Have messed with the options in the side-bar and tidied it up a little. There is now an <b>account options</b> page which is where you must go to change the accounts password, or retire the account.
<br /> - There is also the ability to change style sheets. Though at present i need more style sheets.
<br /> - Fighter kills made by, and against a planet are now added to the players totals.
<br /> - It is no longer possible to see the name of the owner of a fully cloaked ship. This will shortly not be the case if the owner is a clan member.
<br /> - Planets are now secure for the first "hours_before_planet_attack" period. This means, players cannot land, create, or attack planets within that period. This is to alieviate a type of cheating that has only recently come to light.
<br /> - Gamma bombs can now be brought at the appropriate times....
<br /> - Aliens and pirates get wiped if they are not turned on during a universe gen.
<br /> - Aliens sell some of what they "mine" for cash. Shortly this cash will be used to buy more fighters for their ships.
<br /> - have made the game compatible with databases that are not root, and people who do not have root access to dbs(i.e. using a hosting service, like spaceports).
<p /> - Moriarty

<p /><b>20-Jan-01</b>
<br />Things done today:
<br />Aliens are now capable of attacking you if you mess with their systems. And boy are the potent. They do a wonderful ambush too. And their planets can attack too.
<br />Just to remind everyone that the vars for a game can be found in the help pages.
<p /><b> changes</b>
<br /> - Scanner is now a bit more functional. And a bit less too. No longer shows who the owner is for a high stealth ship, however it does up-date probperly now.
<br /> - Fighter kills against, and by planets are now added to the players fighter kill total. No mater who puts fighters on a planet, any kills made by the planet are credited for the owner of the planet.
<br /> - Aliens use a nice system of attacking. They are quite potent now.
<br /> - Should no longer be possible to create or land on a planet within the intial, no messing with planets period.
<br /> - All ships other than the transversor can now have shields. Adding the warmonger broke this, but it should be fixed again.
<br /> - Fixed it so can buy gammas. Had the vars the wrong way around before.
<p /> - Moriarty.

<p /><b>19-Jan-01</b>
<br />Yet more changes. Note that i KNOW the map in the library does not work (its showing an old map), and am trying my utmost to get Bryan to fix it, as its a bit too advanced for me at present.
<p /><b>Changes</b>
<br />Players can post to the forum if the admin sets a var (allow_html)
<br />Admin can no longer retire pirates or aliens.
<br />Aliens now send messages at certain times (as in if u annoy them :) ).
<br />Did some stuff relating to aliens. It'll be a surprise so I won't say what it is now.
<br />When you send a message with a bounty, you now get told you where charged for it.
<br />Created two new DB vars "aliens" and "pirates". These allow players to get on the good or bad side of each. Much more work needs to be done on this front. (as in using them)
<br />Have disabled planet passwords for the duration.
<br />Lots of changes to ports. Including some grammer fixing. Organics now work at ports too.
<br />Added <font color=lime>two new ships</font>. The Stealth trader, and the Warmonger.
<br />Did a comprehensive upgrade of the ship shop display scheme. Now looks much better.
<br />Fixed a problem relating to being able to buy stuff from both the equipment shop AND the upgrade store, both at the same time.
<p />Lots more changes to come, as i now have nothing to do for two weeks, as the semester is finished, and the next one isn't till Feb. Ah the wonders of student life. :)
<p /> - Moriarty

<p /><b>16-Jan-01</b>
<br />Lots of things going on. Have yet to get a build_universe that'll build a universe with maps, so as other people can get it working on their servers (i.e me on mine, and anyone other than kilercris in fact :) ).
<p /><b>Changed</b> lots of things since last post:
<br /> - LOTS OF RANDOM EVENTS ADDED! When a universe is generated these events can be added (depends on the admin var "random_events"):
<li> Nebula
<li> Alien Civilisation (doesn't do anything yet, other than sit around with their huge invisible fleets (up to 150,000 fighters! Maybe more.) The way they [the systems] are deployed i'm well pleased with.)
<li> Pirates (don't do anything either, but have smaller fleets. This one is more random than aliens.)
<li> SuperNova (these go in a one run cycle!!)
<li> In game, <b>Mining Rushes</b> can also happen.
<li> And of course not to forget the already existant BlackHole, which some players have already had the "pleasure" of experiancing :).
<p /><b>Other changes</b>:
<br /> - Admin messages can now appear in a number of different colours. This uses an admin var (message_colour).
<br /> - Discovered yesterday that admins can post HTML to the news. And i've only been an admin for 5/6's of a year :) .
<br /> - Admin posts have HTML enabled. As in the posts they make to the forum, and private messages. All other posts still have HTML disabled.
<br /> - Fixed admins and clans a bit more.
<br /> - Clan leader can now be set.
<br /> - Made it so as specials don't show in the side panel if the "flag_upgrades" var is set to 1.
<br /> - Made it so admin can't see or do anything with the alien or pirates (as in can't see info about them in the player info page).
<br /> - Made it so as the admin doesn't see the "command" link if its your fleet (s)he's looking at in the player_info page.
<p /> So as i said... lots of changes... my favourites being the random events. Note that the Aliens and pirates will do lots when i finish with them i hope. I'll start some serious coding on them on wednesday.
<p /> - Moriarty.

<p /><br /><b>11-Jan-01</b>
<br />First up, being a grouch again. Have limited the number of accounts allowed to 50, cos kilercris's server is going ever slower in my estimation, and its taking ever longer to load, and save pages (and no, it's not my connection). This should help speed things up, though kilercris has the ability to set it back to a high number if he wishes.
<p /><b>Changes</b>:
<br /> - Well erm not much, just added an upgrades page, which allows you to upgrade your ship in different ways, including buying a cloak, a scanner, a transwarp drive, as well as increasing your ship's capacity of figters, shields and cargo.... As i said... not much :) ....
<br /> - Specials are now displayed in the sidbar above cargo, but below shields.
<br /> - It is now possible to send a message with a bounty at a cost of 1 turn for the message.
<br /> - Ranks now appear next to players in the player ranking.
<br /> - Scores are now implemented. A players score is saved, when the player logs in, and logs out, and will remain at 0 till the player has used more than 100 turns. It is easily possible to get a negative score. Scores are based on fighter kills, ship kills (each worth 50 fighter kills), and points are deducted for each ship lost (equivilent to 75 fighters per ship lost), as well as turns used....
<br />Basically the person with the highest score will be the one who has killed the most, and lost the least.
<br /> - Clan scores are now based on the scores of the clan members, divided by the number of members, times 5. So if u have a big clan and u all get awful scores, then u're gonna get a low clan score.
<br /> - The commision for bounties has been taken to 7%.
<p />All the score stuff that has been added today should improve the skill required to get to the top, as it's about not loosing ships now, as well as killing enemy ships. I expect a bit of balancing on the ship front will be required in the future.
<p /> - Moriarty

<p /><br /><b>10-Jan-01</b>
<br />First up, I'm going to remind you folks that this server is primarily for adding new things to. as such some pages may be un-available at times. If you get an error message mentioning a line number from 0 to 5, then do NOT immediatly re-load the page, but wait about 10 secs and try again. This error is generated by me saving a file, and the more people who try and access the file when i'm saving it, the longer it's going to take. So be patient.
<p />Now onto the <b>changes</b>:
<br />- When a gamma bomb is let of, it will add to the ammount of metal and fuel in the star system. The more ships the gamma bomb hits, the more fuel, and metal is generated for mining in the star system.
<br />- The one everyone has been waiting for - <b>Remote command</b> it is now possible to command ships anywhere in the universe by going to the player info screen, and clicking the "command" link. However there is a twist. To stop people getting places for free, or even half price, it's not turn free. There is a fee imposed. The farther the ship u want to command, the more turns it will cost. At most it'll cost about 20 turns to go across a normal universe. The cheapest it will cost is 2 turns, even if the two systems are next to each other.
<br />- The <b>Scanner</b> Has now been added. This can be brought from the equipment shop for the price of 20,000 credits. It will allow the ship it is installed on to see, and even attack cloaked ships. However you must be commanding a ship with a scanner on to see, or attack the cloaked ships, even if there is another ship with a scaner on in the system.
<br />- In the star system, where it displays your ships, it now also shows any specials that you have on the ship:
<p />Admin The Boss (Bussard Ramjet Special Ed. w/ 1000000 fighters) - <b class='b1'>tw:br:sj:sw</b> - Command - Tow
<p />The bit in red is the bit that's added. Note that you can only see special information about your own ships. For more information see the in-game help. The ship information section, theres a link at the bottom. Also Specials information is now shown in the player information too.
<br />-Just to remind people. It is <b>No Longer Possible</b> to attack yourself.

<p />- Moriarty

<p /><br /><b>8-Jan-01</b>
<br />A few <b>changes</b> today:
<p />-Bounties: Now, knowing how very much you players tend not to like each other half the time, and try to put cash on each others heads, i decided to make it easier for you all, by putting in an official bounty system... heres how it works:
<br />Access it from earth "bobs charity shop" (hey, bounties ain't legal in the SE universe don't ya know :) covert name and all that ;-) ). Can only be accessed from Earth.
<br />Will add to an existing bounty, or create a new one.
<br />It's impossible for the admin to place a bounty, or for a player to place a bounty on themselves, or the admin.
<br />You can't place a bounty if u don't have the money.
<br />A bounty is collected when u kill a players EP, and the player has a bounty. you will get a message come up saying that u got the bounty when u have killed the players last ship.
<br />At present u can <b>NOT</b> claim a bounty by using a gamma bomb. Primarily cos i have to figure out how to return things :).
<br />You cannot place a bounty within the hours_before_attack period, as placing a bounty is a form of attack (also this discourages people using it as a way to transfer money between accounts).
<br />One final thing, when u add to a bounty, or place a bounty on a players head, the player will get a message saying their bounty has gone up. The message doesn't say who placed the bounty.
<br /><b>The whole bounty system is anonymous.</b>
<p />- Game Vars. Due to popular demand it is now possible to see the vars for a game, by clikcing the "game variables" link on a games login. At present this doesn't work cos of a URL problem, but, u can still access them by going to <a href='http://kilercris.cjb.net/game_vars.php'>http://kilercris.cjb.net/game_vars.php</a>. The admin has the ability to allow them not to be shown (to increase the dificulty of the game).
<p />- Gamma bombs are working again. Well partially. Still have a few bugs in them.
<p /> - Moriarty

<p /><br /><b>3-Jan-01</b>
<br />Fixed the 3 mil credits bug... so you won't all be rich any more.
<p /><b>Changes:</b>
<br />-The Superweapon for the brob is now complete, and fully working.  costs 30 turns to use, and does between 600-1400 damage to a planet per shot... can even attack attack planets. The Brob is also available now, as its complete. Note you can only buy one per game.
<br />- Theres a rough black hole floating around somewhere. Needs a fair bit more work doing, but the basics are there. Also has some bugs, which i'll flesh out some time soon.
<br />- When buying equipment on earth, you get shown the ammount you where charged per transaction now. As in if u buy 10 fighters, for 100 each, it'll say u got charged 1000 credits.
<br />-Now prints clan disbanding to the news.
<br />-And some other things that you won't notice.
<br />-Moriarty

<p /><br /><b>2-Jan-01</b>
<br />Given all players 3 million. all new players will also get 3 million cash.
<p /><b>Changes:</b>
<br />- Ship info now works properly. Needs some cosmetic alterations though.
<br />- Transwarp drives work:
<br /> To use Transwarp, you need to outfit a ship with it. Costs 100,000 per ship fitted, and can be bought from the equipment shop.
<br /> When fitted, and you are commanding that ship, an option appears to the right, where "use gamma bomb" appears. enter the system u want to go to, and u'll go there. Limit is 15 light years (thats 15 pixels on the map).
<br />Costs are in turns and are based on the distance you travel. It also can tow ships through transwarp, at a cost of 1 turn per ship towed.
<br /> - Subspace jump works:
<br /> This is only available on the "Transversor" ship. Buy one of them, and command it. you can then jump anywhere in the universe, with up to 10 ships in tow. No extra charge in turns for ships that are towed, and the price is based on the distance you jump.
<br /> - Only one Brobdingnagian can be brought per user per game. However Brobdingnagians are not available at present, as more needs to be changed on them.
<p />More changes will probably occur later today.
<br />- Moriarty

<p /><br /><b>1-1-01</b>
<br />Yet more changes, with more to come over the rest of today. So far today:
<p /><b>Changes:</b>
<br />-Admin doesn't use turns.
<br />-Admin can see behind an attack planet.
<br />-Refinement of transwarp system.
<br />-Money transfer now takes into account the admin var "min_before_transfer".
<br />-Created a new admin var "flag_gamma_bomb", which allows the admin to turn on, or off the purchasing of gammas. Admin is excempt, and you  can still use any gamma's u already have.
<br />-Have changed ship info a bit too. Now shows, ship sizes, and ships specials. More changes due on this front.
<br />-Transwarp has been refined even more, and can almost be purchased at the equip shop. admin can choose price, and turn it off completly.
<br />-Fixed clans again today
<br />-Planet pictures can now  differ, and will be random.
<br />- And other stuff too.
<br />- Moriarty

<p /><br /><b>12-31-00</b>
<br /> YAY we got a universe now got it to make the maps sorta..
<br /><h4>Changes:</h4>
- <b>Offensive planets!!!</b> Now called hostile mode
<br />- Ship configuerations programmed in. You will soon start to see specialty ships
<br />- Transwarp will be in very soon. You won't be able to warp bery far and it will cost a bunch of turns. This may not seem usefull for ordinary travel but this means you can get onto star "islands" and take shortctus over star peninsula's
<br />--KilerCris
<br /><br />

<p /><br /><b>12-30-00</b>
<br /> Me and Moriarty are now working off the same server..if bryan ever signs on I'll try to get him to help with the damn universe.
<br /><h4>Changes:</h4> (Mori's)
<br />- Game Front-page maths is cleaned up. Doesn't include admin in anything now.
<br />- Shows number of gamma's and genesis devices left, next to the link to use them.
<br />- Message clan link doesn't appear if only one player in clan.
<br />- Re-laid out the admin options to make them easier to use. Also got rid of retire, and feedback links for admin.
<br /><br />

<p /><br /><b>12-29-00</b>
<br />SWWEEEET My server works fully now. Universe generation doesn't work right I am trying to fix it
<br /><h4>Changes:</h4>
- I changed the shop names back I liked the old names better and I put that old earth acronym back.
<br />- Mine all link is always shown even when current ship is mining.
<br />- When you signup it takes you to the login form for that game.
<br />- Admin can't retire.
<br />- Admin not shown in player rankings.
<br />- In the game stats on the login page admin isn't counted as a player.
<br />- It says the location in the attack message and gamma bomb message.
<br />- Bunch of other minor fixes and stuff. Soon as I get the regen working and make a universe I'll get to work on the fun stuff :)

<p /><br />Post to Sourceforge Solar Empire account.
<br />Posted By: bryan_se
<br />Date: 2000-Dec-23 15:36
<br />Summary:All Games Reset 
<br />I've manually reset all of the games on the solarempire.com. This may be the last set of games to run on that server. We will be moving to wolfden or somewhere else. 
<br />Would anyone like the responsability of running the main games server? Let me know and we'll get it going.

<p /><br />Post to Sourceforge Solar Empire account.
<br />Posted By: bryan_se
<br />Date: 2000-Dec-20 10:23
<br />Summary:The broken release 
<br />This version is very broken. Only the login_form and the new_game.pl program works. 
<br />It's a very good start on the single database ussage problem.

<p /><br />(Solar Empire Frontpage news post)
<br /><b>12-20-00</b>
<br />This will be the last news post here on the login page.
<br />From now on all news will be posted on the <a href='http://sourceforge.net/news/?group_id=16534'>sourceforge page</a>.

<p /><br />(Sourceforge news post)
<br />Posted By: bryan_se
<br />Date: 2000-Dec-19 10:06
<br />Summary:Better Release 
<br />I removed about 1.5 megs of junk out of the inital release and made a new smaller zip file.

<p /><br />Posted By: bryan_se
<br />Date: 2000-Dec-19 22:18
<br />Summary:Waiting for CVS 
<br />To get the all important CVS and shell access we are waiting on the hard working sourceforge crew to get the LDAP authorization working on all their servers. Not an easy task. I don't envy them. It might still be a couple days before we are go on that. Until then if you have some good changes to make send them in as patch files (made with diff). 
<br />Thanks. Bryan.

<p /><br />(Solar Empire Frontpage news post)
<br /><b>12-19-00</b>
<br />The <a href='http://sourceforge.net/projects/solar-empire/'>project account</a> over at sourceforge has been setup.
<br />I don't have CVS setup yet but there is lots of great tools over there.  I'm thinking we will release about once a week.
<br />Please if your attempting to get the SE code working on a server let me know on the sourceforge forums.  I want to help.
<br />Anyone that would like to contribute code I will be happy to make a developer on the project.  Just get a sourceforge account.
<br />and let me know.  Just think, now you can get planet passwords put in.  All you have to do is code it.
<br /> - Bryan

<p /><br />Post to Sourceforge Solar Empire account.
<br />Posted By: bryan_se
<br />Date: 2000-Dec-18 23:08
<br />Summary:Initial Release 
<br />The inital source release has been made. Lots of work needed on it to make it easy to install.
<br />Lemme know if you need help or get it to work.

<p /><b><font color=lime>Below this point, all news posts are from Solar Empire Front Page, on the Original Server. Above this point they could be from a number of different places.</b></font>

<p /><br /><b>12-18-00</b>
<br />Today is a monumental day for Solar Empire.  I've decided to release the Solar Empire source code under the GPL license.
<br />That means that you are free to download and install Solar Empire on your own server and run your own games.
<br /><a href='http://download.sourceforge.net/solar-empire/solar-empire-0.002.zip'>Here is the zip file</a> for the actual sorce code.
<br />I am setting up an account on <a href='http://www.sourceforge.net'>sourceforge</a> to help manage the development.  Everyone will
<br />be invited to submit their own improvements to the game.
<p />Thank you everyone for your support in the past.  Now we have the ability to make this game something really great.
<br />-Bryan

<p /><b>12-1-00</b>
<br />A fix I forgot to announce is that there should be no more - Fuel and - metal, when creating fighers and electronics on planets.

<p /><b>11-30-00</b>
<br />YES!  Solar Empire is now home.  We have the server located in our office now so it should be up ,more, permantly now.
<br />-Yer Fates game name has changed to Celestial Destiny, SICK1 still the admin. Looking forward to a great game there.
<br />-Also TheMadWeaz has taken control of a new game called Veteran Warz this game will be for the expierienced player.
<br />It should be more of and independat start looking for clan action later in the game.  So if your a good player and don't
<br />have a clan check this game out.
<br /><br /> Bug Fixes
<br />-Admin is no longer ranked in top 10.

<p /><b>11-29-00</b>
<br />-Ok I'm going to start working on E-mail for the admins when major events occur so I need to know a few things.
<br />Who is adming each game for sure. If the admins or active co-admin could email me letting me know the handle of the
<br />Admin his Co-admins, the name of the game your in control, icq# or im name,  and then the emails for each.  Thanx this will 
<br />help me clean up the database in preporation for this new feature.
<br />-From the names we recieved we have given the stores on earth new names.
<br />-From now on if your last ship is destroyed you are removed from the game.  Most people would just start over anyways.
<br />and it's a lot less buggy.
<br />-Here is a look at the new <a href='m1_doc.php'>M1_Document</a>

<p /><b>11-28-00</b>
<br />Some big changes today.
<br /><br />-Multiple ports in same star system bug fixed.
<br />-The way the map images are done was completely reworked. Which is why SE time has advanced about 2 weeks.
<br />-Data base cleaned
<br />-The admin Var uv_universe_size is working a new min limit was set at 200.
<br />-Random resources being put at Sol is now fixed.
<br />-There is now an rr_ tag in from of the random resource admin vars.
<br />-The Star System link has been moved to below the Bugzilla link.
<br />-The news archive is now complete as far as I can tell from the creation of SE.
<br />-*The generate universe is now on an hourly maintance.*

<p /><b>11-27-00</b>
<br />This site is up again temporarily,  but we should have it up permanetly here soon.   Thank you to all those who 
<br />have been patient with us.  At any rate at least I can start working on it again. :)
<br />Fixes for the day.
<br />-Stars now have names. Will take place when your game is recreated.
<br />-You can now recieve your first ship when you start a new character.

<p /><b>11-14-00</b>
<br /><b>DUE TO SERVER ISSUES SOLAR EMPIRE WILL BE DOWN FOR 1 WEEK</b>
<br /><br />A few fixes for the day. 
<br />- There is now a star system link under clan control.
<br />- Gamma bombs price can be set by the admins.  cost_gamma_bomb.
<br />- Ships can no longer be bought from space.

<p /><b>11-13-00</b>
<br />Ok today I have a big surprise.  A new feature was added which gets us closer to the completion of the m-1.
<br />Star systems will generate random resources.  Admins there are 6 new vars for this they are.
<br />-metal_chance
<br />-metal_chance_max
<br />-metal_chance_min
<br />-fuel_chance
<br />-fuel_chance_max
<br />-fuel_chance_min
<br /><br />I hope this feature will add to the fun of solarempire.  Also thank you to all though's who have sent in names
<br />for the star systems and shops.  I should be making changes to that area of the game soon.  If you haven't
<br />sent in any names yet it's not to late.  I'm still looking for more star names.  Enjoy the new resources.

<p /><b>11-8-00</b>
<br />Fixes for the day.<br />
<br />-Forum hourly post limit is back. 
<br />-No more slashes in forum post.
<br />-Flag_ planet_attack, hours_safe admin vars now work.<br /><br />


<p /><b>11-3-00</b>
<br />Ok the forums are back up...<br /><br />

<p /><b>11-3-00</b>
<br />I'm aware of the forum problem and I'll look into it. Thankyou for the emails.
<br /><br />

<p /><b>11-2-00</b>
<br />Ok this isn't a contest!  If you would like I'm getting ready to put together a list of possible planet names.
<br />And I would like to rename Wally's Equipmet Shop and Seatogu's Spacecraft Emporium.  So if you have 
<br />idea's or suggestions send them to  <u>wolfpak@solarempire.com</u> and put in the subject line Names.
<p />A few changes took place today.
<br />- Harvester Mammoths may only carry 500 fighters now.
<br />- Admins may now use the Max_Ships, Max_Clans Vars.
<br />- Admins can also disband any clan.
<br /><br /> And since I'm the only one posting to this page.  I'm not going to sign it anymore.

<p /><b>11-1-00</b>
<br />A few changes have been made already today.
<br />-Trex Mercenaries help link is now working.
<br />-The is a new admin for Secondary a new game will start there soon.  Also Yer Fates has just restarted.
<br />-Gamma Bombs are now 150000, that should make it easier for new players. and keep it fun for the elite.
<br /><br />WolfPak

<p /><b>10-30-00</b>
<br />Ok everyone you may have noticed but the gamma bombs are back...  Have fun:)
<br /><br />-WolfPak


<p /><b>10-30-00</b>
<br /><b>I need the Admins of <u>Grimms Domain, Origanal, secondary, and Yer Fates Game</u>, to email me Please. So I can get your
<br />email addresses Thank You.</b> <br /><br /> wolfpak@solarempire.com

<p /><b>10-30-00</b>
<br />Good afternoon everyone.  There are a few suprises today as you can see.  First off the new game Death Realm is kind of an
<br />extension of the old House Forsaken.  Future the Admin for the Death Realm game was told in the past he could admin a game
<br />under certian circumstances.  I look forward to seeing a good game start there for all players.
<br /><b>There will be NO more new games.</b> Remember the game is still in beta and I would like to see a few really good games then
<br />alot of small bad ones. Tech Mercinaries was removed seeing as the game was dead.
<p />As far as some fixes and changes thus far today.
<br /> The admin posting should be working now.
<br /> There is a News Archive and Small Credits page both in the making.  (See bottom of this page)

<br /><br /> - WolfPak

<p /><b>10-26-00</b>
<br />Today the login page recieved a major over haul.  The stats got moved around alot.  There is now a seperate area specificly for
<br />the admins post's to the news.  Also for the admins when you reset your game and your out in the middle of the space. You can now
<br />go back home and buy a ship with out getting an error every move. This is it for the week.  See you all next monday.
<br /><br /> - WolfPak

<p /><b>10-26-00</b>
<br />Before I really get started today I needed to clear up some stuff.  Were not going to start any new games, with maybe one
<br />exception, at this point of development.  Also yesterday I said I hoped that a game would start in the test game.  <u>Seeing 
<br />as the test game could get reset at anytime I wouldn't really spend to much time there</u>, but I do appreciate people coming in 
<br />and looking around when changes take place. Thank you for your patience.
<br /><br />- WolfPak

<p /><b>10-25-00</b>
<br />In the new Test game, it's running with the uv_show_warp_numbers option turned off.  In english it doesn't show the star
<br />numbers that are near you.  Everyone come in and check it out.  Also Bryan made cosmetic changes to the admin options page. 
<br />Admin's now when you make changes to your options there's a message that will let you know if the changes were accepted.
<br />It looks great thanx Bryan.


<p /><b>10-25-00</b>
<br /> Ok I'd like to keep you all updated as to the changes that I'll be making to the game. Today I borrowed Bryan alot and got
<br /> a few things done. I'm mostly going to get the small simple stuff done and out of the way before getting to the big fish. 
<br /> Here is a list of Admin options that should be working now.  If not email me at wolfpak@solarempire.com. 
<br />Thank you and have fun taking over the universe.  Also I restarted the test game if anyone want's to try it.
<br /><br />min_before_transfer
<br />flag_space_attack
<br />max_planet_cash
<br />uv_show_warp_numbers
<br />max_players
<br />new_logins
<br /><br />Now when you join a new game, the name of the game will be sent in the email as well.
<br /><br />-WolfPak

<p /><b>10-24-00</b>
<br /> I'd like to introduce my good friend Randee who is working for me now on this site.
<br /> He goes by WolfPak and will be dedicated to this site even more than Rob was, so I look forward to a lot better maintence for everyone.
<br /> The nightly maintance was broken by the original game having it's min distance between stars set to high.  It's been fixed now.
<br /> The House Forsaken admin asked me to remove their game.
<br /> - Bryan

<p /><b>9-22-00</b>
<br />I've fixed the bug that prevented admins from logging in.  I've also had to reset some of the games again.
<br />Please let me know if there is still a problem with admins logging in.
<br />I've also enabled the admin function to set the starting cash for players for an easy (perhaps even proper) workaround to the big not enough cash to buy your first ship problem.  I'd recommend setting it to 12000.
<br />The admin form acts strange in certain browsers to me.  Is it just me or is happening to others too?
<br /> - Bryan

<p /><b>9-20-00</b>
<br />Welcome back all.
<br />The down time was mainly my own fault.  I appologize to everyone.
<br />Rob is no longer with us.  He transfered to Boston to attend MIT.
<br />I've reset some of the games.
<br /> - Bryan


<p /><b>7-6-00</b>
<br />Several new functions have been added to the game:
<ul>
<li>"Mine All" - lets you set all your ships in a star system to mine at once
</li>
<li>At ports you may now choose to sell all the cargo you have on your ship <br />at once.
You may also sell all cargo you have on all ships in the star system.
</li>
<li>Clan leaders can now choose to assign another clan member to be the <br />leader of
the clan.
</li>
</ul>
- Rob

<p /><b>6-20-00</b>
<br />All players can now change their password from the player info page
<br />which is seen by clicking on your own user name.  You can also now
<br />transfer cash to other players from the player info page when you're
<br />looking at someone else's info.
<br />- Rob

<p /><b>6-13-00</b>
<br />Admins can now control the amount of cash new users get at the beginning of a game and
<br />they can determine how far back the forum will display messages.
<br />Admins should log in to see if they agree with the default values for these.
<br />- Rob

<p /><b>6-13-00</b>
<br />I've added some neat things to the login form and cleaned up the news page a little.
<br />I've also finished the <a href='m1_features.doc'>M1 feature design doc</a> (msword format).  It's not as well fleshed out as I'd like but it is complete.
<br />83 issues to address before M1 is reached.
<br />- Bryan

<p /><b>6-7-00</b>
<br />The Admin page has been upgraded so you admins can actually read it a little easier.
<br />Admins no longer need to make the admin vars active.  Whenever any variable is 
<br />changed, it becomes active immediately.
<br />- Rob

<p /><b>6-7-00</b>
<br />I just made a typo and accidentally wiped out every game.  So sorry everybody.
<br />I might as well declare this game to be beta until we reach M1 (Milestone 1).
<br />The design document for that should be appearing shortly.
<br />- Bryan

<p /><b>6-7-00</b>
<br />I've taken the grey out of the text.  Lemme know what you think color design wise.
<br />I've also been working on the clan pages a bit.  Cleaning them up.
<br />I'll also be adding more stuff to the player info pages.
<br />Send opinions as to whether clan members should get full disclosure on the player info page.
<br />- Bryan

<p /><b>6-7-00</b>
<br />Admins can now join clans w/o passwords.
<br />- Bryan

<p /><b>6-6-00</b>
<br />I apologize for the down time.
<br />The planets now produce each night.  Here are the formulas.
<br />100 colonists, 1 metal, 1 fuel, 1 electronics = 10 fighters
<br />50 colonists, 1 metal, 1 fuel = 1 electronics
<br />They havn't been tested heavily.  Please report bugs to bugzilla.
<br />- Bryan

<p /><b>5-23-00</b>
<br />Admins now control the cost of genesis devices.  The default is still 9000 until 
the admin changes it.
<br />- Rob
<p /><b>5-23-00</b>
<br />Thanks to those who helped diagnose the negative shields and fighters bug.  It should
now be fixed.  There will still be ships that have negative shields or fighters, but 
there shouldn't be any new occurences.
<br />- Rob
<p /><b>5-23-00</b>
<br />From now on messaging other users will no longer cost a turn.  But the cost 
(in turns) for posting to the forum will now be determined by the game Admin.  You'll
see the cost next to the "Post to forum" link.  The default cost is 2 turns until the
admin changes it.
<br />- Rob
<p /><b>5-22-00</b>
<br />I, Bryan, made the choice to remove the planet offense modes.
Rob has no creative control over the game and all decisions come from me. Just so you know.
<p />The planet offence mode in my opinion was one of the most buggy and poorly implemented parts of the game. It distracted from the real point of the game which could have been
                        a good thing since there isn't a point at the moment.
<p />I am taking the game in the direction that I feel will make it the funnest long term for everyone. Offensive modes will
                        be back someday and will be much better when they do.
I am working on a design document/feature set for what I will consider a completed game. Look for it in the next
                        several days. 
<p />Now for more bad news, in a few minutes I'm doing away with the tax income from planets. Again for overall game direction. This will make planets 100%
                        useless at the moment and unfortunately I don't see the taxes ever coming back.
They were first added as a hack to get some kind of income to the players and was a
                        poor design choice on my part. This game should not become some kind of economy balancing game like sim-city or lords of the realms, which is where tax income
                        would have to go to keep it challenging.
<p />I sincerely apologize to everyone whose games I have royally screwed up. It was done in the name of the game.

<p />On the other hand of things.  I have listened to you all and realize now that the news page added more to the game than I thought and have restored it.

<p /><b>5-22-00</b>
<br />All player names can now be clicked on to get info on a player or message them.
<br />Admins may now need to click on the players name to go to the info page to retire a player.

<p /><b>4-28-00</b>
<br />I've removed gamma bombs as they were buggy.  They will come back when the game is in better shape.
<br />The best way to get me to fix something is report it to bugzilla.
<br /> - Bryan

<p /><b>4-27-00</b>
<br />I'm running <a href='/bug'>bugzilla</a> now, so everybody have at it and report all the bugs they can think of.

<p /><b>4-26-00</b>
<br />Clan symbols are comming along and I'm working on some hourly update bugs.

<p /><b>4-12-00</b> 
<br />For all those interested the roberts game above is for the new developer. 

<p /><b>4-10-00</b> 
<br />The position below has been filled with a programmer that I found locally. 
<br />Moriarty has started a story contest that has my blessing. 
<br />I may use some of the plots in the game, but no guarentees. 

<p /><b>3-18-00</b> 
<br />I'm looking to employ a mature PHP programmer part or full time to work on this site. 
<br />Please email me at bryan@cooltext.com if you have PHP experiance you can show. 
<br />Please include your phone number and a time that you will be available so that I can call and do a phone interview. 

<p /><b>3-4-00</b> 
<br />The nightly maintenece was broken by me tring to put in the reset counter. 
<br />It's been fixed now and games will be recreated. It's being run as I write this. 
<br />I'm not taking any more requests for games. So don't ask. 
<br />Please email bryan@cooltext.com with bug reports only. 

<p /><b>2-28-00</b> 
<br />Admins. 
<br />I've update the admin vars of all the games and thus wiped all the settings in them too. Please check your vars. 
<br />Please note that the <*> symbol means that the variable is there but hasn't been utilised yet. 
<br />When they are utilised that symbol will still be there. 

<p /><b>2-27-00</b> 
<br />Planets can no longer be invincible. This has big implications to advanced games. 

<p /><b>2-27-00</b> 
<br />Admins can now force retire players. 

<p /><b>2-27-00</b> 
<br />Admins. I need you to email me or send feedback on the Original game with admin vars that you would like added. 
<br />Check out the bottom of the hosting notes above to see what I'm talkin about. 

<p /><b>2-27-00</b> 
<br />I've taken over the Original game and wiped it. Working on admin vars. 
<br />Please send me critical security holes. 
<br />- Bryan 

<p /><b>1-17-00</b> 
<br />We're back up early and I've chosen a player to admin the original game. 
<br />I'm going to be gone for a few days and when I return I am still going to be focused on stabilising CoolText.com so 
<br />my efforts won't be available a bit still. Admins please take care of your games. I hope that everyone has a good time here. 

<p /><b>1-3-00</b> 
<br />I've reset the original game and am looking for someone to admin for it. 
<br />I've killed the Sudden Death game. Sorry for anyone who was getting into it. 
<br />From now on I'm only admining the test game, which isn't for real playing. 
<br />My time is now stretched between CoolText.com and SE so I don't have time to admin every day. 
<br />By handing it off to someone everyone will have a better time. 
<br />I've taken a couple of requests for the games by hand, but haven't fully automated a request page for hosted games. 
<br />I'm waiting a little while longer until things are hammered down a little more before taking everyone's request. 
<br />All games are subject to being wiped by me for any reason. 

<p /><b>12-24-99</b> 
<br />As some of you have noticed, I've gotten the game automation pretty much running smoothly. 
<br />Which means that I am taking requests for people who would like to run their own games. 
<br />My main requirement is that you operate a decent web site so that you can host the game on your site, which will pretty
<br />much consist of having the login and signup forms on your site. I hope everyone is having a good time and enjoying the holidays. 
<br />PS Fixed a bug so that you can't put html codes in login names, clan names, or ship names. 

<p /><b>12-20-99</b> 
<br />The meeting below went very well. There are some notes available here. 

<br />I apologise for not keeping the game up. I've killed the sudden death game and wiped the current game. 
<br />The reason I didn't preserve the old game is so that I can make some changes to the database design. 

<p /><b>12-2-99</b>
<br />I am holding a development IRC meeting on Sunday December 5th (The date is correct this time ;) at 8pm MST on cooltext.com 
<br />port 6667 in channel #cooltext or #solarempire. All beta testers are invited. 

<br />The main agenda will be discussing lots of economic issues, such as: weather ports will have dynamics sell and buy costs, and 
<br />work out some equations. mining rates for the ships. Set a rate for each ship and perhaps an equation. planet eco. lots of fleshing 
<br />out to do there. Not sure the population/tax kinda work is gonna fly. rates at which planets generate electronics and organics. building 
<br />fighters on planets. Decide on requirements, equation and such. 

<br />Feature requests will be noted, but not heavily discussed by me. 
<br />Newbie help and clan rivalry should be set aside for this meeting. 

<p /><b>11-30-99</b>
<br />I've returned from my vacation. I am currently in feature freeze and am cleaning things up before I move on to hosted games. 
<br />All new feature requests that aren't bug fixes are put on a future feature list and will remain there for a month probably. 
<br />The old original game has been moved to Sudden Death. Sudden Death rules apply. 
<br />A new game has started as the original game. You will need to create a new account for it. 
<br />You'll probably need to clear your browser caches to get current starmaps. 

<p />From other places: 

<br />Posted by Bryan (as Admin) in the "original" game forum. May 22 - 17:22 
<br />I've disabled Offensive mode for planets. Which means there are a heck of a lot of unprotected ships out there. 

<br />Posted by Bryan in the suggestions forum - 4/Feb/00 
<br />Here's the deal. I really appriciate all of your suggestions here. Many of them I hadn't considered yet. Some thinks like space mines 
<br />I had and will be a major influence on the game. 

<br />In one of Moriarties posts he said that there really isn't a goal in the game, which I agree with and that is a big problem if people arn't 
<br />going to keep from getting bored. 

<br />I'm wrapping up my work on cooltext.com for a bit. 
<br />My priorities lie in this order. 
<br />1. Finish and fix all bugs pertaining to multiple games so that I don't flinch every time someone asks for their own game. 
<br />2. Make the game be a full circle that has a start, mid-game, and finish. Such as starcraft or any other game. It can't just be running 
<br />forever like tradewars or Ultima Online. Most likely what will happen is I will set number of days that a game will run for and it will 
<br />automatically reset. 
<br />3. Fix all bugs and implement critical features (such as clan messaging and clan symbols next to names). 
<br />4. Start to prioritize future features and implement them. 

<br />I have a lot of very important work to do and I just can't start thinking about all of your wonderfull new ideas. I have to focus on 
<br />hammering out all the bugs and make the game fully finished first. 



<center>
<br /><br /><p />Back to <a href='/index.php'>Login</a>
</center>
<?php
print_footer();
?>
