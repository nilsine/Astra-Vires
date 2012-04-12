<?php
/* include the libs */
include("xmlrpc-2.2.2/lib/xmlrpc.inc");
include("xmlrpc-2.2.2/lib/xmlrpcs.inc");

require_once('common.inc.php');

if (preg_match('`bp_api\.php`', $_SERVER['PHP_SELF'])) {

	db_connect();
	
	/* get xml */
	$sXml = file_get_contents('php://input');
	
	/* create md5 hash with XML and your secretKey */
	$sAuthHash = strtolower(md5($sXml . $sSecretKey));
	
	/* check authentication */
	if ( strcmp($sAuthHash, $_GET['authHash']) != 0) {
	    /* authentication failed - generate fault code and string */
	    $aOutput = array('faultCode' => '-1', 'faultString' => 'authentication failed');
	    $pResp = new xmlrpcresp( php_xmlrpc_encode($aOutput) );
	    echo $pResp->serialize() ;
	} else {
	    /* authentication okay */
	    /* set common signature for the methods */
	    $aSignature = array(array($xmlrpcStruct, $xmlrpcStruct));
	    
	    $pMyGameClass = new TheGame();
	
	    /* methods to be registered - $pMyGameClass is an instance of class TheGame */
	    $aRegister = array(
	        'game.login' => array('function' => array($pMyGameClass, 'Login'), 'signature' => $aSignature),
	        'game.registerAndLogin' => array('function' => array($pMyGameClass, 'Register'), 'signature' => $aSignature),
	        'game.getUserStats' => array('function' => array($pMyGameClass, 'GetStats'), 'signature' => $aSignature),
	        'bookItem' => array('function' => array($pMyGameClass, 'BookItem'), 'signature' => $aSignature),
	        'blockedNotify' => array('function' => array($pMyGameClass, 'BlockedNotify'), 'signature' => $aSignature),
	        'getUserPaymentLanguage' => array('function' => array($pMyGameClass, 'GetUserPaymentLanguage'), 'signature' => $aSignature),
	        'getUserExchangeRate' => array('function' => array($pMyGameClass, 'GetUserExchangeRate'), 'signature' => $aSignature),
	        'currencySwap' => array('function' => array($pMyGameClass, 'CurrencySwap'), 'signature' => $aSignature)
	    );
	
	    /* create server, set encoding and invoke */
	    $pServer = new xmlrpc_server($aRegister, false);
	    $pServer->response_charset_encoding = 'UTF-8';
	    $pServer->service(); 
	}
}



/* example game class */
class TheGame
{
    function Login($pXml)
    {
        /* do your stuff here and return something like: */
        $aParams = php_xmlrpc_decode($pXml); $aParams = $aParams[0];
        $bpUserID = $aParams['bpUserID'];
        $login_id = $aParams['userID'];
        db("select * from user_accounts where login_id=$login_id and bp_user_id=$bpUserID");
		$data = dbr();
		if (dbc()) {
			if ($data['login_name']) {
				insert_history($login_id, "Connexion via API BP compte $bpUserID");
				require_once('includes/session_funcs.inc.php');
				$session = login_to_server($data['login_name'], '', $data['bp_user_id'], true);
				$url = URL_PREFIX . "/game_listing.php?sid=$session";
			} else {
				insert_history($login_id, "Connexion via API BP compte $bpUserID mais pas de pseudo");
				$url = URL_PREFIX . "/inscription.php?lidbp=$login_id";
			}
	        $aOutput = array(
	            'result'      => new xmlrpcval('OK', 'string'),
	            'redirectURL' => new xmlrpcval($url, 'string')
	        );
		} else {
			insert_history($login_id, "Ce compte BP $bpUserID n'existe pas");
	        $aOutput = array(
	            'faultCode'      => new xmlrpcval(-1, 'int'),
	            'faultString'	 => new xmlrpcval('This account does not exists', 'string')
	        );
		}
        return new xmlrpcresp( php_xmlrpc_encode($aOutput) );
    }

    function Register($pXml)
    {
    	$aParams = php_xmlrpc_decode($pXml); $aParams = $aParams[0];
        $bpUserID = $aParams['bpUserID'];
    	db("select * from user_accounts where bp_user_id=$bpUserID and login_name != ''");
    	$data = dbr();
    	if (!dbc()) {
			insert_history($login_id, "Inscription via API BP $bpUserID");
    		db("select * from user_accounts where bp_user_id=$bpUserID");
	    	if (!dbc()) {
	    		dbn("insert into user_accounts (bp_user_id, affiliate_id, signed_up) values (" . $aParams['bpUserID'] . ", " . $aParams['affiliateID'] . ", " . time() . ")");
		    	$login_id = mysql_insert_id();
	    		insert_history($login_id, "Ajout du compte BP $bpUserID");
		    } else {
		    	$data2 = dbr();
		    	$login_id = $data2['login_id'];
	    		insert_history($login_id, "Compte BP $bpUserID déjà existant mais sans pseudo");
		    }
	    	$url = URL_PREFIX . "/inscription.php?lidbp=$login_id";
    	} else {
    		require_once('includes/session_funcs.inc.php');
			$session = login_to_server($data['login_name'], '', $data['bp_user_id'], true);
			$url = URL_PREFIX . "/game_listing.php?sid=$session";
			$login_id = $data['login_id'];
	    	insert_history($login_id, "Compte BP $bpUserID existant");
    	}
	    $aOutput = array(
	       'result'      => new xmlrpcval('OK', 'string'),
	        'userID'      => new xmlrpcval($login_id, 'int'),
	        'redirectURL' => new xmlrpcval($url, 'string')
	    );
        return new xmlrpcresp( php_xmlrpc_encode($aOutput) );
    }

    function GetStats($pXml)
    {
    	$aParams = php_xmlrpc_decode($pXml); $aParams = $aParams[0];
    	
        $login_id = $aParams['userID'];
        db("select * from user_accounts where login_id=$login_id");
		$data = dbr();
		if (dbc()) {
		    $aOutput = array(
	            'result'          => new xmlrpcval('OK', 'string'),
//	            'userRank'        => new xmlrpcval('100', 'int'),
	            'virtualCurrency' => new xmlrpcval('0', 'int'),
	            'realCurrency'    => new xmlrpcval('0', 'int')
	        );
		} else {
	        $aOutput = array(
	            'faultCode'      => new xmlrpcval(-1, 'int'),
	            'faultString'	 => new xmlrpcval('This account does not exists', 'string')
	        );
		}
        return new xmlrpcresp( php_xmlrpc_encode($aOutput) );
    }
    
    function BookItem($pXml)
    {
    	$aParams = php_xmlrpc_decode($pXml); $aParams = $aParams[0];
    	extract($aParams);
    	db("select * from user_accounts where login_id=$userID");
    	$p_user = dbr();
    	db("select * from ".$p_user['in_game']."_users where login_id=$userID");
    	$user = dbr();
    	dbn("insert into transactions (login_id, game_name, vars) values (".$p_user['login_id'].", '".$p_user['in_game']."', '".print_r($aParams, true)."')");
    	
    	$resultat = mysql_query("select score from ".$p_user['in_game']."_users order by score desc");
		$data = mysql_fetch_array($resultat);
		$max_score = $data['score'];
		$resultat = mysql_query("select score from ".$p_user['in_game']."_users order by score asc");
		$data = mysql_fetch_array($resultat);
		$min_score = $data['score'];

		$jours = floor(($user['score']-$min_score)/($max_score-$min_score)*5)+1;
    	
    	if ($type == 'Cycles' && $user['last_buy'] < (time()-$jours*24*3600)) {
    		db("select ".$p_user['in_game']."_value as max_turns from se_db_vars where name='max_turns'");
    		$data = dbr();
    		dbn("update ".$p_user['in_game']."_users set last_buy=".time().", turns=".$data['max_turns']." where login_id = $userID");
    	}
	    $aOutput = array('result' => new xmlrpcval('OK', 'string'));
        return new xmlrpcresp( php_xmlrpc_encode($aOutput) );
    }
    
    function BlockedNotify($pXml)
    {
    	$aParams = php_xmlrpc_decode($pXml); $aParams = $aParams[0];
    	extract($aParams);
	    $aOutput = array('result' => new xmlrpcval('OK', 'string'));
        return new xmlrpcresp( php_xmlrpc_encode($aOutput) );
    }
    
    function GetUserPaymentLanguage($pXml)
    {
    	$aParams = php_xmlrpc_decode($pXml); $aParams = $aParams[0];
    	extract($aParams);
	    $aOutput = array('result' => new xmlrpcval('OK', 'string'),
	    				 'userLang' => new xmlrpcval('fr', 'string'));
        return new xmlrpcresp( php_xmlrpc_encode($aOutput) );
    }
    
    function GetUserExchangeRate($pXml)
    {
    	$aParams = php_xmlrpc_decode($pXml); $aParams = $aParams[0];
    	extract($aParams);
	    $aOutput = array('result' => new xmlrpcval('OK', 'string'));
        return new xmlrpcresp( php_xmlrpc_encode($aOutput) );
    }
    
    function CurrencySwap($pXml)
    {
    	$aParams = php_xmlrpc_decode($pXml); $aParams = $aParams[0];
    	extract($aParams);
	    $aOutput = array('result' => new xmlrpcval('OK', 'string'));
        return new xmlrpcresp( php_xmlrpc_encode($aOutput) );
    }
    
    function getPageTags($bpUserId, $userId, $pageId)
    {
    	global $partnerId, $projectId;
    	
	    /* prepare the methods parameters */
		$aStructParams = array (
		    'partnerID'     => new xmlrpcval($partnerId, 'int'),
		    'projectID'     => new xmlrpcval($projectId, 'int'),
		    'bpUserID'      => new xmlrpcval($bpUserId, 'int'),
		    'userID'        => new xmlrpcval($userId, 'int'),
		    'pageID'  		=> new xmlrpcval($pageId, 'string'),
		    'userLang'      => new xmlrpcval('fr', 'string'),
		    'userCountry'   => new xmlrpcval('France', 'string'),
		    'authTimestamp' => new xmlrpcval(time(), 'int')
		);
		
		/* create array for the xmlrpymsg including the parameters */
		$aRequestParams = array( php_xmlrpc_encode($aStructParams), "struct");
		
		/* generate XML message for the request */
		$pXmlMsg = new xmlrpcmsg('portal.getPageTags', $aRequestParams);
		
		/* get XML for authentication */
		print $sRequestXml = $pXmlMsg->serialize();
		
		/* generate the authHash with your secretKey and append it to the target URL */
		$sAuthHash   = strtolower(md5($sRequestXml . $sSecretKey));
		$sTargetUrl  = $retreiveUrl . '?authHash=' . $sAuthHash;
		
		/* create xmlrpc client, set encoding and send the request */
		$pClient = new xmlrpc_client($sTargetUrl);
		$pClient->request_charset_encoding = 'UTF-8';
		$pResponse = $pClient->send($pXmlMsg);
    }

}
