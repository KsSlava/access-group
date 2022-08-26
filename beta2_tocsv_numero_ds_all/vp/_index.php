<?php 



	$dt          = 'voiture_passage';
	$dt_numero   = 'numero';
	$dt_vps      = 'virtual_bourse';
	$dt_DS12	 = 'DS12';
	$dt_vpbrs    = 'voiture_passage_bourse';

	$dir         = '../tmp/';
	$csv         = '../tmp/out_numero.csv'; 
	$scanid      = 'out_numero_';
	$rows        = 0; 
	$updatedRows = 0;
	$deletedRows = 0;

	$updatedDS12 = 0;
	$updatedVPSB = 0;

	$csvRows     = 0;

	$outUPD='';
	$outDEL='';

	$annee_modele = '';
	require('../lib/email.php');
require('../st.php');



	$deleteType = ['000923', '000904', '001032', '777002', '777005'];



	//generate outfile 
	$scandir = scandir($dir);
	array_shift($scandir);
	array_shift($scandir);

	$f = ''; 

	 foreach ($scandir as $k => $v) {

		if(preg_match('/('.$scanid.')[0-9]{1,2}/', $v) ){

	     $f .= file_get_contents($dir.$v);

	 	}
	 }

	if(strlen($f)>5){

		file_put_contents($csv, $f);
	}
	//end generate outfile 







$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$conn->set_charset("utf8");






	if(file_exists($csv)){

		if (($handle = fopen($csv, "r")) !== FALSE) {

		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

				$numero  = trim($data[0]);
				$couleur = trim($data[1]);
				$int     = trim($data[2]);
				$options = trim(addslashes($data[3]));
				$compteDemandeur = trim($data[4]);
				$annee_modele = trim($data[5]); 

				$colums ='';



				if(strlen($couleur)>2){

					$colums = "`couleur`='$couleur'";
				}




				if(strlen($int)>2){

					$colums .= ", `int`='$int'";
				}





				if($options !=="---"){

						$colums .= ", `options`='$options'";
				}




				
				 if(strlen($annee_modele)>3){

				 	$colums .= ", `annee_modele`='$annee_modele'";
				 }

		        
		        $csvRows++;


		        //Delete/update car from NUMERO (by compteDemandeur)
				if(!in_array($compteDemandeur, $deleteType)){

					$conn->query("DELETE FROM $dt_numero WHERE `numero` = '$numero' ");
							    
				}else{

					$conn->query("UPDATE $dt_numero SET `compte`='$compteDemandeur' WHERE `numero` = '$numero'");



                    $chkDS12 = $conn->query("SELECT `numero` FROM $dt_DS12 WHERE `numero` = '$numero'");
                    if($chkDS12->num_rows > 0) {$updatedDS12++;}
					$conn->query("UPDATE $dt_DS12 SET $colums WHERE `numero` = '$numero'");


                    $chkVPSBRS = $conn->query("SELECT `numero` FROM $dt_vpbrs WHERE `numero` = '$numero'");
                    if($chkVPSBRS->num_rows > 0) {$updatedVPSB++;}
					$conn->query("UPDATE $dt_vpbrs SET $colums WHERE `numero` = '$numero'");


				}

				//Delete/update car from VOITURE
				$query = "SELECT `numero`, `couleur`, `int`, `options` FROM  $dt WHERE `numero` = $numero LIMIT 1 ";
		        if($result = $conn->query($query)){

		            $q = $result->fetch_assoc(); 

					if($q){


						$n = trim($q['numero']); 
						$c = $q['couleur'];
						$i = $q['int'];
						$o = $q['options'];

						$rows++;

						if($conn->query("UPDATE $dt SET $colums WHERE `numero` = '$numero'")){
						  

							$outUPD  .= '<tr>';

								$outUPD .= '<td>'.$n.'</td>';
								$outUPD .= '<td>'.$c.'</td>';
								$outUPD .= '<td>'.$i.'</td>';
								$outUPD .= '<td>'.$o.'</td>';


								$outUPD .= '<td>></td>';

							    $outUPD .= '<td>'.$numero.'</td>';
								$outUPD .= '<td>'.$couleur.'</td>';
								$outUPD .= '<td>'.$int.'</td>';
								$outUPD .= '<td>'.$options.'</td>';
								$outUPD .= '<td>'.$compteDemandeur.'</td>';
								

							$outUPD  .= '</tr>'; 

							//After update we check type of compte. 
							//if FALSE - chek if numero is exists in table voiture_poubelle_bourses. 
							//If FALSE - copy row, send numero to email
							if(!in_array($compteDemandeur, $deleteType)){ 


								$q2 = "SELECT `numero` FROM  $dt_vps WHERE `numero` = $numero LIMIT 1 ";
								if( !$result2 = $conn->query($q2)->fetch_assoc() ){

									$in2 = "INSERT INTO `voiture_passage_bourse`(`numero`, `serie`, `modele`, `nbre_de_portes`, `cylindree`, `cv`, `essence`, `couleur`, `int`, `options`, `chassis`, `localisation`, `sem`, `prime`, `remarque`, `concessionnaire`, `cle`, `plaque`, `km`, `immatriculation`, `libre`, `annee_modele`) SELECT `numero`, `serie`, `modele`, `nbre_de_portes`, `cylindree`, `cv`, `essence`, `couleur`, `int`, `options`, `chassis`, `localisation`, `sem`, `prime`, `remarque`, `concessionnaire`, `cle`, `plaque`, `km`, `immatriculation`, `libre`, `annee_modele` FROM $dt where `numero` = $numero"; 

									$conn->query($in2);

									email($numero, $benefitsiar); 

									$deleteTag = "delete & email"; 

									
								}else{

									$deleteTag = "delete"; 

								}

								$conn->query("DELETE FROM $dt WHERE `numero` = '$numero' ");


								$outDEL  .= '<tr>';

								$outDEL .= '<td>'.$numero.'</td>';
								$outDEL .= '<td></td>';
								$outDEL .= '<td></td>';
								$outDEL .= '<td></td>';

								$outDEL .= '<td>></td>';

								$outDEL .= '<td></td>';
								$outDEL .= '<td></td>';
								$outDEL .= '<td></td>';
								$outDEL .= '<td><span style="color:#c90202">'.$deleteTag.'</span></td>';
								$outDEL .= '<td>'.$compteDemandeur.'</td>'; 
							
								$outDEL  .= '</tr>'; 


								$deletedRows++; 

							}

							$updatedRows ++; 

						}else{

							echo $conn->error;
						}

					}else{
						//not found cars

						$outNotFound  .= '<tr>';

							$outNotFound .= '<td>'.$numero.'</td>';
							$outNotFound .= '<td></td>';
							$outNotFound .= '<td></td>';
							$outNotFound .= '<td></td>';


							$outNotFound .= '<td>></td>';

						    $outNotFound .= '<td></td>';
							$outNotFound .= '<td></td>';
							$outNotFound .= '<td></td>';
							$outNotFound .= '<td>not found in DB</td>';
							$outNotFound .= '<td>'.$compteDemandeur.'</td>';
							

						$outNotFound  .= '</tr>'; 
					}
		        }


		    }

	    	$message = ':::cars in csv:'.$csvRows.':::<br>'.$dt.' upd:'.$updatedRows.'<br>'.$dt_DS12.' upd:'.$updatedDS12.' <br>'.$dt_vpbrs.' upd:'.$updatedVPSB.'<br><br>'; 



				if($_POST['do']=='update') {

					echo $message; 

				}else{
			   
			        echo $message.'<br/>';
				    echo '<table border="1px" cellspacing="0" style="font-size:12px;">
				          <tr>
				           <td>#</td>
				           <td>couleur</td>
				           <td>int</td>
				           <td>options</td>

							<td>></td>

				 		   <td></td>
				           <td></td>
				           <td></td>
				           <td></td>
						   <td></td>
						   <td></td>
						   
				          </tr>
				    '.$outUPD.$outDEL.$outNotFound.'</table>';

				    

				}

		}
	}else{


		if($_POST['do']=='update') {

			echo json_encode("Missing csv file. Please make export to csv"); 
		}else{
			
            echo "Missing csv file. Please make export to csv"; 
        }
        
	}

	?>