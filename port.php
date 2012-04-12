<?php
require("user.inc.php");


$filename = "port.php";

ship_status_checker();

db("select * from ${db_name}_ports where location = '$user[location]'");
$port = dbr();

if (!$port) {
	print_page($cw['port'],$st[756]);
}


$metal_buy = $GAME_VARS['buy_metal'];
$metal_sell = $GAME_VARS['buy_metal'] - round(($GAME_VARS['buy_metal']/100)*20);
$fuel_buy = $GAME_VARS['buy_fuel'];
$fuel_sell = $GAME_VARS['buy_fuel'] - round(($GAME_VARS['buy_fuel']/100)*20);
$elect_buy = $GAME_VARS['buy_elect'];
$elect_sell = $GAME_VARS['buy_elect'] - round(($GAME_VARS['buy_elect']/100)*20);

$rs = "<p /><a href='port.php'>Return to Starport</a>";

settype($amount, "integer");
$amount = round($amount);
$error_str = "<img src=$directories[images]/places/port.jpg><br>";


#Resource Trading
if(isset($deal)) {
	if($deal == 1){
		$resource_deal = "metal";
		$resource_str = $cw['metal'];
		$buy_cost = $metal_buy;
		$sell_cost = $metal_sell;
	} elseif($deal == 2){
		$resource_deal = "fuel";
		$resource_str = $cw['fuel'];
		$buy_cost = $fuel_buy;
		$sell_cost = $fuel_sell;
	} elseif($deal == 3){
		$resource_deal = "elect";
		$resource_str = $cw['electronics'];
		$buy_cost = $elect_buy;
		$sell_cost = $elect_sell;
	} else {
		echo $st[757];
	}

	#fleet fill with a mineral
	if($buy_sell == 3) {

		$max = "(cargo_bays-metal-fuel-elect-colon)";

		if($GAME_VARS['alternate_play_1'] == 1 && ($deal == 1 || $deal == 2)) {
			print_page($cw['error'],$st[758]);
		} else {
			$error_str .= fill_fleet($resource_deal, $max, $resource_str, $buy_cost, 1)."<p />";
		}


	#find out how much of the material the user wants to deal in.
	} elseif($amount < 1) {
		if($buy_sell == 0) { #buy
			#figure out max capacity.
			$def = floor($user['cash'] / $metal_buy);
			if($def > $user_ship['empty_bays']) {
				$def = $user_ship['empty_bays'];
			}

			#not allowed to buy.
			if($GAME_VARS['alternate_play_1'] == 1 && ($deal == 1 || $deal == 2)) {
				print_page($cw['error'],$st[758]);
			} elseif ($user_ship['empty_bays'] > 0) {
				get_var($cw['buy'] . $resource_str, $filename,sprintf($st[759],$resource_str),'amount',$def);
			} else { #no cargo cap
				$error_str .= $st[760]."<p />";
			}

		} elseif ($user_ship[$resource_deal]) {#sell commodity
			get_var($cw['sell'] . $resource_str,$filename,sprintf($st[761], $resource_str),'amount',$user_ship[$resource_deal]);
		} else { #no commodity to sell
			$error_str .= sprintf($st[762], $resource_str)."<p />";
		}

	} else { #user has entered amount of resource to play with

		if($buy_sell == 0) { # buy continued
			if(($amount * $buy_cost > $user['cash']) && $user['login_id'] != 1) {
				$error_str .= $st[763].$resource_str.".<p />";
			} elseif($GAME_VARS['alternate_play_1'] == 1 && ($deal == 1 || $deal == 2)) {
				print_page($cw['error'],$st[764]);
			} elseif($amount > $user_ship['empty_bays']) {
				$error_str .= "$st[765] $resource_str.<p />";
			} else {
				take_cash($amount * $buy_cost);
				dbn("update ${db_name}_ships set $resource_deal = $resource_deal + $amount where ship_id = $user[ship_id]");
				$user_ship[$resource_deal] += $amount;
				$error_str .= sprintf($st[766], $amount, $resource_str) . $amount*$buy_cost."</b> ".$cw['credits'].".<p />";
			}

		} elseif($buy_sell == 1) { #sell metal
			if($amount > $user_ship[$resource_deal]) {
				$error_str .= "$st[767] $resource_str.<p />";
			} else {
				give_cash($amount * $sell_cost);
				dbn("update ${db_name}_ships set $resource_deal = $resource_deal - $amount where ship_id = $user[ship_id]");
				$user_ship[$resource_deal] -= $amount;
				$error_str .= sprintf($st[768], $amount, $resource_str)." <b>".$amount*$sell_cost."</b> ".$cw['credits'].".<p />";
			}
		}
	}
}


#user wants to sell all
if(isset($sell_all)) {
	$elect_sold = 0;
	$fuel_sold = 0;
	$metal_sold = 0;

	if(isset($all_ships)) {#all being sold from all ships
		$sold_worth = 0;
		$ship_count = 0;
		db("select elect,fuel,metal,ship_id from ${db_name}_ships where location = $user[location] and login_id = $user[login_id]");
		while ($current_ship = dbr()) {
			$sold_worth += (($current_ship['elect'] * $elect_sell) + ($current_ship['fuel'] * $fuel_sell) + ($current_ship['metal'] * $metal_sell));
			$elect_sold = $elect_sold + $current_ship['elect'];
			$fuel_sold = $fuel_sold + $current_ship['fuel'];
			$metal_sold = $metal_sold + $current_ship['metal'];
			if($current_ship['elect'] || $current_ship['metal'] || $current_ship['fuel']) {
				$ship_count++;
			}
		}
		if ($sold_worth < 1) {
			print_page($cw['port'],$st[769]);
		} elseif($ship_count == 1 && $user['turns'] > 0){
			echo "<script>self.location='port.php?sell_all=1&changed=1';</script>";
			echo $st[770];
			print_footer();
		} elseif ($user['turns'] < 5) {
			print_page($cw['port'],$st[771]);
		} elseif(!isset($sure)) {
			get_var($cw['sell_all_cargo'],$filename, sprintf($st[772], $ship_count, $metal_sold, $metal_sell*$metal_sold, $fuel_sold, $fuel_sell*$fuel_sold, $elect_sold, $elect_sell*$elect_sold, $sold_worth),'sure','yes');
		} else {
			dbn("update ${db_name}_ships set elect = 0, metal = 0, fuel = 0 where location = '$user[location]' && login_id = '$user[login_id]' && cargo_bays > 0");
			charge_turns(5);
			$error_str .= $st[773];
			$error_str .= "<p />$st[774]: <b>$metal_sold</b><br />$st[775]: <b>$fuel_sold</b><br />$st[776]: <b>$elect_sold</b>";
			$error_str .= "<p />".$st[777].": <b>";
			$total_goods = $metal_sold + $fuel_sold + $elect_sold;
			$error_str .= "$total_goods</b>";
			$error_str .= "<br />".$st[778].": <b>$sold_worth</b>.";
			$error_str .= "<br />".sprintf($st[779], $ship_count)."<p />";
		}

	} else { #all being sold from just the present ship.
		$sold_worth = (($user_ship['elect'] * $elect_sell) + ($user_ship['fuel'] * $fuel_sell) + ($user_ship['metal'] * $metal_sell));
		if ($user['turns'] < 1) {
			print_page($cw['port'],$st[780]);
			} elseif ($sold_worth < 1) {
			print_page($cw['port'],$st[781]);
		} elseif(!isset($sure)) {
			if(isset($changed)){
				get_var($st[782],$filename,sprintf($st[783], $sold_worth),'sure','yes');
			} else {
				get_var($st[782],$filename,sprintf($st[784], $sold_worth),'sure','yes');
			}
		} else {
			dbn("update ${db_name}_ships set elect = 0, metal = 0, fuel = 0 where ship_id = '$user[ship_id]'");
			$elect_sold = $elect_sold + $user_ship['elect'];
			$fuel_sold = $fuel_sold + $user_ship['fuel'];
			$metal_sold = $metal_sold + $user_ship['metal'];
#			if ($user_ship[metal] > 0) { $error_str .= "You sold $user_ship[metal] units of metal.<p />"; }
#			if ($user_ship[fuel] > 0) { $error_str .= "You sold $user_ship[fuel] units of fuel.<p />";
#			if ($user_ship[elect] > 0) { $error_str .= "You sold $user_ship[elect] units of electronics.<p />";
	 		charge_turns(1);
			$error_str .= $st[785]."<p />";
			$error_str .= "<p />$st[774]: <b>$metal_sold</b><br />$st[775]: <b>$fuel_sold</b><br />$st[776]: <b>$elect_sold</b>";
			$error_str .= "<p />$st[777]: <b>";
			$total_goods = $metal_sold + $fuel_sold + $elect_sold;
			$error_str .= "$total_goods</b>";
			$error_str .= "<br />$st[778]: <b>$sold_worth</b>.<p />";
		}
	}
		dbn("update ${db_name}_users set cash = cash + $sold_worth where login_id = $user[login_id]");
		$user['cash'] += $sold_worth;
		$user_ship['metal'] = 0;
		$user_ship['fuel'] = 0;
		$user_ship['elect'] = 0;
#		$user_ship[colon] = 0;
}


empty_bays($user_ship);


		$sold_worth = 0;
		$ship_count = 0;
		db("select elect,fuel,metal,ship_id from ${db_name}_ships where location = $user[location] and login_id = $user[login_id]");
		while ($current_ship = dbr()) {
			$sold_worth += (($current_ship['elect'] * $elect_sell) + ($current_ship['fuel'] * $fuel_sell) + ($current_ship['metal'] * $metal_sell));
			$elect_sold = $elect_sold + $current_ship['elect'];
			$fuel_sold = $fuel_sold + $current_ship['fuel'];
			$metal_sold = $metal_sold + $current_ship['metal'];
			if($current_ship['elect'] || $current_ship['metal'] || $current_ship['fuel']) {
				$ship_count++;
			}
		}

// print page
$error_str .= "$st[786] #<b>$port[location]</b>";

$error_str .= "<p /><img src='images/logos/titane.gif' align=absmiddle>&nbsp;<b class='b1'>".$cw['metal']."</b>";
if($GAME_VARS['alternate_play_1'] == 0) { #can't buy metal in this style of play.
	$error_str .= "<br /><a href='port.php?deal=1&buy_sell=0'>".$cw['buy']."</a> - <b>$metal_buy</b> " . $cw['credits_per_ton'] . " - <a href='port.php?deal=1&buy_sell=3'>".$cw['fill_fleet']."</a>";
}
$error_str .= "<br /><a href='port.php?deal=1&buy_sell=1'>".$cw['sell']."</a> - ".sprintf($st[1887],($metal_sell * $user_ship["metal"]))." - <b>$metal_sell</b> " . $cw['credits_per_ton'];

$error_str .= "<p /><img src='images/logos/larium.gif' align=absmiddle>&nbsp;<b class='b1'>".$cw['fuel']."</b>";

if($GAME_VARS['alternate_play_1'] == 0) { #can't buy fuel in this style of play.
	$error_str .= "<br /><a href='port.php?deal=2&buy_sell=0'>".$cw['buy']."</a> - <b>$fuel_buy</b> " . $cw['credits_per_ton'] . " - <a href='port.php?deal=2&buy_sell=3'>".$cw['fill_fleet']."</a>";
}
$error_str .= "<br /><a href='port.php?deal=2&buy_sell=1'>".$cw['sell']."</a> - ".sprintf($st[1887],($fuel_sell * $user_ship["fuel"]))." - <b>$fuel_sell</b> " . $cw['credits_per_ton'];

$error_str .= "<p /><img src='images/logos/electronique.gif' align=absmiddle>&nbsp;<b class='b1'>".$cw['electronics']."</b>";
$error_str .= "<br /><a href='port.php?deal=3&buy_sell=0'>".$cw['buy']."</a> - <b>$elect_buy</b> " . $cw['credits_per_ton'] . " - <a href='port.php?deal=3&buy_sell=3'>".$cw['fill_fleet']."</a>";
$error_str .= "<br /><a href='port.php?deal=3&buy_sell=1'>".$cw['sell']."</a> - ".sprintf($st[1887],($elect_sell * $user_ship["elect"]))." - <b>$elect_sell</b> " . $cw['credits_per_ton'];

$error_str .= "<p /><a href='port.php?sell_all=1'>".$cw['sell_amm']."</a>";
$error_str .= "<p />".sprintf($st[1889], $ship_count, $metal_sold, $metal_sell*$metal_sold, $fuel_sold, $fuel_sell*$fuel_sold, $elect_sold, $elect_sell*$elect_sold, $sold_worth);
$error_str .= "<p /><a href='port.php?sell_all=1&all_ships=1'>".$st[787]."</a>";
if($port['location'] != 1){
	$error_str .= "<p />Teleconferance To <a href='bilkos.php'>".$st[788]."</a>";
}

//$error_str .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

$rs = "<p /><a href='location.php'>".$cw['takeoff']."</a><br />";

print_page($cw['port'],$error_str);
?>
