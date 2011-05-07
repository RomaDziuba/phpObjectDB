<?php 

require_once dirname(__FILE__).'/ObjectAdapter.php';

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
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function getAll($sql)
    {
        $result = array();
        
        $query = $this->db->prepare($sql);
        if($this->db->errorCode() > 0) {
            $info = $this->db->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        } 

        $query->execute();
        $result = $query->fetchAll();
        
        return $result;
    }
    
    public function getCol($sql)
    {
        throw new DatabaseException('Undefined method getCol');
    }
    
    
    public function getOne($sql)
    {
        $query = $this->db->prepare($sql);
        if($this->db->errorCode() > 0) {
            $info = $this->db->errorInfo();
            throw new DatabaseException($info[2], $info[1]);
        }

        $query->execute();
        
        return $query->fetchColumn();
    }
    
    public function getAssoc($sql)
    {
        throw new DatabaseException('Undefined method getAssoc');
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