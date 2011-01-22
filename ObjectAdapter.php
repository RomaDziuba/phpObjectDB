<?php 

require_once 'juds/exceptions/NotDeclaredMethodException.php';

/**
 * @abstract
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @author Denis Panaskin <goliathdp@gmail.com>
 * @version 0.1
 */
abstract class ObjectAdapter implements IObject
{
    protected $db;
    
    public function __construct(&$db) 
    {
        $this->db = $db;
    }
    
    public function quote($obj, $type)
	{
		throw new NotDeclaredMethodException;
	}
    
    public function getRow($sql)
	{
		throw new NotDeclaredMethodException;
	}
    
    public function getAll($sql)
	{
		throw new NotDeclaredMethodException;
	}
    
    public function getOne($sql)
	{
		throw new NotDeclaredMethodException;
	}
    
	public function query($sql)
	{
		throw new NotDeclaredMethodException;
	}
	
	protected function getInsertID()
    {
        throw new NotDeclaredMethodException;
    }
	
	public function insert($table, $values, $is_update_dublicate = false)
	{
		$sql = $this->getInsertSQL($table, $values, $is_update_dublicate);
		
		$this->query($sql);
		
		return $this->getInsertID();
	}
	
	public function update($table, $values, $condition = array())
	{
		$sql = $this->getUpdateSQL($table, $values, $condition);
		
		return $this->query($sql);
	}
	
	/**
     * Returns the generated SQL query to insert data
     * 
     * @param string $table
     * @param array $values
     * @param boolean $is_update_dublicate
     * @return string SQL string
     */
    public function getInsertSQL($table, $values, $is_update_dublicate = false) 
    {
        foreach ($values as &$item) {
            if(is_null($item)) {
                $item = 'NULL';
                continue;
            }
            
            if(!in_array($item, array('NOW()', 'NULL'))) {
                $item = $this->quote($item);
            }
        }
        unset($item);
            
        $sql = "INSERT INTO ".$table." (".join(", ", array_keys($values)).") VALUES (".join(", ", $values).")";
            
        if($is_update_dublicate) {
            $sql .= " ON duplicate KEY UPDATE ";
            $rows = array();
            foreach ($values as $field => $value) {
                $rows[] = $field." = ".$value;
            }
                
            $sql .= join(", ", $rows);
        }
            
        return $sql;
    } // end getInsertSQL
    
    /**
     * Returns the generated SQL query to update the data
     * 
     * @param string $table
     * @param array $values
     * @param string|array $where
     * @return string
     */
    public function getUpdateSQL($table, $values, $condition = array()) 
    {
        foreach ($values as $key => &$item) {
            
            if(is_null($item)) {
                $item = $key." = NULL";
                continue;
            }
            
            if(!in_array($item, array('NOW()', 'NULL'))) {
                $item = $key." = ".$this->quote($item);
            } else {
                $item = $key." = ".$item;
            }
        }
        unset($item);
            
        $sql = "UPDATE ".$table." SET ".join(", ", $values);
        
		$sqlCondition = $this->getSqlCondition($condition);
        if($sqlCondition) {
            $sql .= " WHERE ".join(' AND ', $sqlCondition);
        }
            
        return $sql;
    } // end getUpdateSQL
    
    public function getSqlCondition($obj = array(), $is_default = true) 
    {
        $result = $is_default ? array('TRUE') : array();
        
        foreach($obj as $key => $item) {
            $buffer = explode("&", $key);
            $action = !empty($buffer[1]) ? $buffer[1] : "=";
            
            if(in_array($action, array('IN', 'NOT IN'))) {
                $values = array();
                
                foreach($item as $val) {
                    $values[] = $this->quote($val);
                }
                
				if($values) {
					$result[] = $buffer[0]." ".$action." (".join(', ', $values).')';
                }
				continue; 
            }
            
            if(strtolower($action) == 'or') {
                
                list($value, $others) = $item;
                
                $action = empty($buffer[2]) ? '&=' : '&'.$buffer[2]; 
                
                $condition = array($buffer[0].$action => $value);
                $ors = $this->getSqlCondition($condition, false);
                
                $others = $this->getSqlCondition($others, false);
                $conditions = array_merge($ors, $others);
                $result[] = '('.join(' OR ', $conditions).')';
                
                continue;
            }
            
            if(!in_array($item, array('NOW()', 'NULL'))) {
                $result[] = $buffer[0]." ".$action." ".$this->quote($item); 
            } else {
                $result[] = $buffer[0]." ".$action." ".$item;
            }
        }
        
        return $result;
    } // end getSqlCondition
    
    
}
?>