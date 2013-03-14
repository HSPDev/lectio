<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(-1);
set_time_limit(60);

include('lectio.php');
include('/isitagirl/is_it_a_girl.php');

$l = new lectio();
$g = new is_it_a_girl();

$elever = $l->get_elever_fra_gymnasie_sorteret('402'); //Henter alle elever fra Nakskov Gymnasium

$for_processing = array(); //Til at holde alle pigerne :D
foreach($elever as $klasse=>$klassemedlemmer)
{
	foreach ($klassemedlemmer as $elev) {
		$fornavn = $g->get_firstname($elev['navn']); //Henter fornavnet
		$gender = $g->get_gender($fornavn); //Bruger folkekirkens registre til at finde ud af om dreng eller pige
		if($gender == 'pige')
		{
			$for_processing[] = array(
				'navn' => $elev['navn'],
				'klasse' => $klasse,
				'gender' => $gender
				);
		}
	}
}
var_dump($for_processing);
?>