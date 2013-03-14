<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(-1);
set_time_limit(60);

include('lectio.php');
include('/isitagirl/is_it_a_girl.php');

$l = new Lectio();
$g = new is_it_a_girl();

$elever = $l->get_elever_fra_gymnasie_sorteret('402');

$for_sorting = array();
foreach($elever as $klasse=>$klassemedlemmer)
{
	foreach ($klassemedlemmer as $elev) {
		$fornavn = $g->get_firstname($elev['navn']);
		$gender = $g->get_gender($fornavn);
		if($gender == 'pige')
		{
			$for_sorting = array(
				'navn' => $elev['navn'],
				'klasse' => 
				)
		}
	}
}

?>