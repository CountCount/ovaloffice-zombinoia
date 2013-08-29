<?php
include_once 'system.php';
$db = new Database();

$a = (string) strip_tags($_POST['action']);

switch ($a) {
	case 'invite-request':
	{
		$userA = (string) strip_tags($_POST['scode']);
		$userB = (string) strip_tags($_POST['invite-friend-name']);

		$q = ' SELECT * FROM dvoo_citizens WHERE scode = "'.$userA.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('ERROR');
			exit;
		}

		$aid = $r[0]['id'];

		$q = ' SELECT * FROM dvoo_citizens WHERE name = "'.$userB.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('USER_UNKNOWN');
			exit;
		}

		$bid = $r[0]['id'];
		$scode = $r[0]['scode'];
		$bname = $r[0]['name'];
		
		if ( $aid == $bid ) {
			print t('SELFINVITE');
			exit;
		}

		if ( $scode == '' ) {
			// not yet logged in
			print t('USER_NOT_YET_LOGGED_IN');
			exit;
		}

		$q = ' SELECT * FROM dvoo_fl_ignore WHERE a = '.$bid.' AND b = '.$aid.' ';
		$r = $db->query($q);

		if ( count($r[0]) > 0 ) {
			// user ignore
			print t('USER_IGNORES_YOU');
			exit;
		}

		$q = ' SELECT * FROM dvoo_fl_invite WHERE a = '.$bid.' AND b = '.$aid.' ';
		$r = $db->query($q);

		if ( count($r[0]) > 0 ) {
			// user already invites you
			print t('USER_INVITED_YOU');
			exit;
		}

		$q = ' SELECT * FROM dvoo_fl_invite WHERE a = '.$aid.' AND b = '.$bid.' ';
		$r = $db->query($q);

		if ( count($r[0]) > 0 ) {
			// user already invited
			print t('USER_ALREADY_INVITED');
			exit;
		}

		$q = ' INSERT INTO dvoo_fl_invite VALUES ('.$aid.', '.$bid.', '.time().') ';
		$db->iquery($q);

		print t('USER_INVITED', array('%s' => $bname));
		break;
	}
	
	case 'invite-accept':
	{
		$userA = (string) strip_tags($_POST['u']);
		$userB = (int) strip_tags($_POST['f']);

		$q = ' SELECT * FROM dvoo_citizens WHERE scode = "'.$userA.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('ERROR');
			exit;
		}

		$aid = $r[0]['id'];
		$aname = $r[0]['name'];

		$q = ' SELECT * FROM dvoo_citizens WHERE id = "'.$userB.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('USER_UNKNOWN');
			exit;
		}
		
		$bid = $r[0]['id'];
		$bname = $r[0]['name'];
		
		$q = ' INSERT INTO dvoo_fl_friend VALUES ('.$aid.', '.$bid.'), ('.$bid.', '.$aid.') ';
		$db->iquery($q);
		$q = ' DELETE FROM dvoo_fl_invite WHERE a = '.$bid.' AND b = '.$aid.' ';
		$db->iquery($q);
		print t('USER_ACCEPTED', array('%s' => $bname));
		
		break;
	}
	
	case 'invite-decline':
	{
		$userA = (string) strip_tags($_POST['u']);
		$userB = (int) strip_tags($_POST['f']);

		$q = ' SELECT * FROM dvoo_citizens WHERE scode = "'.$userA.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('ERROR');
			exit;
		}

		$aid = $r[0]['id'];
		$aname = $r[0]['name'];

		$q = ' SELECT * FROM dvoo_citizens WHERE id = "'.$userB.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('USER_UNKNOWN');
			exit;
		}
		
		$bid = $r[0]['id'];
		$bname = $r[0]['name'];
		
		#$q = ' INSERT INTO dvoo_fl_friend VALUES ('.$aid.', '.$bid.'), ('.$bid.', '.$aid.') ';
		#$db->iquery($q);
		$q = ' DELETE FROM dvoo_fl_invite WHERE a = '.$bid.' AND b = '.$aid.' ';
		$db->iquery($q);
		print t('USER_DECLINED', array('%s' => $bname));
		
		break;
	}
	
	case 'invite-withdraw':
	{
		$userA = (string) strip_tags($_POST['u']);
		$userB = (int) strip_tags($_POST['f']);

		$q = ' SELECT * FROM dvoo_citizens WHERE scode = "'.$userA.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('ERROR');
			exit;
		}

		$aid = $r[0]['id'];
		$aname = $r[0]['name'];

		$q = ' SELECT * FROM dvoo_citizens WHERE id = "'.$userB.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('USER_UNKNOWN');
			exit;
		}
		
		$bid = $r[0]['id'];
		$bname = $r[0]['name'];
		
		#$q = ' INSERT INTO dvoo_fl_friend VALUES ('.$aid.', '.$bid.'), ('.$bid.', '.$aid.') ';
		#$db->iquery($q);
		$q = ' DELETE FROM dvoo_fl_invite WHERE a = '.$aid.' AND b = '.$bid.' ';
		$db->iquery($q);
		print t('USER_WITHDRAWN', array('%s' => $bname));
		
		break;
	}
	
	case 'contact-delete':
	{
		$userA = (string) strip_tags($_POST['u']);
		$userB = (int) strip_tags($_POST['f']);

		$q = ' SELECT * FROM dvoo_citizens WHERE scode = "'.$userA.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('ERROR');
			exit;
		}

		$aid = $r[0]['id'];
		$aname = $r[0]['name'];

		$q = ' SELECT * FROM dvoo_citizens WHERE id = "'.$userB.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('USER_UNKNOWN');
			exit;
		}
		
		$bid = $r[0]['id'];
		$bname = $r[0]['name'];
		
		#$q = ' INSERT INTO dvoo_fl_friend VALUES ('.$aid.', '.$bid.'), ('.$bid.', '.$aid.') ';
		#$db->iquery($q);
		$q = ' DELETE FROM dvoo_fl_friend WHERE (a = '.$bid.' AND b = '.$aid.') OR (a = '.$aid.' AND b = '.$bid.') ';
		$db->iquery($q);
		print t('USER_DELETED', array('%s' => $bname));
		
		break;
	}	
}

