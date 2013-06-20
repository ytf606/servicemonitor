<?php
class SaeAPNSv2 extends SaeObject{
    /*
        Sina Cloud App Engine , 
        APNs service
    */
    private $_errno     = SAE_Success;
    private $_errmsg    = "OK";
    private $_errmsgs = array(
                                -1 => "push service database error",
                                -2 => "authorize faild",
                                -3 => "certificate number error",
                                -4 => "certificate does not exist",
                                -5 => "error when pushing to the queue",
                                -6 => "client token can not be empty",
                                -7 => "invalid format of client token",
                                -8 => "unknown error",
                                -9 => "body must be Array",
                                /* new version APNs error */
                                -10=> "param error",
                                -11=> "Can't find any SAE APNs Api server",
                                -12=> "unknown any device token",
                                -13=> "body(payload) too long. payload must not exceed 256 bytes",
                                -14=> "Push to Queue failed.",
                                /* manage device token */
                                -21=> "not allow this method",
                                -22=> "operation fails.",
                                -23=> "query error",
                            );
    
    private $curl_handle = NULL;
    protected $api_list = array(
                                'http://push.sae.sina.com.cn/',
                            );
    
    function __construct(){
    }

    //push apns msg (support one or multiple msg push)
    public function push( $cert_id, $body, $device_token=NULL , $retry=false ){
        if(!is_array($body) || !isset($body['aps']['alert'])){
            return $this->error( -9 );
        }
        
        $params = array();
        $params['cert_id'] = intval($cert_id);

        $post = array();
        $post['retry'] = $retry ? 1 : 0;
        $encodings = array( 'UTF-8', 'GBK', 'BIG5' );
        $charset = mb_detect_encoding( $body['aps']['alert'] , $encodings);
        if ( $charset !='UTF-8' ) {
            $body['aps']['alert'] = mb_convert_encoding( $body['aps']['alert'], "UTF-8", $charset);
        }
        $post['body'] = serialize($body);

        if( !$device_token || trim($device_token)=='all_users' ){
            $post['device_token'] = 'all_users';
        }else{
            $post['device_token'] = is_array($device_token) ? array_filter($device_token) : trim($device_token);
            $post['device_token'] = serialize($post['device_token']);        
        }

        return  $this->check_params( $post, $params ) ? 
                $this->push_to_route($post, $params ) : 
                false;
    }
    private function push_to_route( $post , $params ){
        if( is_array($post) )
            $post = http_build_query($post);
        
        do{
            if( ($index  = $this->random_get_api())===false )
                return $this->error(-11);
            
            $api    = $this->api_list[$index].'v2/rec.php' . '?' . http_build_query( $params );
            
            curl_setopt( $this->curl(), CURLOPT_URL, $api);
            curl_setopt( $this->curl(), CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
            curl_setopt( $this->curl(), CURLOPT_TIMEOUT,5);
            curl_setopt( $this->curl(), CURLOPT_RETURNTRANSFER,true);
            curl_setopt( $this->curl(), CURLINFO_HEADER_OUT, true);
            curl_setopt( $this->curl(), CURLOPT_POST,true);
            curl_setopt( $this->curl(), CURLOPT_POSTFIELDS,$post);        
            $result     = curl_exec( $this->curl() );
            $response   = curl_getinfo( $this->curl() );

            if(
                empty($response['http_code']) ||
                $response['http_code'] != 200 ||
                $response['size_download'] === 0.0
            ){
                $retry = true;
                $this->kick_api( $index );
                continue;
            }
            
            $result = json_decode(trim($result), true); 
            if ( is_array($result) && is_int( $result['code'] ) && $result['code'] < 0 ) {
                return $this->error( $result['code'] , $result['message'] );
            }elseif( is_array($result) && is_int($result['code']) ){
                $this->error( SAE_Success, 'ok' );
                return $result;
            }else{
                return $this->error( -8 );
            }

            $retry = false;
        }while( $retry);
    }

    //device_token manage methods
    public function device_token_get( $cert_id ){
        if( !$cert_id=intval($cert_id) )
            return $this->error(-9);           
        
        $post = array();
        $post['cert_id'] = $cert_id;
        
        $result = $this->device_token_curl( 'get', $post );
        return $result['data'];
    }
    public function device_token_destroy( $cert_id ){
        if( !$cert_id=intval($cert_id) )
            return $this->error(-9);           
        
        $post = array();
        $post['cert_id'] = $cert_id;
        
        $result = $this->device_token_curl( 'destroy', $post );
        return $result['data'] ? true : false;
    }
    public function device_token_exists( $cert_id, $device_token ){
        if( !$cert_id=intval($cert_id) )
            return $this->error(-9);           
        
        $post = array();
        $post['cert_id'] = $cert_id;
        $post['device_token'] = trim( $device_token );
        
        $result = $this->device_token_curl( 'exists', $post );
        return $result['data'] ? $result['data'] : false;
    }
    public function device_token_delete( $cert_id, $device_token ){
        if( !$cert_id=intval($cert_id) )
            return $this->error(-9);

        $post = array();
        $post['cert_id'] = $cert_id;
        $post['device_token'] = trim( $device_token );

        $result = $this->device_token_curl( 'delete', $post );
        return $result['data'] ? $result['data'] : false;  
    }
    public function device_token_add( $cert_id, $device_token ){
        $post = array();
        $post['cert_id'] = $cert_id;
        if( is_array($device_token) ){
            $post['device_token'] = array_filter( $device_token );
        }
        elseif( is_string( $device_token ) ){
            $post['device_token'] = trim($device_token);   
        }
        else{
            return $this->error( -10 );
        }
        $result = $this->device_token_curl( 'add' , $post );
        return $result['data'];
    }
    private function device_token_curl( $method, $post ){
        $auth_method = array( 'get', 'destroy', 'exists', 'add', 'delete' );
        if( !in_array(strtolower($method), $auth_method) ){
            return $this->error( -21 );
        }
        if( is_array($post) ){
            if( isset($post['device_token']) )
                $post['device_token'] = serialize( $post['device_token'] );
            $post_data = http_build_query($post);
        }
        
        do{
            if( ($index  = $this->random_get_api())===false )
                return $this->error(-11);
            $api = $this->api_list[$index].'v2/dk.php?do='.$method;
            curl_setopt( $this->curl(), CURLOPT_URL, $api);
            curl_setopt( $this->curl(), CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
            curl_setopt( $this->curl(), CURLOPT_TIMEOUT,5);
            curl_setopt( $this->curl(), CURLOPT_RETURNTRANSFER,true);
            curl_setopt( $this->curl(), CURLINFO_HEADER_OUT, true);
            curl_setopt( $this->curl(), CURLOPT_POST,true);
            curl_setopt( $this->curl(), CURLOPT_POSTFIELDS,$post_data);
            $result     = curl_exec( $this->curl() );
            $response   = curl_getinfo( $this->curl() );
            $result     = json_decode( $result, true );
            if(
                empty($response['http_code']) ||
                $response['http_code'] != 200 ||
                $response['size_download'] === 0.0
            ){
                $retry = true;
                $this->kick_api( $retry );
                continue;
            }

            if( $result['code']!=0 ){
                $this->error( $result['code'] );
            }else{
                $this->error( SAE_Success , 'ok' );
                return $result;
            }
            $retry = false;
        }while( $retry );
    }

    // common functions
    public function errno() {
        return $this->_errno;
    }
    public function errmsg() {
        return $this->_errmsg;
    }
    private function error( $errno, $errmsg=NULL ){
        $this->_errno   = $errno ;
        if( isset( $this->_errmsgs[$errno] ) )
            $this->_errmsg  = $this->_errmsgs[$errno];
        else
            $this->_errmsg  = $errmsg ;
        return false;
    }
    private function curl(){
        if( !isset($this->curl_handle) )
            $this->curl_handle = curl_init();
        return $this->curl_handle;
    }
    private function check_params( $post , $params ){
        if( mb_strlen( pack( 'n' , json_encode(unserialize($post['body'])) ) , '8bit' )>256 ){
            return $this->error( -13 );
        }
        if( !trim($post['body']) )
            return $this->error( -9 );
        return true;
    }
    private function random_get_api(){
        if( empty($this->api_list) )
            return $this->error( -11 );
        return array_rand( $this->api_list );
    }
    private function kick_api( $index ){
        if( isset( $this->api_list[$index] ) )
            unset( $this->api_list[$index] );
        return true;
    }
}
