<?php
class my_fts
{
    private $str = "sae monitor test of 470789d132db1bc0ed6ddc3f9617af9d";
    private $modify_str = "470789d132db1bc0ed6ddc3f9617af9d";
    private $doc_id = '';
    private $fts = '';
    public function __construct()
    {
        $this->fts = new SaeFTS();
        $this->doc_id = time();
    }

    public function fts_addDoc()
    {
        $ret = $this->fts->addDoc($this->doc_id, $this->str);
        if ( $this->fts->errno() != 0 ) {
            return error(-1, 'fts service add doc error errmsg:' . $this->fts->errmsg());
        } else {
            return error(0, 'success');
        }
    }

    public function fts_modifyDoc()
    {
        $ret = $this->fts->modifyDoc($this->doc_id, $this->modify_str);
        if ( $this->fts->errno() != 0 ) {
            return error(-2, 'fts service modify doc error errmsg:' . $this->fts->errmsg());
        } else {
            return error(0, 'success');
        }
    }

    public function fts_search()
    {
        $ret = $this->fts->search($this->modify_str);
        if ( $this->fts->errno() != 0 ) {
            return error(-3, 'fts service search doc error errmsg:' . $this->fts->errmsg());
        } else {
            return error(0, 'success');
        }
        if ( $ret['count'] != 1 ) {
            return error(-4, "fts service search doc error");
        }
        if ($ret['result']['docid'] != $this->doc_id || $ret['result']['abstract'] != $this->modify_str) {
            return error(-5, "fts service search doc not conform base info");
        }
        return error(0, 'success');
    }

    public function fts_delete()
    {
        $ret = $this->fts->deleteDoc($this->doc_id);
        if ( $this->fts->errno() != 0 ) {
            return error(-6, 'fts service delete doc error errmsg:' . $this->fts->errmsg());
        } else {
            return error(0, 'success');
        }
    }
}
?>
