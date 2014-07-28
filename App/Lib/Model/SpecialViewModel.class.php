<?php

//视图模型
class SpecialViewModel extends ViewModel {
	
	protected $viewFields = array(
		'special' => array('id', 'title', 'shorttitle', 'color', 'publishtime', 'updatetime', 'author', 'keywords', 'click', 'description', 'litpic', 'cid', 'filename', 
		'flag', 'jumpurl',
		'_type' => 'LEFT'
		),
		'category' => array(		
		//'name' => 'cate',//v1.1废除
		'name' => 'catename',
		'ename' => 'ename',
		'modelid' => 'modelid',
		'_on' => 'special.cid = category.id',//_on 对应上面LEFT关联条件
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