<?php
class my_tq
{
    private $tq;
    private $tq_name = 'monitor';
    public function __construct(){
        if( $_REQUEST['action'] == 'trigger' ){
            return $this->trigger();
        }
        //if( !($this->tq_name = $_REQUEST['taskq_name']) )
        //    die('the name of queue is empty.');
    }
    private function trigger(){
        $c = new SaeCounter();
        $c->incr( 'sae_checker_wait_tq' );
        die('done!');
    }
    private function tq(){
        if( !$this->tq )
            $this->tq = new SaeTaskQueue( $this->tq_name );
        return $this->tq;
    }
    public function tq_addtask(){
        $c = new SaeCounter();
        if (!$c->exists('sae_checker_wait_tq')) {
            if (!$c->create( 'sae_checker_wait_tq', 0 )) {
                return error(-1, 'create counter failed in tq');
            }
        }
        $url = $_SERVER['SCRIPT_URI'].'?action=trigger&service=tq&token=123456';
        $this->tq()->addTask( $url, NULL, true );
        $this->tq()->push();
        
        $s_time = time();
        $pass   = false;
        while( !$pass && (time()-$s_time<20) ){
            if( $c->get('sae_checker_wait_tq')>0 )
                $pass=true;
            else
                time_nanosleep(0, 100000000); // 1/10 second
        }
        $c->remove( 'sae_checker_wait_tq' );
        if( $pass ){
            return error(0, 'success');
        }else 
            return error(-2, 'add task to tq failed');
    }
}
?>
