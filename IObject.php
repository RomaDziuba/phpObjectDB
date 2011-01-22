<?
interface IObject
{
	public function quote($obj, $type);
	
	public function getRow($sql);
    
    public function getAll($sql);
    
    public function getOne($sql);
    
    public function getAssoc($sql);
	
	public function query($sql);
	
	public function insert($table, $values, $is_update_dublicate);
	
	public function update($table, $values, $condition);
}
?>