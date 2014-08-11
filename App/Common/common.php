<?php
/******公共函数文件*******/

//magic_quotes_gpc如果开启,去掉转义，不然加上TP入库时的转义，会出现两次反斜线转义
if (get_magic_quotes_gpc()) {
    function stripslashes_deep($value) { 
        $value = is_array($value) ?
            array_map('stripslashes_deep', $value) :
            stripslashes($value);//去掉由addslashes添加的转义
        return $value;
   }
   $_POST = array_map('stripslashes_deep', $_POST);
   $_GET = array_map('stripslashes_deep', $_GET);
   $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
   $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

function p($array) {

	//dump(数组参数,是否显示1/0,显示标签('<pre>'),模式[0为print_r])
	dump($array,1,'',0);
}


//删除静态缓存文件
//$isdir 是否是目录
//$rules 规则名称
function delCacheHtml($str, $isdir = false, $rules = ''){
    //为空，且不是目录
    $delflag = true;
    if (empty($str) && !$isdir) {
        return;
    }
    $str_array = array();

    //更新静态缓存
    $html_cache_rules = C('HTML_CACHE_RULES');
    if (C('HTML_CACHE_ON__INDEX')) {
        $str_array[] = HTML_PATH.'Home/'. $str;
    }

    if (C('HTML_CACHE_ON__NOBILE')) {
        $str_array[] = HTML_PATH.'Mobile/'. $str;
    }

    if (!empty($rules) && !isset($html_cache_rules[$rules])) {
        $delflag = false;//不存在规则，则不用清缓存
    }else {
        $delflag = true;
    }

    if ($delflag) {
        foreach ($str_array as $v) {
            if ($isdir && is_dir($v)){
                delDirAndFile($v, false);
            }else {
                $list = glob($v.'*');
                for ($i=0; $i < count($list) ; $i++) { 
                    if (is_file($list[$i])) {                 
                        unlink($list[$i]);
                    }
                }
            }

        }

    }

    

    
}


/**
*取出所有分类
*
*@param string $status 显示部份(0|1|2)， 0显示全部(默认),1显示不隐藏的,2显示type为0(类型为内部模型非外链)全部
*@param string $update 更新缓存(0|1,false|true)， 默认0不更新
*/
function getCategory($status = 0,$update = 0) {//
    $cate_sname = 'fCategery_'. $status;
    $cate_arr = F($cate_sname);
    if ($update  || !$cate_arr) {
        if($status == 1) {
            $cate_arr = D('CategoryView')->where(array('category.status' => 1))->order('category.sort')->select();
        }else if($status == 2) {//后台栏目专用
            $cate_arr = D('CategoryView')->where(array('category.type' => 0))->order('category.sort')->select();
        }else {
            $cate_arr = D('CategoryView')->order('category.sort')->select();
        }
        if (!isset($cate_arr)) {
            $cate_arr = array();
        }
        
        //S(缓存名称,缓存值,缓存有效时间[秒]);        
        //S($cate_sname, $cate_arr, 48 * 60 * 60);
        F($cate_sname, $cate_arr);
    }
    return $cate_arr;   
}

/*get url */
//jumpflag针对文档跳转属性
function getUrl($cate, $id = 0, $jumpflag = false, $jumpurl = '') {
    $url = '';
    //如果是跳转，直接就返回跳转网址
    if ($jumpflag && !empty($jumpurl)) {
        return $jumpurl;
    }

    if (empty($cate)) {
        return $url;
    }
    
    $ename = $cate['ename'];
    if ($cate['type'] == 1) {
        $firstChar = substr($ename, 0, 1);
        if ($firstChar == '@') {//内部
            
            $ename = ucfirst(substr($ename, 1));//
            //开启路由
            if(C('URL_ROUTER_ON') == true) {
                //$url = U('/'.$ename,'', '');
                //$url = $id > 0 ? U('/'.$ename,'', array('id' => $id)) : U('/'.$ename,'', '');
                $url = $id > 0 ? U(''.$ename.'/'.$id,'') : U('/'.$ename,'');
            }else {
                $url  = U(''.$ename.'');
                if ($id > 0) {
                    //$url  = U(GROUP_NAME.'/Show/'.$cate['tablename'], array('id'=> $cate['id']));
                    $url  = U($ename.'/shows', array('e' => $cate['tablename'], 'id'=> $cate['id']));
                }

            }

        }else {
            $url = $ename;//http://
        }
        
    }else {
        //开启路由
        if(C('URL_ROUTER_ON') == true) {
            //$url = $id > 0 ? U('/'.$ename,'', array('id' => $id)) : U('/'.$ename,'', '');
            $url = $id > 0 ? U(''.$ename.'/'.$id,'') : U('/'.$ename,'', '');
        }else {

            //$url  = U(GROUP_NAME.'/List/index', array('cid'=> $cate['id']));
            $url  = U('List/index', array('cid'=> $cate['id']));
            //$url  = U(GROUP_NAME.'/List/index', array('e' => $ename));
            if ($id > 0) {
                //$url  = U(GROUP_NAME.'/Show/'.$cate['tablename'], array('id'=> $cate['id']));
                $url  = U('Show/index', array('cid' => $cate['cid'], 'id'=> $cate['id']));
            }
            
        }

    }

    return $url;

}



/*get url */
//jumpflag针对文档跳转属性
function getContentUrl($id, $cid, $ename, $jumpflag = false, $jumpurl = '') {
    $url = '';
    //如果是跳转，直接就返回跳转网址
    if ($jumpflag && !empty($jumpurl)) {
        return $jumpurl;
    }
    if (empty($id) || empty($cid) || empty($ename)) {
        return $url;
    }
    

    //开启路由
    if(C('URL_ROUTER_ON') == true) {
        $url = $id > 0 ? U(''.$ename.'/'.$id,'') : U('/'.$ename,'', '');
    }else {
        $url  = U('Show/index', array('cid' => $cid, 'id'=> $id));
     
        
    }


    return $url;
}


//$ismobile是否是Mobile,$typeid栏目id,
function getPosition($typeid = 0, $sname = '', $surl = '', $ismobile = false, $delimiter = '&gt;&gt;') {
    if ($delimiter == '' ) {
        $delimiter = '&gt;&gt;';
    }
    $url = $ismobile ? U(GROUP_NAME. '/Index/index/') : C('cfg_weburl');
    $position = '<a href="'. $url .'">首页</a>';

    //Parents of Category
    if (!empty($typeid)) {
        $cate = getCategory(0);//ALL
        import('Class.Category', APP_PATH); 
        $getParents = Category::getParents($cate, $typeid);
        if (is_array($getParents)) {            
            foreach ($getParents as $v) {
                $position .= $delimiter. '<a href="' . getUrl($v) .'">'.$v['name']. '</a>'; 
            }
        }
    }

    if (!empty($sname)) {
        if (empty($surl)) {
            $position .= $delimiter. $sname; 
        }else {
            $position .= $delimiter. '<a href="' . $surl .'">'.$sname. '</a>'; 
        }
    }
    

    return $position;
}

/**
 *  获取枚举的值
 *
 * @access    public
 * @param     string    $group   联动组
 * @param     string    $evalue   联动值
 * @return    string
 */
function getValueOfItem($group, $value = 0) {
    //return $value.'--<br>';
    ${'item_'.$group} = getArrayOfItem($group);
    if(isset(${'item_'.$group}[$value])) {
        return ${'item_'.$group}[$value];
    }
    else {
        return "保密";
    }
}

function getArrayOfItem($group = 'animal', $update  = 0) {//S方法的缓存名都带's'

    $itme_arr = S('sItem_'. $group);
    if ($update  || !$itme_arr) {
        $itme_arr = array();
        $temp = M('iteminfo')->where(array('group' => $group))->order('sort,id')->select(); 
        foreach ($temp as $key => $v) {
            $itme_arr[$v['value']] = $v['name'] ;
                  
        }      
        
        //S(缓存名称,缓存值,缓存有效时间[秒]);        
        S('sItem_'. $group, $itme_arr, 48 * 60 * 60);
    }
    return $itme_arr;   
}

//block 
//$name
function getBlock($name , $update  = 0) {
    $block_sname = 'fBlock/'. md5($name);
    $_block = F($block_sname);
    if ($update  || !$_block) {

        $_block = M('block')->where(array('name' => "$name"))->find();    
        if(!isset($_block)) {
            $_block = null;
            if(!$update) return null;
        }
        //F(缓存名称,缓存值);        
        F($block_sname, $_block);
    }
    return $_block;   
}

function getClick($id, $tablename, $flag = 0) {
        
        $id = intval($id);
        if (empty($id) || empty($tablename)) {
            return '--';
        }
        $num = M($tablename)->where(array('id' => $id))->getField('click');
        M($tablename)->where(array('id' => $id))->setInc('click');
        return "$num";
}

//upload_maxsize,KB转成字节
function getUploadMaxsize($size = 2048, $cfg = 'cfg_upload_maxsize') {
    $maxsize = C($cfg);
     if (empty($maxsize)) {
        $maxsize = $size;
    }
    return $maxsize * 1024;
}




/**
*取出所有分类
*
*@param string $modelid 模型ID
*@param string $update 更新缓存(0|1|2)， 默认0不更新,1更新，2是删除
*/
function getDateList($modelid = 1, $update = 0) {//
    $modelid = intval($modelid);
    $arr = array();
    //为[0]或page模型[2]
    if ($modelid == 0 || $modelid == 2) {
        return $arr;
    }
    $format = '%Y-%m';
    $sname = 'fDateList_'. $modelid;
    //删除，直抒返回
    if ($update == 2) {
        F($sname, null);        
        return $arr;
    }
    $arr = F($sname);
    if ($update  || !$arr) {
        $tablename = M('model')->where(array('id' => $modelid))->getField('tablename');
        if ($tablename) {
            $arr = M($tablename)->field("count(*) as arc_num, FROM_UNIXTIME(publishtime,'%Y') as arc_year, FROM_UNIXTIME(publishtime,'%m') as arc_month")->group("FROM_UNIXTIME(publishtime,'".$format."')")->order('publishtime desc')->select();
        }else {
            $arr = array();
        }
        
        if (!isset($arr)) {
            $arr = array();
        }
             
        F($sname, $arr);
    }
    return $arr;   
}





/*get js of City*/
function getJsOfCity() {
    
    $str = <<<str
function setcity() {
    var SelP=document.getElementsByName(arguments[0])[0];
    var SelC=document.getElementsByName(arguments[1])[0];
    var DefP=arguments[2];
    var DefC=arguments[3];
str;

    $province = M('area')->where(array('pid' => 0))->order('sort,id')->select();
    //Province
    $pcount =count($province)-1;//$key 是从0开始的
    $str .= "var provinceOptions = new Array(";
    $str .= '"请选择省份",0';
    foreach ($province as $k => $v) {
       $str .= ',"'. $v['sname'].'",'. $v['id'] .'';
    }
    $str .= " );\n";

    $str .= <<<str
    SelP.options.length = 0;     
    for(var i = 0; i < provinceOptions.length/2; i++) {
        SelP.options[i]=new Option(provinceOptions[i*2],provinceOptions[i*2+1]);
        if(SelP.options[i].value==DefP) {
            SelP.selectedIndex = i;
        }
    }

    SelP.onchange = function(){
        switch (SelP.value) {
str;
 
    foreach ($province as $v) {
        $str .= 'case "'.$v['id'].'" :'."\n";
        //$str .= 'case "'.$v['sname'].'" :'."\n";
        $str .= "var cityOptions = new Array(";
        $city = M('area')->where(array('pid' => $v['id']))->order('sort,id')->select();
        $count =count($city)-1;//$key 是从0开始的
        foreach ($city as $key => $value) {
            $str .= '"'. $value['sname'].'",'. $value['id'] .'';
            if( $key != $count) {
                $str .= ",";//不为最后一个元素，就加上","
            }
        }
        
        
        $str .= " );\n";
        $str .= " break;\n";
    }    


    $str .= <<<str
        default:
            var cityOptions = new Array("");
            break;
        }

        SelC.options.length = 0;     
        for(var i = 0; i < cityOptions.length/2; i++) {
            SelC.options[i]=new Option(cityOptions[i*2],cityOptions[i*2+1]);
            if (SelC.options[i].value==DefC) {
                SelC.selectedIndex = i;
            }
        }  
    } 

    if (DefP) {
        if(SelP.fireEvent) {
        SelP.fireEvent('onchange');
        //alert('ok');
        }else {
            SelP.onchange();
        }
    }    

}
str;

    //echo $str;
    if (file_put_contents('./Data/resource/js/city.js', $str)) {
        return true;
    } else {
       return false;
    }

}



/**
 * getFileFolderList
 *@fileFlag 0 所有文件列表,1只读文件夹,2是只读文件(不包含文件夹)
 */

//获取文件目录列表,该方法返回数组
function getFileFolderList($pathname,$fileFlag = 0, $pattern='*') {
    $fileArray = array();
    $pathname = rtrim($pathname,'/') . '/';
    $list   =   glob($pathname.$pattern);
    foreach ($list  as $i => $file) {
        switch ($fileFlag) {
            case 0:
                $fileArray[]=basename($file);
                break;
            case 1:
                if (is_dir($file)) {
                    $fileArray[]=basename($file);
                }
                break;

            case 2:
                if (is_file($file)) {                    
                    $fileArray[]=basename($file);
                }
                break;
            
            default:
                break;
        }
    }    

    if(empty($fileArray)) $fileArray = NULL;
    return $fileArray;
}


//循环删除目录和文件函数
function delDirAndFile($dirName, $bFlag = false ) {
    if ( $handle = opendir( "$dirName" ) ) {
        while ( false !== ( $item = readdir( $handle ) ) ) {
            if ( $item != "." && $item != ".." ) {
                if ( is_dir( "$dirName/$item" ) ) {
                    delDirAndFile("$dirName/$item", $bFlag);
                } else {
                    unlink( "$dirName/$item" );
                }
            }
        }
        closedir( $handle );
        if($bFlag) rmdir($dirName);
    }
}

/*计算年龄*/
//$birthy 日期:1981-10-5
function birthday2age($birth) {
    list($byear, $bmonth, $bday)=explode('-', $birth);
    $age=date('Y') - $byear - 1;
    $tmonth=date('n');
    $tday=date('j');
    if ($tmonth > $bmonth || $tmonth ==$bmonth && $tday>$bday) $age++;

    return $age;
}


function str2symbol($str, $num =1 ,$sp = '*') {
    if ($str == '' || $num <= 0) {
        return $str;
    }
    $num = mb_strlen($str, 'utf-8') > $num ? $num : mb_strlen($str, 'utf-8');
    $newstr = '';
    for ($i=0; $i < $num; $i++) { 
        $newstr .='*';
    }
    $newstr .= mb_substr($str, $num,mb_strlen($str, 'utf-8') - $num, 'utf-8');//substr中国会乱码

    return $newstr;

}

function str2sub($str, $num, $flag = 0, $sp = '...') {
    if ($str == '' || $num <= 0) {
        return $str;
    }
    $strlen = mb_strlen($str, 'utf-8');
    $newstr ='';
    $newstr .= mb_substr($str, 0, $num, 'utf-8');//substr中国会乱码
    if ($num < $strlen && $flag) {
        $newstr .= $sp;
    }

    return $newstr;
}

//清除分割符之间的空字符'',为'0'字符
//$flag 强制检测各成员是否为数字[true|false]
function string2filter($str, $delimiter = ',', $flag = false) {
    if (empty($str)) {
        return '';
    }

    $tmp_arr = array_filter(explode($delimiter, $str));//去除空数组'',0,再使用sort()重建索引
    $tmp_arr2 = array();

    //检验是不是数字
    if ($flag) {
        foreach ($tmp_arr as $v) {
            if (is_numeric($v)) {
                $tmp_arr2[] = $v;
            }        
        }
    } else {
        $tmp_arr2 = $tmp_arr;
    }
    
    return implode($delimiter, $tmp_arr2);
      
    
}


//flag相加,返回数值，用于查询
function flag2sum($str, $delimiter = ',') {
    if (empty($str)) {
        return 0;
    }
    $tmp_arr = array_filter(explode($delimiter, $str));//去除空数组'',0,再使用sort()重建索引
    if (empty($tmp_arr)) {
        return 0;
    }

    $arr = array('a' => B_PIC, 'b' => B_TOP, 'c' => B_REC, 'd' => B_SREC, 'e' => B_SLIDE, 'f' => B_JUMP, 'g' => B_OTHER);
    $sum = 0;
    foreach ($arr as $k => $v) {
        if (in_array($k, $tmp_arr)) {
            $sum += $v;
        }
    }

    return $sum;


}


function checkBadWord($content){                  //定义处理违法关键字的方法
    $badword = C('cfg_badword'); //定义敏感词

    if (empty($badword )) {
        return false;
    }
    $keyword = explode('|',$badword);
    $m = 0;
    for($i = 0; $i < count ( $keyword ); $i ++) {   //根据数组元素数量执行for循环
        //应用substr_count检测文章的标题和内容中是否包含敏感词
        if (substr_count ( $content, $keyword [$i] ) > 0) {
            //$m ++;
           return true;
        }
    }
    //return $m;              //返回变量值，根据变量值判断是否存在敏感词
    return false;
}



/**
 * 对用户的密码进行加密
 * @param $password
 * @param $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function get_password($password, $encrypt='') {
    $pwd = array();
    $pwd['encrypt'] =  $encrypt ? $encrypt : get_randomstr();
    $pwd['password'] = md5(md5(trim($password)).$pwd['encrypt']);
    return $encrypt ? $pwd['password'] : $pwd;
}

/**
 * 生成随机字符串
 * @param string $lenth 长度
 * @return string 字符串
 */
function get_randomstr($lenth = 6) {
    return get_random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
}

/**
* 产生随机字符串
*
* @param    int        $length  输出长度
* @param    string     $chars   可选的 ，默认为 0123456789
* @return   string     字符串
*/
function get_random($length, $chars = '0123456789') {
    $hash = '';
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

/**
 * 得到指定cookie的值
 * 
 * @param string $name
 */
//function get_cookie($name, $key = '@^%$y5fbl') {
function get_cookie($name, $key = '') {
    
    if (!isset($_COOKIE[$name])) {
        return null;
    }
    $key = empty($key)? C('cfg_cookie_encode') : $key;

    $value = $_COOKIE[$name];
    import('Class.Syscrypt', APP_PATH);
    $key=md5($key);
    $sc = new SysCrypt($key);
    $value=$sc->php_decrypt($value);
    return unserialize($value);
}

/**
 * 设置cookie
 *
 * @param array $args
 * @return boolean
 */
//使用时修改密钥$key 涉及金额结算请重新设计cookie存储格式
//function set_cookie($args , $key = '@^%$y5fbl') {
function set_cookie($args , $key = '') {
    $key = empty($key)? C('cfg_cookie_encode') : $key;

    $name = $args['name'];
    $expire = isset($args['expire']) ? $args['expire'] : null;
    $path = isset($args['path']) ? $args['path'] : '/';
    $domain = isset($args['domain']) ? $args['domain'] : null;
    $secure = isset($args['secure']) ? $args['secure'] : 0;    
    $value = serialize($args['value']);

    import('Class.Syscrypt', APP_PATH);
    $key = md5($key);
    $sc = new SysCrypt($key);
    $value =$sc->php_encrypt($value);
    //setcookie($cookieName ,$cookie, time()+3600,'/','',false);
    return setcookie($name, $value, $expire, $path, $domain, $secure);//失效时间   0关闭浏览器即失效
}

/**
 * 删除cookie
 * 
 * @param array $args
 * @return boolean
 */
function del_cookie($args){
    $name = $args['name'];
    $domain = isset($args['domain']) ? $args['domain'] : null;
    return isset($_COOKIE[$name]) ? setcookie($name, '', time() - 86400, '/', $domain) : true;
}

/*
*avatart
*/
function get_avatar($str, $size = 160, $rnd = false) {
    
    $ext = 'jpg';
    if (!empty($str)) {
        $ext = explode('.', $str);
        $ext = end($ext);
    }
    
    if (empty($ext) || !in_array(strtolower($ext), array('jpg','gif','png','jpeg'))) {
        $str = '';
    }
    if (empty($str)) {
        $str = __ROOT__.'/avatar/system/0.jpg';
        $ext = 'jpg';
        if ($size > 160 || $size < 30) {
            $size = 160;
        }
    }
   
    if ($size == 0) {
        return $str;
    }
    $rndstr = $rnd ? '?random='.time() : '';
    return $str.'!'.$size. 'X' .$size. '.'. $ext. $rndstr ;
}

/*
*pic
*/
function get_picture($str, $width = 0, $height = 0, $rnd = false) {

    //$ext = end(explode('.', $str));
    $ext = 'jpg';//原文件后缀
    $ext_dest = 'jpg';//生成缩略图格式
    $height = $height == 0? '' : $height;
    if (!empty($str)) {
        $str = preg_replace('/!(\d+)X(\d+)\.'.$ext_dest.'$/i', '', $str);//清除缩略图的!200X200.jpg后缀

        $ext = explode('.', $str);
        $ext = end($ext);
    }
    if (empty($ext) || !in_array(strtolower($ext), array('jpg','gif','png','jpeg'))) {
        $str = '';
    }
    if (empty($str)) {        
        $str =  __ROOT__.'/uploads/system/nopic.png' ;
        $ext = 'png';
        $ext_dest = 'png';
        $width = 0;
    }
    if ($width == 0) {
        return $str;
    }

    $rndstr = $rnd ? '?random='.time() : '';
    return $str.'!'.$width. 'X' .$height. '.'. $ext_dest. $rndstr ;
}



/**
 * 功能：计算文件大小
 * @param int $bytes
 * @return string 转换后的字符串
 */
function get_byte($bytes) {
    if (empty($bytes)) {
        return '--';
    }
    $sizetext = array(" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $sizetext[$i];
}

/**
 *  获取拼音信息
 *
 * @access    public
 * @param     string  $str  字符串
 * @param     int  $ishead  是否为首字母
 * @param     int  $isclose  解析后是否释放资源
 * @return    string
 */
 ////英文全称
//$data['EnglishName'] = $this->get_pinyin(iconv('utf-8','gbk//ignore',$utfstr),0);
function get_pinyin($str, $ishead=0, $isclose=1)
{
    //global $pinyins;
    $pinyins = array();
    $restr = '';
    $str = trim($str);
    $slen = strlen($str);
    //$str=iconv("UTF-8","gb2312",$str);
    //echo $str;
    if($slen < 2)
    {
        return $str;
    }
    if(count($pinyins) == 0)
    {
        $fp = fopen('./Data/pinyin.dat', 'r');
        if (false == $fp) {
        	return '';
        }
        while(!feof($fp))
        {
            $line = trim(fgets($fp));
            $pinyins[$line[0].$line[1]] = substr($line, 3, strlen($line)-3);
        }
        fclose($fp);
    }


    
    for($i=0; $i<$slen; $i++)
    {
        if(ord($str[$i])>0x80)
        {
            $c = $str[$i].$str[$i+1];
            $i++;
            if(isset($pinyins[$c]))
            {
                if($ishead==0)
                {
                    $restr .= $pinyins[$c];
                }
                else
                {
                    $restr .= $pinyins[$c][0];
                }
            }else
            {
                $restr .= "_";
            }
        }else if( preg_match("/[a-z0-9]/i", $str[$i]) )
        {
            $restr .= $str[$i];
        }
        else
        {
            $restr .= "_";
        }
    }
    if($isclose==0)
    {
        unset($pinyins);
    }
    return $restr;
}



/*
*getTpl 获取模板地址
*
*/
function get_tpl($tpl = '', $style = '') {
    $groupName = ucfirst(strtolower(GROUP_NAME));
    //$tplPath = './Public/';
    $tplPath = APP_PATH.'Group/'.$groupName.'/Tpl/';
    $tplPath .= empty($style) ? C('cfg_themestyle'). '/' : $style. '/';

 
    if (trim($tpl) == '') {
       $tplPath .= MODULE_NAME. C('TMPL_FILE_DEPR'). ACTION_NAME. C('TMPL_TEMPLATE_SUFFIX');
    }elseif (strpos($tpl, '.') > 0) {
        $ext = explode('.', $tpl);
        $ext = end($ext);
        $tplPath .= str_replace('.'.$ext, '', $tpl). C('TMPL_TEMPLATE_SUFFIX');
    }elseif (strpos($tpl, ':') > 0){
        $tpl_array = explode(':', $tpl);
        $tplPath .= $tpl_array[0]. C('TMPL_FILE_DEPR'). $tpl_array[1]. C('TMPL_TEMPLATE_SUFFIX');
    }else{
        
        $tplPath .= MODULE_NAME. C('TMPL_FILE_DEPR').$tpl. C('TMPL_TEMPLATE_SUFFIX');
    }
    

    return $tplPath;

}




/**goto mobile*/
function goMobile() {
    $mobileAuto = C('cfg_mobile_auto');
    if ($mobileAuto == 1) {
        $wap2web = I('wap2web', 0, 'intval');//手机访问电脑版
        $agent = $_SERVER['HTTP_USER_AGENT'];   
        if ($wap2web != 1) {
            if(strpos($agent,"comFront") || strpos($agent,"iPhone") || strpos($agent,"MIDP-2.0") || strpos($agent,"Opera Mini") || strpos($agent,"UCWEB") || strpos($agent,"Android") || strpos($agent,"Windows Phone") || strpos($agent,"Windows CE") || strpos($agent,"SymbianOS"))
            {
                header('Location:'.U('Mobile/Index/index').'');
            }
        }   
    }
    
}

function goLinkEncode($weburl = 'http://www.0871k.com/') {
    return U(C('DEFAULT_GROUP'). '/Go/link',array('url' => base64_encode($weburl)));
}



/**
 * D2是D方法的扩展
 * D2函数用于实例化Model 格式 项目://分组/模块 
 * @param string $name Model资源地址
 * @param string $tableName 数据表名
 * @param string $layer 业务层名称
 * @return Model
 */
function D2($name='',$tableName='',$layer='') {
    if(empty($name)) return new Model;
    static $_model  =   array();
    $layer          =   $layer?$layer:C('DEFAULT_M_LAYER');
    if(strpos($name,'://')) {// 指定项目
        list($app)  =   explode('://',$name);
        $name       =   str_replace('://','/'.$layer.'/',$name);
    }else{
        $app        =   C('DEFAULT_APP');
        $name       =   $app.'/'.$layer.'/'.$name;
    }
    $_name = empty($tableName)? $name :  $name .'/'.$tableName;
   
    if(isset($_model[$_name]))   return $_model[$_name];
    $path           =   explode('/',$name);
    if($list = C('EXTEND_GROUP_LIST') && isset($list[$app])){ // 扩展分组
        $baseUrl    =   $list[$app];
        import($path[2].'/'.$path[1].'/'.$path[3].$layer,$baseUrl);
    }elseif(count($path)>3 && 1 == C('APP_GROUP_MODE')) { // 独立分组
        $baseUrl    =   $path[0]== '@' ? dirname(BASE_LIB_PATH) : APP_PATH.'../'.$path[0].'/'.C('APP_GROUP_PATH').'/';
        import($path[2].'/'.$path[1].'/'.$path[3].$layer,$baseUrl);
    }else{
        import($name.$layer);
    } 
    $class          =   basename($name.$layer);
    if(class_exists($class)) {
        $model      = empty($tableName)? new $class(basename($name)) :  new $class(basename($tableName),$tableName)  ;
    }else {
        $model      =   new Model(basename($name));
    }
    $_model[$_name]  =  $model;
    return $model;
}



/*
*提示信息
*$msg   信息内容
*$title 页面title
*/
function exitMsg($msg = '', $title = '提示') {
    $msg = nl2br($msg);
    $str = <<<str
<!DOCTYPE html><html><head><meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'/> 
<title>{$title}</title>
<style type="text/css">
body{background:#fff;font-family: 'Microsoft YaHei'; color: #333;}
.info{width:90%;font-size:100%; line-height:150%; margin:20px auto; padding:10px;border:solid 1px #ccc;}
</style>
</head>
<body>  
<div class="info">{$msg}</div>
</body>
</html>
str;
    echo $str;
    exit();
}

/**********
* 发送邮件 *
**********/
/**
*发送邮件
*
* @param    string   $address       地址
* @param    string    $title 标题
* @param    string    $message 邮件内容
* @param    string $attachment 附件列表
* @return   boolean 
*/
function SendMail($address, $title, $message, $attachment = null)
{
    vendor('PHPMailer.class#phpmailer');

    $mail = new PHPMailer;
    //$mail->Priority = 3;
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();
    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet='UTF-8';
    $mail->SMTPDebug  = 0; // 关闭SMTP调试功能
    $mail->SMTPAuth   = true; // 启用 SMTP 验证功能
   // $mail->SMTPSecure = 'ssl';  // 使用安全协议
    $mail->IsHTML(true);//body is html

    // 设置SMTP服务器。
    $mail->Host=C('cfg_email_host');
    $mail->Port = C('cfg_email_port') ? C('cfg_email_port') : 25 ;  // SMTP服务器的端口号

    // 设置用户名和密码。
    $mail->Username =C('cfg_email_loginname');
    $mail->Password = C('cfg_email_password');

    // 设置邮件头的From字段
    $mail->From=C('cfg_email_from');
    // 设置发件人名字
    $mail->FromName = C('cfg_email_from_name');

    // 设置邮件标题
    $mail->Subject=$title;
    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($address);
    // 设置邮件正文
    $mail->Body=$message;
    // 添加附件
    if(is_array($attachment)){ 
        foreach ($attachment as $file){
            is_file($file) && $mail->AddAttachment($file);
        }
    }



    // 发送邮件。
    //return($mail->Send());
    return $mail->Send() ? true : $mail->ErrorInfo;
}


function isDate($str,$format="Y-m-d"){
    $unixTime_1 = strtotime($str);//strtotime 成功则返回时间戳，否则返回 FALSE。在 PHP 5.1.0 之前本函数在失败时返回 -1
    if ( !is_numeric($unixTime_1) || $unixTime_1 == -1) return false;
    $checkDate = date($format, $unixTime_1);
    $unixTime_2 = strtotime($checkDate);;
    if($unixTime_1 == $unixTime_2){
        return true;
    }else{
        return false;
    }
}

/**
*将字符串转换为数组
*
*@param string $data 字符串
*/
function string2array($data) {
	if($data == '') return array();
	@eval("\$array = $data;");
	return $array;
}

/**
*将数组转换为字符串
*
*@param    array   $data       数组
*@param    bool    $isformdata 如果为0，则不使用new_stripslashes处理，可选参数，默
*/

function array2string($data, $isformdata =1) {
	if($data == '') return '';
	if($isformdata) $data = new_stripslashes($data);
	return addslashes(var_export($data, true));
}


function new_stripslashes($string) {
    if(!is_array($string)) return stripslashes($string);
    foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
    return $string;

}

?>