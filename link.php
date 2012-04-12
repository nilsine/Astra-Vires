<?php
//Last audited: 23/5/04 by Moriarty
require_once("dir_names.php");
require_once("$directories[games]/config.dat.php");
?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $directories['games']; ?>/style1.css" />
</head>
<body>
<h2>Server Linking policy.</h2>


<p /><b>Linking to other server's</b>
<br />This server will only link to other SE servers on the front page, if they link directly to this server from their front-page.
<br />SE servers will only be linked too if when they are somewhat established. ("somewhat established" being whatever the Server Operator chooses it to be).
<br />It is the Server Admins prerogative on who get's linked to.

<p /><b>Other Links</b>
<p />If you would like to link to this server feel free. However clearly this server can't reciprocate all links as it lacks enough front-page space. But a return link will be made to your site if it is deemed beneficial to the community.

<p /><br /><br /><h2>Link to the server</h2>
<br />You could use a standard HTML link to the server. Or you could use the 'look's like a image but isn't' link below.
<p />At present there are no link 'images' for Solar Empire. Feel free to develop some and message an in-game admin (or the server admin if you can find him/her) with them.

<p />But we do have the following non-image link you can use:

<p /><a style="text-decoration:none" href="<?php echo URL_PREFIX?>"/><span style="color:#FFBB99; background-color:#223344; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:160%; padding:2px 4px 2px 4px"><?php echo SERVER_NAME;?></span></a>

<p />Code to do it::
<p /><code>
&lt;a style="text-decoration:none" href="<?php echo URL_PREFIX?>/"&gt;&lt;span style="color:#ffffff; background-color:#FFFFFF; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:160%; padding:2px 4px 2px 4px"&gt;<?php echo SERVER_NAME;?>&lt;/span&gt;&lt;/a&gt;
</code>

<p /><center><a href='/'>Back to the Server</a></center>

</body>
</html>