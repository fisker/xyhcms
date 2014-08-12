<?php

//视图模型
class ArcViewModel extends ExViewModel {
	

 	protected $viewFields = array();

	/**
	* 架构函数
	* 取得DB类的实例对象 字段检查
	* @access public
    * @param string $name 模型名称
	* @param string $tableName 主表名称
	* @param string $tablePrefix 表前缀
	* @param mixed $connection 数据库连接信息
	*/
	public function __construct($name='',$tableName='article',$tablePrefix='',$connection='') {
		//
		$this->viewFields = array(
		$tableName => array('*','_type' => 'LEFT'),
			'category' => array(
			'name' => 'catename',
			'ename' => 'ename',
			'modelid' => 'modelid',
			'_on' => $tableName.'.cid = category.id',//_on 对应上面LEFT关联条件
			//'_type' => 'LEFT'
			),
		);
		
		 // 模型初始化
		$this->_initialize();
		// 获取模型名称
		if(!empty($name)) {
			if(strpos($name,'.')) { // 支持 数据库名.模型名的 定义
				list($this->dbName,$this->name) = explode('.',$name);
			}else{
				$this->name   =  $name;
			}
		}elseif(empty($this->name)){
			$this->name =   $this->getModelName();
		}
		// 设置表前缀
		if(is_null($tablePrefix)) {// 前缀为Null表示没有前缀
			$this->tablePrefix = '';
		}elseif('' != $tablePrefix) {
			$this->tablePrefix = $tablePrefix;
		}else{
			$this->tablePrefix = $this->tablePrefix?$this->tablePrefix:C('DB_PREFIX');
		}

		// 数据库初始化操作
		// 获取数据库操作对象
		// 当前模型有独立的数据库连接信息
		$this->db(0,empty($this->connection)?$connection:$this->connection);
	}


}

?>