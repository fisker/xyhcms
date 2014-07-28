<?php

//视图模型
class ArticleViewModel extends ViewModel {
	
	protected $viewFields = array(
		'article' => array('id', 'title', 'shorttitle', 'color', 'copyfrom', 'author', 'publishtime', 'updatetime', 'click', 'description', 'litpic', 'cid',
		'flag', 'jumpurl',
		'_type' => 'LEFT'
		),
		'category' => array(
		//'name' => 'cate',//v1.1废除
		'name' => 'catename',
		'ename' => 'ename',
		'modelid' => 'modelid',
		'_on' => 'article.cid = category.id',//_on 对应上面LEFT关联条件
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