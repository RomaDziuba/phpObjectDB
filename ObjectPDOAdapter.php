<?php

/**
 * Adapter for PDO
 *
 * @package    phpObjectDB
 * @author     Denis Panaskin <goliathdp@gmail.com>
 */
class ObjectPDOAdapter extends ObjectAdapter
{
    public function quote($obj, $type = null)
    {
        return $this->db->quote($obj, $type);
    }

    public function getRow($sql)
    {
        $query = $this->db->prepare($sql);

        if($this->db->errorCode() > 0) {
            $info = $this->db->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        }

    	$res = $query->execute();
        if (!$res) {
            $info = $query->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        }

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll($sql)
    {
        $result = array();

        $query = $this->db->prepare($sql);
        if($this->db->errorCode() > 0) {
            $info = $this->db->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        }

        $res = $query->execute();
        if (!$res) {
            $info = $query->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        }

        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function getCol($sql)
    {
    	$query = $this->db->prepare($sql);
        if($this->db->errorCode() > 0) {
            $info = $this->db->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        }
           
        $res = $query->execute();
        if (!$res) {
            $info = $query->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        }

        $result = array();
        while ($cell = $query->fetchColumn()) {
        	$result[] = $cell;
        }
        
        return $result;
    }


    public function getOne($sql)
    {
        $query = $this->db->prepare($sql);
        if($this->db->errorCode() > 0) {
            $info = $this->db->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        }

        $res = $query->execute();

    	if (!$res) {
        	$info = $query->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        }

        return $query->fetchColumn();
    }

    public function getAssoc($sql)
    {
    	$result = array();

    	$query = $this->db->prepare($sql);
    	if($this->db->errorCode() > 0) {
    		$info = $this->db->errorInfo();
    		throw new DatabaseException($info[2], $info[1]);
    	}

    	$res = $query->execute();
    	if (!$res) {
    		$info = $query->errorInfo();
    		throw new DatabaseException($info[2], $info[1]);
    	}

    	$result = $query->fetchAll(PDO::FETCH_KEY_PAIR);

    	return $result;
    }

    public function begin($isolationLevel = false)
    {
        // TODO: Savepoint
        //if ($this->db->inTransaction()) {
        //    $this->commit();
        //}

        $this->db->beginTransaction();
    }

    public function commit()
    {
        $this->db->commit();
    }

    public function rollback()
    {
        $this->db->rollBack();
    }

    public function query($sql)
    {
        $affected_rows = $this->db->exec($sql);

        if($this->db->errorCode() > 0) {
            $info = $this->db->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        }

        return $affected_rows;
    }

    protected function getInsertID()
    {
        return $this->getOne("SELECT LAST_INSERT_ID()");
    }

}

?>