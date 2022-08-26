<?php
function email($n="0"){
	
$to = "info@nh3.be, serge.simonis@bradis.com"; 
$msg = "Cette voiture est dans le stock virtuel de bourse, si elle n'a pas de CCF, il faudra la rentrer en manuel";
$subject = 'Numero:'.$n.'  Cette voiture est dans le stock virtuel de bourse';
$msg = wordwrap($msg,70);
mail($to, $subject, $msg);
}
?>