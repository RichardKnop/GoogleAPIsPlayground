<?php

namespace Service\Google;

use Service\Google\Freebase,
	Entity\TravelDestination as Entity,
	Collection\TravelDestination as Collection;

class TravelDestinationFreebase extends Freebase
{

	public function __construct($config)
	{
		parent::__construct($config);
	}

	public function getTravelDestinations($howMany)
	{
		$collection = new Collection();
		foreach ($this->listTopics('/travel/travel_destination', $howMany) as $topic) {
			$entity = $this->getTravelDestinationEntity($topic['name'], $this->getTopic($topic['id']));
			$collection->add($entity);
		}
		return $collection;
	}
	
	protected function getTravelDestinationEntity($name, $data)
	{
		$travelDestination = new Entity();
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

	protected function getMonthlyClimateData($data, $property)
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

}