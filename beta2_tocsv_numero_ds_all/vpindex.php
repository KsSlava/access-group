<?php
 //ini_set('error_reporting', E_ALL);
 //ini_set('display_errors', 1);
 //ini_set('display_startup_errors', 1);
set_time_limit(0);

require('vendor/autoload.php');
require('lib/shd.php');
require('lib/encoding.php');
require('st.php');
require('gs.php');

use \ForceUTF8\Encoding; 



$hostStart = 'https://cronos.opel.com/dvnWeb/home.action'; 
$host = 'https://cronos.opel.com/';

$numeric     ='';
$titre       =0;
$habillages1 ='';
$habillages2 ='';
$options     ='---';
$compteDemandeur = '';
$benefitsiar = '';
$franchisesDagiosFacture = "";
$dateDeReglement = "";
$prime ="---";
$vencha = "---";
$dateDeLivrasionAnnoncee ="";
$dateDeFacture = "";
//Parc Filiale/libelle 
$localisation = "";
$compare='';
$stock='';
$observations = "";
$fco = '';
$codeVendeur = "";
$vpvu = "";
$dateSold="";



$totalId     = 0; 
$updateId    = 0; 
$notfoundId  = 0; 




$ids = array();








if($_POST['do']=='getList'){

	if($_POST['source']==0){
	$db = 'voiture'; $db2 = 'voiture_passage'; $db3 = 'voiture_passage_bourse'; $db4 = 'DS12';

	}


	$conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 


    $td = date('Y-m-d'); 

		$q = $conn->query("SELECT `numero`  FROM `nouveau`
		
	
	");
		//$testCount=0;

		while($row = $q->fetch_assoc()){
			$id = trim($row['numero']);
					if(strlen($id)<=6 & strlen($id)>2){
						$ids[] = $id;
					}
    	}
	
		
		//get cars from txt
		
	
		// $cars  = file_get_contents("tmp/cars.txt");

		
		// $carsList = explode("\n", $cars); 
		// foreach($carsList as $k => $v) {
		
		// 	$ids[] = trim($v);
		
		// }
	
				
		$ids = array_unique($ids);

	
		foreach($ids as $k=>$v){ $idsUnq[] =$v;}
		$conn->close();

		sort($idsUnq);
		$lk = count($idsUnq) - 1;
	  	$st = $idsUnq[$lk];	 
		for($i = $st; $i<=$st+2500; $i++){
			$idsUnq[] = $i;
		}			

	
	
		echo json_encode(['ids'=>$idsUnq, 'accMax'=>count($accounts) ]);

}




if($_POST['do']=='grabb'){
	
	  
 $a=$_POST['a'];
	//get log/pwd 
	//require('tmp/credential.php');
	

	
	

	$USR =$accounts[0][0]; 
	$PWD =$accounts[0][1]; 
	

	

	


	


	if($_POST['source']==0){
		
		$fCSV = 'tmp/out_numero_'.$a.'.csv';
		$fNotFoundIdsCSV = 'tmp/out_no_numero_'.$a.'.csv';
	}


	if($_POST['k']==0){

		if(file_exists("tmp/all.csv")){
			unlink("tmp/all.csv");
		}

		
		if(file_exists("tmp/out_numero.csv")){
		
			unlink("tmp/out_numero.csv");
		
		}
		
		 
		
	     if(file_exists($fCSV)){
	     	unlink($fCSV);
	     }
		
		
	     if(file_exists($fNotFoundIdsCSV)){
	     	unlink($fNotFoundIdsCSV); 
	     }
	}


   $client = new GuzzleHttp\Client(['cookies' => true, 'exceptions' => false]);
    
    $numero = $_POST['id'];

	  $res = $client->request('GET', $host, [
	    'auth' => [$USR, $PWD],
	    'allow_redirects' => [
	    'max'             => 10,        
	    'referer'         => true,    
	    'connect_timeout' => 3.14,
	    'track_redirects' => true,
	    ]
	  ]);
	
	


	

	if($res->getStatusCode()==200) {

		
		$query = ['from'=>'home', 'orderNumber'=>$numero, 'brandCountry'=>'38']; 
           

	    $res2 = $client->request('GET', $host."/dvnWeb/networkDisplayOrder.action", [
		  'query'=> $query,
	      'auth' => [$USR, $PWD],
	      'allow_redirects' => [
	      'max'             => 10,        // allow at most 10 redirects.
	      'referer'         => true,      // add a Referer header
	      'connect_timeout' => 3.14,
	      'track_redirects' => true
	      ]
	    ]);

		


		if($res2->getStatusCode()==200) {

			$file = $res2->getBody(); 

	        //VP / VU
	        preg_match('/vp\s+[\/]\s+vu\s+[:].*?(<\/td>.*?<\/td>)/si', $file, $m); 
	        if(count($m)==2){
	          $vpvu = trim( strip_tags($m[1]) );
	        }    			


	        //FCO
	        preg_match('/customerOrderNumberLink\'>(.*?)<\/a>/si', $file, $m);
	        if(count($m)==2){
	          $str = trim($m[1]);
	          if(strlen($str)>4){
	            $fco = $str;
	          }
	        }
			
			//Observations
			preg_match('/observations\s+[:].*?(<\/td>.*?<\/td>)/si', $file, $m); 
			if(count($m)==2){
			  $observations = trim( strip_tags($m[1]) );
			}
			
			//Stock
			preg_match('/stock\s+[:].*?(<\/td>.*?<\/td>)/si', $file, $m); 
			if(count($m)==2){
			  $stock = trim( strip_tags($m[1]) );
			}			

			//compare
			preg_match('/orderNumberLink[^>]+.css\([^>]+line-through.*?\)\;/si', $file, $cro);
			if(count($cro)==1){
			$compare = '1'; 
			}

			$html = str_get_html($file);
			
    		// facturation 
		    $fct = $html->getElementById('fsInvoicing');

		    if($fct) {

		        foreach($fct->find('tr') as $k => $elem){

					$fct_str = trim($elem->plaintext);
					$fct_str = preg_replace("/\s{2,}/sim"," ", $fct_str);
					
             //Date de Facture
              if(preg_match('/Date\sde\sfacture/', $fct_str)){

                 $fct_o = str_get_html($elem->innertext);
                 $dateDeFacture = $fct_o->find('td', 1)->plaintext;
                 $dateDeFacture =  preg_replace("/\s{2,}/si"," ", $dateDeFacture);
                 $dateDeFacture = trim($dateDeFacture);
                 $dateDeFacture = explode("/", $dateDeFacture);

								 if(count($dateDeFacture)>1){
					
						        $dateDeFacture = $dateDeFacture[2].$dateDeFacture[1].$dateDeFacture[0];

								 }else{

								 	  $dateDeFacture = '';
								 }
                
              }					
					
					
	              //Montant facture
	              if(preg_match('/Montant/', $fct_str)){

	                 $fct_o = str_get_html($elem->innertext);
	                 $montantFacture = $fct_o->find('td', 1)->plaintext;
	                 $montantFacture =  preg_replace("/\s{2,}/si"," ", $montantFacture);
	                 $montantFacture = str_replace("&euro;", "", $montantFacture);
	                 $montantFacture = str_replace(",", ".", $montantFacture);
	                 $montantFacture = trim($montantFacture);
	              }	
					
					//Franchises d'agios facture:
					if( preg_match('/Franchises.*?:/sim', $file)){
						preg_match('/Franchises.*?:[\s\n]+<\/td>(.*?)<\/td>/sim', $file, $m );
						if(count($m)==2){
							$franchisesDagiosFacture = strip_tags($m[1]);
							$franchisesDagiosFacture = preg_replace("/[^0-9]+/",'', $franchisesDagiosFacture);
						}
					}

		         //Date de règlement 
		          if(preg_match('/Date\sde\sr/', $fct_str)){

		             $fct_o = str_get_html($elem->innertext);
		             $dateDeReglement = $fct_o->find('td', 1)->plaintext;
		             $dateDeReglement =  preg_replace("/\s{2,}/si"," ", $dateDeReglement);
		             $dateDeReglement = trim($dateDeReglement);
		             $dateDeReglement = explode("/", $dateDeReglement);
								 if(count($dateDeReglement)>1){
								 
									 $dateDeReglement = $dateDeReglement[2].$dateDeReglement[1].$dateDeReglement[0];
									 
								 }else{

								 		$dateDeReglement = '';

								 }

		          }

		        }
		    } 


			if( $tt = $html->getElementById('titleField')) {
			   $titre =  $tt->value;



					//get Compte demandeur
					$cd = str_get_html($html->getElementById('fsGenInfo'));
					if($cd){

						$cdHTML = $cd->innertext;

						if(!empty($cdHTML)){

							$cdObj = str_get_html($cdHTML);

							$compteDemandeur = trim($cdObj->find('td', 5)->plaintext);

							if(strlen($compteDemandeur)>2){

								$compteDemandeur = substr($compteDemandeur, 0, 6);
							}
							
							$benefitsiar = trim($cdObj->find('td', 17)->plaintext);

							if(strlen($benefitsiar)>2){

								$benefitsiar = $benefitsiar;
							}
							//Date de livraison annoncée :
							if( preg_match('/Date\s+de\s+livraison\s+annoncée\s+:/', $file)){
								preg_match('/Date\s+de\s+livraison\s+annoncée\s+:[\s\n]+<\/td>(.*?)<\/td>/sim', $file, $m );
                    if(count($m)==2){
                        $dateDeLivrasionAnnoncee = strip_tags($m[1]);
                        $dateDeLivrasionAnnoncee = preg_replace("/[^0-9\/]+/",'', $dateDeLivrasionAnnoncee);
                        if(preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $dateDeLivrasionAnnoncee)){
                            $ex = explode("/", $dateDeLivrasionAnnoncee);
                            $dateDeLivrasionAnnoncee = $ex[2].$ex[1].$ex[0];
                        }
                        
                    }
							}							
							
							

						}
					}
			}


	   		//prime
	        $pr = $html->getElementById('fsCommercialOperation');

	        if($pr) {

	            foreach($pr->find('td') as $k => $elem){

	              $elem = trim($elem->plaintext); 
	              $elem = str_replace(" ", "", $elem);
	              $elem = str_replace(":", "", $elem); 
	 
	              if($elem=="Valeur"){

	                $k++;

	                $prime=trim($pr->find('td', $k)->plaintext);

	                break;
	              }

	            }
	        } 

	        //Véhicule(chassis)
	        $vch = $html->getElementById('vehicleNumberLink');

	        if($vch){
	          $vencha = trim($vch->plaintext); 
	        }   
			
			//localisation
					
	        if(strlen($vencha)>5){

	                                
	          $res4 = $client->request('GET', $host.'/dvnWeb/networkDisplayVehicle.action?from=home&vinNumber='.$vencha.'&brandCountry=38&saveType=', [
	                         'auth' => [$USR, $PWD],
	              'allow_redirects' => [
	              'max'             => 10,        // allow at most 10 redirects.
	              'referer'         => true,      // add a Referer header
	              'connect_timeout' => 3.14,
	              'track_redirects' => true
	              ]
	            ]);


	            if($res4->getStatusCode()==200) {
	          
	              
	              

	                preg_match('/parc\sfiliale.*?<td.*?>(.*?)<\/td>/sim', $res4->getBody(), $m);

	                if(array_key_exists(1, $m)){

	                  $m = trim($m[1]);

	                  if(strlen($m)>5){


	                    if(preg_match('/ZEEBRUGGE/', $m)){


	                      $localisation = 'ZEEBRUGGE';


	                    

	                    }elseif(preg_match('/GHISLENGHIEN/', $m)){


	                      $localisation = 'GHISLENGHIEN';


	                    }


	                  }
	                }              

	              

	            }

	        }

	        //Code vendeur
	        if(strlen($fco)>1){
	          $r = $client->request('POST', $host.'/dvnWeb/networkDisplayCustomerOrder.action?from=home&customerOrderNumber='.$fco.'&brandCountry=38', [
	            'auth' => [$USR, $PWD],
	            'allow_redirects' => [
	              'max'             => 10,        // allow at most 10 redirects.
	              'referer'         => true,      // add a Referer header
	              'connect_timeout' => 3.14,
	              'track_redirects' => true,
	              ],
	          ]);

	          if($r->getStatusCode()==200){
	            $f = $r->getBody();
	            
	            //Code vendeur
	            preg_match('/Code\s+vendeur\s+[:].*?(<\/td>.*?<\/td>)/si', $f, $m); 
	            if(count($m)==2){
	              $codeVendeur = trim( strip_tags($m[1]) );
	            }

	            //Date of sold, try #2
	            preg_match('/Créée\s+le\s+[:].*?(<\/td>.*?<\/td>)/si', $f, $m); 
	            if(count($m)==2){
	              $ds = explode("/", trim( strip_tags($m[1]) ) );
	              if(count($ds)==3){
	                $dateSold = $ds[2].'-'.$ds[1].'-'.$ds[0];
	              }
	              
	            }
	            
	          }
	        }
	        //end Code vendeur  


	        if(strlen($titre)>5){



	            $res3 = $client->request('POST', $host.'/dvnWeb/updateTitleLabel.action', [
	                    'auth' => [$USR, $PWD],
	                    'allow_redirects' => [
	                    'max'             => 10,        // allow at most 10 redirects.
	                    'referer'         => true,      // add a Referer header
	                    'connect_timeout' => 3.14,
	                    'track_redirects' => true
	                    ],
	                    'form_params' => [
	                    'titleValue' => $titre,
	                    'previousExactTitleValue' => '',
	                    'nic'=>'VIEW'
	                ]
	            ]);

	          //  echo  $res3->getStatusCode();

	            if($res3->getStatusCode()==200){




					$file = $res3->getBody(); 
					$html = str_get_html($file);

					
                    preg_match("/Habillages.*?[<]span.*?[>](.*?)[\/]span[>].*?[<]span.*?[>](.*?)[\/]span[>]/sim", $html, $h);

                    if(count($h)==3){
			
						
						$hab1 = trim(strip_tags($h[1]));
						$hab2 = trim(strip_tags($h[2]));			
						if(!preg_match('/[(]/', $hab1) or preg_match('/titre|grande|vente/i', $hab1)){$hab1='---';}			
						if(!preg_match('/[(]/', $hab2) or preg_match('/titre|grande|vente/i', $hab2)){$hab2='---';}
						
						$habillages1 = html_entity_decode(str_replace("/", " ", $hab1));
						$habillages2 = html_entity_decode(str_replace("/", " ", $hab2));
						$habillages1 = Encoding::fixUTF8($habillages1);
						$habillages2 = Encoding::fixUTF8($habillages2);

						$habillages1 = str_replace(array("'",'"'), '', $habillages1);
						$habillages2 = str_replace(array("'",'"'), '', $habillages2);
						$habillages1 = preg_replace("/\s{2,}/"," ", $habillages1);
						$habillages2 = preg_replace("/\s{2,}/"," ", $habillages2);

					}
						//beta 2   grabb options, options100%, options + options100%

						      //Options
	                  preg_match('/options\s+[:].*?(<\/td>.*?<\/td>)/si', $file, $m); 
	                  if(count($m)==2){
	                    $options = trim( strip_tags($m[1]) );
	                  }  

	                  //Options 100%
	                  preg_match('/options\s+100%\s+[:].*?(<\/td>.*?<\/td>)/si', $file, $m); 
	                  if(count($m)==2){
	                    $options .= ". Options 100%: ".trim( strip_tags($m[1]) );
	                  }  

	                  $options = preg_replace("/\s{2,}/"," ", $options); 
	                  $options = preg_replace('/\xc2\xa0/',"", $options);   
	                  $options = html_entity_decode($options);
	                  $options = str_replace(array("'",'"'), '', $options);

						preg_match('/.*?\s/', $titre, $m);

						$titre = trim($m[0]); 




					    if(file_exists($fCSV)){
     						unlink($fCSV);
    					}	
						

						$fp = fopen($fCSV, 'a+');
						
						fputs($fp, implode([$numero, $habillages1, $habillages2, $options, $compteDemandeur, $titre, $dateDeReglement, $franchisesDagiosFacture, $montantFacture, $vencha, $prime, $dateDeLivrasionAnnoncee, $dateDeFacture, $localisation, $benefitsiar, $compare, $stock, $observations, $fco, $codeVendeur, $vpvu, $dateSold], ',')."\n" );
						fclose($fp);

						
						$fp = fopen('tmp/all.csv', 'a+');
						
						fputs($fp, implode([$numero, $habillages1, $habillages2, $options, $compteDemandeur, $titre, $dateDeReglement, $franchisesDagiosFacture, $montantFacture, $vencha, $prime, $dateDeLivrasionAnnoncee, $dateDeFacture, $localisation, $benefitsiar, $compare, $stock, $observations, $fco, $codeVendeur, $vpvu, $dateSold], ',')."\n" );
						fclose($fp);	


						require('/v/index.php');	
						


						echo json_encode("ok");


				

				}
	        }else{

	            $fp = fopen($fNotFoundIdsCSV, 'a+');
	            fputcsv($fp, array($numero));   
	            fclose($fp);
	            echo json_encode("no");
	        }  

	    }
	     
	}



	if($res->getStatusCode()==401){

		echo json_encode("401");
	}
}










	







?>

