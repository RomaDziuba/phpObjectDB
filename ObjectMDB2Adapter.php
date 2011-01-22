<?php 

require_once 'juds/objects/ObjectAdapter.php';

class ObjectMDB2Adapter extends ObjectAdapter
{
    public function quote($obj, $type = null)
    {
        return $this->db->quote($obj, $type);
    }
    
    public function getRow($sql)
    {
        $result = $this->db->getRow($sql);
        
        if(PEAR::isError($result)) {
            throw new DatabaseException($result->userinfo, $result->code);
        }

        return $result;
    }
    
    public function getAll($sql)
    {
        $result = $this->db->getAll($sql);
        
        if(PEAR::isError($result)) {
            throw new DatabaseException($result->userinfo, $result->code);
        }

        return $result;
    }
    
    public function getOne($sql)
    {
        $result = $this->db->getOne($sql);
        
        if(PEAR::isError($result)) {
            throw new DatabaseException($result->userinfo, $result->code);
        }

        return $result;
    }
    
    public function getAssoc($sql)
    {
        $result = $this->db->getAssoc($sql);
        
        if(PEAR::isError($result)) {
            throw new DatabaseException($result->userinfo, $result->code);
        }

        return $result;
    }
    
    public function query($sql)
    {
        $result = $this->db->query($sql);
        
        if(PEAR::isError($result)) {
            throw new DatabaseException($result->userinfo, $result->code);
        }

        return $result;
    }
    
	protected function getInsertID()
    {
        return $this->getOne("SELECT LAST_INSERT_ID()");
    }
    
   
}
?>