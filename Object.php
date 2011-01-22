<?php

require_once 'juds/exceptions/DatabaseException.php';
require_once 'juds/objects/IObject.php';

abstract class Object implements IObject
{
	protected $adapter;
	
	public static function factory(&$db)
    {
        $libName = false;
        $parentClass = get_parent_class($db);
        $currentClass = get_class($db);
        
        switch($currentClass) {
            case 'wpdb':
                $libName = 'WPDB';
                break;
                
            default:
                if($parentClass == 'MDB2_Driver_Common') {
                    $libName = 'MDB2';
                }                
        } // end switch
        
        
        
        if(!$libName) {
            throw new SystemException(_('Object Adapter not found'));
        }
        
        $className = 'Object'.$libName.'Adapter';
        $path = 'juds/objects/'.$className.'.php';
        
        if(!include_once($path)) {
            throw new SystemException(_('Object Adapter not installed'));
        }
        
        $instance = new $className($db);
        
        return $instance;
    } // end factory
    
	public function __construct(&$adapter) 
    {
        $this->adapter = $adapter;
    }
	
	public function quote($obj, $type = null)
	{
		return $this->adapter->quote($obj, $type);
	}
    
    public function getRow($sql)
	{
		return $this->adapter->getRow($sql);
	}
    
    public function getAll($sql)
	{
		return $this->adapter->getAll($sql);
	}
    
    public function getOne($sql)
	{
		return $this->adapter->getOne($sql);
	}
	
	public function insert($table, $values, $is_update_dublicate = false)
	{
		return $this->adapter->insert($table, $values, $is_update_dublicate);
	}
	
	public function update($table, $values, $condition = array())
	{
		return $this->adapter->update($table, $values, $condition);
	}
	
	public function query($sql)
	{
		return $this->adapter->query($sql);
	}
	
	public function getAssoc($sql)
	{
	    return $this->adapter->getAssoc($sql);
	}
	
	public function getAllSplit($query, $col, $page)
    {
        $result = array();
        $page -= 1;
        
        if(!preg_match('/SQL_CALC_FOUND_ROWS/Umis', $query)) {
            $query = preg_replace("/^SELECT/Umis", "SELECT SQL_CALC_FOUND_ROWS ", $query);
        }
        
        $query .= " LIMIT ".($page * $col).", ".$col;
        
        $result['rows'] = $this->getAll($query);
        
        $result['cnt']      = $this->getOne('SELECT FOUND_ROWS()');   
        $result['pageCnt']  = ceil($result['cnt'] / $col);
        
        return $result; 
    }// end getAllSplit
	
    public function getSqlCondition($obj = array()) 
    {
         return $this->adapter->getSqlCondition($obj);
    } // end getSqlCondition
	
    protected function getInsertSQL($table, $values, $is_update_dublicate = false) 
    {
        return $this->adapter->getInsertSQL($table, $values, $is_update_dublicate);
    }
    
    protected function getUpdateSQL($table, $values, $condition = array()) 
    {
        return $this->adapter->getUpdateSQL($table, $values, $condition);
    }
    
}
?>