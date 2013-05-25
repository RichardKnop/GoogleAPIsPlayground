<?php

namespace Database;

use Database\Adapter\Factory;

class DatabaseService
{

	const TRAVEL_DESTINATION_TABLE = 'travelDestination';
	const AIRPORT_TABLE = 'airport';
	const TRAVEL_DESTINATION_AIRPORT_TABLE = 'travelDestinationAirport';
	const TOURIST_ATTRACTION_TABLE = 'touristAttraction';
	const TRAVEL_DESTINATION_TOURIST_ATTRACTION_TABLE = 'travelDestinationTouristAttraction';
	const AVERAGE_MAXIMUM_TEMPERATURE_TABLE = 'averageMaximumTemperature';
	const AVERAGE_MINIMUM_TEMPERATURE_TABLE = 'averageMinimumTemperature';
	const AVERAGE_RAINFALL_TABLE = 'averageRainfall';
	const IMAGE_TABLE = 'image';

	private $config;
	private $adapter;

	public function __construct($config)
	{
		$this->config = $config;
		$this->adapter = Factory::manufacture($this->config);
	}

	public function saveTravelDestinationsFromJson($json)
	{
		foreach (json_decode($json) as $travelDestination) {
			if (FALSE === $this->validateTravelDestinationJson($travelDestination)) {
				continue;
			}
			$this->saveTravelDestination(
				$travelDestination->name, $travelDestination->shortDescription, $travelDestination->longDescription, $travelDestination->officialWebsite, $travelDestination->latitude, $travelDestination->longitude
			);
			foreach ($travelDestination->nearbyAirports as $airport) {
				$this->saveAirport($travelDestination->name, $airport);
			}
			foreach ($travelDestination->touristAtractions as $touristAtraction) {
				$this->saveTouristAttraction($travelDestination->name, $touristAtraction);
			}
			foreach ($travelDestination->averageMaxTemps as $month => $temperature) {
				$this->saveAverageMaximumTemperature($travelDestination->name, $month, $temperature);
			}
			foreach ($travelDestination->averageMinTemps as $month => $temperature) {
				$this->saveAverageMinimumTemperature($travelDestination->name, $month, $temperature);
			}
			foreach ($travelDestination->averageRainfalls as $month => $rainfall) {
				$this->saveAverageRainfall($travelDestination->name, $month, $rainfall);
			}
			foreach ($travelDestination->images as $image) {
				$this->saveImage($travelDestination->name, $image);
			}
		}
	}

	private function validateTravelDestinationJson($travelDestination)
	{
		$cannotBeNull = array(
			$travelDestination->name,
			$travelDestination->shortDescription,
			$travelDestination->longDescription,
			$travelDestination->officialWebsite,
			$travelDestination->latitude,
			$travelDestination->longitude,
		);
		foreach ($cannotBeNull as $value) {
			if (empty($value)) {
				return FALSE;
			}
		}
		return TRUE;
	}

	private function saveTravelDestination($id, $shortDescription, $longDescription, $officialWebsite, $latitude, $longitude)
	{
		$data = array(
			'id' => $id,
			'shortDescription' => $shortDescription,
			'longDescription' => $longDescription,
			'officialWebsite' => $officialWebsite,
			'latitude' => $latitude,
			'longitude' => $longitude,
		);
		return $this->adapter->save(self::TRAVEL_DESTINATION_TABLE, $data);
	}

	private function saveAirport($travelDestinationId, $airport)
	{
		$this->adapter->saveIfNotExists(
			self::AIRPORT_TABLE, array('id' => $airport)
		);

		$this->adapter->saveIfNotExists(
			self::TRAVEL_DESTINATION_AIRPORT_TABLE, array(
			'airportId' => $airport,
			'travelDestinationId' => $travelDestinationId,
			)
		);
	}

	private function saveTouristAttraction($travelDestinationId, $touristAttraction)
	{
		$this->adapter->saveIfNotExists(
			self::TOURIST_ATTRACTION_TABLE, array('id' => $touristAttraction)
		);

		$this->adapter->saveIfNotExists(
			self::TRAVEL_DESTINATION_TOURIST_ATTRACTION_TABLE, array(
			'touristAttractionId' => $touristAttraction,
			'travelDestinationId' => $travelDestinationId,
			)
		);
	}

	private function saveAverageMaximumTemperature($travelDestinationId, $month, $temperature)
	{
		$data = array(
			'month' => $month,
			'temperature' => (float) $temperature,
			'travelDestinationId' => $travelDestinationId,
		);
		return $this->adapter->save(self::AVERAGE_MAXIMUM_TEMPERATURE_TABLE, $data);
	}

	private function saveAverageMinimumTemperature($travelDestinationId, $month, $temperature)
	{
		$data = array(
			'month' => $month,
			'temperature' => (float) $temperature,
			'travelDestinationId' => $travelDestinationId,
		);
		return $this->adapter->save(self::AVERAGE_MINIMUM_TEMPERATURE_TABLE, $data);
	}

	private function saveAverageRainfall($travelDestinationId, $month, $rainfall)
	{
		$data = array(
			'month' => $month,
			'rainfall' => (float) $rainfall,
			'travelDestinationId' => $travelDestinationId,
		);
		return $this->adapter->save(self::AVERAGE_RAINFALL_TABLE, $data);
	}

	private function saveImage($travelDestinationId, $image)
	{
		$data = array(
			'id' => $image,
			'travelDestinationId' => $travelDestinationId,
		);
		return $this->adapter->save(self::IMAGE_TABLE, $data);
	}

	private function getTravelDestinations($offset = NULL, $limit = NULL)
	{
		return $this->adapter->get(self::TRAVEL_DESTINATION_TABLE, $offset, $limit);
	}

	private function getTravelDestination($id)
	{
		$data = array(
			'id' => $id,
		);
		return $this->adapter->getSingle(self::TRAVEL_DESTINATION_TABLE, $data);
	}

}