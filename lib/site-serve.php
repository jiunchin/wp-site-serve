<?php

/**
 *  This class will be where the business rules will be implemented
 */

  class SiteServe
  {
     private $endPoint;
     private $accessToken;
     private $refreshToken;
     const TESTAPIURL = 'https://api.sandbox.neoogilvy.com';
     const LIVEAPIURL = 'https://api.neoogilvy.com'; 

     public function __construct()
     {
        $this->_setEndPoint();
     }
     private function _setAccessToken()
     {
         if(!hasToken()) {
           $this->$accessToken = SiteServeAPI::generateAuthorizationToken($this->$endPoint);
         }
     }
     
     private function hasToken()
     {
         if(!empty($refreshToken))
         {
             return true;
         }
         return false;
     }
     
     /* This function process incoming form post and format data to send to site serve */
     public function sendLead($arrayData) {
         $acceptedPostFields = array('campaign_id'=>'',
                                     'campaign_name'=>'',
                                     'publisher_id'=>'',
                                     'placement_name'=>'',
                                     'source_site'=>'',
                                     'unique_order_number'=>'',
                                     'title'=>'',
                                     'first_name'=>'',
                                     'middle_name'=>'',
                                     'last_name'=>'',
                                     'job_title'=>'',
                                     'department'=>'',
                                     'company'=>'',
                                     'company_size'=>'',
                                     'address_line_1'=>'',
                                     'address_line_2'=>'', 
                                     'address_line_3'=>'',
                                     'city'=>'',
                                     'state'=>'',
                                     'zip_code'=>'',
                                     'country'=>'',
                                     'phone'=>'',
                                     'extension'=>'',
                                     'fax'=>'',
                                     'email'=>'',
                                     'ts_received'=>'',
                                     'geography'=>'',
                                     'ov_code'=>'',
                                     'ww_score'=>'',
                                     'custinfo1'=>'',
                                     'custinfo2'=>'',
                                     'privacy'=>'',
                                     'tactic'=>'',
                                     'response_type'=>'',
                                     'asset_name'=>'',
                                     'response_type'=>'',
                                     'questionnum1_ooemail'=>'',
                                     'email_verification'=>'',
                                     'questionnum2_ootele'=>'',
                                     'phone_verification'=>'',
                                     'questionnum3_oopostal'=>'',
                                     'zipcode_verification'=>'');
                                     
         $arrayData = array_intersect_key($arrayData,$acceptedPostFields);
         
         //Create The Post
         try {
         $post_id = $this->_createPost($arrayData);
         
         //Change unique order number
         $arrayData['unique_order_number'] = $post_id;
         
         $SiteServeAPI = new SiteServeAPI();
         $response = $SiteServeAPI->generateAuthorizationToken($this->getEndPoint());
         $authToken = $response->authorization_token;
         
         $response = $SiteServeAPI->generateAccessToken($this->getEndPoint(),$authToken);
         $access_token = $response->access_token;
         $refresh_token = $response->refresh_token;
    
         $data = array('access_token'=>$response->access_token);
         $leadData = $arrayData;
         $data['leadData'] = $leadData;                
         $postResult = $SiteServeAPI->uploadlead($this->getEndPoint(),$data);
         
         $postResponse = $postResult->response;
         $postError = $postResponse->errors;
         
         $status = $postResponse->status;

         if($status == 'Failed') {
           $message = serialize($postError);
           update_post_meta($post_id,'error',$message);
         }
       
         //Update Post Result Status
         update_post_meta($post_id,'status',$status); 
         update_post_meta($post_id,'request_id',$postResponse->request_id); 
         }
         catch(Exception $e) {
             update_post_meta($post_id,'error',$e->getMessage());
         }
         
     }
    
     private function _createPost($arrayData) {
       
        $post = array(
                          'post_title'    => $arrayData['first_name'] . $arrayData['last_name'],
                          'post_content'  => serialize($arrayData),
                          'post_status'   => 'publish',
                          'post_type'     => WPSiteServe::POST_TYPE
                         );
        $post_id = wp_insert_post( $post, $wp_error);  
        
        
        foreach ($arrayData as $key=>$value) {
            update_post_meta($post_id,$key,$value);
        }
        return $post_id;
     }
     
     public function updateLeadStatus($post_id) {
        //Get data from post id
         $request_id = get_post_meta($post_id,'request_id',true);
         $SiteServeAPI = new SiteServeAPI();
         $response = $SiteServeAPI->generateAuthorizationToken($this->getEndPoint());
         $authToken = $response->authorization_token;
         $response = $SiteServeAPI->generateAccessToken($this->getEndPoint(),$authToken);
         $access_token = $response->access_token;

         $data = array('access_token'=>$access_token,'request_id'=>$request_id);
         $leadStatusResult = $SiteServeAPI->getLeadStatus($this->getEndPoint(),$data);
         $leadStatusResponse  = $leadStatusResult->response;
         $status = $leadStatusResponse->status;
         
         update_post_meta($post_id,'status',$status); 
        
     }
     
     public function _unitTest() {
        SiteServeAPI::generateAuthorizationToken($this->$endPoint);
     }
     
     private function _isStaging()
     {
        $mode = get_option( 'site_serve_setting_mode','Test');
        if($mode == 'Test')
        {
            return true;
        }
        return false;
     }
     
     private function _isProduction()
     {
        $mode = get_option( 'site_serve_setting_mode','Test');
        if($mode == 'Live')
        {
            return true;
        }
        return false;
     }
     
     public function getEndPoint()
     {
         return $this->endPoint;
     }
     
     private function _setEndPoint()
     { 
        $client_id = "";
        $client_key = "";
        $currentEndpoint = "";
        if($this->_isProduction())
        {
           $client_id = get_option( 'site_serve_setting_client_id','' );
           $client_key = get_option( 'site_serve_setting_client_secret','' );
           $host = self::LIVEAPIURL;
        }
        else{
            $client_id = get_option( 'site_serve_setting_test_client_id','' );
            $client_key = get_option( 'site_serve_setting_test_client_secret','' );
            $host = self::TESTAPIURL;
        }
        
        $currentEndpoint = array('ClientID' => $client_id,
                                 'ClientKey' => $client_key,
                                 'Host' => $host);
        
        $this->endPoint = $currentEndpoint;
     }
  }


?>