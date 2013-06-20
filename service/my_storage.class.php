<?php
class my_storage
{
    private $stor;
    private $domain = 'monitor';
    private $file_name;
    private $file_data;
    public function __construct(){
        //if( !($this->domain=v('domain')) )
        //    die('domain not exsits.');
        $this->file_name = 'sae_checker/storage_checker/tmp_' . mt_rand(10000,99999) . '.jpg';
        $this->file_data = pub_data();
    }
    private function stor(){
        if( !$this->stor )
            $this->stor = new SaeStorage();
        return $this->stor;
    }
    public function storage_write( $for_other_method=false ){
        $this->stor()->write( 
                                $this->domain, 
                                $this->file_name, 
                                $this->file_data
                            );
        $get = $this->stor()->read( $this->domain, $this->file_name);
        if( $get==$this->file_data && $for_other_method )
            return true;
        elseif( $get==$this->file_data )
            return error(0, 'success');
        elseif( $for_other_method )
            return false;
        else
            return error(-1, 'write to storage failed');
    }
    public function storage_read(){
        if( $this->storage_write(true) )
            return error(0, 'success');
        else
            return error(-2, 'read from storage failed');
    }
    public function storage_update(){
        $tmp_file = '/sae_checker.jpg';
        file_put_contents( SAE_TMP_PATH.$tmp_file, $this->file_data );
        $this->stor()->delete( $this->domain, $this->file_name );
        $this->stor()->upload( 
                                $this->domain,
                                $this->file_name,
                                SAE_TMP_PATH.$tmp_file
                             );
        $get = $this->stor()->read( $this->domain, $this->file_name);
        if( $get==$this->file_data )
            return error(0, 'success');
        else
            return error(-3, 'update storage failed');
    }
    public function storage_delete(){
        if( !$this->stor()->fileExists($this->domain,$this->file_name) ){
            $this->stor()->write( $this->domain, $this->file_name, '123' );
        }
        if( !$this->stor()->fileExists($this->domain,$this->file_name) )
            return error(-5, 'file exists from stroage failed'); 
        $this->stor()->delete( $this->domain, $this->file_name );
        if( !$this->stor()->fileExists($this->domain,$this->file_name) )
            return error(0, 'success');
        else
            return error(-6, 'delete file from storage failed');
    }
    public function __destruct(){
        if( $this->stor && $this->stor()->fileExists($this->domain,$this->file_name) )
            $this->stor()->delete( $this->domain, $this->file_name );
    }
}
?>
