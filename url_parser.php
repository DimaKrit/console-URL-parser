<?php

$options = getopt('u:',['url:']);

if (isset($options['url'])) {
	$url = $options['url'];
} elseif (isset($options['u'])) {
	$url = $options['u'];
} else {
	$url = $argv[1];
}

if (filter_var($url, FILTER_VALIDATE_URL) === false) {
    echo 'Not a valid URL';
    exit(1);
}

$resultArray = [];

function analyseHost($host)
{
	$hostArray   = explode('.', $host);
	$resultArray = ['subdomain' => null, 'domain' => null, 'tld' => null];

	// if host is ip, only set as root
	if (filter_var($host, FILTER_VALIDATE_IP))
	{
		// something like 127.0.0.1
		$resultArray['domain'] = $host;
	}
	elseif (count($hostArray) === 1)
	{
		// something like localhost
		$resultArray['domain'] = $host;
	}
	elseif (count($hostArray) === 2)
	{
		// like google.com
		$resultArray['domain'] = $my_host[0].$my_host[1];
		$resultArray['tld']  = $my_host[1];
	}
	elseif (count($hostArray) >= 3)
	{
		
		// get last one as tld
		$resultArray['tld']  = end($hostArray);
		array_pop($hostArray);

		// check last one after remove is probably tld or not
		$knownTld = ['com', 'co', 'org', 'in', 'us', 'gov', 'mil',  'net', 'int', 'info', 'edu', 'sch', 'biz'];

		$probablyTld = end($hostArray);

		if (in_array($probablyTld, $knownTld))
		{
			$resultArray['sld'] = $probablyTld. '.'. $resultArray['tld'];
			array_pop($hostArray);
		}

		if (isset($resultArray['sld'])) {
			$resultArray['domain'] = end($hostArray).'.'.$resultArray['sld'];
		} else {
			$resultArray['domain'] = end($hostArray).'.'.$resultArray['tld'];
		}
		
		array_pop($hostArray);

		// all remain is subdomain
		if (count($hostArray) > 0)
		{
			$resultArray['subdomain'] = implode('.', $hostArray);
		}
	}

	return $resultArray;
}

function getParsedQuery($query)
{
	if (!empty($query)) {

		parse_str($query, $getArray);

		return ['parsedQuery' => $getArray];
	}
}

$urlArray = parse_url($url);

$resultArray['scheme'] = parse_url($url, PHP_URL_SCHEME);
$resultArray['host'] = parse_url($url, PHP_URL_HOST);
$resultArray['path'] = parse_url($url, PHP_URL_PATH);
$resultArray['query'] = parse_url($url, PHP_URL_QUERY);
$resultArray['fragment'] = parse_url($url, PHP_URL_FRAGMENT);
$resultArray['extension'] = pathinfo($resultArray['path'], PATHINFO_EXTENSION);

// get tld, domain, sld
$resultHost = analyseHost($resultArray['host']);

$resultAll = array_merge($resultArray, $resultHost);

// unset empty value
$resultAll = array_diff($resultAll , ['']);

//get query parsedQuery
$queryResult = getParsedQuery($resultArray['query']);

echo json_encode($resultAll,JSON_PRETTY_PRINT).PHP_EOL;

if ($queryResult) {
	echo json_encode($queryResult,JSON_PRETTY_PRINT).PHP_EOL;
}

exit(0);


