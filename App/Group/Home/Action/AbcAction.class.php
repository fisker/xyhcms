<?php

class AbcAction extends Action{
	//shows
	public function shows(){
		
		$id = I('id', 0, 'intval');
		$flag = I('flag', 0, 'intval');
		if (!empty($id)) {
			echo getAbc($id,$flag);
		}else {
			echo '';
		}

	}
}

?>