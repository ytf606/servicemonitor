<?php
class my_image
{
    private $img;    
    private $img_data;
    public function __construct(){
        $this->img_data = pub_data();
        $this->img()->setData( $this->img_data );
    }
    private function img(){
        if( !$this->img )
            $this->img = new SaeImage();
        return $this->img;
    }
    public function image_info(){
        $r = $this->img()->getImageAttr();
        if( $r[0]==$r[1] && $r[0]==500 && $r['mime']=='image/jpeg' )
            return error(0, 'success');
        else
            return error(-2, 'image service failed');
    }
}


?>
