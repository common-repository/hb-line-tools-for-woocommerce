<?php

/*
 * Plugin Name: HB Line Tools for WooCommerce
 * Plugin URI: https://piglet.me/hb-line-tools-for-woocommerce
 * Description: A Simple LINE Send Plugin.
 * Version: 0.5.5
 * Author: heiblack
 * Author URI: https://piglet.me
 * License:  GPL 3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/


class hb_line_tools_for_woocommerce_admin {
    public $payload,$ChannelAccessToken,$orderData,$socialId,$HBNickName,$protocol;
    public function __construct() {
        if ( !defined( 'ABSPATH' ) ) {
            http_response_code( 404 );
            die();
        }
        if (! function_exists('plugin_dir_url')){
            return;
        }
        if (! function_exists( 'is_plugin_active' )){
            require_once (ABSPATH . 'wp-admin/includes/plugin.php');
        }
        if(! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            return;
        }
        $this->init();
    }
    public function init(){

        $page = @$_GET['page'];
        if($page=='HB_LINE_send_setting' || $page=='HB_LINE_send_setting_statuses' || $page=='HB_LINE_send_setting_new_order'){
            if(!wp_script_is('HEIBLACK-Axios','enqueued')){
                wp_enqueue_script('HEIBLACK-Axios', plugin_dir_url(__FILE__) . 'assets/axios.js');
            }
            if(!wp_script_is('HEIBLACK-Vue','enqueued')){
                wp_enqueue_script('HEIBLACK-Vue', plugin_dir_url(__FILE__) . 'assets/vue.js');
            }
        }


        $this->ChannelAccessToken   =   get_option('_hb_woo_line_channel_access_token');
        $this->orderEventAjaxAtion();
        $this->HBLineMenuPages();
        $this->AddMetaBoxOnWoo();
        $this->WhenOrderChange();
        $this->HasNewOrder();
        $this->LineBotEvent();
    }
    private function orderEventAjaxAtion(){
        add_action('wp_ajax_get_AllStatuses_action', function (){
            if ( current_user_can( 'administrator' ) ) {
                $HB_order_statuses = wc_get_order_statuses();
                echo  json_encode($HB_order_statuses, JSON_UNESCAPED_UNICODE);
            }
            die();
        });
        add_action('wp_ajax_get_Order_action', function (){
            if ( current_user_can( 'administrator' ) ) {
                $result = get_option('_hb_woo_line_st_2');
                //$json = htmlspecialchars_decode($_hb_woo_line_new_order, ENT_QUOTES);
                return wp_send_json_success($result);
            }

            die();
        });
        add_action('wp_ajax_get_NewOrder_action', function (){
            if ( current_user_can( 'administrator' ) ) {
                $result = get_option('_hb_woo_new_order_2');

                return wp_send_json_success($result);
            }
            die();
        });
//        add_action('wp_ajax_simpleSend_action', function (){
//            if ( current_user_can( 'administrator' ) ) {
//                require_once(dirname(__FILE__) . '/page/HB-SimpleSendEvent-API.php');
//            }
//            die();
//        });
        add_action('wp_ajax_simpleTest_action', function (){
            if ( current_user_can( 'administrator' ) ) {
                $ULIE = sanitize_textarea_field($_POST['ULINE']);


                $this->ChannelAccessToken   =   get_option('_hb_woo_line_channel_access_token');

                $this->payload = [
                    'to' =>[$ULIE],
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => 'Send successfully, thank you for your use. by heiblack'
                        ]
                    ]
                ];

                $this->SendMessage();
            }
            die();
        });
    }
    private function AddMetaBoxOnWoo(){
        add_action( 'add_meta_boxes', function () {
            add_meta_box(
                'HB_LINE_send_tools',
                __( 'Line Message',"hb-line-tools-for-woocommerce" ),
                function ( $post ){
                    $order_id       = $post->ID;
                    $order          = wc_get_order( $order_id );
                    $order_data     = $order->get_data();
                    $user_meta      = get_option('_hb_woo_line_user_meta');

                    if($user_meta=='SuperSocializer'){
                        $this->socialId         =   get_user_meta($order_data['customer_id'],'thechamp_social_id', true);
                        if(!$this->socialId){
                            esc_html_e("Unable to get the customer's Line id or the customer is not a Line user","hb-line-tools-for-woocommerce");
                            return;
                        }
                    }else if($user_meta=='NEXTENDSOCIALLOGIN'){
                        global $wpdb;
                        $sql         =   $wpdb->prepare("SELECT identifier FROM `wp_social_users` WHERE `ID` = {$order_data['customer_id']} ");
                        $social_links   = $wpdb->get_results( $sql );
                        if(!$social_links[0]){
                            esc_html_e("Unable to get the customer's Line id or the customer is not a Line user","hb-line-tools-for-woocommerce");
                            return;
                        }
                        $this->socialId         =   $social_links[0]->identifier;
                    }else if($user_meta=='HBLineLogin'){
                        $this->socialId         =   get_user_meta($order_data['customer_id'],'_heiblack_social_line_id', true);
                        if(!$this->socialId){
                            esc_html_e("Unable to get the customer's Line id or the customer is not a Line user","hb-line-tools-for-woocommerce");
                            return;
                        }

                    }
                    $nickname = get_user_meta($order_data['customer_id'],'nickname' , true);
                    echo '<p><b>Nickname: </b>'. esc_html($nickname).'</p>';
                    echo '<p><b>LINE ID:</b><br>'. esc_html($this->socialId).'</p>';
                },
                'shop_order',
                'side',
                'default'
            );
        });
    }
    private function HBLineMenuPages() {
        add_action('admin_menu',function (){
            add_menu_page(
                'HB Line Tools',
                'HB Line Tools',
                'administrator',
                'HB_LINE_send_setting',
                function (){
                    $user_ChannelAccessToken    = '';
                    $user_meta                  = '';
                    $user_ChannelAccessToken    =   get_option('_hb_woo_line_channel_access_token');
                    $user_channelSecret         =   get_option('_hb_woo_line_channelSecret');
                    $user_meta                  =   get_option('_hb_woo_line_user_meta');
                    require_once(dirname(__FILE__) . '/page/HB-LineAdminPage.php');
                },
                plugin_dir_url(__FILE__) . 'assets/logo.png'
            );
            add_submenu_page(
                'HB_LINE_send_setting',
                __('State event','hb-line-tools-for-woocommerce'),
                __('State event','hb-line-tools-for-woocommerce'),
                'administrator',
                'HB_LINE_send_setting_statuses',
                function (){
                    require_once(dirname(__FILE__) . '/page/HB-OrderStatusesEventPage.php');
                });
            add_submenu_page(
                'HB_LINE_send_setting',
                __('New order event','hb-line-tools-for-woocommerce'),
                __('New order event','hb-line-tools-for-woocommerce'),
                'administrator',
                'HB_LINE_send_setting_new_order',
                function (){
                    require_once(dirname(__FILE__) . '/page/HB-NewOrderEventPage.php');
                });
            add_submenu_page(
                'HB_LINE_send_setting',
                'LineBotEvent',
                'LineBotEvent',
                'administrator',
                'HB_LINE_LineBotEvent',
                function (){
                    require_once(dirname(__FILE__) . '/page/HB-LineBotEventPage.php');
                });
            add_submenu_page(
                'HB_LINE_send_setting',
                __('Basic settings','hb-line-tools-for-woocommerce'),
                __('Basic settings','hb-line-tools-for-woocommerce'),
                'administrator',
                'HB_LINE_send_setting'
            );
        });
    }
    private function GetHttpProtocol(){
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $this->protocol = 'https://';
        }
        else {
            $this->protocol = 'http://';
        }
    }
    private function LineBotEvent(){
        $this->GetHttpProtocol();
        add_action('template_include', function ($original_template){
            if(esc_url_raw($this->protocol.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"])==home_url().'/heiblack/Line'){
                status_header(200);
                return  dirname(__FILE__ ). '/page/HB-LineBotEvent.php';
            }

            return $original_template;
        });
    }
    private function WhenOrderChange(){
        add_action('woocommerce_order_status_changed', function ($order_id,$old_status,$new_status) {
            $user_meta                  =   get_option('_hb_woo_line_user_meta');
            if(!$user_meta){
                return;
            }
            try {
                $order                  =   wc_get_order( $order_id );
                $this->orderData        =   $order->get_data();
                if($user_meta=='SuperSocializer'){
                    $this->socialId         =   get_user_meta($this->orderData['customer_id'],'thechamp_social_id', true);
                }else if($user_meta=='NEXTENDSOCIALLOGIN'){
                    global $wpdb;
                    $sql           =   $wpdb->prepare("SELECT identifier FROM `wp_social_users` WHERE `ID` = {$this->orderData['customer_id']} ");
                    $social_links   = $wpdb->get_results( $sql );
                    if(!$social_links[0]){
                        return;
                    }
                    $this->socialId         =   $social_links[0]->identifier;
                }else if($user_meta=='HBLineLogin'){
                    $this->socialId         =   get_user_meta($this->orderData['customer_id'],'_heiblack_social_line_id', true);
                }
                if(!$this->socialId){
                    return;
                }
                $this->HBNickName       =   get_user_meta($this->orderData['customer_id'],'nickname' , true);
                $_hb_woo_line_st        =   get_option('_hb_woo_line_st_2');


                foreach ($_hb_woo_line_st as $value ){
                    if(isset($value['checked']) && $value['checked']=='on'){
                        if ($value['Statusestype']=='wc-'.$new_status){
                            $this->SwitchTypeEvent($value);
                        }
                    }
                }
            } catch (Exception $e) {

            }
        }, 10, 3);
    }
    private function HasNewOrder(){
        add_action('woocommerce_new_order', function ($order_id) {
            $user_meta      = get_option('_hb_woo_line_user_meta');
            if(!$user_meta){
                return;
            }
            try {
                $order          = wc_get_order( $order_id );
                $this->orderData     = $order->get_data();


                if($user_meta=='SuperSocializer'){
                    $this->socialId         =   get_user_meta($this->orderData['customer_id'],'thechamp_social_id', true);
                }else if($user_meta=='NEXTENDSOCIALLOGIN'){
                    global $wpdb;

                    $sql           =   $wpdb->prepare("SELECT identifier FROM `wp_social_users` WHERE `ID` = {$this->orderData['customer_id']} ");
                    $social_links   = $wpdb->get_results( $sql );


                    if(!$social_links[0]){
                        return;
                    }
                    $this->socialId         =   $social_links[0]->identifier;
                }else if($user_meta=='HBLineLogin'){
                    $this->socialId         =   get_user_meta($this->orderData['customer_id'],'_heiblack_social_line_id', true);
                }
                if(!$this->socialId){
                    return;
                }
                $this->HBNickName = get_user_meta($this->orderData['customer_id'],'nickname' , true);
                $_hb_woo_line_new_order =  get_option('_hb_woo_new_order_2');


                foreach ($_hb_woo_line_new_order as $value ){
                    $result = '';
                    if(isset($value['checked']) && $value['checked']=='on'){

                        $this->SwitchTypeEvent($value);
                    }
                }
            } catch (Exception $e) {

            }
        }, 10, 1);
    }
    private function SwitchTypeEvent($value){
        switch ($value['type']) {
            case 'text':
                $result = $this->Mestonor($value['text']);
                $mes_array =  array('type' => 'text','text' => $result);
                $this->payload = [
                    'to' =>[$this->socialId],
                    'messages' => [
                        $mes_array
                    ]
                ];
                break;
            case 'image':
                $result = $this->Mestonor($value['text']);
                $mes_array =  array('type' => 'image','originalContentUrl' => $result,'previewImageUrl'=>$result);
                $this->payload = [
                    'to' =>[$this->socialId],
                    'messages' => [
                        $mes_array
                    ]
                ];
                break;
            case 'template':
                $resultText = $this->Mestonor($value['text']);
                $resultUrl = $this->Mestonor($value['url']);
                $resultTitle = $this->Mestonor($value['title']);
                $this->payload = [
                    'to' =>[$this->socialId],
                    'messages' => [
                        array(
                            'type' => 'template',
                            'altText' => $resultTitle,
                            'template' => array(
                                'type' => 'buttons',
                                "thumbnailImageUrl" => $value['image'],
                                'title' => $resultTitle,
                                'text' => $resultText,
                                'actions' => array(
                                    array(
                                        'type' => 'uri',
                                        'label' => $value['button'],
                                        'uri' => $resultUrl
                                    )
                                )
                            )
                        )
                    ]
                ];
                break;
        }
        $this->SendMessage();
    }
    private function Mestonor($value){
        $HB_id                   =   $this->orderData['id'];
        $HB_total                =   $this->orderData['total'];
        $HB_payment_method       =   $this->orderData['payment_method_title'];

        $result = $value;
        $result  = str_replace("{{order.id}}",$HB_id ,$result);
        $result  = str_replace("{{order.nickname}}",$this->HBNickName ,$result);
        $result  = str_replace("{{order.total}}",$HB_total,$result);
        $result  = str_replace("{{order.payment}}",$HB_payment_method,$result);
        return $result;
    }
    private function SendMessage(){
        try {
            $headers = array(  'Content-Type' => 'application/json', 'Authorization'=> 'Bearer ' .  $this->ChannelAccessToken);
            wp_remote_post('https://api.line.me/v2/bot/message/multicast',array(
                'headers' => $headers,
                'method' => 'POST',
                'body' => json_encode($this->payload),
            ));
        }catch (Exception $e) {

        }
    }
}

new  hb_line_tools_for_woocommerce_admin();



