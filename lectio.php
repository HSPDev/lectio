<?php
/*
Uofficielt API til Lectio
Baseret på simple_html_dom.php,
cURL og cacert.pem
Lavet af Henrik Pedersen
og Daniel Poulsen.
*/
include('simple_html_dom.php'); //Include vores class
class lectio
{
	private function get_content($url) {
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
	public function get_skema($url_til_skema)
	{
		$lectio_html = $this->get_content($url_til_skema);
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
	//Hent skema ud fra gymnasiekode og Lectio ID for elev
	public function get_skema_til_elev($gymnasie_kode, $lectio_id)
	{
		$url_to_get = 'https://www.lectio.dk/lectio/'.$gymnasie_kode.'/SkemaNy.aspx?type=elev&elevid='.$lectio_id;
		return $this->get_skema($url_to_get);
	}
	//Hent skema ud fra gymnasiekode, Lectio ID for elev og ugekode i format WWYYYY
	public function get_skema_til_elev_og_uge($gymnasie_kode, $lectio_id, $uge_kode)
	{
		$url_to_get = 'https://www.lectio.dk/lectio/'.$gymnasie_kode.'/SkemaNy.aspx?type=elev&elevid='.$lectio_id.'&week='.$uge_kode;
		return $this->get_skema($url_to_get);
	}
	//Hent skema ud fra gymnasiekode, Lectio ID for lærer
	public function get_skema_til_laerer($gymnasie_kode, $lectio_id)
	{
		$url_to_get = 'https://www.lectio.dk/lectio/'.$gymnasie_kode.'/SkemaNy.aspx?type=laerer&laererid='.$lectio_id;
		return $this->get_skema($url_to_get);
	}
	//Hent skema ud fra gymnasiekode, Lectio ID for lærer og ugekode i format WWYYYY
	public function get_skema_til_laerer_og_uge($gymnasie_kode, $lectio_id,$uge_kode)
	{
		$url_to_get = 'https://www.lectio.dk/lectio/'.$gymnasie_kode.'/SkemaNy.aspx?type=laerer&laererid='.$lectio_id.'&week='.$uge_kode;
		return $this->get_skema($url_to_get);
	}
	//Henter den offentlige liste over elever ud fra en URL til siden med elever. Afhængig af get_content(). 
	private function get_elever_fra_side($url_til_elevside)
	{
		$lectio_html = $this->get_content($url_til_elevside);
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
	public function get_elever_fra_gymnasie($gymnasie_kode)
	{
		$url_start = 'http://www.lectio.dk/lectio/'.$gymnasie_kode.'/FindSkema.aspx?type=elev&forbogstav=';
		$final_result = array();
		$keys_to_search = array('A','B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 
			'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 
			'W', 'X', 'Y', 'Z', 'Æ', 'Ø', 'Å', '?');
		foreach ($keys_to_search as $key) {
			$final_result = array_merge($final_result, $this->get_elever_fra_side($url_start.$key));
		}
		return $final_result;
	}
	//Hent elever fra gymnasiekode sorteret efter klasse
	public function get_elever_fra_gymnasie_sorteret($gymnasie_kode)
	{
		$sorterede_elever = array();
		$input = $this->get_elever_fra_gymnasie($gymnasie_kode);
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
	//Henter den offentlige liste over lærere ud fra en URL til siden med elever. Afhængig af get_content(). 
	public function get_laerere_fra_side($url_til_laererside)
	{
		$lectio_html = $this->get_content($url_til_laererside);
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
	public function get_laerere($gymnasie_kode)
	{
		$link_start = 'https://www.lectio.dk/lectio/'.$gymnasie_kode.'/FindSkema.aspx?type=laerer';
		return $this->get_laerere_fra_side($link_start);
	}

}
?>