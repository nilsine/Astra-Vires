jQuery.noConflict();
jQuery(document).ready(function() {
	jQuery("#annuler_didac").click(function() {
		jQuery("#didacticiel").fadeOut();
		jQuery.get('ajax.php?cmd=annuler_didac');
	});
	
	jQuery("#suiv_didac").click(function() {
		jQuery.get('ajax.php?cmd=suiv_didac', function (data) {
			jQuery("#didacticiel").fadeOut(function() {
				jQuery("#texte_didac").html(data);
				jQuery("#didacticiel").fadeIn();
			});
		});
	});
	
	jQuery("#prec_didac").click(function() {
		jQuery.get('ajax.php?cmd=prec_didac', function (data) {
			jQuery("#didacticiel").fadeOut(function() {
				jQuery("#texte_didac").html(data);
				jQuery("#didacticiel").fadeIn();
			});
		});
	});
	
	jQuery("#mc_chgt_flotte").click(function () {
		jQuery("#chgt_flotte").slideToggle();
	});
});
