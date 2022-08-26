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
	$compareRows = 0;

	$outUPD='';
	$outDEL='';

	$annee_modele = '';
	require('../lib/email.php');
require('../st.php');



$deleteType = ['BE01740','BE0358'];
$cons = ['BE01740'=>'BE01740','BE0358'=>'BE0358'];


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

		    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {

				$numero  = trim($data[0]);
				$couleur = trim($data[1]);
				$int     = trim($data[2]);
				$options = trim(addslashes($data[3]));
				$compteDemandeur = trim($data[4]);
				$annee_modele = trim($data[5]); 
				$chassis        = trim($data[9]);
				$prime        = '';
				$dateDeLivrasionAnnoncee = trim($data[11]);
				$dateDeFacture = trim($data[12]);
				$localisation =  trim($data[13]);	
				$benefitsiar = trim($data[14]); 
				$compare = trim($data[15]);
				$stock = trim($data[16]);

// trim($data[10]);
				$colums ='';



				$colums = "`numero`='$numero'";
				
				if(strlen($couleur)>3 and $couleur!=="---"){

					$colums .= ", `couleur`='$couleur'";
				}





				if(strlen($int)>2){

					$colums .= ", `int`='$int'";
				}





				if($options !=="---"){

						$colums .= ", `options`='$options'";
				}


				if(strlen($chassis)>3){

						$colums .= ", `chassis`='$chassis'";
				}
				
				if(strlen($prime)>3){

						$colums .= ", `prime`='$prime'";
				}		

				
				if(strlen($annee_modele)>2){

				 	$colums .= ", `annee_modele`='$annee_modele'";
				 }

				//concessionnaire
                preg_match('/[0-9]{6}/', $benefitsiar, $m);

                if(count($m)>0){

	                if(array_key_exists($m[0], $cons)){

		               $concessionnaire = $cons[$m[0]];

		               $colums .= ", `concessionnaire`='$concessionnaire'";
						
	                }

                }

               //end concessionnaire


				if($compare=="1"){
				    if(!is_object( $conn->query("SELECT * FROM `compare` LIMIT 1") )){
				    	$conn->query("CREATE TABLE compare LIKE voiture");
				    }
				        $q = $conn->query("SELECT * FROM $dt WHERE `numero` LIKE '%$numero%' LIMIT 1");
					
							
							
				            if($q->num_rows>0){
				                $values = 'NULL'; 
		          		        $l=0;
				                foreach($q->fetch_row() as $v){
				                	if($l>0){
				                    	$values .=  ', "'.$v.'"';
				                	}
				                	$l++;
				                }
								
				                    if($conn->query("INSERT INTO `compare` VALUES ($values)")){
										echo "test"; 
				                       $conn->query("DELETE FROM $dt WHERE `numero` LIKE '%$numero%' LIMIT 1");
				                            $compareRows++;
				                            $compare = '';
				                    }        

				            }
				    
				}else{

        
			        $csvRows++;
					
					

						//insert/update "date_facturation"
					if(strlen($dateDeFacture)>5){
						
						$q = "SELECT * FROM date_facturation WHERE `id_voiture` LIKE '%$numero%' LIMIT 1 ";
						
						
						$result = $conn->query($q);
						
						$jour  = date('d', strtotime($dateDeFacture));
						$mois = date('m', strtotime($dateDeFacture));
						$annee =  date('Y', strtotime($dateDeFacture));
						
						
						
								
						if($result->num_rows > 0){
							
							$conn->query("UPDATE `date_facturation` 
				
					    	SET `jour`='$jour', `mois`='$mois', `annee`='$annee', `date` = '$dateDeFacture' 
				
				            WHERE `id_voiture` LIKE '%$numero%'"); 
				
						}else{
							
							$conn->query("INSERT INTO `date_facturation` 
						   VALUES (NULL, '$numero', '$jour', '$mois', '$annee', '$dateDeFacture', '', '', '', '') ");
						
						}
					
					}
					//end insert/update "date_facturation"	
					
			        //Delete/update car from NUMERO (by compteDemandeur)
					if(!in_array($compteDemandeur, $deleteType)){

						$conn->query("DELETE FROM $dt_numero WHERE `numero` = '$numero' ");
								    
					}else{

						$conn->query("UPDATE $dt_numero SET `compte`='$compteDemandeur' WHERE `numero` = '$numero'");

	  				}

					//Delete/update car from VOITURE
					$query = "SELECT * FROM  $dt WHERE `numero` = '$numero' LIMIT 1 ";
			        if($result = $conn->query($query)){

			            $q = $result->fetch_assoc(); 

						if($q){


							$n = trim($q['numero']); 
							$c = $q['couleur'];
							$i = $q['int'];
							$o = $q['options'];
		                    $sem = $q['sem']; 
							$l = $q['localisation'];
							$remarque = $q['remarque'];

							//Stock 
							if(strlen($stock)>3 and strlen(trim($remarque))<1 ){
								$colums .= ", `remarque`='$stock'";
							}
							
							if(trim($l)=="" and strlen($localisation)>5 and strlen($chassis)>5){
								
								$colums .= ", `localisation`='$localisation'";
							
							}
						//sem 
							if(strlen($chassis) > 10){
				
								//if date is not strange (ex 11-05-2099)
								
								//if(date('Y', strtotime($dateDeFacture)) < (date('Y')+10) ){
											
									$colums .= ", `sem`='DISPO'";

								//else{
								
						
								//    $colums .= ", `sem`='PROD'";
								
								//}



							}elseif(strlen($dateDeLivrasionAnnoncee)>5){
							
								if(date('Y', strtotime($dateDeLivrasionAnnoncee)) < (date('Y')+10)){
									

									$semdd = date('W', strtotime($dateDeLivrasionAnnoncee));
															
									$colums .= ", `sem`='$semdd'";
									
								}else{
								
									$colums .= ", `sem`='PROD'";
								
								}
							
							
							}
						
							//end sem 
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


									$q2 = "SELECT `numero` FROM  $dt_vps WHERE `numero` = '$numero' LIMIT 1 ";
									if( !$result2 = $conn->query($q2)->fetch_assoc() ){

										$in2 = "INSERT INTO `voiture_passage_bourse`(`numero`, `serie`, `modele`, `nbre_de_portes`, `cylindree`, `cv`, `essence`, `couleur`, `int`, `options`, `chassis`, `localisation`, `sem`, `prime`, `remarque`, `concessionnaire`, `cle`, `plaque`, `km`, `immatriculation`, `libre`, `annee_modele`) SELECT `numero`, `serie`, `modele`, `nbre_de_portes`, `cylindree`, `cv`, `essence`, `couleur`, `int`, `options`, `chassis`, `localisation`, `sem`, `prime`, `remarque`, `concessionnaire`, `cle`, `plaque`, `km`, `immatriculation`, `libre`, `annee_modele` FROM $dt where `numero` = $numero"; 

										$conn->query($in2);

										email($numero, $benefitsiar); 

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
										$deleteTag = "delete & email"; 

										
									}else{

										$deleteTag = "delete"; 

									}



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


		    }

		    	$message = '<br>'.$dt.' upd:'.$updatedRows.' compare: '.$compareRows.'<br>'; 

		    			






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

			echo json_encode("No cars for updating."); 
		}else{
			
            echo "No cars for updating."; 
        }
        
	}

	?>