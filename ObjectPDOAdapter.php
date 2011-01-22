<?php 
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