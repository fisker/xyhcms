<?php
/****关联模型***/
class UserRelationModel extends RelationModel {
	
	//定义(主)表名
	protected $tableName = 'admin';

	//定义关联关系
	protected $_link = array(
		//role为角色表名
		'role' => array(
			'mapping_type' => 	MANY_TO_MANY, 		//关系类型：多对多
			'foreign_key' => 	'user_id',			//主表在中间表中的字段名称
			'relation_foreign_key' => 	'role_id',			//关联表(副表)在中间表中的字段名称(外键)
			//安装的时候表前缀一定要更改//debug//不能使用C()动态改变
			'relation_table' => 'yy_role_user', 	//中间表的表名(多对多关系中必须指定)
			'mapping_fields' => 'id,name,remark',	//只读取关联表(副表)中的部分字段,不指定则为全部字段
			'mapping_order' => 'id' ,//排序
			'mapping_limit' => 0,					//返回记录数
		),

	);

}

?>