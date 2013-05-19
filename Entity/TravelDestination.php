<?php

namespace Entity;

class TravelDestination
{
	
	public $name;
	public $shortDescription;
	public $longDescription;
	public $officialWebsite;
	public $latitude;
	public $longitude;
	public $nearbyAirports = array();
	public $touristAtractions = array();
	public $averageMaxTemps = array();
	public $averageMinTemps = array();
	public $averageRainfalls = array();
	public $images = array();
	
}