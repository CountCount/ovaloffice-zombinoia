<?php
include_once 'system.php';
$db = new Database();

$t = (int) $_POST['t'];
$u = (int) $_POST['u'];
$a = (int) $_POST['a'];

// get key (ajax)
if ( isset($_POST['rbngID']) ) {
	// NEW GROUP
	$keys = explode('.',$_POST['rbngID']);
	$key = $keys[1]; // uid
	$tid = $keys[0]; // tid
	$as = 1;
}
elseif ( isset($_POST['rbogID']) ) {
	// NEW GROUP
	$keys = explode('.',$_POST['rbogID']);
	$key = $keys[1]; // uid
	$tid = $keys[0]; // tid
	$as = 2;
}
elseif ( isset($_POST['groupAction']) ) {
	$a = $_POST['groupAction'];
	$g = $_POST['groupID'];
	$u = $_POST['userID'];
	$as = 3;
}

#var_dump($_POST);

$session = $db->query(' SELECT xml FROM dvoo_rawdata WHERE id = '.$u.' ORDER BY time DESC LIMIT 1 ');
$data = unserialize($session[0]['xml']);


if ( $as == 3 ) {
	$res = $db->query(' SELECT * FROM dvoo_group_citizens WHERE gid = '.$g.' AND uid = '.$u.' ');
	if ( count($res) > 0 ) {
		// valid
		if ( $a == 'leave' ) {
			$db->iquery(' DELETE FROM dvoo_group_citizens WHERE gid = '.$g.' AND uid = '.$u.' ');
		}
		elseif ( $a == 'close' ) {
			$db->iquery(' DELETE FROM dvoo_groups WHERE gid = '.$g.' AND uid = '.$u.' ');
			$db->iquery(' DELETE FROM dvoo_group_citizens WHERE gid = '.$g.' ');
		}
	}
}
else {

	if ( $a == 1 ) {
		$name = $_POST['newgroup_name'];
		$route = $_POST['newgroup_route'];
		$persistent = ($_POST['newgroup_persistence'] == 'persistent' ? 1 : 0);
		
		// save
		$db->iquery(' INSERT INTO dvoo_groups VALUES ( 
			NULL,
			'.$t.',
			"'.$u.'", 
			"'.$name.'", 
			"'.$route.'",
			'.$persistent.',
			'.time().'
			) ON DUPLICATE KEY UPDATE  
			name = "'.$name.'",
			route = "'.$route.'",
			persistent = '.$persistent.',
			stamp = '.time()
		);
	
	}
	elseif ( $a == 2 ) {
		$gid = $_POST['owngroup_id'];
		$names = $_POST['member_'.$gid];
		
		#var_dump($_POST);
		$db->iquery('DELETE FROM dvoo_group_citizens WHERE gid = '. $gid );
		// save
		$db->iquery(' INSERT INTO dvoo_group_citizens VALUES ( 
				'.$gid.',
				'.$u.')'
			);
		foreach ( $names AS $id ) {
			$db->iquery(' INSERT INTO dvoo_group_citizens VALUES ( 
				'.$gid.',
				'.$id.')'
			);
		}
	}
}
	
	$res = $db->query('SELECT * FROM dvoo_groups WHERE uid = '.$u);
	if ( $res ) {
		$group = $res[0];
	}
	else {
		$group = array();
	}
	
	print '<script type="text/javascript">loadTabContent('.$u.');</script>';