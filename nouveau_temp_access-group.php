<?php 

set_time_limit(0);
		
	require('beta2_tocsv_numero_ds_all/st.php');
	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 
	$conn->set_charset("utf8");



	$q = $conn->query("SELECT * FROM `nouveau` ORDER BY `numero` ASC");
	$data = [];
	if($q->num_rows>0){

		while ($d = $q->fetch_assoc() ) {
			$data[]=$d;
		}

		$url = 'https://car-value.eu/nouveau_temp_car-value.php';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'data='.json_encode($data));
		$out = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);


		echo $out;

	}











?>