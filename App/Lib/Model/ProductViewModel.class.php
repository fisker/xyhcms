<?php

//视图模型
class ProductViewModel extends ViewModel {
	
	protected $viewFields = array(
		'product' => array('id', 'title', 'color', 'publishtime', 'updatetime', 'click', 'description', 'price', 'trueprice', 'brand', 'units', 'specification', 'litpic', 'cid',
		'flag', 'jumpurl',
		'_type' => 'LEFT'
		),
		'category' => array(
		//'name' => 'cate',//v1.1废除
		'name' => 'catename',
		'ename' => 'ename',
		'modelid' => 'modelid',
		'_on' => 'product.cid = category.id',//_on 对应上面LEFT关联条件
		),

	);
}

?>