<?php
/**
 *
 * @link http://code.google.com/p/php-handlersocket/wiki/HandlerSocketExecuteSingle
 * @link http://tokarchuk.ru/2010/12/handlersocket-protocol-and-php-handlersocket-extension/
 */
//http://code.google.com/p/php-handlersocket-wrapper/source/browse/trunk/handlersocket_wrapper.php?r=2

//FIXME: Change index and connection logic
class MysqlHandlerSocket
{
	public $host;
	public $name;
	public $readPort;
	public $writePort;

	public function __construct($host, $name, $readPort = false, $writePort = false)
	{
		$this->host = $host;
		$this->name = $name;
		$this->readPort = $readPort ? $readPort : 9998;
		$this->writePort = $writePort ? $writePort : 9999;
	}

	public function get($table, $columns, $indexKey, $indexValue)
	{
		$hs = new HandlerSocket($this->host, $this->readPort);

		if (is_scalar($indexValue)) {
			$indexValue = array($indexValue);
		}

		$select = join(',', $columns);

		$res = $hs->openIndex(1, $this->name, $table, $indexKey, $select);

		if (!$res) {
			throw new DatabaseException($hs->getError());
		}

		$rows = $hs->executeSingle(1, '=', $indexValue, 1, 0);

		unset($hs);

		if (!$rows) {
			return array();
		}

		list($row) = $rows;

		$result = array();

		foreach ($row as $index => $value) {
			$result[$columns[$index]] = $value;
		}

		return $result;
	} // end get

	public function insert($table, $values, $indexKey)
	{
		$hs = new HandlerSocket($this->host, $this->writePort);

		$res = $hs->openIndex(1, $this->name, $table, $indexKey, join(',', array_keys($values)));

		if (!$res) {
			throw new DatabaseException($hs->getError());
		}

		$ret = $hs->executeInsert(1, array_values($values));

		return $ret;
	} // end insert

	public function remove($table, $indexKey, $value)
	{
		$hs = new HandlerSocket($this->host, $this->writePort);

		$res = $hs->openIndex(1, $this->name, $table, $indexKey, array());



		$ret = $hs->executeDelete(1, "=", array($value));

		return $ret;
	}

	public function update($table, $values, $indexKey, $value)
	{
		$hs = new HandlerSocket($this->host, $this->writePort);

		$res = $hs->openIndex(1, $this->name, $table, $indexKey, join(',', array_keys($values)));

		if (!$res) {
			throw new DatabaseException($hs->getError());
		}

		$res = $hs->executeUpdate(1, "=", array($value), array_values($values), 1, 0);

		if (!$res) {
			throw new DatabaseException($hs->getError());
		}

		return $res;
	}




}

?>