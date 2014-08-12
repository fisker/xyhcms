<?php

//扩展ViewModel,不能直接实例化
class ExViewModel extends ViewModel {


    /**
    * 表达式过滤方法
    * @access protected
    * @param string $options 表达式
    * @return void
    */
    protected function _options_filter(&$options) {
        if(isset($options['field']))
            $options['field'] = isset($options['nofield'])? $this->checkFields($options['field'],$options['nofield']) : $this->checkFields($options['field']);
        else
            $options['field'] = isset($options['nofield'])?$this->checkFields('',$options['nofield']) : $this->checkFields();
        if(isset($options['group']))
            $options['group']  =  $this->checkGroup($options['group']);
        if(isset($options['where']))
         $options['where']  =  $this->checkCondition($options['where']);
        if(isset($options['order']))
            $options['order']  =  $this->checkOrder($options['order']);
    }

    /**
     * 检查fields表达式中的视图字段
     * @access protected
     * @param string $fields 字段
     * @param string $nofield 排除字段
     * @return string
     */
    protected function checkFields($fields='', $nofield='') {
        if(empty($fields) || '*'==$fields ) {
            // 获取全部视图字段
            $fields =   array();
            foreach ($this->viewFields as $name=>$val){
                $k = isset($val['_as'])?$val['_as']:$name;
                $val  =  $this->_checkFields($name,$val);
                $val  =  $nofield?array_diff($val,$nofield):$val;//去掉不包含字段
                foreach ($val as $key=>$field){
                    if(is_numeric($key)) {
                        $fields[]    =   $k.'.'.$field.' AS '.$field;
                    }elseif('_' != substr($key,0,1)) {
                        // 以_开头的为特殊定义
                        if( false !== strpos($key,'*') ||  false !== strpos($key,'(') || false !== strpos($key,'.')) {
                            //如果包含* 或者 使用了sql方法 则不再添加前面的表名
                            $fields[]    =   $key.' AS '.$field;
                        }else{
                            $fields[]    =   $k.'.'.$key.' AS '.$field;
                        }
                    }
                }
            }
            $fields = implode(',',$fields);
        }else{
            if(!is_array($fields))
                $fields =   explode(',',$fields);
            // 解析成视图字段
            $array =  array();
            foreach ($fields as $key=>$field){
                if(strpos($field,'(') || strpos(strtolower($field),' as ')){
                    // 使用了函数或者别名
                    $array[] =  $field;
                    unset($fields[$key]);
                }
            }
            foreach ($this->viewFields as $name=>$val){
                $k = isset($val['_as'])?$val['_as']:$name;
                $val  =  $this->_checkFields($name,$val);                
                $val  =  $nofield?array_diff($val,$nofield):$val;//去掉不包含字段
                foreach ($fields as $key=>$field){
                    if(false !== $_field = array_search($field,$val,true)) {
                        // 存在视图字段
                        if(is_numeric($_field)) {
                            $array[]    =   $k.'.'.$field.' AS '.$field;
                        }elseif('_' != substr($_field,0,1)){
                            if( false !== strpos($_field,'*') ||  false !== strpos($_field,'(') || false !== strpos($_field,'.'))
                                //如果包含* 或者 使用了sql方法 则不再添加前面的表名
                                $array[]    =   $_field.' AS '.$field;
                            else
                                $array[]    =   $k.'.'.$_field.' AS '.$field;
                        }
                    }
                }
            }
            $fields = implode(',',$array);
        }
        return $fields;
    }


    /**
    * 指定字段排除
    * @access public
    * @param mixed $field
    * @return array
    */
    public function nofield($field){
        if(is_string($field)) {
            $field  =  explode(',',$field);
        }
        $this->options['nofield']   =   $field;
        return $this;
    }

    /**
    * 检查是否定义了所有字段
    * @access protected
    * @param string $name 模型名称
    * @param array $fields 字段数组
    * @return array
    */
    protected function _checkFields($name,$fields) {
        if(false !== $pos = array_search('*',$fields)) {// 定义所有字段
            $fields  =  array_merge($fields,M($name)->getDbFields());
            unset($fields[$pos]);
        }
        return $fields;
    }


}

?>