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
//Henter den offentlige liste over elever ud fra en URL til siden med elever. Afhængig af get_content(). 
function get_elever_fra_side($url_til_elevside)
{
	$lectio_html = get_content($url_til_elevside);
	$elever = array(); //Opret array til elever fra denne side

	$html = new simple_html_dom();
	$html->load($lectio_html); //Parse HTML
	$elev_objekter = $html->find('td a'); //Søg efter alle elever på denne side
	$link = '';
	for($i=32; $i<count($elev_objekter); $i++)
	{
		$link = $elev_objekter[$i]->href;
		$link_pos = strrpos($link, 'elevid=');
		if($link_pos <> -1)
		{
			$link = trim(substr($link, $link_pos+7));
			if(is_numeric($link))
			{
				$link = (int)$link;
				$elever[] = array(
					'navn' => trim(html_entity_decode($elev_objekter[$i]->plaintext)),
					'lectio_id' => $link
					);
			}
		}
	}
	return $elever;
}
//Hent elever fra gymnasie i relativt usorteret liste (for det meste alfabetisk men ikke garanteret)
function get_elever_fra_gymnasie($gymnasie_kode)
{
	$url_start = 'http://www.lectio.dk/lectio/'.$gymnasie_kode.'/FindSkema.aspx?type=elev&forbogstav=';
	$final_result = array();
	$keys_to_search = array('A','B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 
		'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 
		'W', 'X', 'Y', 'Z', 'Æ', 'Ø', 'Å', '?');
	foreach ($keys_to_search as $key) {
		$final_result = array_merge($final_result, get_elever_fra_side($url_start.$key));
	}
	return $final_result;
}
//Hent elever fra gymnasiekode sorteret efter klasse
function get_elever_fra_gymnasie_sorteret($gymnasie_kode)
{
	$sorterede_elever = array();
	$input = get_elever_fra_gymnasie($gymnasie_kode);
	foreach ($input as $elev) {
		preg_match("/\((.*)\s(.*)\)/", $elev['navn'], $match_data); //Parse klasse data
		if(count($match_data) == 3) //Hvis der er både klasse og elev ID så sæt det ind
		{
			if(!array_key_exists($match_data[1], $sorterede_elever))
			{
				//Hvis elevens klasse ikke allerede eksisterer så tilføjer vi den lige
				$sorterede_elever[$match_data[1]] = array();
			}
			$sorterede_elever[$match_data[1]][] = array(
				'navn' => trim(substr($elev['navn'], 0, strpos($elev['navn'], '('))), //Vi fjerner lige klasse fra navn
				'lectio_id' => $elev['lectio_id'],
				'elev_nummer' => (int)$match_data[2],
				'klasse' => $match_data[1]
				);
		}
	}
	return $sorterede_elever;
}

$elever = get_elever_fra_gymnasie_sorteret('402');
var_dump($elever);
?>