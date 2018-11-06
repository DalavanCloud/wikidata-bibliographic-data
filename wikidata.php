<?php

//----------------------------------------------------------------------------------------
function get($url, $user_agent='', $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);

	if ($content_type != '')
	{
		$opts[CURLOPT_HTTPHEADER] = array("Accept: " . $content_type);
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}

//----------------------------------------------------------------------------------------
// Does wikidata have this DOI?
function wikidata_item_from_doi($doi)
{
	$item = '';
	
	$sparql = 'SELECT * WHERE { ?work wdt:P356 "' . strtoupper($doi) . '" }';
	
	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get($url, '', 'application/json');
		
	if ($json != '')
	{
		$obj = json_decode($json);
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) != 0)	
			{
				$item = $obj->results->bindings[0]->work->value;
				$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
			}
		}
	}
	
	return $item;
}

//----------------------------------------------------------------------------------------
// Does wikidata have this JSTOR id?
function wikidata_item_from_jstor($jstor)
{
	$item = '';
	
	$sparql = 'SELECT * WHERE { ?work wdt:P888 "' . $jstor . '" }';
	
	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get($url, '', 'application/json');
		
	if ($json != '')
	{
		$obj = json_decode($json);
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) != 0)	
			{
				$item = $obj->results->bindings[0]->work->value;
				$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
			}
		}
	}
	
	return $item;
}

//----------------------------------------------------------------------------------------
// Do we have a journal with this ISSN?
function wikidata_item_from_issn($issn)
{
	$item = '';
	
	$sparql = 'SELECT * WHERE { ?work wdt:P236 "' . strtoupper($issn) . '" }';
	
	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get($url, '', 'application/json');
	
	if ($json != '')
	{
		$obj = json_decode($json);
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) != 0)	
			{
				$item = $obj->results->bindings[0]->work->value;
				$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
			}
		}
	}
	
	return $item;
}

//----------------------------------------------------------------------------------------
function wikidata_item_from_journal_name($name)
{
	$item = '';
	
	// Try English description
	$sparql = 'SELECT * WHERE { ?item rdfs:label "' . addcslashes($name, '"') . '"@en . ?item wdt:P31 wd:Q5633421}';
	
	// echo $sparql . "\n";
	
	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get($url, '', 'application/json');
	
	if ($json != '')
	{
		$obj = json_decode($json);
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) != 0)	
			{
				$item = $obj->results->bindings[0]->item->value;
				$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
			}
		}
	}
	
	return $item;
}

//----------------------------------------------------------------------------------------
function wikidata_item_from_orcid($orcid)
{
	$item = '';
	
	$sparql = 'SELECT * WHERE { ?author wdt:P496 "' . $orcid . '" }';
	
	// echo $sparql . "\n";
	
	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get($url, '', 'application/json');
	
	if ($json != '')
	{
		$obj = json_decode($json);
		
		//print_r($obj);
		
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) == 1)	
			{
				$item = $obj->results->bindings[0]->author->value;
				$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
			}
		}
	}
	
	return $item;
}

//----------------------------------------------------------------------------------------
function wikidata_item_from_wikispecies_author($wikispecies)
{
	$item = '';
	
	$sparql = 'SELECT * WHERE { VALUES ?article {<https://species.wikimedia.org/wiki/' . urlencode($wikispecies) . '> } ?article schema:about ?author . ?author wdt:P31 wd:Q5 . }';
	
	//echo $sparql . "\n";
	//echo urlencode($sparql) . "\n";
	
	$url = 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' . urlencode($sparql);
	$json = get($url, '', 'application/json');
	
	if ($json != '')
	{
		$obj = json_decode($json);
		
		//print_r($obj);
		
		if (isset($obj->results->bindings))
		{
			if (count($obj->results->bindings) == 1)	
			{
				$item = $obj->results->bindings[0]->author->value;
				$item = preg_replace('/https?:\/\/www.wikidata.org\/entity\//', '', $item);
			}
		}
	}
	
	return $item;
}

//----------------------------------------------------------------------------------------
// Convert a csl json object to Wikidata quickstatments
function csljson_to_wikidata($work)
{
	// Do we have this already in wikidata?
	$item = '';
	
	if (isset($work->message->DOI))
	{
		$item = wikidata_item_from_doi($work->message->DOI);
	}
	
	if ($item == '')
	{
		if (isset($work->message->JSTOR))
		{
			$item = wikidata_item_from_jstor($work->message->JSTOR);
		}
	}	
	
	if ($item == '')
	{
		$item = 'LAST';
	}
	
	$w = array();
	
	/*
$this->props = array(
			'pmid' => 'P698' ,
			'pmcid' => 'P932' ,
			'doi' => 'P356' ,
			'title' => 'P1476' ,
			'published in' => 'P1433' ,
			'original language' => 'P364' ,
			'volume' => 'P478' ,
			'page' => 'P304' ,
			'issue' => 'P433' ,
			'publication date' => 'P577' ,
			'main subject' => 'P921' ,
			'author' => 'P50' ,
			'short author' => 'P2093' ,
			'order' => 'P1545' ,
		) ;*/
		
	$wikidata_properties = array(
		'type'		=> 'P31',
		'BHL' 		=> 'P687',
		'DOI' 		=> 'P356',
		'HANDLE'	=> 'P1184',
		'JSTOR'		=> 'P888',
		'PMID'		=> 'P698',
		'PMC' 		=> 'P932',
		//'URL'		=> 'P854',	
		'title'		=> 'P1476',	
		'volume' 	=> 'P478',
		'issue' 	=> 'P433',
		'page' 		=> 'P304',
		'PDF'		=> 'P953'
	);
	
	// Need to think how to handle multi tag
	
	foreach ($work->message as $k => $v)
	{
		switch ($k)
		{
			case 'type':
				switch ($v)
				{
					case 'article-journal':
					case 'journal-article':
					default:
						$w[] = array('P31' => 'Q13442814');
						break;
				}
				break;
		
			case 'title':
				// Handle multiple languages
				$done = false;
				
				if (isset($work->message->multi))
				{
					if (isset($work->message->multi->_key->title))
					{					
						foreach ($work->message->multi->_key->title as $language => $v)
						{
							// title
							$w[] = array($wikidata_properties[$k] => $language . ':' . '"' . addcslashes($v, '"') . '"');
							// label
							$w[] = array('L' . $language => '"' . addcslashes($v, '"') . '"');
						}					
						$done = true;
					}					
				}
			
				if (!$done)
				{			
					$title = $v;
					if (is_array($v))
					{
						$title = $v[0];
					}				
				
					// assume English
					$language = 'en';
					
					$title = strip_tags($title);
			
					// title
					$w[] = array($wikidata_properties[$k] => $language . ':' . '"' . addcslashes($title, '"') . '"');
					// label
					$w[] = array('L' . $language => '"' . addcslashes($title, '"') . '"');
				}
				break;
				
			case 'author':
				// For now just use author names, but will want to do lookup to see if there is an item for each person
				// in which case we would only add the item, not the name (can have one or the other)
				// Note that we can't seem to add language codes to author names, they are just dumb strings
				$count = 1;
				foreach ($work->message->author as $author)
				{
					/*
					if (isset($author->multi))
					{
						if (isset($author->multi->_key->literal))
						{
							foreach ($author->multi->_key->literal as $language => $v)
							{
								$w[] = array('P2093' => $language . ':' . '"' . addcslashes($v, '"') . '"' . "\tP1545\t\"$count\"");
							}											
						}
					}
					*/
					
					$done = false;
					
					if (!$done)
					{
						if (isset($author->ORCID))
						{
							$author_item = wikidata_item_from_orcid($author->ORCID);
						
							if ($author_item != '')
							{							
								$w[] = array('P50' => $author_item . "\tP1545\t\"$count\"");
								$done = true;
							}						
						}						
					}
					
					if (!$done)
					{
						if (isset($author->WIKISPECIES))
						{
							$author_item = wikidata_item_from_wikispecies_author($author->WIKISPECIES);
						
							if ($author_item != '')
							{							
								$w[] = array('P50' => $author_item . "\tP1545\t\"$count\"");
								$done = true;
							}						
						}						
					}
					
					if (!$done)
					{
						$name = '';
						if (isset($author->literal))
						{
							$name = $author->literal;
						}
						else
						{
							$parts = array();
							if (isset($author->given))
							{
								$parts[] = $author->given;
							}
							if (isset($author->family))
							{
								$parts[] = $author->family;
							}
							$name = join(' ', $parts);				
						}
						$w[] = array('P2093' => '"' . addcslashes($name, '"') . '"' . "\tP1545\t\"$count\"");
					}
					$count++;
				}
				break;
		
			case 'volume':
			case 'issue':
			case 'page':
				$w[] = array($wikidata_properties[$k] => '"' . addcslashes($v, '"') . '"');
				break;
				
			case 'BHL':
				$w[] = array($wikidata_properties[$k] => '"' . $v . '"');
				break;
				
			case 'DOI':
				$w[] = array($wikidata_properties[$k] => '"' . strtoupper($v) . '"');
				break;
				
			case 'JSTOR':
				$w[] = array($wikidata_properties[$k] => '"' . $v . '"');
				break;
				
			/*
			case 'URL':
				if (is_array($v))
				{
					foreach ($v as $url)
					{
						$w[] = array($wikidata_properties[$k] => '"' . $url . '"');
					}
				}
				else
				{			
					$w[] = array($wikidata_properties[$k] => '"' . $v . '"');
				}
				break;
			*/
				
			case 'WIKISPECIES':
				$w[] = array('Sspecieswiki' => $v);
				break;
								
			case 'container-title':
				$container = $v;
				if (is_array($v))
				{
					$container = $v[0];
				}
				
				// OK, we need to link this to a Wikidata item
				
				// try via ISSN
				$journal_item = '';
				
				if ($journal_item == '')
				{
					if (isset($work->message->ISSN))
					{
						$n = count($work->message->ISSN);
						$i = 0;
						while (($journal_item == '') && ($i < $n))
						{
							$journal_item = wikidata_item_from_issn($work->message->ISSN[$i]);
							$i++;
						}
					}
				}	
				
				if ($journal_item == '')
				{
					$journal_item = wikidata_item_from_journal_name($container);
				}
				
				// If we have the container in Wikidata link to it
				if ($journal_item != '')
				{
					$w[] = array('P1433' => $journal_item);
				}
				break;
				
			// based on https://bitbucket.org/magnusmanske/sourcemd/src/6c998c4809df/sourcemd.php?at=master
			case 'issued':			
				$date = '';
				$d = $v->{'date-parts'}[0];
				if ( count($d) > 0 ) $year = $d[0] ;
				if ( count($d) > 1 ) $month = preg_replace ( '/^0+(..)$/' , '$1' , '00'.$d[1] ) ;
				if ( count($d) > 2 ) $day = preg_replace ( '/^0+(..)$/' , '$1' , '00'.$d[2] ) ;
				if ( isset($month) and isset($day) ) $date = "+$year-$month-$day"."T00:00:00Z/11";
				else if ( isset($month) ) $date = "+$year-$month-00T00:00:00Z/10";
				else if ( isset($year) ) $date = "+$year-00-00T00:00:00Z/9";
				
				$w[] = array('P577' => $date);
				break;
				
	
			default:
				break;
		}
	}
	
	// assume create
	if ($item == 'LAST')
	{
		echo "CREATE\n";
	}	
	
	foreach ($w as $statement)
	{
		foreach ($statement as $property => $value)
		{
			$row = array();
			$row[] = $item;
			$row[] = $property;
			$row[] = $value;
		
			echo join("\t", $row) . "\n";
		}
	}
	
}

//----------------------------------------------------------------------------------------

// test

// DOI
$doi = '10.1080/00222933.2010.520169';
$doi = '10.3956/2011-13.1';
$doi = '10.3969/J.ISSN.2095-0845.2001.02.002';
$item = wikidata_item_from_doi($doi);
echo "$doi $item\n";

// ISSN
$issn = '1175-5326';
$issn = '0001-6799';
$item = wikidata_item_from_issn($issn);
echo "$issn $item\n";

// Journal name
$name = 'Allertonia';
//$name = 'Munis Entomology & Zoology';
//$name = 'The Pan-Pacific entomologist';
$item =  wikidata_item_from_journal_name($name);
echo "$name $item\n";


$guid = '10.3969/j.issn.2095-0845.2001.02.002';
$guid = '10.13346/j.mycosystema.140275';
$guid = 'http://www.jstor.org/stable/23187393';

$guid = urlencode('http://www.museunacional.ufrj.br/publicacoes/wp-content/arquivos/Arqs%2065%20n%204%20p%20485-504%20Calvo%20et%20al.pdf');

$json = get('http://localhost/~rpage/microcitation/www/citeproc-api.php?guid=' . $guid);



$obj = json_decode($json);

print_r($obj);

$work = new stdclass;
$work->message = $obj;

csljson_to_wikidata($work);

?>
