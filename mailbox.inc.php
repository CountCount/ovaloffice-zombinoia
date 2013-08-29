<?php
	
	$autocomplete_friends = array();
	$q = ' SELECT name FROM dvoo_citizens WHERE scode <> "" ORDER BY name ASC ';
	$r = $db->query($q);
	foreach ( $r AS $o ) {
		$autocomplete_friends[] = $o['name'];
	}

?>
<div id="mailbox-content">
	<h2><?php print t('YOUR_MAILBOX'); ?></h2>
	<div id="mailbox-messages">
		<h4><?php print t('YOUR_MESSAGES'); ?></h4>
	</div>
	<div id="mailbox-friendlist">
		<h4><?php print t('YOUR_FRIENDS'); ?></h4>
		<form type="POST" id="mailbox-invite-form" onsubmit="inviteFriend();return false;">
			<input type="hidden" name="action" value="invite-request" />
			<input type="hidden" name="scode" value="<?php print $key; ?>" />
			<input type="text" name="invite-friend-name" id="invite-friend-name" value="" />
			<input type="submit" id="invite-friend-submit" value="<?php print t('INVITE'); ?>" />
		</form>
		<div id="mailbox-invite-status"></div>
		<div id="mailbox-friendlist-list">
		</div>
	</div>
	<div id="mailbox-mainscreen">
		
	</div>
	
	<script type="text/javascript">
		$(function() {
			var availableItems = [ <?php print '"'.implode('","', $autocomplete_friends).'"'; ?>	];
			$("#invite-friend-name").autocomplete({
				source: availableItems,
				minLength: 3
			});
			
			var flcl = $.ajax({
				type: 'POST',
				url: 'mailbox.contactlist.php',
				data: 'u=<?php print $key; ?>',
				success: function(msg) {
					$('#mailbox-friendlist-list').html(msg);
				}
			});
			
			var flml = $.ajax({
				type: 'POST',
				url: 'mailbox.message.php',
				data: 'action=list&u=<?php print $key; ?>',
				success: function(msg) {
					$('#mailbox-messages').html(msg);
				}
			});
		});
		
		function inviteFriend() {  
			$("#invite-friend-submit").hide();
			$("#mailbox-invite-status").html("<div class=\'loading\'></div>");
			var ai = $.post(  
				"mailbox.invite.php",  
				$("#mailbox-invite-form").serialize(),  
				function(data){  
					$("#mailbox-invite-status").hide().html(data).slideDown(800).delay(5000).slideUp(400);
					$("#invite-friend-submit").fadeIn(500);
					
					var flcl = $.ajax({
						type: 'POST',
						url: 'mailbox.contactlist.php',
						data: 'u=<?php print $key; ?>',
						success: function(msg) {
							$('#mailbox-friendlist-list').html(msg);
						}
					});
				}  
			);
		}
		
		function inviteAction(a,f) {  
			$("#invite-friend-submit").hide();
			$("#mailbox-invite-status").html("<div class=\'loading\'></div>");
			var ia = $.post(  
				"mailbox.invite.php",  
				"action=invite-"+a+"&u=<?php print $key; ?>&f="+f,  
				function(data){  
					$("#mailbox-invite-status").hide().html(data).slideDown(800).delay(5000).slideUp(400);
					$("#invite-friend-submit").fadeIn(500);
					$("#mailbox-friendlist-list").html("<div class=\'loading\'></div>");
					var flcl = $.ajax({
						type: 'POST',
						url: 'mailbox.contactlist.php',
						data: 'u=<?php print $key; ?>',
						success: function(msg) {
							$('#mailbox-friendlist-list').html(msg);
						}
					});

				} 
			);
		}
		function contactAction(a,f) {  
			var ia = $.post(  
				"mailbox.invite.php",  
				"action=contact-"+a+"&u=<?php print $key; ?>&f="+f,  
				function(data){  
					$("#mailbox-invite-status").hide().html(data).slideDown(800).delay(5000).slideUp(400);
					$("#mailbox-friendlist-list").html("<div class=\'loading\'></div>");
					var flcl = $.ajax({
						type: 'POST',
						url: 'mailbox.contactlist.php',
						data: 'u=<?php print $key; ?>',
						success: function(msg) {
							$('#mailbox-friendlist-list').html(msg);
						}
					});
				}
			);
		}
		function mailAction(a,m) {  
			var ia = $.post(  
				"mailbox.message.php",  
				"action="+a+"&u=<?php print $key; ?>&m="+m,  
				/*function(data){  
					var iov = $('#io').val();
					var flml = $.ajax({
						type: 'POST',
						url: 'mailbox.message.php',
						data: 'action=list&u=<?php print $key; ?>&io='+iov,
						success: function(msg) {
							$('#mailbox-messages').html(msg);
						}
					});
				}*/
				function() {
					$('#msg'+m).slideUp().remove();
				}
			);
		}
		
		function createMsg(f) {
			var nm = $.post(  
				"mailbox.message.php",  
				"action=create&f="+f+"&u=<?php print $key; ?>",  
				function(data){  
					$("#mailbox-mainscreen").html(data);
					$("#msg-subject").focus().select();
				}  
			);
		}
		
		function answerMsg() {
			$("#mailbox-msg-button").remove();
			var am = $.post(  
				"mailbox.message.php",  
				$("#mailbox-message").serialize(),  
				function(data){  
					$("#mailbox-mainscreen").html(data);
					$("#msg-subject").focus().select();
				}  
			);
		}
		
		function sendMsg() {
			$("#mailbox-msg-button").remove();
			var sm = $.post(  
				"mailbox.message.php",  
				$("#mailbox-newmessage").serialize(),  
				function(data){  
					$("#mailbox-mainscreen").html(data);
				}  
			);
		}
		
		function readMsg(m) {
			var nm = $.post(  
				"mailbox.message.php",  
				"action=read&m="+m+"&u=<?php print $key; ?>",  
				function(data){  
					$("#mailbox-mainscreen").html(data);
					
					var iov = $('#io').val();
					var flml = $.ajax({
						type: 'POST',
						url: 'mailbox.message.php',
						data: 'action=list&u=<?php print $key; ?>&io='+iov,
						success: function(msg) {
							$('#mailbox-messages').html(msg);
						}
					});
				}  
			);
		}
		
		function togglePostbox(n) {
			var flml = $.ajax({
				type: 'POST',
				url: 'mailbox.message.php',
				data: 'action=list&u=<?php print $key; ?>&io='+n,
				success: function(msg) {
					$('#mailbox-messages').html(msg);
				}
			});
		}
	</script>
</div>