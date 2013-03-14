<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(-1);
set_time_limit(60);
class is_it_a_girl
{
	public function get_gender($fornavn)
	{
		$drengenavne = file('C:\xampp\htdocs\lectio\isitagirl\drengenavne.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$pigenavne = file('C:\xampp\htdocs\lectio\isitagirl\pigenavne-updated.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$unisexnavne = file('C:\xampp\htdocs\lectio\isitagirl\unisexnavne.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if(in_array($fornavn, $unisexnavne))
		{
			return 'ukendt';
		}
		if(in_array($fornavn, $drengenavne))
		{
			return 'dreng';
		}
		if(in_array($fornavn, $pigenavne))
		{
			return 'pige';
		}
	}
	public function get_firstname($fullname)
	{
		list($first_word) = explode(' ', trim($fullname));
		$first_word = trim($first_word);
		$first_word = ucfirst($first_word);
		return $first_word;
	}
}
?>