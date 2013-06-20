<?php
class my_fetchurl
{
    private $contents = 'sae';
    private $fch;
    public function __construct(){
        if( $_REQUEST['action'] == 'trigger' )
            return $this->trigger();
    }
    private function trigger(){
        die( $this->contents );
    }
    private function fch(){
        if( !$this->fch )
            $this->fch = new SaeFetchurl();
        return $this->fch;
    }
    public function fetchurl_fetch(){
        $url = $_SERVER['SCRIPT_URI'].'?service=fetchurl&action=trigger&token=123456';
        $back= $this->fch()->fetch( $url );
        if( $back == $this->contents ) {
            return error(0, 'success');
        } else {
            return error(-2, 'fetch a url failed');
        }
    }
}

?>
