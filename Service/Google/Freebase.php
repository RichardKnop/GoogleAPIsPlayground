<?php

namespace Service\Google;

use Service\Google\AbstractService;

class Freebase extends AbstractService
{
	
	public function __construct($config)
	{
		parent::__construct($config, 'freebase');
	}

	public function getTravelDestinations($max)
	{
		$topics = array();
		foreach ($this->listTopics('/travel/travel_destination', $max) as $travelDestination) {
			$topics[] = $this->getTopic($travelDestination['id']);
		}
		return $topics;
	}
	
	public function getTopic($id)
	{
		return $this->topicRequest($id);
	}

	//
	// private functions
	//

	private function listTopics($id, $max)
	{
		$results = array();
		$cursor = '';

		for ($i = 0; $i < $max; ++$i) {
			$response = $this->listTopicsPaginated($id, $cursor);

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

	private function listTopicsPaginated($type, $cursor)
	{
		return $this->mqlReadRequest(
				array(
				'type' => $type,
				'limit' => 100,
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