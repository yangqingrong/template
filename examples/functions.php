<?php


 
function w_strlen($string){
	return strlen( $string );
}

function w_substr($s,$o,$len ){
	return substr($s,$o,$len );
}
function w_date( $a,$b=null ){
	return date( $a,$b );
}