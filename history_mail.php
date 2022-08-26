<?php

error_reporting(E_ALL);

require 'include/acces.inc.php';
	
$query = "SELECT `nouveau`.`date`, `voiture`.* 
FROM `nouveau`, `voiture` 
WHERE DATE_FORMAT(`nouveau`.`date`, '%Y-%m-%d') = '" . date('Y-m-d'). "' AND `nouveau`.`numero` = `voiture`.`numero`";

$dbResult = mysql_query($query,$dblink);

$i = 0;
$htmlContent = '';
while($row = mysql_fetch_array($dbResult))
{

	$htmlContent .= 
	"
	    <tr>
			<td>{$row['id_voiture']}</td>
			<td>{$row['numero']}</td>
			<td>{$row['serie']}</td>
			<td>{$row['modele_name']}</td>
			<td>{$row['nbre_de_portes']}</td>
			<td>{$row['cylindree']}</td>
			<td>{$row['cv']}</td>
			<td>{$row['essence']}</td>
			<td>{$row['couleur']}</td>
			<td>{$row['int']}</td>
			<td>{$row['options']}</td>
			<td>{$row['chassis']}</td>
			<td>{$row['localisation']}</td>
			<td>{$row['sem']}</td>
			<td>{$row['prime']}</td>
			<td>{$row['remarque']}</td>
			<td>{$row['concessionnaire']}</td>
			<td>{$row['cle']}</td>
			<td>{$row['plaque']}</td>
			<td>{$row['km']}</td>
			<td>{$row['immatriculation']}</td>
			<td>{$row['libre']}</td>
			<td>{$row['annee_modele']}</td>			
		</tr>
	";
	$i++;
}

if($i>0){
	$codehtml2 = "
		<table border=1>
			<tr>
				<td><strong>id_voiture</strong></td>
				<td><strong>numero</strong></td>
				<td><strong>serie</strong></td>
				<td><strong>modele</strong></td>
				<td><strong>nbre_de_portes</strong></td>
				<td><strong>cylindree</strong></td>
				<td><strong>cv</strong></td>
				<td><strong>essence</strong></td>
				<td><strong>couleur</strong></td>
				<td><strong>int</strong></td>
				<td><strong>options</strong></td>
				<td><strong>chassis</strong></td>
				<td><strong>localisation</strong></td>
				<td><strong>sem</strong></td>
				<td><strong>prime</strong></td>
				<td><strong>remarque</strong></td>
				<td><strong>concessionnaire</strong></td>
				<td><strong>cle</strong></td>
				<td><strong>plaque</strong></td>
				<td><strong>km</strong></td>
				<td><strong>immatriculation</strong></td>
				<td><strong>libre</strong></td>
				<td><strong>annee_modele</strong></td>			
			</tr>
			$htmlContent
		</table>

	";


	//Excel file 
	require_once 'vendor/PHPExcel/PHPExcel.php';

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("vnvd-schyns.be")
								 ->setLastModifiedBy("vnvd-schyns.be")
								 ->setTitle("vnvd")
								 ->setSubject("history_mail");



	$dbResult = mysql_query($query,$dblink) ;
	$count = mysql_num_fields($dbResult) ;
	for ($i = 1; $i < $count; $i++){
	     $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(64 + $i) .'1', mysql_field_name($dbResult, $i));
	}

	$xls_row_value = 2;
	while($row = mysql_fetch_row($dbResult))
	{
		$aCounter = 1;
		foreach($row as $rowIndex =>  $value){	
			if($rowIndex == 0)	continue; //not to print modele-name which is fetched at first 
			if($rowIndex == 4)$value = $row[0]; //replacing modele-id by modele-name;

			$value = mb_convert_encoding($value, 'UTF-8');							

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(64 + $aCounter) .$xls_row_value, $value);//$line .= $value;
			$aCounter++;
		}
					//$data .= trim($line)."\n";

		$xls_row_value++;
	}


	$xlsFilename = 'history_mail.xlsx';
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

	$objWriter->save($xlsFilename);


	include_once('class.phpmailer.php');
	
        $mail             = new PHPMailer(); 
        $mail->From       = 'info@vnvd-opel.be';;
        $mail->FromName   = "info";
        $mail->Subject    = " AJOUTE DANS LE STOCK - " . date('d-m-Y H:i:s');
        $mail->MsgHTML($codehtml2);		
        $mail->AddAttachment($xlsFilename);

        $mail->AddAddress("Paul.Fastre@groupschyns.net");
        $mail->AddCC("opcommunication@gmail.com");
        $mail->AddCC("Paul.Fastre@groupschyns.net");
		
		if(!$mail->Send()) {
			echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
			echo "Message sent!";
		}
}else{
	echo "No new cars today";
}