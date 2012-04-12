<script language="JavaScript1.2" type="text/javascript">

var clientVer = parseInt(navigator.appVersion); // Get browser version
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav  = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1) && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1) && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));

var is_win   = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac    = (clientPC.indexOf("mac")!=-1);

bbcode = new Array();
bbtags = new Array('[b]','[/b]','[i]','[/i]','[bi]','[/bi]','[u]','[/u]','','','[url]','[/url]');
var theSelection = false;

// Replacement for arrayname.length property
function getarraysize(thearray) {
	for (i = 0; i < thearray.length; i++) {
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null))
			return i;
		}
	return thearray.length;
}

// Replacement for arrayname.push(value) not implemented in IE until version 5.5
// Appends element to the array
function arraypush(thearray,value) {
	thearray[ getarraysize(thearray) ] = value;
}

// Replacement for arrayname.pop() not implemented in IE until version 5.5
// Removes and returns the last element of an array
function arraypop(thearray) {
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
}

//Highlight image script- By Dynamic Drive
//For full source code and more DHTML scripts, visit http://www.dynamicdrive.com
//This credit MUST stay intact for use

function makevisible(cur,which) {
	strength=(which==0)? 1 : 0.6
	if (cur.style.MozOpacity) {
		cur.style.MozOpacity=strength
	} else if (cur.filters) {
		cur.filters.alpha.opacity=strength*100
	}
}
function emoticon(msg) {

 msg = ' ' + msg + ' ';
 document.get_var_form.text.value  += msg;
 document.get_var_form.text.focus();
}

function bbstyle(bbnumber) {

	donotinsert = false;
	theSelection = false;
	bblast = 0;

	if (bbnumber == -1) { // Close all open tags & default button names
		while (bbcode[0]) {
			butnumber = arraypop(bbcode) - 1;
			document.get_var_form.text.value += bbtags[butnumber + 1];
			buttext = eval('document.get_var_form.addbbcode' + butnumber + '.value');
			eval('document.get_var_form.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
		}
		document.get_var_form.text.focus();
		return;
	}

	if ((clientVer >= 4) && is_ie && is_win)
		theSelection = document.selection.createRange().text; // Get text selection

	if (theSelection) {
		// Add tags around selection
		document.selection.createRange().text = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
		document.get_var_form.text.focus();
		theSelection = '';
		return;
	}

	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < bbcode.length; i++) {
		if (bbcode[i] == bbnumber+1) {
			bblast = i;
			donotinsert = true;
		}
	}

	if (donotinsert) {		// Close all open tags up to the one just clicked & default button names
		while (bbcode[bblast]) {
				butnumber = arraypop(bbcode) - 1;
				document.get_var_form.text.value += bbtags[butnumber + 1];
				buttext = eval('document.get_var_form.addbbcode' + butnumber + '.value');
				eval('document.get_var_form.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
			}
			document.get_var_form.text.focus();
			return;
	} else { // Open tags

		// Open tag
		document.get_var_form.text.value += bbtags[bbnumber];
		arraypush(bbcode,bbnumber+1);
		eval('document.get_var_form.addbbcode'+bbnumber+'.value += "*"');
		document.get_var_form.text.focus();
		return;
	}
	storeCaret(document.get_var_form.text);
}


// Insert at Claret position. Code from
// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}

</script>


<table border=0 cellspacing=1 cellpadding=2>
<tr><td colspan=7>&nbsp;<a href='javascript:bbstyle(-1)'>Fermer toutes les balises</a></td></tr>
<tr><td>
<table border=0 cellspacing=1 cellpadding=2 width=100%>
<tr><td><input type='button' class='button' accesskey='b' name='addbbcode0' value=' B ' style='font-weight:bold; width: 30px' onClick='bbstyle(0)' /></td>
<td><input type='button' class='button' accesskey='i' name='addbbcode2' value=' i ' style='font-style:italic; width: 30px' onClick='bbstyle(2)' /></td>
<td><input type='button' class='button' accesskey='bi' name='addbbcode4' value=' Bi ' style='font-style:italic; font-weight:bold; width: 30px' onClick='bbstyle(4)' /></td>
<td><input type='button' accesskey='u' name='addbbcode6' value=' u ' onClick='bbstyle(6)' /></td>
<td><input type='button' accesskey='w' name='addbbcode10' value='URL' onClick='bbstyle(10)' /></td>
<td>
<select name=colorchanger>
<option value='#FFFFFF'>Par d&eacute;faut</option>
<option value='darkred'>Rouge fonc&eacute;</option>
<option value='red'>Rouge</option>
<option value='orange'>Orange</option>
<option value='brown'>Marron</option>
<option value='yellow'>Jaune</option>
<option value='green'>Vert</option>
<option value='olive'>Olive</option>
<option value='cyan'>Cyan</option>
<option value='blue'>Bleu</option>
<option value='darkblue'>Bleu fonc&eacute;</option>
<option value='indigo'>Indigo</option>
<option value='violet'>Violet</option>
<option value='white'>Blanc</option>
<option value='black'>Noir</option>
</select>
</td></tr></table>

<textarea name=text cols=50 rows=15 wrap=virtual><?php echo $var_default?></textarea>
<p /><input type=submit name=submit value='Envoyer'> - <input type=submit name=preview value='Aper&ccedil;u'></td>
<td width=100 valign=top>
<table cellspacing=0 cellpadding=4 width=100 border=0>
<tr><th colspan=2>Emoticones</th></tr>
<tr align='center' valign='middle'>
<td width=50><a href=javascript:emoticon('[lol]')><img src='images/smiles/lol.gif' border='0' alt='Smile' title='Smile' /></a></td>
<td width=50><a href=javascript:emoticon('[sad]')><img src='images/smiles/sad.gif' border='0' alt='Sad' title='Sad' /></a></td></tr>

<tr align='center' valign='middle'>
<td width=50><a href=javascript:emoticon('[surp]')><img src='images/smiles/surp.gif' border='0' alt='Surprised' title='Surprised' /></a></td>
<td width=50><a href=javascript:emoticon('[help]')><img src='images/smiles/help.gif' border='0' alt='Help' title='Help' /></a></td></tr>

<tr align='center' valign='middle'>
<td width=50><a href=javascript:emoticon('[cool]')><img src='images/smiles/cool.gif' border='0' alt='Cool' title='Cool' /></a></td>
<td width=50><a href=javascript:emoticon('[check]')><img src='images/smiles/check.gif' border='0' alt='Check This' title='Check This' /></a></td></tr>

<tr align='center' valign='middle'>
<td width=50><a href=javascript:emoticon('[wink]')><img src='images/smiles/wink.gif' border='0' alt='Wink' title='Wink' /></a></td>
<td width=50><a href=javascript:emoticon('[huh]')><img src='images/smiles/huh.gif' border='0' alt='huh' title='huh' /></a></td></tr>

<tr align='center' valign='middle'>
&nbsp;<td width=50><a href=javascript:emoticon('[evil]')><img src='images/smiles/evil.gif' border='0' alt='Evil' title='Evil' /></a></td>
&nbsp;<td width=50><a href=javascript:emoticon('[tongue]')><img src='images/smiles/tongue.gif' border='0' alt='Tongue' title='Tongue' /></a></td></tr>

<tr align='center' valign='middle'>
<td width=50><a href=javascript:emoticon('[mad]')><img src='images/smiles/mad.gif' border='0' alt='Mad' title='Mad' /></a></td>
<td width=50><a href=javascript:emoticon('[ass]')><img src='images/smiles/ass.gif' border='0' alt='Ass' title='Ass' /></a></td></tr>

<tr align='center' valign='middle'>
<td width=50><a href=javascript:emoticon('[bad]')><img src='images/smiles/bad.gif' border='0' alt='Mad2' title='Mad2' /></a></td>
<td width=50><a href=javascript:emoticon('[scream]')><img src='images/smiles/scream.gif' border='0' alt='Scream' title='scream' /></a></td></tr>

<tr align='center' valign='middle'>
<td width=50><a href=javascript:emoticon('[upto]')><img src='images/smiles/upto.gif' border='0' alt='Up To' title='Up To' /></a></td>
<td width=50><a href=javascript:emoticon('[thumb]')><img src='images/smiles/thumb.gif' border='0' alt='Thumbs Up' title='Thumbs Up' /></a></td></tr>

</table></td></tr></table>