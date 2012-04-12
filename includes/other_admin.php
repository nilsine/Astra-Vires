<?php
/*****************
* Script to allow for the automating of admin requests
* Created:
* By: Moriarty
* On: 22/2/03
*****************/


if(isset($_GET['admin_readme'])){

	require_once("../user.inc.php");

	if($user['login_id'] != 1 && $user['login_id'] != OWNER_ID) {
		print_page("Error","You are not the admin. Go away.");
	}

	print_header("Admin Readme");
	print_leftbar();
	print_topbar();

?>
<a href="other_admin.php?uni_gen=1">Universe Generation Guide</a>
<hr><center><h3>Absolutely Must Follow Rules</h3></center>
These rules must be adhered to at ALL times, in all games.

<UL>
	<LI>The maximum turns must <b class=b1>ALWAYS</b> be at <b class=b1>least</b> 25 times greater than your hourly_turns. (for Blitz replace 25 with 13).
	<LI>Do not use expletives in the forums under the Admin account.
	<LI>Think of what the results in the game will be if you set the vars in a certain way!
	<LI>An Admin may not play his/her own game.
</UL>


<hr><center><h3>Less Important Rules</h3></center>
These rules may be bypassed, but <b class=b1>ONLY</b> if by doing so, a certain type of game will be created. Don't bypass them just for the hell of it!

<OL>
	<LI>You should give the players ONLY as many turns as they need at the start. Generally enough to allow them to explore a few sectors around Sol so as they have an idea of what it's like out there. 50 is usually more than enough.
	<LI>The higher hourly_turns is set the less need for turns players will have. Turns are the maintstay of SE. Give players too many, and the winner will be the one whos online the most.<br />Give the players <b class=b1>just enough</b>, and everyone will have to be conservative in their use, thus allowing for more challenge in the game.
	<LI>Players should NOT be able to destroy a whole enemies fleet with anything less than a days worth of turns. (In SD it should still be a few hours worth of turns!). Fleets take time to build, and they shouldn't be destroyable on a whim. It should be a focused and expensive action.
	<LI>Universes should <b class=b1>not</b> have 100% metal and fuel. These should take time to seek out, and be a valuble commodity. This applys to the initial universe generation, and the daily resource regeneration.
	<LI><b class=b1>hourly_tech</b> should be kept low. BM tech is supposed to be hard to get hold of. Peeps should not be able to buy fleets of any of the BM ships.
	<LI>Start cash should not be so high as to allow them to buy a huge great fleet at the start.
</OL>


<hr><center><h3>Suggestions</h3></center>
The following are suggestions, and should generally be abided by, unless you have a majorly different game-plan in mind.
<UL>
	<LI><b>bilkos_time </b>and <b>retire_period</b> should be set to whatever a "game day" is for your game.
	<LI>When using <b>alternate_play_2</b> you should make sure the start_turns are relativly low (say 40). Also be sure not to give players more turns than they need, or things will progress slowly. Keep <b>start_cash</b> low too.
	<LI><b>min_before_transfer</b> should be set to the equivalent of 2-3 "game days".
	<LI>Generating a universe with 20% metal, 15% fuel will give players something to find at the start of the game. (each system could get between 1000 and 120,000 of each mineral).
	<LI>Keep the daily mineral Regeneration stats low, so players will have to keep bouncing around to find stuff.
</UL>
<p /><br /><hr><center>And remember. Always think out what the game will be like with whatever the vars are.</center><br /><br /><br />

<?php
$rs .= "<br /><a href='../admin.php'>Back to the Admin page</a><br /><br /><br />";
print_footer();


//uni_gen info
} elseif(isset($_GET['uni_gen'])) {

	require_once("../user.inc.php");

	if($user['login_id'] != 1 && $user['login_id'] != OWNER_ID) {
		print_page("Error","You are not the admin. Go away.");
	}

	print_header("Generating Universes");
	print_leftbar();
	print_topbar();
?>

<hr><center><h2>Moriarty's Guide to being God</h2></center>
<br />This guide it centred around the the uv_map_layout admin var. But it'll start off with a brief explanation of the revelvent variables.
<p />Below are all of the variables that change the actual appearance of the map generated for the universe.
<UL>
	<LI>uv_map_layout - The type of map layout you want to end up with. Discussed in more detail below.
	<LI>uv_max_link_dist - The maximum distance that a link between star systems. System 1 is exempt from this var.<br />This var can allow you to create star islands with ease, and when used in conjunction with map layouts, can provide some very interesting results.
	<LI>uv_min_star_dist - The minimum distance between star systems.<br />The greater the number, the easier the resulting map will be to read. But also, the less likely it will be that layed out universes will be in their best conditions
	<LI>uv_num_stars - Obvious one. The more stars, the easier it is for peeps to get lost/hidden. But also, the more crowed the star map will be.<br />Whenever you increase this, you should probably increase uv_universe_size too.
	<LI>uv_show_warp_numbers - Make the peeps do some exploring and set this off. :)
	<LI>uv_universe_size - The size of the final universe (on a 2d grid). The bigger, the more spread out the stars will be.
	<LI>wormholes - Make map's look a mess, however they do provide handy routes around certain galaxy types (clusters and wheel especially).
</UL>

<p /><br /><br />Below is a list of the map layouts, and some suggestions for how to use them best.

<br /><hr><p /><b class=b1>Random Universe</b> - There isn't much That can be said about ideally setting up this type of universe. It's random and just about anything works.
<br />The only thing to remember, is to always make <b>uv_universe_size</b> large enough to be able to hold all of the stars.

<br /><hr><p /><b class=b1>Grid of Stars</b> - A simple little affair. All of the stars are laid out in a nice grid pattern. Numerically sorted too.<br />Can take players a while to get around though.<br />Any variable setups will probably work fine for this one.

<br /><hr><p /><b class=b1>Galactic Core</b> - Stars are 'layered', as in there are more stars in the centre than there are in the out-skirts.
<br />With the right setup, interesting maps can be created. For the best results, use with star count and universe size set to max.
<br /> - To keep things readable, setting <b>uv_min_star_dist</b> to a higher number (15+) is recommended.
<br /> - The setting of <b>uv_max_link_dist</b> can have some dramatic effects on this layout. Setting it to default will generally result in a sprawling mass of lines. But set it to some something fairly moderate, and you can have the inner systems linked, whilst there are lots of scattered stars in the outer reaches.

<br /><hr><p /><b class=b1>Clusters</b> - A very tricky one to get right. Be sure to allow lots of space for the clusters to be spread apart.
<br /> - <b>uv_min_dist</b> should be kept low, to allow clusters to remain intact.
<br /> - <b>uv_max_link_dis</b>t: Leave it at default to get a load of clusters that will probably be linked up. - But set it to a lower number if you want the clusters to be seperate. However this will result in quite a few 'lone stars'. It's a matter of balance.

<br /><hr><p /><b class=b1>Circle Filled with stars</b> - Basically, all the stars are placed within an invisible circle. There's a very low chance of getting star-islands on this one unless you twiddle with the <b>uv_max_link_dist</b> var a lot.
<br /> - <b>uv_min_dist</b> should probably be set to a higher value, or everything may get crowded.

<br /><hr><p /><b class=b1>Ring</b> - Simple but effective. A wheel of stars with system 1 in the middle, which is linked to a random collection of outer stars.
<br />- Unless you have a very low star count, the stars will only be linked to their nearby stars.
<br />- If you set the <b>uv_max_link_dist</b> just right, it's possible to link each star only to it's direct neighbours (i.e. system 3 points to systems 2 and 4 ONLY). This results in system 1 being a shortcut.
<br /> - For both rings, the <b>uv_min_dist</b> should be set to minimum.

<br /><hr><p /><b class=b1>Layers of Rings</b> - Basically many <b>Rings</b>, situated in such a manner as each ring connects to the next one.
<br /> - It is possible to get each ring to only link to other's within it's ring using the <b>uv_max_link_dist</b> var. Its them to only link to their direct neighbours (as suggested for normal ring).

<?php
$rs .= "<br /><a href='../admin.php'>Back to the Admin page</a><br /><br /><br />";
print_footer();

} else {

	require_once("../common.inc.php");
	print_header("Admin Request Submission");

	if(isset($enter_details)){

		//Connect to the database
		db_connect();

		dbn("insert into admin_request values ('$login_id', '".mysql_escape_string((string)$l_name)."-".mysql_escape_string((string)$login_name)."', '".date("M d - H:i (T)")."', '".mysql_escape_string((string)$email)."', '".mysql_escape_string((string)$game)."', '".mysql_escape_string((string)$time_playing)."', '".mysql_escape_string((string)$reason)."', '".mysql_escape_string((string)$do_to_game)."', '".mysql_escape_string((string)$other)."')");
		echo "Request submitted, Thank you.<p /><a href'=../game_listing.php'>Back to Game Listing</a>";

	#submitting info.
	} else {

?>
<p /><a href='../game_listing.php'>Back to Game Listing</a>
<hr><center><h3>Please Read</h3></center>
Please read the following before submitting your request to be an admin.
<p />
<ul>
	<li>Do <b class=b1>NOT</b> submit a request unless you are serious about it. Admining is a serious job, and can prove to be beyond some peoples skill set.
	<li>Only submit one request at a time. Do not request to be admin of more than 1 game or all your requests will be deleted!
	<li>You should be able to get online at least once a day, and be able to dedicate at least 5 mins to admining. Preferably more. :)
	<li>Do not expect a reply from this submission unless you are actually chosen. Sorry but I don't have the time to send out apologies to everyone. :)
	<LI>If you are presently admining a game, or have developer level access to a server, you will not be applicable for this job. purely SE only takes admins who are not admining elsewhere.
</ul>
<hr><center><h3>Submission Form</h3></center>

<FORM METHOD=POST ACTION="other_admin.php" name=admin_request>
<INPUT TYPE="hidden" name=login_id value=<? echo $login_id ?> >
<INPUT TYPE="hidden" name=enter_details value=1>

<p /><INPUT TYPE="text" NAME="email" value=""> : Email Addy.
<br /><INPUT TYPE="text" NAME="l_name" value=""> : Your most recognisable in-game name.
<br /><INPUT TYPE="text" NAME="time_playing" value=""> : Months playing SE.
<br /><INPUT TYPE="text" NAME="game" value=""> : Game you want to Admin
<p />Please enter your reason for wanting to be admin below:
<br /><TEXTAREA NAME="reason" ROWS="5" COLS="50"></TEXTAREA>

<p />State what you would do to a game you got your hands onto. Be concise.
<br /><TEXTAREA NAME="do_to_game" ROWS="5" COLS="50"></TEXTAREA>

<p />Any other info you regard as being pertinent.
<br /><TEXTAREA NAME="other" ROWS="5" COLS="50"></TEXTAREA>

<p /><INPUT TYPE="submit">

</FORM>
<p /><a href='../game_listing.php'>Back to Game Listing</a>
<?php
}
print_footer();

}

?>