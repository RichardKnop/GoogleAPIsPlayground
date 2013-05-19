<?php

namespace Service\Google;

use Service\Google\AbstractService;
use Entity\TravelDestination;

class Freebase extends AbstractService
{

	const MQL_QUERY_LIMIT = 100;

	public function __construct($config)
	{
		parent::__construct($config, 'freebase');
	}

	public function getTravelDestinations($howMany)
	{
		$travelDestinations = array();
		foreach ($this->listTopics('/travel/travel_destination', $howMany) as $topic) {
			$travelDestinations[] = $this->getTravelDestinationEntity($topic['name'], $this->getTopic($topic['id']));
		}
		return $travelDestinations;
	}

	public function getTopic($id)
	{
		return $this->topicRequest($id);
	}

	//
	// private functions
	//
	
	private function getTravelDestinationEntity($name, $data)
	{
		$travelDestination = new TravelDestination();
		$travelDestination->name = $name;

		$travelDestination->officialWebsite = $data['property']['/common/topic/official_website']['values'][0]['text'];
		$travelDestination->shortDescription = $data['property']['/common/topic/article']['values'][0]['property']['/common/document/text']['values'][0]['text'];
		$travelDestination->longDescription = $data['property']['/common/topic/article']['values'][0]['property']['/common/document/text']['values'][0]['value'];
		
		// geolocation
		$travelDestination->latitude = $data['property']['/location/location/geolocation']['values'][0]['property']['/location/geocode/latitude']['values'][0]['text'];
		$travelDestination->longitude = $data['property']['/location/location/geolocation']['values'][0]['property']['/location/geocode/longitude']['values'][0]['text'];
		
		foreach ($data['property']['/location/location/nearby_airports']['values'] as $nearbyAirport) {
			$travelDestination->nearbyAirports[] = $nearbyAirport['text'];
		}
		
		foreach ($data['property']['/travel/travel_destination/tourist_attractions']['values'] as $touristAttraction) {
			$travelDestination->touristAtractions[] = $touristAttraction['text'];
		}
		
		foreach ($data['property']['/travel/travel_destination/climate']['values'] as $monthlyClimateData) {
			$month = $monthlyClimateData['property']['/travel/travel_destination_monthly_climate/month']['values'][0]['text'];
			$travelDestination->averageMaxTemps[$month] = $monthlyClimateData['property']['/travel/travel_destination_monthly_climate/average_max_temp_c']['values'][0]['text'];
			$travelDestination->averageMinTemps[$month] = $monthlyClimateData['property']['/travel/travel_destination_monthly_climate/average_min_temp_c']['values'][0]['text'];
			$travelDestination->averageRainfalls[$month] = $monthlyClimateData['property']['/travel/travel_destination_monthly_climate/average_rainfall_mm']['values'][0]['text'];
		}
		
		foreach ($data['property']['/common/topic/image']['values'] as $image) {
			//TODO
			$travelDestination->images[] = $image['id'];
		}
		
		return $travelDestination;
	}

	private function listTopics($id, $howMany)
	{
		$results = array();
		$cursor = '';

		$maxIterations = round($howMany / self::MQL_QUERY_LIMIT) + 1;
		for ($i = 0; $i < $maxIterations; ++$i) {
			$response = $this->listTopicsPaginated($id, $cursor, $howMany);

			if (0 === count($response['result'])) {
				continue;
			}

			foreach ($response['result'] as $result) {
				$results[] = $result;
			}

			$cursor = $response['cursor'];
		}

		return $results;
	}

	private function listTopicsPaginated($type, $cursor, $howMany)
	{
		return $this->mqlReadRequest(
				array(
				'type' => $type,
				'limit' => $howMany < self::MQL_QUERY_LIMIT ? $howMany : self::MQL_QUERY_LIMIT,
				'id' => NULL,
				'name' => NULL,
				), $cursor
		);
	}

	private function mqlReadRequest($params, $cursor)
	{
		$request = $this->client->get($this->getMqlReadPath());
		$request->getQuery()->set('key', $this->config['apiKey']);
		$request->getQuery()->set('query', $this->getMqlQuery($params));
		$request->getQuery()->set('cursor', $cursor);
		return $request->send()->json();
	}

	private function getMqlReadPath()
	{
		return '/freebase/' . $this->config['freebase']['version'] . '/mqlread';
	}

	private function getMqlQuery($params)
	{
		return '[' . json_encode($params) . ']';
	}

	private function topicRequest($id)
	{
		$request = $this->client->get($this->getTopicPath($id));
		$request->getQuery()->set('key', $this->config['apiKey']);
		return $request->send()->json();
	}

	private function getTopicPath($id)
	{
		return '/freebase/' . $this->config['freebase']['version'] . '/topic/' . urlencode($id);
	}

}