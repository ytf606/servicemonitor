<?php
class my_segment
{
    private $str = "明天星期天";
    private $seg = '';
    public function __construct()
    {
        $this->seg = new SaeSegment();
    }

    public function segment_seg()
    {
        $ret = $this->seg->segment($this->str, 1);
        if ( $this->seg->errno() != 0 ) {
            return error(-1, 'segment service connect error errmsg:' . $this->seg->errmsg());
        }
        if ($ret[0]['word'] != '明天' || $ret[1]['word'] != '星期天' ) {
            return error(-2, "segment service seg error");
        } else if ($ret[0]['word_tag'] != 132 || $ret[1]['word_tag'] != 132) {
            return error(-3, "segment service seg word_tag error");
        } else if ($ret[0]['index'] != 0 || $ret[1]['index'] != 1) {
            return error(-4, "segment service seg index error");
        } else {
            return error(0, 'success');
        }
    }
}
?>
