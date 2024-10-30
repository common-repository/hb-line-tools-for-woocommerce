<?php

class HEIBLACK_SendMessage {
    private $ChannelAccessToken,$HBUserID,$data,$HBMessage,$payload,$PostID,$order_data=[];
    public function __construct() {

        if (!defined('ABSPATH') || !current_user_can('administrator')) {
            http_response_code(404);
            die();
        }


        $this->init();
    }
    private function init(){
        $this->HBMessage            =   sanitize_text_field($_REQUEST['HBSendLineText']);
        $this->HBUserID             =   sanitize_text_field($_REQUEST['HBuserID']);
        $this->ChannelAccessToken   =   get_option('_hb_woo_line_channel_access_token');
        $this->PostID               =   sanitize_text_field($_REQUEST['PostID']);
        $order_id = $this->PostID;
        $order = wc_get_order( $order_id );
        $order_datas = $order->get_data();
        $this->order_data['id'] = $order_datas['id'];
        $this->order_data['nickname'] = get_user_meta($order_datas['customer_id'],'nickname' , true);

        $this->sendType();
    }
    private function sendType(){
        $this->HBMessage = $this->mestonor();
        $this->payload = [
            'to' =>[$this->HBUserID],
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $this->HBMessage
                ]
            ]
        ];

        $this->SendMessage();
    }
    private function mestonor(){

        $this->HBMessage = str_replace("{{order.usernickname}}",$this->order_data['nickname'],$this->HBMessage);
        $this->HBMessage = str_replace("{{order.number}}",$this->order_data['id'],$this->HBMessage);
        return $this->HBMessage;
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
new HEIBLACK_SendMessage();


