<?php
class my_mc 
{
    private $_mc;
    private $_mc_key;
    public function __construct()
    {
        $this->_mc_key = 'sae_checker_automake_'.mt_rand(1,99).date('YmdHi');
    }
    private function mc_handle()
    {
        if( !$this->_mc ) $this->_mc=memcache_init();
        return $this->_mc;
    }
    public function mc_connect()
    {
        if( $this->mc_handle() ) return error(0, 'success');
        else return error(-1, 'memcache init failed');
    }
    public function mc_set()
    {
        $value = mt_rand(100,999);
        memcache_set( $this->mc_handle(), $this->_mc_key, $value, null, 30 );
        $rep = memcache_get( $this->mc_handle(), $this->_mc_key );
        if( $rep==$value ) return error(0, 'success');
        else return error(-2, 'memcache set failed');
    }
}
?>

