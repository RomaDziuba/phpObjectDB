<?php

require_once dirname(__FILE__).'/DatabaseException.php';
require_once dirname(__FILE__).'/IObject.php';

abstract class Object implements IObject
{
    const FETCH_ALL = 100;
    const FETCH_ROW = 101;
    const FETCH_ASSOC = 102;
    const FETCH_COL = 103;
    const FETCH_ONE = 104;
    
    private static $_instances;
    
    protected $adapter;
    
    public static function factory(&$db)
    {
        $libName = false;
        $parentClass = get_parent_class($db);
        $currentClass = get_class($db);
        
        if ( $parentClass == 'ObjectAdapter') {
            return $db;
        }
        
        switch($currentClass) {
            case 'wpdb':
                $libName = 'WPDB';
                break;
                
            case 'PDO':
                $libName = 'PDO';
                break;
                
            default:
                if($parentClass == 'MDB2_Driver_Common') {
                    $libName = 'MDB2';
                }                
        } // end switch
        
        if (!$libName) {
            throw new DatabaseException(_('Object Adapter not found'));
        }
        
        $className = 'Object'.$libName.'Adapter';
        $path = dirname(__FILE__).'/'.$className.'.php'; 
        
        if(!include_once($path)) {
            throw new DatabaseException(_('Object Adapter not installed'));
        }
        
        $instance = new $className($db);
        
        return $instance;
    } // end factory
   
    
    public function __construct(&$adapter) 
    {
        $this->adapter = $adapter;
    }
    
    /**
     * Returns objects instance by name
     * 
     * @param string $name
     * @return Object
     */
    public static function &getInstance($name, $db, $path = false)
    {
        if (isset(self::$_instances[$name])) {
            return self::$_instances[$name];
        }
        
        $adapter = self::factory($db);
        
        $className = $name.'Object';
       
        // default path to objects
        if ($path) {
            $classFile = $path.$className.'.php';
            if (!file_exists($classFile)) {
                $path = false;
            }
        }

        if ( !$path ) {
            $path = realpath(dirname(__FILE__).'/../../objects/').'/';
        }
       
        $classFile = $path.$className.'.php';
                          
        if ( !file_exists($classFile) ) {
            throw new DatabaseException(sprintf(_('File "%s" for object "%s" was not found.'), $classFile, $name));
        }
            
        require_once $classFile;
        if ( !class_exists($className) ) {
            throw new DatabaseException(sprintf(_('Class "%s" was not found in file "%s".'), $className, $classFile));
        }
        
        self::$_instances[$name] = new $className($adapter);
       
        return self::$_instances[$name];
    } // end get
    
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
    
    public function getCol($sql)
    {
        return $this->adapter->getCol($sql);
    }
    
    public function insert($table, $values, $is_update_dublicate = false)
    {
        return $this->adapter->insert($table, $values, $is_update_dublicate);
    }
    
    public function delete($table, $condition)
    {
        return $this->adapter->delete($table, $condition);
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
    
    public function begin($isolationLevel = false)
    {
        return $this->adapter->begin($isolationLevel);
    }
    
    public function commit()
    {
        return $this->adapter->commit();
    }
    
    public function rollback()
    {
        return $this->adapter->rollback();
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
    
    public function searchByPage($sql, $condition, $ordeBy, $col, $page)
    {
        $where = $this->getSqlCondition($condition);
        
        $sql .= " WHERE ".join(" AND ", $where); 
        
        if ($ordeBy) {
            $sql .= " ORDER BY ".join(", ", $ordeBy);
        }
        
        return $this->getAllSplit($sql, $col, $page);
    }
    
    
    public function getSqlCondition($obj = array()) 
    {
         return $this->adapter->getSqlCondition($obj);
    } // end getSqlCondition
    
    public function getInsertSQL($table, $values, $is_update_dublicate = false) 
    {
        return $this->adapter->getInsertSQL($table, $values, $is_update_dublicate);
    }
    
    public function getUpdateSQL($table, $values, $condition = array()) 
    {
        return $this->adapter->getUpdateSQL($table, $values, $condition);
    }
    
    public function massInsert($table, $values) 
    {
        return $this->adapter->massInsert($table, $values);
    }
    
    
    /**
     * Returns sql query without where. The method should be overridden
     * 
     * @throws DatabaseException
     */
    protected function getSql()
    {
        throw new DatabaseException('Undefined method getSql', 2001);
    } // end getSql
    
    /**
     * Returns generate select sql query
     *
     * @param array $condition
     * @param string $selectSql
     * @return string
     */
    public function getSelectSQL($condition, $selectSql = false, $orderBy = array())
    {
        $selectSql = $selectSql ? $selectSql : $this->getSql();
        
        $sql = $selectSql.' WHERE '.join(' AND ', $this->getSqlCondition($condition));
        
        if ($orderBy) {
            $sql .= " ORDER BY ".join(', ', $orderBy);
        }
        
        return $sql;
    } // end getSelectSQL
    
    /**
     * Fetch rows returned from a query
     *
     * @param string $selectSql
     * @param array $condition
     * @param string $type
     * @throws DatabaseException
     * @return array
     */
    public function select($selectSql, $condition = array(), $orderBy = array(), $type = self::FETCH_ALL)
    {
        $sql = $this->getSelectSQL($condition, $selectSql, $orderBy);
        
        $methods = array(
            self::FETCH_ALL   => 'getAll',
            self::FETCH_ROW   => 'getRow',
            self::FETCH_ASSOC => 'getAssoc',
            self::FETCH_COL   => 'getCol',
            self::FETCH_ONE   => 'getOne',
        );
        
        if ( !isset($methods[$type]) ) {
            throw new DatabaseException( sprintf(_('Undefined select type %s'), $type), 3005);
        }
        
        if ( !is_callable(array($this, $methods[$type])) ) {
            throw new DatabaseException(sprintf(_('Method "%s" was not found in Object.'), $methods[$type]));
        }
            
        return call_user_func_array(array($this, $methods[$type]), array($sql));
    } // end select
    
    /**
     * Returns an array of filter fields
     * 
     * @param $search
     * @return array
     */
    public function getConditionFields($search)
    {
        $fields = array();
        
        foreach ($search as $key => $item) {
            $buffer = explode("&", $key);
            
            $info = explode('.', $buffer[0]);
            
            if (!isset($info[1])) {
                continue;
            }
            
            $fields[$info[0]][$info[1]] = $buffer[0]; 
        }
        
        return $fields;
    } // end getConditionFields
    
    
    
}
?>