<?php

class DatabaseAction extends CommonAction {

    public $config = '';
    // 列出所有数据表信息
    public function index() {
        //query是查功能，execute是增删改功能
        $dbtables = M()->query('SHOW TABLE STATUS');
        $total = 0;
        foreach ($dbtables as $k => $v) {
            $dbtables[$k]['size'] = get_byte($v['Data_length'] + $v['Index_length']);
            $total+=$v['Data_length'] + $v['Index_length'];
        }
        $this->vlist = $dbtables;
        $this->total = get_byte($total);
        $this->tableNum = count($dbtables);
        $this->type = '数据表列表';
        $this->display();
    }

    //备份数据库
    public function backup() {
        if (!IS_POST){
            $this->error("Access Denied");
        }            
        $M = M();
        //防止备份数据过程超时
        function_exists('set_time_limit') && set_time_limit(0); 
        $tables = I('key', array(), '');
        if (empty($tables)) {
            $this->error('请选择要备份的数据表');
        }

        $time = time();//开始时间
        $path = $this->getDbPath() . "/yycmstables_" . date("Ymd") . "_" . get_randomstr(5);
     
        $pre = "# -----------------------------------------------------------\n";
        //取得表结构信息

        //1，表示表名和字段名会用``包着的,0 则不用``
 
        //M()->query("SET SQL_QUOTE_SHOW_CREATE = 1"); //Log会有警告信息DbMysql.class.php(109|80)        
        M()->execute("SET SQL_QUOTE_SHOW_CREATE = 1"); 
        $outstr = '';
       
        foreach ($tables as $table) {
            $outstr.="# 表的结构 {$table} \n";
            $outstr .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $tmp = $M->query("SHOW CREATE TABLE {$table}");
            $outstr .= $tmp[0]['Create Table'] . " ;\n\n";
        }
        $sqlTable = $outstr;
        $outstr = "";
        $file_n = 1;
        $backedTable = array();
        //表中的数据
        foreach ($tables as $table) {
            $backedTable[] = $table;
            $outstr.="\n\n# 转存表中的数据：{$table} \n";
            $tableInfo = $M->query("SHOW TABLE STATUS LIKE '{$table}'");
            $page = ceil($tableInfo[0]['Rows'] / 10000) - 1;
            for ($i = 0; $i <= $page; $i++) {
                $query = $M->query("SELECT * FROM {$table} LIMIT " . ($i * 10000) . ", 10000");
                foreach ($query as $val) {
                    $temSql = "";
                    $tn = 0;
                    $temSql = '';
                    foreach ($val as $v) {
                        $temSql.=$tn == 0 ? "" : ",";
                        $temSql.=$v == '' ? "''" : "'{$v}'";
                        $tn++;
                    }
                    $temSql = "INSERT INTO `{$table}` VALUES ({$temSql});\n";

                    $sqlNo = "\n# Time: " . date("Y-m-d H:i:s") . "\n" .
                            "# -----------------------------------------------------------\n" .
                            "# SQLFile Label：#{$file_n}\n# -----------------------------------------------------------\n\n\n";
                       if ($file_n == 1) {
                        $sqlNo = "# Description:备份的数据表[结构]：" . implode(",", $tables) . "\n".
                                 "# Description:备份的数据表[数据]：" . implode(",", $backedTable) . $sqlNo;
                    } else {
                        $sqlNo = "# Description:备份的数据表[数据]：" . implode(",", $backedTable) . $sqlNo;
                    }

                    if (strlen($pre) + strlen($sqlNo) + strlen($sqlTable) + strlen($outstr) + strlen($temSql) > C("USER_SQL_FILESIZE")) {
                        $file = $path . "_" . $file_n . ".sql";
                        $outstr = $file_n == 1 ? $pre . $sqlNo . $sqlTable . $outstr : $pre . $sqlNo . $outstr;
                       
                        if (!file_put_contents($file, $outstr, FILE_APPEND)) {
                            $this->error("备份文件写入失败！", U('Database/index'));
                        }
    
                        $sqlTable = $outstr = "";
                        $backedTable = array();
                        $backedTable[] = $table;
                        $file_n++;
                    }
                    $outstr.=$temSql;
                }
            }
        }
        if (strlen($sqlTable . $outstr) > 0) {
            $sqlNo = "\n# Time: " . date("Y-m-d H:i:s") . "\n" .
                    "# -----------------------------------------------------------\n" .
                    "# SQLFile Label：#{$file_n}\n# -----------------------------------------------------------\n\n\n";
            if ($file_n == 1) {
                $sqlNo = "# Description:备份的数据表[结构] " . implode(",", $tables) . "\n".
                         "# Description:备份的数据表[数据] " . implode(",", $backedTable) . $sqlNo;
            } else {
                $sqlNo = "# Description:备份的数据表[数据] " . implode(",", $backedTable) . $sqlNo;
            }
            $file = $path . "_" . $file_n . ".sql";
            $outstr = $file_n == 1 ? $pre . $sqlNo . $sqlTable . $outstr : $pre . $sqlNo . $outstr;
            //file_put_contents($file, $outstr, FILE_APPEND);
            if (!file_put_contents($file, $outstr, FILE_APPEND)) {
                $this->error("备份文件写入失败！" ,U('Database/index'));
            }
           

            $file_n++;
        }
        $time = time() - $time;
        $this->success("成功备份数据表，本次备份共生成了" . ($file_n - 1) . "个SQL文件。耗时：{$time} 秒",  U('Database/restore'));
    }

    /**
     * 还原数据库内容
     */
    public function restore() {

               
        $size = 0;
        $pattern = "*.sql";
        $filelist = glob($this->getDbPath().'/'.$pattern);
        $fileArray = array(); 
        foreach ($filelist  as $i => $file) {
            //只读取文件
            if (is_file($file)) { 
                $_size = filesize($file);    
                $size += $_size;
                $name = basename($file);  
                $pre = substr($name, 0, strrpos($name, '_')); 
                $number = str_replace(array($pre. '_', '.sql'), array('', ''), $name);     
                $fileArray[] = array(
                    'name' => $name,
                    'pre' => $pre,
                    'time' => filemtime($file),
                    'size' => $size,
                    'number' => $number,
                );
            }
        }   

        if(empty($fileArray)) $fileArray = array();        
        krsort($fileArray); //按备份时间倒序排列
        $this->vlist = $fileArray;
        $this->total = get_byte($size);
        $this->filenum = count($fileArray);
        $this->type = '备份文件列表';
        $this->display();
    }

    //读取要导入的sql文件列表并排序后插入SESSION中
    private function getRestoreFiles() {
        
        $sqlfilepre = I('sqlfilepre', '');//获取sql文件前缀
        if (empty($sqlfilepre)) {
            $this->error('请选择要还原的数据文件！');
        }
        $pattern=$sqlfilepre. "*.sql";
        $sqlFiles = glob($this->getDbPath().'/'.$pattern);  
        if (empty($sqlFiles)) {
            $this->error('不存在对应的SQL文件！');
        }
            
        //将要还原的sql文件按顺序组成数组，防止先导入不带表结构的sql文件
        $files = array();
        foreach ($sqlFiles as $sqlFile) {
            $sqlFile = basename($sqlFile);
            $k = str_replace(".sql", "", str_replace($sqlfilepre . "_", "", $sqlFile));
            $files[$k] = $sqlFile;
        }
        unset($sqlFiles, $sqlfilepre);
        ksort($files);
        return $files;
    }

    //执行还原数据库操作
    public function restoreData() {
        //ini_set("memory_limit", "256M");
        function_exists('set_time_limit') && set_time_limit(0); //防止备份数据过程超时
        //取得需要导入的sql文件
        if (!isset($_SESSION['cacheRestore']['files'])) {
            $_SESSION['cacheRestore']['starttime'] = time();
            $_SESSION['cacheRestore']['files'] = $this->getRestoreFiles();
        }        
        $files = $_SESSION['cacheRestore']['files'] ;
        if (empty($files)) {
            unset($_SESSION['cacheRestore']);
            $this->error('不存在对应的SQL文件');
        }
      
        //取得上次文件导入到sql的句柄位置
        $position = isset($_SESSION['cacheRestore']['position']) ? $_SESSION['cacheRestore']['position'] : 0;
        $M = M();
        $execute = 0;
        foreach ($files as $fileKey => $sqlFile) {        
        
            $file = $this->getDbPath() .'/'. $sqlFile;
          
            if (!file_exists($file))
                continue;
            $file = fopen($file, "r");
            $sql = "";
            fseek($file, $position); //将文件指针指向上次位置
            while (!feof($file)) {

                $tem = trim(fgets($file));
                //过滤,去掉空行、注释行(#,--)
                if (empty($tem) || $tem[0] == '#' || ($tem[0] == '-' && $tem[1] == '-'))
                    continue;
                //统计一行字符串的长度
                $end = (int) (strlen($tem) - 1);
                //检测一行字符串最后有个字符是否是分号，是分号则一条sql语句结束，否则sql还有一部分在下一行中

                if ($tem[$end] == ";") {
                    $sql.=$tem;                                        
                    $M->execute($sql);//query
                    $sql = "";
                    $execute++;
                    if ($execute > 500) {
                        $_SESSION['cacheRestore']['position'] = ftell($file);
                        $imported = isset($_SESSION['cacheRestore']['imported']) ? $_SESSION['cacheRestore']['imported'] : 0;
                        $imported += $execute;
                        $_SESSION['cacheRestore']['imported'] = $imported;
                        //echo json_encode(array("status" => 1, "info" => '如果导入SQL文件卷较大(多)导入时间可能需要几分钟甚至更久，请耐心等待导入完成，导入期间请勿刷新本页，当前导入进度：<font color="red">已经导入' . $imported . '条Sql</font>', "url" => U('Database/restoreData', array(get_randomstr(5) => get_randomstr(5)))));
                        $this->success('如果SQL文件卷较大(多),则可能需要几分钟甚至更久,<br/>请耐心等待完成，<font color="red">请勿刷新本页</font>，<br/>当前导入进度：<font color="red">已经导入' . $imported . '条Sql</font>', U('Database/restoreData', array(get_randomstr(5) => get_randomstr(5))));                       
                        exit();
                    }
                } else {
                    $sql.=$tem;
                }
            }
            fclose($file);
            unset($_SESSION['cacheRestore']['files'][$fileKey]);
            $position = 0;
        }
        $time = time() - $_SESSION['cacheRestore']['starttime'];
        unset($_SESSION['cacheRestore']);
        $this->success("导入成功，耗时：{$time} 秒钟", U('Database/restore'));
    }


    //删除sql文件
    public function delSqlFiles() {

        $id = I('id',0 , 'intval');
        $batchFlag = I('get.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
           $files = I('key', array());
        }else {
            $files[] = I('sqlfilename' , '');
        }

        if (empty($files)) {
            $this->error('请选择要删除的sql文件');
        }
        
        foreach ($files as $file) {
            unlink($this->getDbPath(). '/' . $file);
        }
        $this->success("已删除：" . implode(",", $files), U('Database/restore'));

    }

    //优化
    public function optimize() {

        $id = I('id',0 , 'intval');
        $batchFlag = I('get.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
           $table = I('key', array());
        }else {
            $table[] = I('tablename' , '');
        }

        if (empty($table)) {
            $this->error('请选择要优化的表');
        }
        
        $strTable = implode(', ', $table);
        if (!M()->query("OPTIMIZE TABLE {$strTable} ")) {
            $strTable = '';
        }

        $this->success("优化表成功" . $strTable, U('Database/index'));

    }

    //修复
    public function repair() {

        $id = I('id',0 , 'intval');
        $batchFlag = I('get.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
           $table = I('key', array());
        }else {
            $table[] = I('tablename' , '');
        }

        if (empty($table)) {
            $this->error('请选择修复的表');
        }

        $strTable = implode(', ', $table);
        if (!M()->query("REPAIR TABLE {$strTable} ")) {
            $strTable = '';
        }

        $this->success("修复表成功" . $strTable, U('Database/index'));

    }



    

    public function downFile() {
        if (empty($_GET['file']) || empty($_GET['type']) || !in_array($_GET['type'], array("zip", "sql"))) {
            $this->error("下载地址不存在");
        }
        $path = array("zip" => $this->getDbPath() . "Zip/", "sql" => $this->getDbPath(). '/');
        $filePath = $path[$_GET['type']] . $_GET['file'];
        if (!file_exists($filePath)) {
            $this->error("该文件不存在，可能是被删除");
        }
        $filename = basename($filePath);
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header("Content-Length: " . filesize($filePath));
        readfile($filePath);
    }


    //返回数据目录
    public function getDbPath() {
        return C('USER_DATA_PATH'). '/resource/backupdata';
    }

}