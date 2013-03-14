<?php
include('simple_html_dom.php');
header('Content-Type: text/html; charset=utf-8');
error_reporting(-1);
set_time_limit(60);
function get_content($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt ($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
} 
//Henter den offentlige liste over lærere ud fra en URL til siden med elever. Afhængig af get_content(). 
function get_laerere_fra_side($url_til_laererside)
{
	$lectio_html = get_content($url_til_laererside);
	$laerere = array(); //Opret array til lærere fra denne side

	$html = new simple_html_dom();
	$html->load($lectio_html); //Parse HTML
	$laerer_objekter = $html->find('td a'); //Søg efter alle lærere 
	for($i=0; $i<count($laerer_objekter); $i++)
	{
		$l = $laerer_objekter[$i];
		if(strlen($l->lectiocontextcard)>0)
		{
			//Det er et lærer objekt vi har med at gøre!
			$navn = trim(html_entity_decode($l->plaintext));
			$initialer = preg_match("/\((.*)\)/", $navn, $match_data); //Match for parantesen med initialer
			$link = $l->href;
			$lectio_id = substr($link, strpos($link, 'laererid=')+9);
			$laerere[] = array(
				'navn' => $navn,
				'initialer' => $match_data[1],
				'lectio_id' => $lectio_id
				);
		}

	}
	return $laerere;
}
//Hent lærere ud fra gymnasiekode
function get_laerere($gymnasie_kode)
{
	$link_start = 'https://www.lectio.dk/lectio/'.$gymnasie_kode.'/FindSkema.aspx?type=laerer';
	return get_laerere_fra_side($link_start);
}
$laerere = get_laerere('402');
var_dump($laerere);
?>