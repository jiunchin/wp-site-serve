<form method="POST" action="options.php">
<?php 
      settings_fields( 'site-serve-setting' );            
      do_settings_sections( 'site-serve-setting' ); 	  
      submit_button( 'Submit');
?>
</form>


<form method="POST">
  <input type="hidden" name="test" value="true">      
  <?php 
     submit_button( 'Test API', 'primary', 'submit-form', false );
     echo '<br><br>';
     submit_button( 'Test Form Post', 'primary', 'submit-form-post-test', false );
  ?>
</form>

<?php

  if(isset($_POST['submit-form-post-test'])) {
    
    $url = '/?SiteServe&action=postlead';  
    echo '<br/>' . 'Test form submit' . '<br/>';
    
    $leadData = array('campaign_id'=>'C3634',
                      'campaign_name'=>'2015 Q1|IBM_Non-CA_SWG_BA for Finance|Non-Intel|NYC|USA',
                      'publisher_id' => 'CFO.COM(00100324)',
                      'placement_name' => 'CFO.com|Business Analytics for Finance_Content Syndication|0x0',
                      'source_site' => 'CFO.com',
                      'unique_order_number' => '16001',
                      'job_title' => 'CFO',
                      'last_name' => 'Chin',
                      'company' => 'KARCHER NORTH AMERICA',
                      'company_size' => '5,000-9,999',
                      'address_line_1' => '744 Some where',
                      'state' => 'CO',
                      'zip_code' => '801102166',
                      'country' => 'US',
                      'first_name' => 'test',
                      'city' => 'ENGLEWOOD',
                      'phone' => '7180093401',
                      'email' => 'tes3t@gmail.com',
                      'ov_code' => 'ov4502',
                      'tactic' => '101G92BW',
                      'asset_name' => 'Test Asset',
                      'response_type'=> 'WEBRESP',
                      'questionnum1_ooemail' => 'Q_XSYS:OOEMAIL',
                      'email_verification' => 'CHECKED',
                      'questionnum2_ootele' => 'Q_XSYS:OOTELE',
                      'phone_verification' => 'CHECKED',
                      'questionnum3_oopostal'=> 'Q_XSYS:OOPOSTAL',
                      'zipcode_verification' => 'CHECKED');
                      
    echo '<form action="' . $url . '" name="postlead" method="post">';
  
    foreach ($leadData as $attribute=>$value) {
      echo '<label>' . $attribute . '</label>';
      echo '<input type="text" size="100" name="' . $attribute . '" value="' . $value . '"/>' . '<br/>';
    }
    echo '<input type="submit" name="submit" value="submit"/>'; 
    echo '</form>';


  }

  if(isset($_POST['submit-form']))
  {
      echo 'Testing Site Serve API <br/>';
      $SiteServeAPI = new SiteServeAPI();
      $SiteServeBusinessObject = new SiteServe();
      
      echo '<br/>';
      
      echo 'Test Get Authorization Token <br/>';
      $response = $SiteServeAPI->generateAuthorizationToken($SiteServeBusinessObject->getEndPoint());
      $authToken = $response->authorization_token;
      echo $authToken;
      
      echo '<br/><br/>';
      
      echo 'Test Get Acces Token <br/>';
      $response = $SiteServeAPI->generateAccessToken($SiteServeBusinessObject->getEndPoint(),$authToken);
      $access_token = $response->access_token;
      $refresh_token = $response->refresh_token;
      echo 'AccessToken: ';
      echo $access_token;
      echo '<br/>';
      echo 'Refresh Token: ' . $refresh_token;
      echo  $accesstoken;
      echo '<br/><br/>';
      
      echo 'Refresh Token<br/>';
      $response = $SiteServeAPI->refreshToken($SiteServeBusinessObject->getEndPoint(),$refresh_token);
      echo 'New Access Token" ' ;
      echo $response->access_token;

      echo '<br/><br/>';
      
      echo 'Upload Lead<br/>';
      
      $data = array('access_token'=>$response->access_token);
      
      $leadData = array('campaign_id'=>'C3634',
                        'campaign_name'=>'2015 Q1|IBM_Non-CA_SWG_BA for Finance|Non-Intel|NYC|USA',
                        'publisher_id' => 'CFO.COM(00100324)',
                        'placement_name' => 'CFO.com|Business Analytics for Finance_Content Syndication|0x0',
                        'source_site' => 'CFO.com',
                        'unique_order_number' => '16001',
                        'job_title' => 'CFO',
                        'last_name' => 'Chin',
                        'company' => 'KARCHER NORTH AMERICA',
                        'company_size' => '5,000-9,999',
                        'address_line_1' => '744 Some where',
                        'state' => 'CO',
                        'zip_code' => '801102166',
                        'country' => 'US',
                        'first_name' => 'test',
                        'city' => 'ENGLEWOOD',
                        'phone' => '7180093401',
                        'email' => 'test2@gmail.com',
                        'ov_code' => 'ov4502',
                        'tactic' => '101G92BW',
                        'asset_name' => 'Test Asset',
                        'response_type'=> 'WEBRESP',
                        'questionnum1_ooemail' => 'Q_XSYS:OOEMAIL',
                        'email_verification' => 'CHECKED',
                        'questionnum2_ootele' => 'Q_XSYS:OOTELE',
                        'phone_verification' => 'CHECKED',
                        'questionnum3_oopostal'=> 'Q_XSYS:OOPOSTAL',
                        'zipcode_verification' => 'CHECKED');
                      
      $data['leadData'] = $leadData;                
      $postResult = $SiteServeAPI->uploadlead($SiteServeBusinessObject->getEndPoint(),$data);
      $postResp = $postResult->response;
      $postError = $postResp->errors;

      echo 'Request ID: ' . $postResp->request_id;
      echo '<br/>';
      echo 'Code: ' . $postResp->code;
      echo '<br/>';
      echo 'Status:' . $postResp->status;
      echo '<br/>';
      echo 'Total Leads: ' . $postResp->total_leads;
      echo '<br/>';
      echo 'Upload Leads: ' . $postResp->uploaded_leads;
      echo '<br/>';
      echo 'Duplicate Leads: ' . $postResp->duplicate_leads ;
      echo '<br/>';
      echo 'Invalid Leads: ' . $postResp->invalid_leads;
      echo '<br/>';
      echo 'Error: ' . '<br/>';
      var_dump($postError);
      
      echo '<br/><br/>';
      echo 'Test Get Lead Request Status:';
      $data = array('access_token'=>$response->access_token,'request_id'=>$postResp->request_id);

      $leadStatusResult = $SiteServeAPI->getLeadStatus($SiteServeBusinessObject->getEndPoint(),$data);
      $leadStatusResponse  = $leadStatusResult->response;
      
      echo 'Request ID: ' . $leadStatusResult->request_id;
      echo '<br/>';
      echo 'Code: ' . $leadStatusResponse->code;
      echo '<br/>';
      echo 'Status:' . $leadStatusResponse->status;
      echo '<br/>';
      echo 'Total Leads: ' . $leadStatusResponse->total_leads;
      echo '<br/>';
      echo 'Upload Leads: ' . $leadStatusResponse->uploaded_leads;
      echo '<br/>';
      echo 'Duplicate Leads: ' . $leadStatusResponse->duplicate_leads ;
      echo '<br/>';
      echo 'Invalid Leads: ' . $leadStatusResponse->invalid_leads;
      echo '<br/>';
      echo 'Error: ' . '<br/>';
      var_dump($leadStatusResponse->errors);
  }
  
?>
