<?php
  /*   
       This class operates as the api / wrappper for communication with the end point
       This should be a static object
       API doc:  https://api.neoogilvy.com/docs/SiteServe-v1
   */
 
  class SiteServeAPI
  {
     const MODE = 'LIVE'; //DEBUG or LIVE
     
     public static function generateAuthorizationToken($endPoint)
     {
         $Random = rand(1,10000000);
         $ClientID = $endPoint['ClientID'];
         $Host = $endPoint['Host'];
         $url = sprintf($Host . '/oauth/authorize?response_type=code&client_id=%s&redirect_uri=inapp&state=init',$ClientID,$Random);
         $data = array('authorized'=>'yes');
         $response = self::call($url,$data,'POST','Form','json');
         return $response;
     }
     
     public static function generateAccessToken($endPoint,$authcode)
     {
         $ClientID = $endPoint['ClientID'];
         $Host = $endPoint['Host'];
         $ClientKey = $endPoint['ClientKey'];
         $url = $Host . '/oauth';
         $data = array('client_id'=>$ClientID,
                       'client_secret' => $ClientKey,
                       'redirect_uri' => 'inapp',
                       'code'=>$authcode,
                       'grant_type' => 'authorization_code');
         $response = self::call($url,$data,'POST','json','json');
         return $response;
     }
     
     public static function refreshToken($endPoint,$refresh_token)
     {
         $ClientID = $endPoint['ClientID'];
         $Host = $endPoint['Host'];
         $ClientKey = $endPoint['ClientKey'];
         $url = $Host . '/oauth';
         $data = array('client_id'=>$ClientID,
                       'client_secret' => $ClientKey,
                       'refresh_token' => $refresh_token,
                       'grant_type' => 'refresh_token');
         $response = self::call($url,$data,'POST','json','json');
         return $response;
     }
     
     public static function uploadlead($endPoint,$data)
     {
         $default = array(
            'campaign_id' => '',
            'campaign_name' => '',
            'publisher_id' => '',
            'placement_name' => '',
            'source_site' => '',
            'unique_order_number' => '',
            'title' => '',
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'job_title' => '',
            'company' => '',
            'company_size' => '',
            'address_line_1' => '',
            'address_line_2' => '',
            'address_line_3' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
            'country' => '',
            'phone' => '',
            'fax' => '',
            'email' => '',
            'ts_received' => '',
            'extension' => '',
            'department' => '',
            'geography' => '',
            'ov_code' => '',
            'ww_score' => '',
            'iadmid' => '',
            'custinfo1' => '',
            'custinfo2' => '',
            'privacy' => '',
            'tactic' => '',
            'response_type' => '',
            'asset_name' => '',
            'questionnum1_ooemail' => '',
            'email_verification' => '',
            'questionnum2_ootele' => '',
            'phone_verification' => '',
            'questionnum3_oopostal' => '',
            'zipcode_verification' => '',
            'timestamp' => ''
                 );
         
         $data['leadData'] = wp_parse_args($data['leadData'],$default);

         $ClientID = $endPoint['ClientID'];
         $Host = $endPoint['Host'];
         $ClientKey = $endPoint['ClientKey'];
         $url = $Host . '/siteserve/upload';
         
         $response = self::call($url,$data,'POST','json','json');
         
         if(self::MODE == 'DEBUG')
            {
                echo '<Br/>';
                echo 'Data: '; var_dump($data);
                echo '<br/>';
                echo '<br/>';
                echo 'RESPONSE: '. var_dump($response);
            }
         
         
         return $response;         
         
     }
     
     public static function getLeadStatus($endPoint,$data)
     {
         $ClientID = $endPoint['ClientID'];
         $Host = $endPoint['Host'];
         $ClientKey = $endPoint['ClientKey'];
         $url = $Host . '/siteserve/status/' . $data['request_id'];
         $response = self::call($url,$data,'GET','json','json');
         return $response;      
     }
     
     public static function call($url, $data='', $actionType='GET', $postformat='json',$resultformat='json')
     {
        //use wp_remote_get or wp_remote_post
        $args = array('timeout'=> 5);    
        $body = '';
        $headers = array();
        
        if(isset($data['code'])) {
          $headers['Authorization'] = 'Bearer ' . $data['code'];
        }
        
        if(isset($data['access_token'])) {
          $headers['Authorization'] = 'Bearer ' . $data['access_token'];
        }
        
        if($postformat != 'json')
        {
          $headers['content-type'] = 'application/x-www-form-urlencoded';
        }
        else
        {
          $headers['content-type'] = 'application/json';
        }
        
        if(!empty($data) && $postformat == 'json')
        {
            if(isset($data['leadData']))  {
                $dataNew = array();
                $dataNew[0] = $data['leadData'];
                $data = $dataNew;
            } 
            $data = wp_json_encode($data);
            $args['body'] = $data;
        }
        else {
            $args['body'] = $data;
        }
        
        $args['headers'] = $headers;
        
        if($actionType == 'GET') {
         try {
            $response = wp_remote_get($url,$args);          
            $result = wp_remote_retrieve_body($response);
          }
          catch(Exception $e)
          {
            $errorMessage = 'Error: ' . $e->getMessage();
          }
        }
        else {
         try {
            $args['method'] = 'POST';
            $response = wp_remote_post($url,$args);          
            $result = wp_remote_retrieve_body($response);
            
            if(self::MODE == 'DEBUG')
            {
                echo '<Br/>';
                echo 'ARGS: '; var_dump($args);
                echo '<br/>';
                echo '<br/>';
                echo 'RESPONSE: '. var_dump($response);
            }
            
          }
          catch(Exception $e)
          {
            $errorMessage = 'Error: ' . $e->getMessage();
          }
            
        }
        
        if($resultformat == 'json')
        {
            $result = json_decode($result);

        }
        return $result;
     }
  }
  
?>