<?php
/**
 * Plugin Name: WP Site Serve
 * Plugin URI: http://CFOPublishing/
 * Description: This plugin will integrate with Site Serve API as implemented by Ogilvy
 * Version: 1.0
 * Author: Jiun Chin
 * Author URI: http://github.com/JiunChin
 * License: Private
 */

  class WPSiteServe
  {
     const SETTINGS_SLUG = 'site-serve-setting'; 
     const TEXT_DOMAIN = 'site_serve';
     const PLUGIN_NAME = 'WP Site Serve';
     const POST_TYPE   = 'site_serve_lead';
     
     public function __construct() {
        add_action( 'admin_menu', array( __CLASS__, 'add_plugin_options_menu' ) );
        add_action( 'admin_init', array( $this,'admin_init' ) );
        add_action('init',  array( __CLASS__,'action_init' ),0);
        register_activation_hook( __FILE__, array( __CLASS__, 'plugin_activate' ) );
     }
     
     public static function admin_init() {
         if(is_admin()) {
             self::settings_init(); 
         }
     }
     
    public static function plugin_activate() {
        //echo 'i am plugin activate';
    }
     
    public static function action_init() {
       self::add_includes();
       self::register_wp_site_serve_post_type();
       if(isset($_GET['SiteServe'])) {
           if( isset($_GET['action']) && ($_GET['action'] == 'postlead')) {
              $SiteServe = new SiteServe();
              //Status can be Success, Failed, Pending
              $postdata = $_POST;
              $SiteServe->processFormPost($postdata);
              exit;
            }
       }
       add_filter('manage_' . self::POST_TYPE . '_posts_columns', array(__CLASS__,'site_serve_custom_table_header'));
       add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array(__CLASS__,'site_serve_custom_table_custom'), 10, 2 );
       add_action('restrict_manage_posts', array(__CLASS__,'wp_site_serve_filter_by_status'));
       add_filter('parse_query', array(__CLASS__,'wp_site_serve_post_filtered' ));
       add_action('admin_footer-edit.php', array(__CLASS__,'custom_bulk_admin_footer'));
       add_action('load-edit.php', array(__CLASS__,'site_serve_custom_bulk_action'));
       add_action('admin_notices', array(__CLASS__,'site_serve_custom_bulk_admin_notices'));
    }
    
    public static function site_serve_custom_table_header ( $columns ) {
        unset($columns['title']);
        unset($columns['date']);
        $columns['first_name']  = 'First Name';
        $columns['last_name']  = 'Last Name';
        $columns['email']  = 'Email';
        $columns['campaign_id']  = 'Campaign ID';
        $columns['campaign_name']  = 'Campaign Name';
        $columns['job_title']  = 'Job Title';
        $columns['department']  = 'Department';
        $columns['company']  = 'Company';
        $columns['address_line_1']  = 'Address';
        $columns['company_size']  = 'Company Size';
        $columns['city']  = 'City';
        $columns['state']  = 'State';
        $columns['zip_code']  = 'Zip';
        $columns['country']  = 'Country';
        $columns['phone']  = 'Phone';
        $columns['status']  = 'Status';
        $columns['error']  = 'Message';
        $columns['extra'] = 'Extra';
        $columns['date'] = 'Date';
        return $columns;
    }
    
    public static function site_serve_custom_table_custom ($column_name,$post_id) {
        
      if($column_name == 'extra')   {
          echo 'Campaign Id:' . get_post_meta($post_id,'campaign_id',true) . '<br/>';
          echo 'Campaign Name:' . get_post_meta($post_id,'campaign_name',true) . '<br/>';
          echo 'Placement Name:' . get_post_meta($post_id,'placement_name',true) . '<br/>';
          echo 'Order Number:' . get_post_meta($post_id,'unique_order_number',true) . '<br/>';
          echo 'Response type:' . get_post_meta($post_id,'response_type',true) . '<br/>';
          abort;
      }       
      $metavalue = get_post_meta( $post_id, $column_name, true );
      if(!empty($metavalue)) {
          echo $metavalue;
      }    
    }
    
    
    public static function add_includes()
    {
      include( dirname( __FILE__ ) . '/lib/site-serve.php' );
      include( dirname( __FILE__ ) . '/lib/site-serve-wrapper.php' );
    }

    public static function render_settings_page() {
       if ( !current_user_can( 'manage_options' ) )
       {
	     wp_die( __( 'You do not have sufficient permissions to access this page.', SELF::TEXT_DOMAIN ) );
       }
       else
       {    
         include( dirname( __FILE__ ) . '/includes/admin-options.php' );
       }         
    }
    
    // Register Custom Post Type
    public static function register_wp_site_serve_post_type() {
         $labels = array(
        		'name'                => _x( 'Site Serve Leads', 'Post Type General Name', SELF::TEXT_DOMAIN ),
        		'singular_name'       => _x( 'Site Serve Lead', 'Post Type Singular Name', SELF::TEXT_DOMAIN ),
        		'menu_name'           => __( 'Site Serve', SELF::TEXT_DOMAIN),
        		'name_admin_bar'      => __( 'Site Serve', SELF::TEXT_DOMAIN ),
        		'parent_item_colon'   => __( 'Parent Lead:', SELF::TEXT_DOMAIN ),
        		'all_items'           => __( 'All Leads', SELF::TEXT_DOMAIN ),
        		'add_new_item'        => __( 'Add New Lead', SELF::TEXT_DOMAIN ),
        		'add_new'             => __( 'Add New', SELF::TEXT_DOMAIN ),
        		'new_item'            => __( 'New Lead', SELF::TEXT_DOMAIN ),
        		'edit_item'           => __( 'Edit Lead', SELF::TEXT_DOMAIN ),
        		'update_item'         => __( 'Update Lead', SELF::TEXT_DOMAIN ),
        		'view_item'           => __( 'View Lead', SELF::TEXT_DOMAIN ),
        		'search_items'        => __( 'Search Lead', SELF::TEXT_DOMAIN ),
        		'not_found'           => __( 'Not found', SELF::TEXT_DOMAIN ),
        		'not_found_in_trash'  => __( 'Not found in Trash', SELF::TEXT_DOMAIN ),
        	);
        	$args = array(
        		'label'               => __( 'Site Serve Lead', 'site_serve' ),
        		'description'         => __( 'Leads posted to site serve', 'site_serve' ),
        		'labels'              => $labels,
        		'supports'            => array( 'custom-fields', ),
        		'hierarchical'        => false,
        		'public'              => false,
        		'show_ui'             => true,
        		'show_in_menu'        => true,
        		'menu_position'       => 2,
        		'show_in_admin_bar'   => false,
        		'show_in_nav_menus'   => true,
        		'can_export'          => false,
        		'has_archive'         => false,		
        		'exclude_from_search' => true,
        		'publicly_queryable'  => false,
        		'capability_type'     => 'page',
        	);
        	register_post_type(SELF::POST_TYPE, $args );

    }


    public static function add_plugin_options_menu() {        
      add_options_page(SELF::PLUGIN_NAME, SELF::PLUGIN_NAME, 'manage_options', self::SETTINGS_SLUG, array(__CLASS__,'render_settings_page'));
    }
     
    public static function settings_init()
    {
        add_settings_section(
                'site_serve_setting_section',
                'Site Serve Setting',
                array(__CLASS__,'setting_section_callback_function'),
                'site-serve-setting'
        );

        add_settings_field(
                'site_serve_setting_mode',
                'API Environment',
                 array(__CLASS__,'setting_mode_callback_function'),
                'site-serve-setting',
                'site_serve_setting_section'
        );

        add_settings_field(
                'site_serve_setting_test_client_id',
                'Test Client ID',
                 array(__CLASS__,'setting_site_serve_test_client_id_callback_function'),
                'site-serve-setting',
                'site_serve_setting_section'
        );

        add_settings_field(
                'site_serve_setting_test_client_secret',
                'Test Secret Key',
                 array(__CLASS__,'setting_site_serve_test_secret_callback_function'),
                'site-serve-setting',
                'site_serve_setting_section'
        );

        add_settings_field(
                'site_serve_setting_client_id',
                'Live Client ID',
                 array(__CLASS__,'setting_site_serve_client_id_callback_function'),
                'site-serve-setting',
                'site_serve_setting_section'
        );

        add_settings_field(
                'site_serve_setting_client_secret',
                'Live Secret Key',
                 array(__CLASS__,'setting_site_serve_secret_callback_function'),
                'site-serve-setting',
                'site_serve_setting_section'
        );


        register_setting( 'site-serve-setting', 'site_serve_setting_mode' );
        register_setting( 'site-serve-setting', 'site_serve_setting_client_id' );
        register_setting( 'site-serve-setting', 'site_serve_setting_client_secret' );
        register_setting( 'site-serve-setting', 'site_serve_setting_test_client_id' );
        register_setting( 'site-serve-setting', 'site_serve_setting_test_client_secret' );
        
    }
    
    public static function setting_section_callback_function() {
        echo '<p>Please configure Site Serve below.</p>';
    }
    
    public static function setting_site_serve_client_id_callback_function() {
        echo '<input name="site_serve_setting_client_id" id="site_serve_setting_client_id" size="50" type="text value="" value="' . get_option( 'site_serve_setting_client_id','' ) . '" />';
    }
    public static function setting_site_serve_secret_callback_function() {
        echo '<input name="site_serve_setting_client_secret" id="site_serve_setting_client_secret" size="75" type="text value="" value="' . get_option( 'site_serve_setting_client_secret','' ) . '" />';
    }
    public static function setting_site_serve_test_client_id_callback_function() {
        echo '<input name="site_serve_setting_test_client_id" id="site_serve_setting_test_client_id" size="50" type="text value="" value="' . get_option( 'site_serve_setting_test_client_id','' ) . '" />';
    }
    public static function setting_site_serve_test_secret_callback_function() {
        echo '<input name="site_serve_setting_test_client_secret" id="site_serve_setting_test_client_secret" size="75" type="text value="" value="' . get_option( 'site_serve_setting_test_client_secret','' ) . '" />';
    }
    
    
    public static function wp_site_serve_filter_by_status() {
    	global $typenow;
    	$post_type = self::POST_TYPE;
    	$leadStatus = array('All Status'=>'',
    	                    'Success'=> 'Success',
    	                    'Failed'=>'Failed',
    	                    'Pending'=>'Pending');
    	                    
    	if ($typenow == $post_type) {
    		$selected      = isset($_GET['LeadStatus']) ? $_GET['LeadStatus'] : '';
    		echo '<select name="LeadStatus">';
    		foreach ($leadStatus as $key=>$value) {
    		  if($selected == $value) {
    		      echo '<option selected value="' . $value . '">' . $key . '</option>';   
    		  }
    		  else {
    		    echo '<option value="' . $value . '">' . $key . '</option>';   
    		  }
    		}    
    		echo '</select>';
    	};
    }
    
    public static function wp_site_serve_post_filtered( $query ){
        global $pagenow;
        if (isset($_GET['post_type']) && self::POST_TYPE == $_GET['post_type']) {
            if(empty($_GET['LeadStatus'])) { return; }
            $query->query_vars['meta_key'] = 'status';
            $query->query_vars['meta_value'] = $_GET['LeadStatus'];
        } else {
            return;
        }
    }
    
    public static function custom_bulk_admin_footer() {
      global $post_type;
      if($post_type == self::POST_TYPE) {
        ?>
        <script type="text/javascript">
          jQuery(document).ready(function() {
            jQuery('<option>').val('update_status').text('Update Status').appendTo("select[name='action']");
          });
        </script>
        <?php
      }
    }    

    function site_serve_custom_bulk_admin_notices() {
      global $post_type, $pagenow;
     
      if($pagenow == 'edit.php' && $post_type == self::POST_TYPE &&
         isset($_REQUEST['updated']) && (int) $_REQUEST['updated']) {
        $message = sprintf( _n( 'Post updated.', '%s posts updated.', $_REQUEST['updated'] ), number_format_i18n( $_REQUEST['updated'] ) );
        echo "<div class='updated'><p>{$message}</p></div>";
      }
    }
    
    public static function site_serve_custom_bulk_action() {
      // 1. get the action
      $wp_list_table = _get_list_table('WP_Posts_List_Table');
      $wp_site_serve = new SiteServe();
      $action = $wp_list_table->current_action();
      // 2. security check
      switch($action) {
        // 3. Perform the action
        case 'update_status':
          $updated = 0;
          foreach( $post_ids as $post_id ) {
            if ($$wp_site_serve->update_status($post_id))
            {
                echo 'i did it';
            }
            else {
              wp_die( __('Error updating status.') );
            }
            $updated++;
          }
          // build the redirect url
          $sendback = add_query_arg( array('updated' => $updated, 'ids' => join(',', $post_ids) ), $sendback );
        break;
        default: return;
      }
      // 4. Redirect client
      wp_redirect($sendback);
      exit();
    }

    public static function setting_mode_callback_function() {
        $mode = get_option( 'site_serve_setting_mode','Test');
        $arry = array('Test','Live');
        echo '<select name="site_serve_setting_mode" id="site_serve_setting_mode">';
        foreach ($arry as $rec)
        {
            if($rec == $mode) {
                echo '<option selected name="' . $rec . '" value"' . $rec . '">' .  $rec  .'</option>';
            }
            else {
                echo '<option name="' . $rec . '" value"' . $rec . '">' .  $rec  .'</option>';
            }
        }
        echo '</select>';
    }

  }
global $MyWpSiteServe;
$MyWpSiteServe = new WPSiteServe();

?>