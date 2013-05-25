<?php

namespace Service\Google;

use Service\Google\AbstractService;

class Freebase extends AbstractService
{

	const MQL_QUERY_LIMIT = 100;

	public function __construct($config)
	{
		parent::__construct($config, 'freebase');
	}

	protected function getTopic($id)
	{
		return $this->topicRequest($id);
	}

	protected function getPropertyValueText($data, $property)
	{
		if (isset($data['property'][$property])) {
			return $data['property'][$property]['values'][0]['text'];
		}
		return NULL;
	}

	protected function getPropertyPropertyValueText($data, $property1, $property2)
	{
		if (isset($data['property'][$property1])) {
			if (isset($data['property'][$property1]['values'][0]['property'][$property2])) {
				return $data['property'][$property1]['values'][0]['property'][$property2]['values'][0]['text'];
			}
		}
		return NULL;
	}

	protected function getPropertyPropertyValueValue($data, $property1, $property2)
	{
		if (isset($data['property'][$property1])) {
			if (isset($data['property'][$property1]['values'][0]['property'][$property2])) {
				return $data['property'][$property1]['values'][0]['property'][$property2]['values'][0]['value'];
			}
		}
		return NULL;
	}

	protected function getPropertyValueTexts($data, $property)
	{
		$values = array();
		if (isset($data['property'][$property])) {
			foreach ($data['property'][$property]['values'] as $propertyValue) {
				$values[] = $propertyValue['text'];
			}
		}
		return $values;
	}
	
	//TODO
	protected function getImages($data)
	{
		$images = array();
		if (isset($data['property']['/common/topic/image'])) {
			foreach ($data['property']['/common/topic/image']['values'] as $image) {
				$images[] = $image['id'];
			}
		}
		return $images;
	}

	protected function listTopics($id, $howMany)
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

	protected function listTopicsPaginated($type, $cursor, $howMany)
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

	protected function mqlReadRequest($params, $cursor)
	{
		$request = $this->client->get($this->getMqlReadPath());
		$request->getQuery()->set('key', $this->config['apiKey']);
		$request->getQuery()->set('query', $this->getMqlQuery($params));
		$request->getQuery()->set('cursor', $cursor);
		return $request->send()->json();
	}

	protected function getMqlReadPath()
	{
		return '/freebase/' . $this->config['freebase']['version'] . '/mqlread';
	}

	protected function getMqlQuery($params)
	{
		return '[' . json_encode($params) . ']';
	}

	protected function topicRequest($id)
	{
		$request = $this->client->get($this->getTopicPath($id));
		$request->getQuery()->set('key', $this->config['apiKey']);
		return $request->send()->json();
	}

	protected function getTopicPath($id)
	{
		return '/freebase/' . $this->config['freebase']['version'] . '/topic/' . urlencode($id);
	}

}