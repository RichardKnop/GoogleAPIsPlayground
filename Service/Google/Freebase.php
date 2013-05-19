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

	private function getTopic($id)
	{
		return $this->topicRequest($id);
	}
	
	private function getTravelDestinationEntity($name, $data)
	{
		$travelDestination = new TravelDestination();
		$travelDestination->name = $name;

		// website, short and long description
		$travelDestination->officialWebsite = $this->getPropertyValueText($data, '/common/topic/official_website');
		$travelDestination->shortDescription = $this->getPropertyPropertyValueText($data, '/common/topic/article', '/common/document/text');
		$travelDestination->longDescription = $this->getPropertyPropertyValueValue($data, '/common/topic/article', '/common/document/text');

		// geolocations
		$travelDestination->latitude = $this->getPropertyPropertyValueText($data, '/location/location/geolocation', '/location/geocode/latitude');
		$travelDestination->longitude = $this->getPropertyPropertyValueText($data, '/location/location/geolocation', '/location/geocode/longitude');

		$travelDestination->nearbyAirports = $this->getPropertyValueTexts($data, '/location/location/nearby_airports');
		$travelDestination->touristAtractions = $this->getPropertyValueTexts($data, '/travel/travel_destination/tourist_attractions');
		
		// climate data
		$travelDestination->averageMaxTemps = $this->getMonthlyClimateData($data, '/travel/travel_destination_monthly_climate/average_max_temp_c');
		$travelDestination->averageMinTemps = $this->getMonthlyClimateData($data, '/travel/travel_destination_monthly_climate/average_min_temp_c');
		$travelDestination->averageRainfalls = $this->getMonthlyClimateData($data, '/travel/travel_destination_monthly_climate/average_rainfall_mm');

		// pictures
		$travelDestination->images = $this->getImages($data);

		return $travelDestination;
	}

	private function getPropertyValueText($data, $property)
	{
		if (isset($data['property'][$property])) {
			return $data['property'][$property]['values'][0]['text'];
		}
		return NULL;
	}

	private function getPropertyPropertyValueText($data, $property1, $property2)
	{
		if (isset($data['property'][$property1])) {
			if (isset($data['property'][$property1]['values'][0]['property'][$property2])) {
				return $data['property'][$property1]['values'][0]['property'][$property2]['values'][0]['text'];
			}
		}
		return NULL;
	}

	private function getPropertyPropertyValueValue($data, $property1, $property2)
	{
		if (isset($data['property'][$property1])) {
			if (isset($data['property'][$property1]['values'][0]['property'][$property2])) {
				return $data['property'][$property1]['values'][0]['property'][$property2]['values'][0]['value'];
			}
		}
		return NULL;
	}

	private function getPropertyValueTexts($data, $property)
	{
		$values = array();
		if (isset($data['property'][$property])) {
			foreach ($data['property'][$property]['values'] as $propertyValue) {
				$values[] = $propertyValue['text'];
			}
		}
		return $values;
	}

	private function getMonthlyClimateData($data, $property)
	{
		$values = array();
		if (isset($data['property']['/travel/travel_destination/climate'])) {
			foreach ($data['property']['/travel/travel_destination/climate']['values'] as $monthlyClimateData) {
				if (isset($monthlyClimateData['property']['/travel/travel_destination_monthly_climate/month'])) {
					$month = $monthlyClimateData['property']['/travel/travel_destination_monthly_climate/month']['values'][0]['text'];
					if (isset($monthlyClimateData['property'][$property])) {
						$values[$month] = $monthlyClimateData['property'][$property]['values'][0]['text'];
					}
				}
			}
		}
		return $values;
	}
	
	//TODO
	private function getImages($data)
	{
		$images = array();
		if (isset($data['property']['/common/topic/image'])) {
			foreach ($data['property']['/common/topic/image']['values'] as $image) {
				$images[] = $image['id'];
			}
		}
		return $images;
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