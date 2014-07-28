<?php

//视图模型
class CategoryViewModel extends ViewModel {
	
	protected $viewFields = array(
		'category' => array('id', 'name', 'ename', 'pid', 'type', 'seotitle', 'keywords', 'description', 'modelid',
		'template_category', 'template_list', 'template_show', 'status', 'sort',
		'_type' => 'LEFT'
		),
		'model' => array(
		'name' => 'modelname',//显示字段name as model
		'tablename' => 'tablename',//显示字段name as model
		'_on' => 'category.modelid = model.id',//_on 对应上面LEFT关联条件
		),

	);
}

?>