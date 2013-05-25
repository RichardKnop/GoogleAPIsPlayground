<?php

define('APPLICATION_PATH', __DIR__);

require_once APPLICATION_PATH . '/vendor/autoload.php';
require_once APPLICATION_PATH . '/Service/Google/AbstractService.php';
require_once APPLICATION_PATH . '/Service/Google/Freebase.php';
require_once APPLICATION_PATH . '/Service/Google/TravelDestinationFreebase.php';
require_once APPLICATION_PATH . '/Entity/AbstractEntity.php';
require_once APPLICATION_PATH . '/Entity/TravelDestination.php';
require_once APPLICATION_PATH . '/Collection/AbstractCollection.php';
require_once APPLICATION_PATH . '/Collection/TravelDestination.php';
require_once APPLICATION_PATH . '/Database/DatabaseService.php';
require_once APPLICATION_PATH . '/Database/Adapter/Factory.php';
require_once APPLICATION_PATH . '/Database/Adapter/AbstractAdapter.php';
require_once APPLICATION_PATH . '/Database/Adapter/Sqlite.php';

$config = require_once APPLICATION_PATH . '/config/application.config.php';
$saveFile = APPLICATION_PATH . '/test.data';

// fetch data from Google APIs
//$service = new \Service\Google\TravelDestinationFreebase($config);
//$collection = $service->getTravelDestinations(5);

// save as JSON
//if (file_exists($saveFile)) {
//	unlink($saveFile);
//}
//file_put_contents(__DIR__ . '/test.data', $collection->getAsJson());

// load JSON and save to sqlite
$databaseService = new \Database\DatabaseService($config);
$databaseService->saveTravelDestinationsFromJson(file_get_contents($saveFile));

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