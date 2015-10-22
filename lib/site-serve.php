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
     public function processFormPost($formPost) {
         $acceptedPostFields = array('campaign_id',
                                     'campaign_name',
                                     'publisher_id',
                                     'placement_name',
                                     'source_site',
                                     'unique_order_number',
                                     'title',
                                     'first_name',
                                     'middle_name',
                                     'last_name',
                                     'job_title',
                                     'department',
                                     'company',
                                     'company_size',
                                     'address_line1',
                                     'address_line2',
                                     'address_line3',
                                     'city',
                                     'state',
                                     'zip_code',
                                     'country',
                                     'phone',
                                     'extension',
                                     'fax',
                                     'email',
                                     'ts_received',
                                     'geography',
                                     'ov_code',
                                     'ww_score',
                                     'custinfo1',
                                     'custinfo2',
                                     'privacy',
                                     'tactic',
                                     'response_type',
                                     'asset_name',
                                     'response_type',
                                     'questionnum1_ooemail',
                                     'email_verification',
                                     'questionnum2_ootele',
                                     'phone_verification',
                                     'questionnum3_oopostal',
                                     'zipcode_verification');
         $filteredPostFieldsDefault = array();
         $this->sendLead($filteredPostFields);
     }
     
     public function sendLead($arrayData)
     {
         $postCreated = $this->_createPost($arrayData);
         $postResult = SiteServeAPI::uploadlead($arrayData,$endPoint);  
         $this->_updatePost($postResult);
     }
    
     private function _createPost($arrayData) {
        wp_insert_post( $post, $wp_error);
        
     }
     
     private function _updatePost($status) {
         
     }
     
     public function updateLeadStatus($post) {
         
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