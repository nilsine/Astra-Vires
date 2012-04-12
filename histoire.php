<?php
$titre_page = "Histoire et récits";
$nomPage = 'histoires';
include('includes/haut_index.inc.php');
?>

<div id="contenu_public">    
    <?php
    	$help_type = $cw['game_stories'];
        $histoire = ($_GET['t'] == 'histoire');
    
    	//load stories
    	$results = load_xml("$directories[includes]/stories.xml");
    
    	$header_str = "";
    	$content_str = "";
    
    	 //list stories from arrray.
    	foreach($results['story'] as $key => $stories_array){
        		if ($key == 'Histoire')
        		    $header_str .= "\n<a href='histoire.html#$key'>$stories_array[title]</a><br />";
                else
                    $header_str .= "\n<a href='recits.html#$key'>$stories_array[title]</a><br />";
        		if (($histoire && $key == 'Histoire') || (!$histoire && $key != 'Histoire')) $content_str .= "\n<a name='$key'><center><b>$stories_array[title]</b></center></a><br /><p>$stories_array[content]</p>".$cw['written_by']." <b class=b1>$stories_array[author]</b> et traduit par Talath";
    	}
    
    	$out_str .= "<h3><b>Récits d'Astra Vires</b></h3><br />".$header_str."<br /><br />".$content_str;
    	echo $out_str;
    ?>
</div>

<!-- fermeture div accueilPrincipal -->
</div>

<?php include('includes/bas_index.inc.php'); ?>
