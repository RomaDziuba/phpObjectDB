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
    const MYSQL_HANDLER_ERROR_ALREDY_EXISTS = 121;

	public $host;
	public $name;
	public $readPort;
	public $writePort;

// 	protected $connection;

	protected $index = 1;

	public function __construct($host, $name, $readPort = false, $writePort = false)
	{
		$this->host = $host;
		$this->name = $name;
		$this->readPort = $readPort ? $readPort : 9998;
		$this->writePort = $writePort ? $writePort : 9999;

		/*
		try {
			$this->connection = new HandlerSocket($this->host, $this->readPort);
		} catch(Exception $exp) {
			throw new DatabaseException($exp->getMessage(), $exp->getCode());
		}
		*/
	}

	private function throwException($method, $table, $index, $code = 0)
	{
	    $msg = sprintf("Method: %s; table: %s; index: %s; code: %s;", $method, $table, $index, $code);
	    throw new DatabaseException($msg);
	}

	public function get($table, $columns, $indexKey, $indexValue)
	{
	    try {
	        $connection = new HandlerSocket($this->host, $this->readPort);
	    } catch(Exception $exp) {
	        throw new DatabaseException($exp->getMessage(), $exp->getCode());
	    }

		if (is_scalar($indexValue)) {
			$indexValue = array($indexValue);
		}

		$select = join(',', $columns);
		$res = $connection->openIndex(1, $this->name, $table, $indexKey, $select);

		if (!$res) {
		    $this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
		}

		$rows = $connection->executeSingle(1, '=', $indexValue, 1, 0);

		if ($rows === false) {
		    $this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
		}

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

	public function getLimit($table, $columns, $indexKey, $indexValue, $op = '>', $limit = 1, $offset = 0)
	{
	    try {
	        $connection = new HandlerSocket($this->host, $this->readPort);
	    } catch(Exception $exp) {
	        throw new DatabaseException($exp->getMessage(), $exp->getCode());
	    }

		if (is_scalar($indexValue)) {
			$indexValue = array($indexValue);
		}

		$select = join(',', $columns);
		$res = $connection->openIndex(1, $this->name, $table, $indexKey, $select);

		if (!$res) {
		    $this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
		}

		$rows = $connection->executeSingle(1, $op, $indexValue, $limit, $offset);

		if ($rows === false) {
		    $this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
		}

		if (!$rows) {
			return array();
		}

		$result = array();

		if (is_array($rows)) {
			foreach($rows as $rowIndex => $row) {
				foreach ($row as $index => $value) {
					$result[$rowIndex][$columns[$index]] = $value;
				}
			}
		}

		return $result;
	} // end get


	public function insert($table, $values, $indexKey)
	{
		$connection = new HandlerSocket($this->host, $this->writePort);

		$res = $connection->openIndex(1, $this->name, $table, $indexKey, join(',', array_keys($values)));

		if (!$res) {
		    $this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
		}

		$ret = $connection->executeInsert(1, array_values($values));

		if ($ret === false) {
			$this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
		}

		return $ret;
	} // end insert

	public function remove($table, $indexKey, $value, $limit = 1)
	{
		$connection = new HandlerSocket($this->host, $this->writePort);

		$res = $connection->openIndex(1, $this->name, $table, $indexKey, '');
		if ($res === false) {
		    $this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
		}

		$ret = $connection->executeDelete(1, "=", array($value), $limit);
		if ($ret === false) {
		    $this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
		}

		return $ret;
	}

	public function multipleRemove($table, $indexKey, $values)
	{
	    $connection = new HandlerSocket($this->host, $this->writePort);

	    $res = $connection->openIndex(1, $this->name, $table, $indexKey, '');
	    if ($res === false) {
	        $this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
	    }

	    foreach ($values as $value) {
    	    $ret = $connection->executeDelete(1, "=", array($value));
    	    if ($ret === false) {
    	        $this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
    	    }
	    }

	    return $ret;
	}

	public function multipleInsert($table, $values, $indexKey)
	{
		$connection = new HandlerSocket($this->host, $this->writePort);
		
		list(, $fields) = each($values);
		$fields = array_keys($fields);

		$res = $connection->openIndex(1, $this->name, $table, $indexKey, join(',', $fields));

		if (!$res) {
			throw new DatabaseException($connection->getError());
		}

		foreach ($values as $value) {
			$ret = $connection->executeInsert(1, array_values($values));
			if ($ret === false) {
				throw new DatabaseException($connection->getError());
			}
		}

		return $ret;
	} // end insert
	

	public function update($table, $values, $indexKey, $value)
	{
		$connection = new HandlerSocket($this->host, $this->writePort);
		$res = $connection->openIndex(1, $this->name, $table, $indexKey, join(',', array_keys($values)));

		if (!$res) {
			throw new DatabaseException($connection->getError());
		}

		if (is_scalar($value)) {
		    $value = array($value);
		}

		$res = $connection->executeUpdate(1, "=", $value, array_values($values), 1, 0);

		if ($res === false) {
		    $this->throwException(__METHOD__, $table, $indexKey, $connection->getError());
		}

		return $res;
	}

	public function replace($table, $values, $indexKey, $value)
	{
		$columns = array_keys($values);
		$result = $this->get($table, $columns, $indexKey, $value);

		if (empty($result)) {
			$result = $this->insert($table, $values, $indexKey);
		} else {
			if (isset($values[$indexKey])) {
				unset($values[$indexKey]);
			}

			$this->update($table, $values, $indexKey, $value);
		}

		return $result;
	} // end replace


}

?>