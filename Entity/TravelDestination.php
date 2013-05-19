<?php

namespace Entity;

class TravelDestination
{
	
	public $name;
	public $shortDescription;
	public $longDescription;
	public $officialWebsite = NULL;
	public $latitude = NULL;
	public $longitude = NULL;
	public $nearbyAirports = array();
	public $touristAtractions = array();
	public $averageMaxTemps = array();
	public $averageMinTemps = array();
	public $averageRainfalls = array();
	public $images = array();
	
}