<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Service/Google/AbstractService.php';
require_once __DIR__ . '/Service/Google/Freebase.php';
require_once __DIR__ . '/Entity/TravelDestination.php';

$config = require_once __DIR__ . '/config/application.config.php';

$service = new \Service\Google\Freebase($config);
$travelDestinations = $service->getTravelDestinations(1);

foreach ($travelDestinations as $travelDestination) {
	var_dump($travelDestination);
}

//// GOOGLE PLACES

// Create a client and provide a base URL
//$client = new Client('https://maps.googleapis.com');
//
//$request = $client->get('/maps/api/place/nearbysearch/json');
//
//$request->getQuery()->set('key', $apiKey);
//$latitude = '-33.8670522';
//$longitude = '151.1957362';
//$request->getQuery()->set('location', $latitude . ',' . $longitude);
//$request->getQuery()->set('radius', '500');
//$request->getQuery()->set('sensor', 'false');
//$request->getQuery()->set('types', 'food');
//
//$response = $request->send();
//
//if (!$response->isSuccessful()) {
//	throw new \Exception('Google Places API request failed');
//}
//
//echo $response->getBody();