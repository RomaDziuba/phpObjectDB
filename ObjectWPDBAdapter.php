<?php 

require_once dirname(__FILE__).'/ObjectAdapter.php';

/**
 * Adapter for WordpressDB 
 *
 * @package    phpObjectDB
 * @author     Denis Panaskin <goliathdp@gmail.com>
 */
class ObjectWPDBAdapter extends ObjectAdapter
{
    public function quote($obj, $type = null)
    {
        return "'".$this->db->escape($obj)."'";
    }
    
    public function getRow($sql)
    {
        $result = $this->db->get_row($sql, ARRAY_A);
        
        if($userinfo = mysql_error($this->db->dbh)) {
            throw new DatabaseException($userinfo, mysql_errno($this->db->dbh));
        }

        return $result;
    }
    
    public function getAll($sql)
    {
        $result = $this->db->get_results($sql, ARRAY_A);
        
        if($userinfo = mysql_error($this->db->dbh)) {
            throw new DatabaseException($userinfo, mysql_errno($this->db->dbh));
        }

        return $result;
    }
    
    public function getOne($sql)
    {
        $result = $this->db->get_row($sql, ARRAY_N);
        
        if($userinfo = mysql_error($this->db->dbh)) {
            throw new DatabaseException($userinfo, mysql_errno($this->db->dbh));
        }

        return $result[0];
    }
    
    public function query($sql)
    {
        $result = $this->db->query($sql);
        
        if($userinfo = mysql_error($this->db->dbh)) {
            throw new DatabaseException($userinfo, mysql_errno($this->db->dbh));
        }
    }
    
    public function getAssoc($sql)
    {
        throw new DatabaseException('Undefined method getAssoc');
    }
    
    public function begin($isolationLevel = false)
    {
        throw new DatabaseException('Undefined method begin');
    }
    
    public function commit()
    {
        throw new DatabaseException('Undefined method commit');
    }
    
    public function rollback()
    {
        throw new DatabaseException('Undefined method rollback');
    }
    
    
    protected function getInsertID()
    {
        return $this->getOne("SELECT LAST_INSERT_ID()");
    }
}
?>