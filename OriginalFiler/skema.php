<?php
include('simple_html_dom.php');
header('Content-Type: text/html; charset=utf-8');
error_reporting(-1);
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
//Henter det offentlige skema fra en Lectio skema URL. Afhængig af get_content(). 
function get_skema($url_til_skema)
{
	$lectio_html = get_content($url_til_skema);
	$skema = array(); //Definer skema variabel til return værdi!
	$skema['titel'] = ''; //Definer struktur for titel
	$skema['ugedage'] = array(); //Definer struktur for liste over ugedage
	$skema['dagskema'] = array(); //Definer struktur for dagskemaet
	$html = new simple_html_dom();
	$html->load($lectio_html);
	$skema['titel'] = $html->find('.s2weekHeader td', 0)->plaintext; //Hent overskriften uden html (uge og år)
	$headers = $html->find('.s2dayHeader td'); //Søg efter listen over dage i det pågældende skema 
	for($i=1;$i<count($headers);$i++) //Bemærk vi starter i = 1 fordi vi springer den første over (altid tom)
	{
		$skema['ugedage'][] = $headers[$i]->plaintext; //Tilføj alle overskrifterne på ugedagene (uden html)
		$skema['dagskema'][$headers[$i]->plaintext] = array(
			'noter' => array(),
			'fag' => array()
			); //Tilføj en struktur til nøglen for hvert dagskema
	}
	$collection = $html->find('.s2skemabrikcontainer'); //Søg efter den ydre skema container for hver dag
	/*
	BEMÆRK!!!! 
	Den ydre skema container, der er langt flere end antal dage. Feks. kan en uge med 5 dage
	have 11 ydre containere pga. 5 til moduler, 5 til "noter" i toppen, og én til sidebaren.
	*/
	$skemabrik = new simple_html_dom(); //Definer den her for genbrugens skyld da den kan tømmes efter hver iteration
	for($i=0; $i<count($skema['ugedage']); $i++) //Iterer alle noterne i "toppen" af ugedagene
	{
		$skemabrik->load($collection[$i]->innertext);
		$noter = $skemabrik->find('.s2skemabrikcontent');
		foreach($noter as $note)
		{
			//Tilføj til liste over noter for dagen
			$skema['dagskema'][$skema['ugedage'][$i]]['noter'][] = trim(html_entity_decode($note->plaintext));
		}
	}
	//Iterer alle fagene/noterne i selve skemaet for hver dag
	//Vi starter iteratoren i count($skema['ugedage']) + 1 fordi vi vil springe topnoter + et stil element i mellem over.
	for($i=count($skema['ugedage'])+1; $i<(2*count($skema['ugedage'])+1); $i++) 
	{
		$skemabrik->load($collection[$i]->innertext);
		$noter = $skemabrik->find('.s2skemabrikcontent');
		foreach($noter as $note)
		{
			//Tilføj til dagskemaet
			$skema['dagskema'][$skema['ugedage'][$i-count($skema['ugedage'])-1]]['fag'][] = array(
				'tekst' => trim(html_entity_decode($note->plaintext)),
				'note' => ''
				);
		}
	}
	for($i=5; $i<count($skema['ugedage'])+6; $i++) //Gennemgå alle dagene
	{
		$skemabrik->load($collection[$i]->innertext); //Indlæs hver dag
		$skemabrik_elementer = $skemabrik->find('.s2skemabrik'); //Søg efter alle skemabrikkerne på denne dag
		for($y=0; $y<count($skemabrik_elementer); $y++) //Gennemgå alle noterne til hvert fag/note/whatever
		{
			//Tilføj noten til det korrekte fag 
			$skema['dagskema'][$skema['ugedage'][$i-6]]['fag'][$y]['note']=trim(html_entity_decode($skemabrik_elementer[$y]->title));
		}
	}
	return $skema;
}
function get_skema_til_elev($gymnasie_kode, $lectio_id)
{
	$url_to_get = 'https://www.lectio.dk/lectio/'.$gymnasie_kode.'/SkemaNy.aspx?type=elev&elevid='.$lectio_id;
	return get_skema($url_to_get);
}
function get_skema_til_elev_og_uge($gymnasie_kode, $lectio_id, $uge_kode)
{
	$url_to_get = 'https://www.lectio.dk/lectio/'.$gymnasie_kode.'/SkemaNy.aspx?type=elev&elevid='.$lectio_id.'&week='.$uge_kode;
	return get_skema($url_to_get);
}
$skema = get_skema_til_elev(402, 4763365585);
var_dump($skema);
?>