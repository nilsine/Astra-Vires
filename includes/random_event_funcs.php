<?php
#page used to hold random event related functions (save on processing time in non-random event games).



#function to check if user has just run into random event. Called from location.php (subspace/normal moving)
function random_event_checker($star,$user,$autowarp) {
	global $db_name, $GAME_VARS;

	if($star['event_random'] == 1) {
		if ($user['turns_run'] < $GAME_VARS['turns_safe']) {
			$ret_str = "<center>Attention ! Attention ! <b class=b1>Trou Noir</b> Attention ! Attention !</center>";
			$ret_str .= "<p />Votre statut de débutant vous confère une protection contre le trou noir. Vous avez de la chance et vous n'en aurez surement pas autant la prochaine fois.";
			$ret_str .= "<center><p />Warp: ";
			if($star['link_1']) {
				$ret_str .= "&lt;<a href=location.php?toloc=$star[link_1]>$star[link_1]</a>&gt; ";
			}
			if($star['link_2']) {
				$ret_str .= "&lt;<a href=location.php?toloc=$star[link_2]>$star[link_2]</a>&gt; ";
			}
			if($star['link_3']) {
				$ret_str .= "&lt;<a href=location.php?toloc=$star[link_3]>$star[link_3]</a>&gt; ";
			}
			if($star['link_4']) {
				$ret_str .= "&lt;<a href=location.php?toloc=$star[link_4]>$star[link_4]</a>&gt; ";
			}
			if($star['link_5']) {
				$ret_str .= "&lt;<a href=location.php?toloc=$star[link_5]>$star[link_5]</a>&gt; ";
			}
			if($star['link_6']) {
				$ret_str .= "&lt;<a href=location.php?toloc=$star[link_6]>$star[link_6]</a>&gt; ";
			}

			if($autowarp) {
				$path_str = str_replace("+", " ", $autowarp);
				$autowarp_path = array();
				$autowarp_path = explode(" ", $path_str);
				//$ret_str .= "<br />Path_str is $path_str";
				$next_sector = array_shift($autowarp_path);
				//$ret_str .= " Next Sector is $next_sector";
				if($next_sector && ($next_sector == $star['link_1'] || $next_sector == $star['link_2'] || $next_sector == $star['link_3'] || $next_sector == $star['link_4'] || $next_sector == $star['link_5'] || $next_sector == $star['link_6'])) {
					$temp328 = implode($autowarp_path, "\x2B");
					if(!empty($temp328)){
						$ret_str .= "<br />Vortex vers le prochain système : &lt;<a href=location.php?toloc=$next_sector&autowarp=$temp328>$next_sector</a>&gt;";
					} else {
						$ret_str .= "<br />Vortex vers le prochain système : &lt;<a href=location.php?toloc=$next_sector>$next_sector</a>&gt;";
					}
				}
			}
			$ret_str .= "</center>";

			print_header("Black Hole");
			print_leftbar();
			print_topbar();
			echo $ret_str;
			echo '</td></tr></table>';
			echo '</body></html>';
			exit();

		} else {
			black_hole($user,$star);
		}

	//player runs into nebula
	} elseif ($star['event_random'] == 2) {
		dbn("update ${db_name}_ships set shields = 0 where login_id = $user[login_id] && location = $user[location] && $user[login_id] != 1");	
		$user_ship['shields'] = 0;

	#Solar Storm
	} elseif ($star['event_random'] == 12) {
		dbn("update ${db_name}_ships set shields = 0 where login_id = '$user[login_id]' && location = '$user[location]' && '$user[login_id]' != 1");	
		$user_ship['shields'] = 0;
		
	//end random things
	}
}

# -----------------------------------------
# Other functions


//Black Hole
function black_hole($user,$star) {
global $db_name,$user_ship;

	$bh_text = "Attention ! Attention ! Un trou noir a été détecté dans le systéme <b$star[star_id]</b>.<br />Vous manquez de peu d'être aspiré dedans mais vous parvenez à vous échapper au dernier moment. Vous prenez la fuite mais votre flotte à été dispersée dans la galaxie !";
	db("select count(star_id) from ${db_name}_stars");
	$total1 = dbr();
	$total = $total1[0];
	if($user_ship['ship_id']) {
		$n_text = "";
		db2("select ship_id,shields,fighters,ship_name from ${db_name}_ships where fleet_id = '$user_ship[fleet_id]' and location = '$user_ship[location]' && login_id = '$user[login_id]'");
		while($tow_ship = dbr2()) {

			$rand_star = random_system_num();

			dbn("update ${db_name}_ships set location = '$rand_star', mine_mode=0 where ship_id = $tow_ship[ship_id]");

			$totaldefs = $tow_ship['shields'] + $tow_ship['fighters'];

			if($totaldefs > 9) {
				$damtodo = round(($totaldefs /100) * 5);
				$damtodo2 = $damtodo;
				$shield_damage = $damtodo;
				if($shield_damage > $tow_ship['shields']) {
					$shield_damage = $tow_ship['shields'];
				}
				$damtodo -= $shield_damage;

				dbn("update ${db_name}_ships set fighters = fighters - $damtodo, shields = shields - $shield_damage where ship_id = '$tow_ship[ship_id]'");
				$n_text .= "<br />The <b class=b1>$tow_ship[ship_name]</b> took <b>$damtodo2</b> damage and was thrown to system #<b>$rand_star</b>.";
			}
		}
			
		$rand_star = mt_rand(2,$total);

		if($star['star_id'] == $rand_star) {
			if($rand_star != $total) {
				$rand_star ++;
			} else {
			$rand_star = $rand_star - 1;
			}
		}

		dbn("update ${db_name}_ships set location = $rand_star, mine_mode=0 where ship_id = $user_ship[ship_id]");

		$totaldefs = $user_ship['shields'] + $user_ship['fighters'];

		if($totaldefs > 9) {
			$damtodo = round(($totaldefs /100) * 5);
			$damtodo2 = $damtodo;
			$shield_damage = $damtodo;
			if($shield_damage > $user_ship['shields']) {
				$shield_damage = $user_ship['shields'];
			}
			$damtodo -= $shield_damage;
			dbn("update ${db_name}_ships set fighters = fighters - $damtodo, shields = shields - $shield_damage where ship_id = '$user_ship[ship_id]'");
			$m_text .="<p />The <b class=b1>$user_ship[ship_name]</b> took <b>$damtodo2</b> damage and was thrown to system #<b>$rand_star</b>.";
			$user_ship['shields'] -= $shield_damage;
			$user_ship['fighters'] -= $damtodo;
		}
		$bh_text .= $m_text;
		if(!empty($n_text)){
			$bh_text .= "<p />Reports from the rest of the Fleet Follow:<br />";
			$bh_text .= $n_text;
		}

		$tow_ship = $user_ship;
		dbn("update ${db_name}_users set location = $rand_star where login_id = '$user[login_id]'");
		dbn("update ${db_name}_ships set location = $rand_star where ship_id = '$user[ship_id]'");
	}

	post_news("Mayday, Mayday. Ici le capitaine <b class=b1>$user[login_name]</b>. Nous avons rencontré un trou noir...... *crackle* ..... besoin d'aide.... *static*", "random_event, ship");

	print_page("Location",$bh_text);
}
?>
