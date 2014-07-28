<?php

//视图模型
class PictureViewModel extends ViewModel {
	
	protected $viewFields = array(
		'picture' => array('id', 'title', 'color', 'copyfrom', 'publishtime', 'updatetime', 'click', 'description', 'litpic', 'cid',
		'flag', 'jumpurl',
		'_type' => 'LEFT'
		),
		'category' => array(
		//'name' => 'cate',//v1.1废除
		'name' => 'catename',
		'ename' => 'ename',
		'modelid' => 'modelid',
		'_on' => 'picture.cid = category.id',//_on 对应上面LEFT关联条件
		),

	);
}

?>