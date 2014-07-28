<?php

//视图模型
class SoftViewModel extends ViewModel {
	
	protected $viewFields = array(
		'soft' => array('id', 'title',  'color', 'publishtime', 'updatetime', 'click', 'description' , 'litpic',
		'version','softtype', 'copytype', 'language', 'os', 'filesize', 'officialurl', 'cid', 'flag', 'jumpurl',
		'_type' => 'LEFT'
		),
		'category' => array(		
		//'name' => 'cate',//v1.1废除
		'name' => 'catename',
		'ename' => 'ename',
		'modelid' => 'modelid',
		'_on' => 'soft.cid = category.id',//_on 对应上面LEFT关联条件
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