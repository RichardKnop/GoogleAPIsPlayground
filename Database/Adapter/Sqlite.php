<?php

namespace Database\Adapter;

use Database\Adapter\AbstractAdapter;

class Sqlite extends AbstractAdapter
{

	private $db;

	public function __construct($config)
	{
		parent::__construct($config);
		$this->db = new \SQLite3(APPLICATION_PATH . '/' . $config['database']['name']);
	}

	public function save($table, array $data)
	{
		$table = $this->db->escapeString($table);
		foreach ($data as $column => $value) {
			$data[$column] = "'" . $this->db->escapeString($value) . "'";
		}

		$columns = implode(',', array_keys($data));
		$values = implode(',', array_values($data));
		$sql = 'INSERT INTO ' . $table . ' (' . $columns . ')';
		$sql .= ' VALUES(' . $values . ');';

		return $this->db->exec($sql);
	}
	
	public function saveIfNotExists($table, array $data)
	{
		if (NULL === $this->getSingle($table, $data)) {
			return $this->save($table, $data);
		}
		return NULL;
	}

	public function get($table, $limit = NULL, $offset = NULL)
	{
		$table = $this->db->escapeString($table);
		$sql = 'SELECT * FROM ' . $table;
		if (NULL !== $limit) {
			$limit = $this->db->escapeString($limit);
			$sql .= ' LIMIT ' . $limit;
			if (NULL !== $offset) {
				$offset = $this->db->escapeString($offset);
				$sql .= ' OFFSET ' . $offset;
			}
		}
		$rowset = array();
		$resultSet = $this->db->query($sql);
		while ($row = $resultSet->fetchArray()) {
			$rowset[] = $row;
		}
		return $rowset;
	}

	public function getSingle($table, array $data)
	{
		$table = $this->db->escapeString($table);
		$sql = 'SELECT * FROM ' . $table;

		if (count($data) > 0) {
			$conditions = array();
			foreach ($data as $column => $value) {
				$column = $this->db->escapeString($column);
				$value = $this->db->escapeString($value);
				$conditions[] = $column . " = '$value'";
			}
			$sql .= ' WHERE ' . implode(' AND ', $conditions);
		}
		
		$row = $this->db->querySingle($sql, TRUE);
		if (!empty($row)) {
			return $row;
		}
		return NULL;
	}

}