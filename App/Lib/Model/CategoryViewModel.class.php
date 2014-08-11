<?php

//视图模型
class CategoryViewModel extends ExViewModel {
	
	protected $viewFields = array(
		'category' => array('*','_type' => 'LEFT'),
		'model' => array(
		'name' => 'modelname',//显示字段name as model
		'tablename' => 'tablename',//显示字段name as model
		'_on' => 'category.modelid = model.id',//_on 对应上面LEFT关联条件
		),

	);
}

?>