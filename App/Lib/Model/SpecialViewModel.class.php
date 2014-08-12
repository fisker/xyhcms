<?php

//视图模型
class SpecialViewModel extends ExViewModel {
	
	protected $viewFields = array(
		'special' => array('*','_type' => 'LEFT'),
		'category' => array(		
		'name' => 'catename',
		'ename' => 'ename',
		'modelid' => 'modelid',
		'_on' => 'special.cid = category.id',//_on 对应上面LEFT关联条件
		),

	);
}

?>