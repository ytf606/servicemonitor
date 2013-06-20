<?php
class my_mysql
{
    private $_db_table  = 'sae_checker_';
    private $_db_host   = array( 'm'=>SAE_MYSQL_HOST_M, 's'=>SAE_MYSQL_HOST_S );
    private $_db        = array( 'm'=>null, 's'=>null );
    public function __construct(){
        $this->_db_table .= mt_rand(10,99) . '_' . date('YmdH');
    }
    private function db_handle( $type ){
        if( !$this->_db[$type] ){
            $mysqli = new mysqli( 
                                    $this->_db_host[$type], 
                                    SAE_MYSQL_USER, 
                                    SAE_MYSQL_PASS, 
                                    SAE_MYSQL_DB, 
                                    SAE_MYSQL_PORT
                                );
            if( mysqli_connect_errno() )
                return false;
            $this->_db[$type] = $mysqli;
        }
        else{
            if( !$this->_db[$type]->ping() )
                return false;
        }
        return $this->_db[$type];
    }
    public function mysql_connect_m(){
        if( $this->db_handle('m') ) return error(0, 'success');
        else return error(-1, 'connect m database failed');
    }
    public function mysql_connect_s(){
        if( $this->db_handle('s') ) return error(0, 'success');
        else return error(-2, 'connect s database failed');
    }
    public function mysql_create_table(){
        $drop_sql = 'DROP TABLE `'.$this->_db_table.'`';
        if( $this->table_exsits('m') ){
            $this->db_handle('m')->query($drop_sql);
        }
        if( $this->need_table('m') ){
            if( $this->table_exsits('m') )
                return error(0, 'success');
            else
                return error(-3, 'create table error');
        }
        return error(-4, 'create table error');
    }
    public function mysql_insert( $ret_type = 'defalut'){
        $this->need_table( 'm' );
        $key = 'key_' . mt_rand(100,999);
        $value = 'value_' . mt_rand(10000,99999);
        $sql = "insert into `".$this->_db_table."` ( `selfchk_id` , `selfchk_value` ) ";
        $sql.= "VALUES ( '$key' , '$value' )";
        $this->db_handle('m')->query( $sql );
        $sql = "select selfchk_value from `".$this->_db_table."` where selfchk_id='$key' limit 0,1";
        $result = $this->db_handle('m')->query( $sql );
        if( !$result ) return error(-5, 'insert into table error');
        $result = $result->fetch_assoc();
        if( $result['selfchk_value']==$value && $ret_type == 'key')
            return $key;
		elseif( $result['selfchk_value']==$value && $ret_type == 'array')
            return array($key=>$value);
        elseif( $result['selfchk_value']==$value )
            return error(0, 'success');
        else
            return error(-6, 'failed to insert table');
    }
    public function mysql_sync(){
		sleep(2);
        list($key, $value) = $this->mysql_insert( 'array' );
        $sql 	= "select selfchk_value from `".$this->_db_table."` where selfchk_id='$key' limit 0,1";
        $result = $this->db_handle('s')->query( $sql );
        if( !$result ) return error(-7, 'sync table data failed');
        $result = $result->fetch_assoc();
        if( $result['selfchk_value']==$value )
            return error(0, 'success');
        else
            return error(-8, 'mysql sync result failed');
    }
    public function mysql_update(){
        $key = $this->mysql_insert( 'key' );
        $value = mt_rand(10000,99999);
        $sql = "update `".$this->_db_table."` set selfchk_value='$value' ";
        $sql.= "where selfchk_id='$key'";
        $this->db_handle( 'm' )->query( $sql );
        $sql = "select selfchk_value from `".$this->_db_table."` where selfchk_id='$key' limit 0,1";
        $result = $this->db_handle('m')->query( $sql );
        if( !$result ) return error(-9, 'update data failed');
        $result = $result->fetch_assoc();
        if( $result['selfchk_value']==$value )
            return error(0, 'success');
        else
            return error(-10, 'mysql update data failed');
    }
    public function mysql_delete(){
        $key = $this->mysql_insert( 'key' );
        $sql = "delete from `".$this->_db_table."` where selfchk_id='$key'";
        $this->db_handle( 'm' )->query( $sql );
        $sql = "select count(*) as count from `".$this->_db_table."` where selfchk_id='$key' limit 0,1";
        $result = $this->db_handle('m')->query( $sql );
        if( !$result ) return error(-11, 'mysql delete data failed');
        $result = $result->fetch_assoc();
        if( $result && $result['count']==0 )
            return error(0, 'success');
        else
            return error(-11, 'mysql delete data failed');
    }
    public function mysql_truncate(){
        $this->need_table( 'm' );
        $sql = "truncate table `".$this->_db_table."`";
        $this->db_handle( 'm' )->query( $sql );
        $sql = "select count(*) from `".$this->_db_table."` where 1;";
        $result = $this->db_handle('m')->query( $sql );
        if( !$result ) return error(-12, 'truncate mysql error');
        $result = $result->fetch_assoc();
        if( $result && $result['count']==0 )
            return error(0, 'success');
        else
            return error(-13, 'mysql truncate failed');
    }
    public function mysql_droptable(){
        $this->need_table( 'm' );
        $sql = "drop table `".$this->_db_table."`";
        $this->db_handle( 'm' )->query( $sql );
        if( !$this->table_exsits('m') )
            return error(0, 'success');
        else
            return error(-14, 'droptable data failed');
    }
    private function need_table( $type='m' ){
        $create_sql = 'CREATE TABLE `app_'.SAE_APPNAME.'`.`'.$this->_db_table.'` (
                        `selfchk_id` VARCHAR( 80 ) NOT NULL ,
                        `selfchk_value` TEXT NULL ,
                        PRIMARY KEY ( `selfchk_id` )
                      ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        if( !$this->table_exsits($type) ){
            if( !$this->db_handle( $type )->query( $create_sql ) )
                return false;
        }
        return true;
    }
    private function table_exsits( $type='m' ){
        $sql = "show tables;";
        if( $result=$this->db_handle($type)->query($sql) ){
            $tag = false;
            while($row = $result->fetch_assoc()){
                if( current($row)==$this->_db_table ){
                    $tag=true;
                    break;
                }
            }
            return $tag;
        }
        return false;  
    }
    public function __destruct(){
        foreach( $this->_db as $type=>$db ){
            if( $db ){
                if( $type=='m' && $this->table_exsits('m') ){
                    $db->query('drop table `'.$this->_db_table.'`');
                }
                $db->close();    
            }
        }
    }
}
?>
