<?php
include_once 'system.php';
$db = new Database();

$scode = (string) strip_tags($_POST['u']);

$q = ' SELECT * FROM dvoo_citizens WHERE scode = "'.$scode.'" ';
$r = $db->query($q);

if ( count($r[0]) == 0 || $scode == '' ) {
	// user not found
	print t('ERROR');
	exit;
}

$uid = $r[0]['id'];
$out = '';
$inv = 0;

// invites
$q = ' SELECT a.id, a.name FROM dvoo_citizens a INNER JOIN dvoo_fl_invite i ON a.id = i.a AND i.b = '.$uid.' ORDER BY a.name ';
$r = $db->query($q);

if ( count($r) > 0 ) {
	$out .= '<h5>'.t('INVITES').'</h5>';
	$out .= '<ul id="mailbox-friendlist-list-invites">';
	foreach ( $r AS $p ) {
		$out .= '<li><!--<a title="'.t('IGNORE_REQUEST').'" class="invite-action invite-action-ignore" href="javascript:void(0);" onclick="inviteAction(\'ignore\', '.$p['id'].');"></a>--><a title="'.t('DECLINE_REQUEST').'" class="invite-action invite-action-decline" href="javascript:void(0);" onclick="inviteAction(\'decline\', '.$p['id'].');"></a><a title="'.t('ACCEPT_REQUEST').'" class="invite-action invite-action-accept" href="javascript:void(0);" onclick="inviteAction(\'accept\', '.$p['id'].');"></a>'.$p['name'].'</li>';
	}
	$out .= '</ul>';
	$inv = 1;

}

// pending
$q = ' SELECT b.id, b.name FROM dvoo_citizens b INNER JOIN dvoo_fl_invite i ON b.id = i.b AND i.a = '.$uid.' ORDER BY b.name ';
$r = $db->query($q);

if ( count($r) > 0 ) {
	$out .= '<h5>'.t('PENDING').'</h5>';
	$out .= '<ul id="mailbox-friendlist-list-pending">';
	foreach ( $r AS $p ) {
		$out .= '<li><a title="'.t('WITHDRAW_INVITE').'" class="invite-action invite-action-withdraw" href="javascript:void(0);" onclick="inviteAction(\'withdraw\', '.$p['id'].');"></a>'.$p['name'].'</li>';
	}
	$out .= '</ul>';
}

// active
$q = ' SELECT b.id, b.name FROM dvoo_citizens b INNER JOIN dvoo_fl_friend i ON b.id = i.b AND i.a = '.$uid.' ORDER BY b.name ';
$r = $db->query($q);

if ( count($r) > 0 ) {
	$out .= '<h5>'.t('ACTIVE_CONTACTS').'</h5>';
	$out .= '<ul id="mailbox-friendlist-list-active">';
	foreach ( $r AS $p ) {
		$out .= '<li><a title="'.t('DELETE_CONTACT').'" class="invite-action invite-action-decline" href="javascript:void(0);" onclick="contactAction(\'delete\', '.$p['id'].');"></a><a title="'.t('SEND_MSG').'" href="javascript:void(0);" onclick="createMsg('.$p['id'].');">'.$p['name'].'</a></li>';
	}
	$out .= '</ul>';
}

	$out .= '<script type="text/javascript">';
	$out .= ( $inv == 1 ? '$("#mailbox-toggle").addClass("invite");' : '$("#mailbox-toggle").removeClass("invite");'); 
	$out .= '</script>';

print $out;