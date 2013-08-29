<?php
include_once 'system.php';
$db = new Database();

// get version number
$v = (int) $_POST['v'];
// get process number
$p = (int) $_POST['p'];
if ( $p == 2 ) {
	$k = htmlspecialchars(strip_tags($_POST['k']));
	$u = (int) $_POST['u'];
}
elseif ( $p == 1 ) {
	$k = htmlspecialchars(strip_tags($_POST['k']));
	$n = htmlspecialchars(strip_tags($_POST['n']));
}
else {
	// no data send -> start
	print '<script type="text/javascript">
			$("#link_auswaertigesamt").remove();
			$("#auswaertigesamt").remove();
		</script>';
	exit;
}
?>
<?php 
if ( (date('z') >= 111 && date('z') <= 114) ) {
	if ( mt_rand(1,1000) > 900 ) {
		print '<div class="easteregg ee'.mt_rand(10000,99999).' egg'.mt_rand(1,4).' rot'.mt_rand(1,5).'" style="top:'.mt_rand(0,900).'px;left:'.mt_rand(0,900).'px;opacity:'.(mt_rand(2,8)/10).';"></div>';
	}
}
?>
<p></p>
<div id="fb_form_wrapper">
<form id="fb_form" method="POST" onsubmit="submitFBform();return false;">
<strong>Respuesta:</strong><input id="fb-button" type="submit" value="<?php print 'Enviar'; ?>" /><br/><textarea id="fb" name="fb" /></textarea>
<input type="hidden" value="<?php print $u; ?>" name="u" />
</form>
</div><p class="hideme" style="font-size:0.875em;color:#aac;padding-left:1em;text-align:justify;"><br><br><br><strong>[card]</strong>&nbsp;Draw a random card from <em>complete</em> deck<br><strong>[ht]</strong>&nbsp;Throw a coin<br><strong>[dx]</strong>&nbsp;Throw a dice with <em>x</em> sides, i.e. [d6] for a six-sided one<br><strong>[cocktail]</strong>&nbsp;Have a drink!<br>Some smileys supported: <strong>:) ;) :| :( :D :O</strong></p>

<h3 id="fb_pinheader"><a href="javascript:void(0);" onclick="loadFBlist();this.blur();" style="display:block;float:right;margin-left:12px;font-size:.875em;font-weight:bold;text-decoration:none;color:#962;">Refrescar</a><input type="text" size="4" value="25" id="postcount" maxlength="3"> última respuesta</h3>
<div id="fb_pinboard"><div class="loading"></div></div>

<script type="text/javascript">				
				function submitFBform() {  
					$('#fb-button').hide();
					var fb = $.post(  
						"wb.form.ajax.php",  
						$("#fb_form").serialize(),  
						function(data){ 
							//alert (data);
							$('#fb').val('');
							$('#fb_pinboard').html(data);
							$('#fb-button').fadeIn(500);
						}  
					);
				}
				
				var oo_fb = $.ajax({
						type: 'POST',
						url: 'wb.list.ajax.php',
						data: 'u=<?php print $u; ?>&p='+$('#postcount').val(),
						success: function(msg) {
							$('#fb_pinboard').html(msg);
						}
					});
					
				function loadFBlist() {
          $('#fb_pinboard').html('<div class="loading"></div>');  
					var oo_fb = $.ajax({
						type: 'POST',
						url: 'wb.list.ajax.php',
						data: 'u=<?php print $u; ?>&p='+$('#postcount').val(),
						success: function(msg) {
							$('#fb_pinboard').html(msg);
						}
					});
				}
				
				// loadFBlist();
					
		jQuery(function($) {
			$("a[rel^='lightbox']").slimbox({/* Put custom options here */}, null, function(el) {
					return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
			});
    });
	</script>
<?php
