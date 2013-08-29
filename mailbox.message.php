<?php
include_once 'system.php';
$db = new Database();

$a = (string) strip_tags($_POST['action']);
if ( $_POST['u'] == '' && $a == 'list' ) {
	exit;
}

// -- Piwik Tracking API init -- 
require_once "PiwikTracker.php";
PiwikTracker::$URL = 'http://sindevel.com/piwik/';

$piwikTracker = new PiwikTracker( $idSite = 3 );
// You can manually set the visitor details (resolution, time, plugins, etc.) 
// See all other ->set* functions available in the PiwikTracker.php file
$piwikTracker->setURL('http://d2n.sindevel.com/oo/mailbox.message.php');
$piwikTracker->setCustomVariable(1, 'mailboxMessage', $a);

// Sends Tracker request via http
$piwikTracker->doTrackPageView('OO mailbox message');


switch ($a) {
	case 'create':
	{
		$userA = (string) strip_tags($_POST['u']);
		$userB = (string) strip_tags($_POST['f']);

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

		$q = ' SELECT * FROM dvoo_fl_friend WHERE a = '.$aid.' AND b = '.$bid.' ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user ignore
			print t('USER_NOT_YOUR_FRIEND');
			exit;
		}

		// ok, create form
		$o = '<form id="mailbox-newmessage" onsubmit="sendMsg();return false;">';
		$o .= '<input type="hidden" name="action" value="send" />';
		$o .= '<input type="hidden" name="a" value="'.$userA.'" />';
		$o .= '<input type="hidden" name="b" value="'.$bid.'" />';
		$o .= '<table border="0"><tr><td>'.t('FROM').':</td><td><input type="text" disabled="true" value="'.$aname.'" /></td></tr><tr><td>'.t('TO').':</td><td><input type="text" disabled="true" value="'.$bname.'" /></td></tr>';
		$o .= '<tr><td>'.t('SUBJECT').':</td><td><input type="text" name="msg-subject" id="msg-subject" value="Hallo '.$bname.'" /></td></tr>';
		$o .= '<tr><td colspan="2"><textarea name="msg-body" id="msg-body"></textarea></td></tr>';
		$o .= '<tr><td colspan="2"><input id="mailbox-msg-button" type="submit" value="'.t('SEND_MSG').'"></td></tr>';
		$o .= '</form>';
		
		print $o;
		break;
	}
	
	case 'send':
	{
		$userA = (string) strip_tags($_POST['a']);
		$userB = (int) strip_tags($_POST['b']);

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
		
		$q = ' SELECT * FROM dvoo_fl_friend WHERE a = '.$aid.' AND b = '.$bid.' ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user ignore
			print t('USER_NOT_YOUR_FRIEND');
			exit;
		}
		
		$subject = secureText(trim(strip_tags($_POST['msg-subject'])));
		$body = secureText(trim(strip_tags($_POST['msg-body'])));
		// ok, send msg
		$q = ' INSERT INTO dvoo_fl_mailbox VALUES (NULL, '.$aid.', '.$bid.', "'.mysql_real_escape_string($subject).'", "'.mysql_real_escape_string($body).'", '.time().', NULL, NULL) ';
		$db->iquery($q);
		
		print t('MSG_SENT');
		
		break;
	}
	
	case 'answer':
	{
		$userA = (string) strip_tags($_POST['a']);
		$userB = (string) strip_tags($_POST['b']);

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

		$q = ' SELECT * FROM dvoo_fl_friend WHERE a = '.$aid.' AND b = '.$bid.' ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user ignore
			print t('USER_NOT_YOUR_FRIEND');
			exit;
		}

		// ok, create form
		$o = '<form id="mailbox-newmessage" onsubmit="sendMsg();return false;">';
		$o .= '<input type="hidden" name="action" value="send" />';
		$o .= '<input type="hidden" name="a" value="'.$userA.'" />';
		$o .= '<input type="hidden" name="b" value="'.$bid.'" />';
		$o .= '<table border="0"><tr><td>'.t('FROM').':</td><td><input type="text" disabled="true" value="'.$aname.'" /></td></tr><tr><td>'.t('TO').':</td><td><input type="text" disabled="true" value="'.$bname.'" /></td></tr>';
		
		$sub = trim(strip_tags($_POST['msg-subject']));
		if ( substr($sub,0,2) != 'RE' ) {
			$sub = 'RE:'.$sub;
		}
		elseif ( substr($sub,0,3) == 'RE:' ) {
			$sub = 'RE (2):'.substr($sub,3);
		}
		else {
			$sub = preg_replace_callback('/(RE \()([0-9]+)(\):)/', create_function('$match','return $match[1].($match[2]+1).$match[3];'), $sub);
		}
		
		$o .= '<tr><td>'.t('SUBJECT').':</td><td><input type="text" name="msg-subject" id="msg-subject" value="'.$sub.'" /></td></tr>';
		$o .= '<tr><td colspan="2"><textarea name="msg-body" id="msg-body">'."\n\n\n".'--'."\n".t('FROM').' '.$bname.':'."\n".trim(strip_tags($_POST['msg-body'])).'</textarea></td></tr>';
		$o .= '<tr><td colspan="2"><input id="mailbox-msg-button" type="submit" value="'.t('SEND_MSG').'"></td></tr>';
		$o .= '</form>';
		
		print $o;
		break;
	}
	
	case 'list':
	{
		$scode = (string) strip_tags($_POST['u']);
		$userB = (int) strip_tags($_POST['b']);
		$io = (int) strip_tags($_POST['io']);

		$q = ' SELECT * FROM dvoo_citizens WHERE scode = "'.$scode.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('ERROR');
			exit;
		}

		$uid = $r[0]['id'];

		if (!isset($io) || is_null($io) || $io == 0) {
			$q = ' SELECT m.id, m.subject, m.send, m.read, s.name FROM dvoo_fl_mailbox m INNER JOIN dvoo_citizens s ON s.id = m.sender WHERE m.receiver = '.$uid.' AND m.deleted IS NULL ORDER BY m.send DESC ';
		}
		elseif ($io == 1) {
			$q = ' SELECT m.id, m.subject, m.send, m.read, s.name FROM dvoo_fl_mailbox m INNER JOIN dvoo_citizens s ON s.id = m.receiver WHERE m.sender = '.$uid.' ORDER BY m.send DESC '; //AND m.deleted IS NULL
		}
		$r = $db->query($q);
		
		if ( count($r) == 0 ) {
			print t('NO_MSGS');
			exit;
		}
		
		$o = '<div class="io-toggle'.($io != 1 ? ' active' : '').'" onclick="togglePostbox(0);">Inbox</div><div class="io-toggle'.($io == 1 ? ' active' : '').'" onclick="togglePostbox(1);">Outbox</div><table id="message-list" border="0">';
		$o .= '<tr><th>'.($io == 1 ? t('TO') : t('FROM')).'</th><th>'.t('SUBJECT').'</th><th width="120">'.($io == 1 ? t('TIME_SENT') : t('TIME_RECEIVED')).'</th>'.($io != 1 ? '<th width="20">'.t('TRASH').'</th>' : '').'</tr>';
		$i = 0;
		$new = 0;
		foreach ( $r AS $m ) {
			$i++;
			$zebra = ( $i % 2 == 0 ? 'even' : 'odd' );
			$o .= '<tr id="msg'.$m['id'].'" class="msg '.$zebra.($m['read'] > 0 ? '' : ' new').'"><td onclick="readMsg('.$m['id'].');">'.$m['name'].'</td><td onclick="readMsg('.$m['id'].');">'.$m['subject'].'</td><td>'.utf8_encode(strftime(t('DATETIME_MINI'), $m['send'])).'</td>'.($io != 1 ? '<td><a title="'.t('TRASH_MAIL').'" class="mail-action mail-action-delete" href="javascript:void(0);" onclick="mailAction(\'delete\', '.$m['id'].');"></a></td>' : '').'</tr>';
			if ( !($m['read'] > 0) ) { $new = 1; }
		}
		$o .= '</table>';
		
		if ( $io == 1 ) {
			$q = ' SELECT COUNT(*) FROM dvoo_fl_mailbox m WHERE m.receiver = '.$uid.' AND m.read = 1 ';
			$r = $db->query($q);
			$c = $r[0][0];
			if ( $c == 1 ) {
				$new = 1;
			}
			else {
				$new = 0;
			}
		}
		
		$o .= '<input type="hidden" id="io" name="io" value="'.(int) $io.'" />';
		$o .= '<script type="text/javascript">';
		$o .= ( $new == 1 ? '$("#mailbox-toggle").addClass("alert");' : '$("#mailbox-toggle").removeClass("alert");'); 
		$o .= '</script>';
		
		print $o;
		
		break;
	}
	
	case 'read':
	{
		$user = (string) strip_tags($_POST['u']);
		$msg = (string) strip_tags($_POST['m']);

		$q = ' SELECT * FROM dvoo_citizens WHERE scode = "'.$user.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('ERROR');
			exit;
		}

		$uid = $r[0]['id'];	
		$uname = $r[0]['name'];

		$q = ' SELECT * FROM dvoo_fl_mailbox WHERE id = "'.$msg.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('NO_MSGS');
			exit;
		}
		
		$m = $r[0];
		
		if ( $m['receiver'] != $uid && $m['sender'] != $uid ) {
			print t('ERROR');
			exit;
		}
		
		$q = ' SELECT * FROM dvoo_citizens WHERE id = "'.$m['sender'].'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('ERROR');
			exit;
		}

		$sname = $r[0]['name'];	

		$q = ' SELECT * FROM dvoo_citizens WHERE id = "'.$m['receiver'].'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('ERROR');
			exit;
		}

		$rname = $r[0]['name'];	

		if (!($m['read'] > 0) && $uid == $m['receiver']) {
			$q = ' UPDATE dvoo_fl_mailbox SET `read` = '.time().' WHERE id = '.$m['id'].' ';
			$db->iquery($q);
		}

		$o = '';
		// ok, create form
		if ( $uid == $m['receiver'] ) {
			$o .= '<form id="mailbox-message" onsubmit="answerMsg();return false;">';
			$o .= '<input type="hidden" name="action" value="answer" />';
			$o .= '<input type="hidden" name="a" value="'.$user.'" />';
			$o .= '<input type="hidden" name="b" value="'.$m['sender'].'" />';
			$o .= '<input type="hidden" name="msg-subject" value="'.$m['subject'].'" />';
			$o .= '<input type="hidden" name="msg-body" value="'.$m['message'].'" />';
		}
		$o .= '<table border="0"><tr><td>'.t('FROM').':</td><td><input type="text" disabled="true" value="'.$sname.'" /></td></tr><tr><td>'.t('TO').':</td><td><input type="text" disabled="true" value="'.$rname.'" /></td></tr>';
		$o .= '<tr><td>'.t('SUBJECT').':</td><td><input type="text" disabled="true" name="msg-subject" id="msg-subject" value="'.$m['subject'].'" /></td></tr>';
		$o .= '<tr><td colspan="2"><textarea disabled="true" name="msg-body" id="msg-body">'.$m['message'].'</textarea></td></tr>';
		if ( $uid == $m['receiver'] ) {
			$o .= '<tr><td colspan="2"><input id="mailbox-msg-button" type="submit" value="'.t('ANSWER_MSG').'"></td></tr>';
		}
		$o .= '</table>';
		if ( $uid == $m['receiver'] ) {
			$o .= '</form>';
		}
		print $o;
		break;
	}
	
	case 'delete':
	{
		$user = (string) strip_tags($_POST['u']);
		$msg = (int) strip_tags($_POST['m']);

		$q = ' SELECT * FROM dvoo_citizens WHERE scode = "'.$user.'" ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('ERROR');
			exit;
		}

		$uid = $r[0]['id'];	
		$uname = $r[0]['name'];

		$q = ' SELECT * FROM dvoo_fl_mailbox WHERE id = '.$msg.' ';
		$r = $db->query($q);

		if ( count($r[0]) == 0 ) {
			// user not found
			print t('NO_MSGS');
			exit;
		}
		
		$m = $r[0];
		
		if (!($m['deleted'] > 0) && $uid == $m['receiver']) {
			$q = ' UPDATE dvoo_fl_mailbox SET `deleted` = '.time().' WHERE id = '.$m['id'].' ';
			$db->iquery($q);
		}

		$o = '';
		// ok, create form
		if ( $uid == $m['receiver'] ) {
			$o .= '<form id="mailbox-message" onsubmit="answerMsg();return false;">';
			$o .= '<input type="hidden" name="action" value="answer" />';
			$o .= '<input type="hidden" name="a" value="'.$user.'" />';
			$o .= '<input type="hidden" name="b" value="'.$m['sender'].'" />';
			$o .= '<input type="hidden" name="msg-subject" value="'.$m['subject'].'" />';
			$o .= '<input type="hidden" name="msg-body" value="'.$m['message'].'" />';
		}
		$o .= '<table border="0"><tr><td>'.t('FROM').':</td><td><input type="text" disabled="true" value="'.$sname.'" /></td></tr><tr><td>'.t('TO').':</td><td><input type="text" disabled="true" value="'.$rname.'" /></td></tr>';
		$o .= '<tr><td>'.t('SUBJECT').':</td><td><input type="text" disabled="true" name="msg-subject" id="msg-subject" value="'.$m['subject'].'" /></td></tr>';
		$o .= '<tr><td colspan="2"><textarea disabled="true" name="msg-body" id="msg-body">'.$m['message'].'</textarea></td></tr>';
		if ( $uid == $m['receiver'] ) {
			$o .= '<tr><td colspan="2"><input id="mailbox-msg-button" type="submit" value="'.t('ANSWER_MSG').'"></td></tr>';
		}
		$o .= '</table>';
		if ( $uid == $m['receiver'] ) {
			$o .= '</form>';
		}
		print $o;
		break;
	}
}

function secureText($t) {
	$t = str_replace(
		array('&',    '"',     "'",    ),
		array('&amp;','&quot;','&apos;'),
		$t);
	$t = addslashes($t);
	return $t;
}

