<?php

function bingImage($search) { // Custom function to return random image from bing with a search term.
	$acctKey = "<your bing api key>";
	$auth = base64_encode("$acctKey:$acctKey");
	$data = array(
	'http' => array(
	'request_fulluri' => true,
	'ignore_errors' => true,
	'header' => "Authorization: Basic $auth")
	);
	$requestUri = 'https://api.datamarket.azure.com/Bing/Search/Image?$format=json&Query=%27' . $search . '%27';
	$context = stream_context_create($data);
	$response = file_get_contents($requestUri, 0, $context);
	$response = json_decode($response);
	$response = $response->d->results;
	$image = $response[rand(0,count($response))];
	$image = $image->MediaUrl;
	return $image;
}

function array_random($arr, $num = 1) { // Better than array_rand
    shuffle($arr);
    
    $r = array();
    for ($i = 0; $i < $num; $i++) {
        $r[] = $arr[$i];
    }
    return $num == 1 ? $r[0] : $r;
}

function snoopify($input) // Custom function to turn text to snoopdog
{
	$postData = array();
	$matches = array();

	$postData['translatetext'] = $input;
	$postData['translate'] ='submit';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://gizoogle.net/textilizer.php');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	$result = curl_exec($ch);
	curl_close($ch);

	$regex = '#<\s*?textarea\b[^>]*>(.*?)</textarea\b[^>]*>#s';
	preg_match($regex, $result, $matches);
	return $matches[1];
	return $input;
}
