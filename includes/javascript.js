function TickAll(form_name)
{
	alpha = eval("document."+form_name);
	len = alpha.elements.length;
	var index = 0;
	for( index=0; index < alpha.elements.length; index++ ) {
		if(alpha.elements[index].name != 'fleet_type' && alpha.elements[index].name != 'fleet_manip'){
			if(alpha.elements[index].checked == false) {
				alpha.elements[index].checked = true;
			} else {
				alpha.elements[index].checked = false;
			}
		}
	}
}
function popup( url, width, height) {
//	window.open( url,"Help","resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,width="+width+",height="+height);
//	Boxy.load(url, {title: 'Information'});

	jQuery("#popup_contenu").load(url);
	jQuery("#popup").show();
}

function GetId(id)
{
	return document.getElementById(id);
}
var i=false; // La variable i nous dit si la bulle est visible ou non

function move(e) {
	if(i) {  // Si la bulle est visible, on calcul en temps reel sa position ideale
		if (navigator.appName!="Microsoft Internet Explorer") { // Si on est pas sous IE
			GetId("curseur").style.left=e.pageX + 5+"px";
			GetId("curseur").style.top=e.pageY + 10+"px";
		}
		else { // Modif proposé par TeDeum, merci �   lui
			if(document.documentElement.clientWidth>0) {
				GetId("curseur").style.left=20+event.x+document.documentElement.scrollLeft+"px";
				GetId("curseur").style.top=10+event.y+document.documentElement.scrollTop+"px";
			} else {
				GetId("curseur").style.left=20+event.x+document.body.scrollLeft+"px";
				GetId("curseur").style.top=10+event.y+document.body.scrollTop+"px";
			}
		}
	}
}

function montre(text) {
	if(i==false) {
		GetId("curseur").style.visibility="visible"; // Si il est cacher (la verif n'est qu'une securité) on le rend visible.
		GetId("curseur").innerHTML = text; // on copie notre texte dans l'élément html
		i=true;
	}
}
function cache() {
	if(i==true) {
		GetId("curseur").style.visibility="hidden"; // Si la bulle est visible on la cache
		i=false;
	}
}
document.onmousemove=move;
