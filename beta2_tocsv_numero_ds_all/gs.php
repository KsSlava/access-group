<?php 

$key = 'Slava,infomixks@gmail.com';


	$str = file_get_contents('http://vnvd-opel.be/beta_5/gs.php', false,  stream_context_create(['ssl'  => ['verify_peer' => false, 'verify_peer_name' => false, ] ])); 






		if(!function_exists('hash_equals'))
		{
		function hash_equals($str1, $str2)
		{
		if(strlen($str1) != strlen($str2))
		{
		    return false;
		}
		else
		{
		    $res = $str1 ^ $str2;
		    $ret = 0;
		    for($i = strlen($res) - 1; $i >= 0; $i--)
		    {
		        $ret |= ord($res[$i]);
		    }
		    return !$ret;
		}
		}
		}

		// Decrypt
		$c = base64_decode($str);
		$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len=32);
		$ciphertext_raw = substr($c, $ivlen+$sha2len);
		$plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, 1, $iv);
		$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
		if (hash_equals($hmac, $calcmac))
		{
		     $plaintext;
	            

	         


		    $upArr = explode(";", $plaintext); 
	        $accounts =array();
		    foreach ($upArr as $up) {

		    	$account = explode("|", $up);

		    	if(strlen($account[0])>2){

		    	$accounts[] =array($account[0], $account[1], $account[2]); 
	            }

		    	
		    }
		   //print_r($accounts);
		}



?>