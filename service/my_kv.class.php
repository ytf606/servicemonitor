<?php
class my_kv
{
    private $kv;
    private $key;
    private $value;
    public function __construct()
    {
        $this->key = 'sae_checker_' . mt_rand(100,999);
        $this->value = mt_rand(10000,99999);
    }
    private function init()
    {
        if( !$this->kv )
            $this->kv = new SaeKV();
        return $this->kv->init();
    }
    public function kv_init()
    {
        if( $this->init() ) return error(0, 'success');
        else    return error(-1, 'kvdb init failed');
    }
    public function kv_add(){
        $this->init();
        $r = $this->kv->add( $this->key , $this->value );
        if( $this->value == $this->kv->get($this->key) )
            return error(0, 'success');
        else
            return error(-2, 'add a new key failed to kvdb');
    }
    public function kv_set( $for_other_method=false )
    {
        $this->init();
        $this->kv->set( $this->key , $this->value );
        $saved = $this->kv->get( $this->key );
        if( $this->value==$saved && $for_other_method )
            return true;
        elseif( $this->value==$saved )
            return error(0, 'success');
        elseif( $for_other_method )
            return false;
        else
            return error(-2, 'set key-values failed directly');
    }
    public function kv_replace(){
        $this->init();
        $tmp_value = 'ytf606_'.mt_rand(100,999);
        $this->kv->replace( $this->key , $tmp_value );
        if( $this->kv->get($this->key)==$tmp_value )
            return error(0, 'success');
        else
            return error(-3, 'replace key-values failed');
    }
    public function kv_delete()
    {
        $this->init();
        if( $this->kv_set(true) ){
            $this->kv->delete( $this->key );
            if( !$this->kv->get($this->key) )
                return error(0, 'success');
            else
                return error(-4, 'delete key-values failed');
        } else {
            return error(-2, 'set key-values failed');
        }
    }
    public function kv_mget()
    {
        $this->init();
        $keys = array();
        for($i=0;$i<10;$i++)
            $keys[] = $this->key . '_m' . mt_rand() . mt_rand(1,99);
        foreach( $keys as $k )
            $this->kv->set($k,$this->value);
        $ret = $this->kv->mget($keys);
        if( !$ret ) return error(-5, 'mget key-values all failed');
        $err_sign = false;
        foreach( $ret as $r ) {
            if( $r != $this->value ) {
                $err_sign = true;
                break;
            }
        }
        if ( !$err_sign ) {
            return error(0, 'success');
        } else {
            return error(-6, 'mget key-values part failed');
        }
    }
    public function kv_pkrget(){
        $this->init();
        $keys = array();
        $prefix = 'saeck_pkrget_' . uniqid() . '_';
        for($i=0;$i<10;$i++)
            $keys[] = $prefix.$i;
        foreach( $keys as $k ){
            $this->kv->delete( $k );
            $this->kv->set($k,$this->value);
        }
        $ret = $this->kv->pkrget( $prefix, 10 );
        if( !$ret || count($ret)!=10 ) return error(-7, 'pkrget key-values all failed');
        $err_sign = false;
        foreach( $ret as $key=>$r ){
            if( (!in_array($key,$keys) || $r!=$this->value) && $err_sign == false )
                $tag = true;
            $this->kv->delete( $key );
        }
        if ( !$err_sign ) {
            return error(0, 'success');
        } else {
            error(-8, 'pkrget key-values part failed');
        }
    }
}   


?>
