<?php

//视图模型,假设是一对一，其实 不是多对多，暂时用吧
class RoleViewModel extends ViewModel {
	
	protected $viewFields = array(
		'role_user' => array(
		'user_id' => 'user_id',
		//'_type' => 'LEFT'
		),
			
		'role' => array(
		'name' => 'name',//显示字段name as role
		'remark' => 'remark',//显示字段name as role
		'status' => 'rstatus',
		'_on' => 'role_user.role_id = role.id',//_on 对应上面LEFT关联条件
		),
		/*	*/
		

	);
}

?>