<?php
header("Content-type: image/png");

$width = 24;
$height = 24;
$colorset = (isset($_GET['cs']) ? (int) $_GET['cs'] : 2);

$img = imagecreatetruecolor($width,$height);

imagelayereffect($img,IMG_EFFECT_ALPHABLEND);

if ( $colorset == 1 ) {
	$fff = imagecolorallocatealpha($img,255,255,255,0);
	$ink = imagecolorallocate($img,255,0,0);
}
elseif ( $colorset == 2 ) {
	$fff = imagecolorallocatealpha($img,255,0,0,0);
	$ink = imagecolorallocate($img,255,255,0);
}

for ( $c = 0; $c < (int) $_GET['cc']; $c++ ) {
	$cx = mt_rand(3,$width - 3);
	$cy = mt_rand(3,$height - 3);
	
	#imagefilledellipse ( $img , $cx, $cy, 7, 7, $fff );
	#imagefilledellipse ( $img , $cx, $cy, 5, 5, $ink );
	#imagefilledellipse ( $img , $cx, $cy, 3, 3, $ink );
	imagefilledrectangle ( $img , $cx-1, $cy-2, $cx+1, $cy+2, $fff );
	imagefilledrectangle ( $img , $cx-2, $cy-1, $cx+2, $cy+1, $fff );
	imagefilledrectangle ( $img , $cx-1, $cy-1, $cx+1, $cy+1, $ink );
	#imagefilledrectangle ( $img , $cx, $cy, 5, 5, $ink );
}

$trans_index = imagecolorexact($img, 0, 0, 0); 
imagecolortransparent($img, $trans_index); 

imagepng($img);
imagedestroy($img);

?>