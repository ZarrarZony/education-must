<?php
/*
Plugin Name: Demo
Description: Demo Plugin
Author:      Zarrar aka Zony
Version:     1.x.x
Author URI:  https://linkedin.com/in/muhammadzarrar& https://fiverr.com/zony101
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
defined( 'ABSPATH' ) or die( 'Action will be taken' );

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    '',
    __FILE__,
    'demo'
);
$myUpdateChecker->setAuthentication('');
$myUpdateChecker->setBranch('');

class EducationMust{

    public $webhook_url = '';
    private static $instance = null;

    private function __construct() {
    }
 
    public static function getInstance() {
       if (self::$instance == null) {
          self::$instance = new Sample();
       }
       return self::$instance;
    }

    public function init(){
        $this->edu_must_define_constants();
        $this->edu_must_includes();
        add_action('admin_menu',array($this,'menu_manager'));
        add_action( 'init', array($this,'register_custom_woo_order_status') );
        add_filter( 'wc_order_statuses', array($this,'add_custom_woo_order_status') );
        if(!empty(get_option('edu_must_options')) && in_array('new_order',get_option('edu_must_options'))){
            //removed
        }
        if(!empty(get_option('edu_must_options')) && in_array('webhook',get_option('edu_must_options'))){
            //removed
        }
        add_action( 'admin_post_edu_submited', array( $this,'form_edu_must_option'));
        add_action( 'admin_post_crm_config_submited', array( $this,'form_crm_config'));
        register_activation_hook( __FILE__, array( $this, 'plugin_install' ) );
    }

    public function register_custom_woo_order_status(){
        register_post_status( 'wc-completed-psp', array(
            'label'                     => 'Completed PSP',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Completed PSP (%s)', 'Completed PSP (%s)' )
        ) );
    }

    public function add_custom_woo_order_status( $order_statuses ){
        $new_order_statuses = array();
        foreach ( $order_statuses as $key => $status ) {
            $new_order_statuses[ $key ] = $status;
            if ( 'wc-processing' === $key ) {
                $new_order_statuses['wc-completed-psp'] = 'Completed PSP';
            }
        }
        return $new_order_statuses;
    }

    public function edu_must_define_constants(){
        $this->edu_must_define( 'path_edu_must', plugin_dir_path( __FILE__ ) );
        $this->edu_must_define( 'basename_edu_must', dirname( __FILE__ ) );
        $this->edu_must_define( 'edu_must_plugin_url', plugin_dir_url( __FILE__ ) );
        $this->edu_must_define( 'Plugin_Unique_Id_Demo', 'demo' );
    }

    public function edu_must_define( $name, $value ){
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    public function menu_manager() {
        global $_wp_last_object_menu;
        $_wp_last_object_menu++;
        add_menu_page( 'Demo', 'Demo', 'manage_options', Plugin_Unique_Id_Demo.'-dashboard', array($this,'edu_must_manager'), plugins_url('assets/education.png', __FILE__),$_wp_last_object_menu);
        add_submenu_page(Plugin_Unique_Id_Demo.'-dashboard', 'Crm Config','Crm Config', 'manage_options',Plugin_Unique_Id_Demo.'-crm-config', array($this,'manage_crm_config'));
    }

    public function edu_must_manager(){
        include( path_edu_must . 'views/admin/edu_manager.php');
    }

    public function manage_crm_config(){
        include( path_edu_must . 'views/admin/manage_crm_config.php');
    }


    public function backend_intletinputscripts(){
        wp_enqueue_script('intl-tel-js', edu_must_plugin_url . 'assets/js/intlTelInput.js');
        wp_enqueue_style('int-tel-css', edu_must_plugin_url . 'assets/css/intlTelInput.css');
        wp_enqueue_script('custom-script', edu_must_plugin_url . 'assets/js/backend_custom_script.js');
    }

    public function form_edu_must_option(){
        update_option('edu_must_options',$_POST['edu_must_options']);
        wp_safe_redirect($_SERVER['HTTP_REFERER'].'&response=success');
        exit();
    }

    public function form_crm_config(){
        //removed
        wp_safe_redirect($_SERVER['HTTP_REFERER'].'&response=success');
        exit();
    }

    public function createAccount( $orderId ) {
        $order = new WC_Order($orderId);
        $user = $order->get_user_id();
        if( $user ) return;

       //removed
                
        $index = get_post_meta( $orderId, '_billing_address_index' );
        update_post_meta( $orderId, '_billing_address_index', implode(' ', $index) . " $username" );
		$this->sendWelcomeEmail( $orderId );
    }

    public function edu_webhooks( $orderId ){
        $order = new WC_Order($orderId);
        $send_data = ['action'=>'order_placed','arg' => $orderId, 'brand' => get_brand($order->get_meta('referral')), 'source' =>get_web_url()];
        wp_remote_post( $this->webhook_url, array(
            'method'      => 'POST',
            'timeout'     => 60,
            'body'        => json_encode($send_data),
            )
        );
    }

    public function edu_must_webhook_order_status($this_get_id, $this_status_transition_from, $this_status_transition_to, $instance){
        $order = new WC_Order($this_get_id);
        if(!empty($order->get_transaction_id())){
            $send_data = ['action'=>'woocommerce_order_status_changed','arg' => $this_get_id,'brand'=>get_brand($order->get_meta('referral')), 'source'=>get_web_url()];
            wp_remote_post( $this->webhook_url, array(
                'method'      => 'POST',
                'timeout'     => 60,
                'body'        => json_encode($send_data),
                )
            );
        }
    }

    public function edu_webhooks_partial_refund( $order_id, $refund_id ){
        $order = new WC_Order($order_id);
        if(!empty($order->get_transaction_id())){
            $send_data = ['action'=>'woocommerce_order_partially_refunded','arg' => $order_id,'brand'=>get_brand($order->get_meta('referral')), 'source'=>get_web_url()];
            wp_remote_post( $this->webhook_url, array(
                'method'      => 'POST',
                'timeout'     => 60,
                'body'        => json_encode($send_data),
                )
            );
        }
    }

    private function sendWelcomeEmail( $orderId ) {
		
		$order = new WC_Order( $orderId );
	
		$sendData = [
            'educationSite'	=> get_web_url(),
            'source' => true,
			'firstName' => $order->get_billing_first_name(),
			'docId' => $order->get_meta('_docId'),
			'language' => $order->get_meta('wpml_language'),
			'username' => $order->get_meta('username'),
			'password' => $order->get_meta('password'),
		];
		
		$body = [
			'method' => 'post',
			'url' => '',
			'headers' => [
				'content-type' => 'application/json',
				'x-api-key' => ''
			],
			'data' => $sendData,
		];

		$response = $this->proxy( $body );
		
		$statusCode = $response['response']['code'];
		$body = $response['body'];
		
		if( $statusCode !== 200 ) {
			$order->add_order_note( 'There was an error sending the "new-account" email<br>' . $body );
		} else {
			$order->add_order_note( 'The "new-account" email was sent successfully' );
		}	
    }
	
	private function proxy( $body ) {
		
		$url = '';
		$response = wp_remote_post( $url, [
				'method'      => 'POST',
				'timeout'     => 60,
				'headers' => [ 'content-type' => 'application/json' ],
				'body'        => json_encode( $body ),
			]
        );

		return $response;
	}
    
    public function edu_must_includes(){
        include_once path_edu_must . '/includes/helper.php';
    }

    public function admin_enqueue() {
        wp_enqueue_script('my_custom_script', edu_must_plugin_url.'assets/js/custom.js');
    }
    
    public function plugin_install() {
        $defaultsettings = array('new_order','webhook');
        update_option('edu_must_options',$defaultsettings);
    }

}

$instance = new EducationMust;
$instance->init();