<?php

//视图模型
class MemberViewModel extends ViewModel {
	
	protected $viewFields = array(
		'member' => array('id', 'email', 'nickname', 'amount', 'score', 'face', 'regtime', 'logintime', 'loginip', 'loginnum', 'groupid', 'status', 'islock',
		'_type' => 'LEFT'
		),
		'membergroup' => array(
		'name' => 'groupname',
		'_on' => 'member.groupid = membergroup.id',//_on 对应上面LEFT关联条件
		//'_type' => 'LEFT'
		),
		/*
		'model' => array(
		'tablename' => 'tablename',//显示字段name as model
		'_on' => 'category.modelid = model.id',//_on 对应上面LEFT关联条件
		),
		*/

	);
}

?>