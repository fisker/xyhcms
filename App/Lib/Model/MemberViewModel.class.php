<?php

//视图模型
class MemberViewModel extends ExViewModel {
	
	protected $viewFields = array(
		'member' => array('*','_type' => 'LEFT'),
		'membergroup' => array(
		'name' => 'groupname',
		'_on' => 'member.groupid = membergroup.id',//_on 对应上面LEFT关联条件
		'_type' => 'LEFT'
		),		
		'memberdetail' => array(
		'*',//显示字段name as model
		'_on' => 'member.id = memberdetail.userid',//_on 对应上面LEFT关联条件
		),
	);
}

?>