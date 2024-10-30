<?php
class HEIBLACK_LineBotEventSend{
    private  $ChannelAccessToken,$channelSecret,$LineBotEvent,$client,$nickname,$order,$total,$status,$payment,$HB_order_statuses;
    public function __construct() {
     
        require_once(dirname(dirname(__FILE__)) . '/lib/LINEBotTiny.php');
        $this->init();
    }
    public  function init(){
        $this->ChannelAccessToken   =   get_option('_hb_woo_line_channel_access_token');
        $this->channelSecret = get_option('_hb_woo_line_channelSecret');

        if(!$this->ChannelAccessToken&&!$this->channelSecret){
            return;
        }

        $message = '';
        $event = '';


        $_hb_woo_LineBotEvent =  get_option('_hb_woo_LineBotEvent_2');

        if(!$_hb_woo_LineBotEvent){
            return;
        }

        $this->LineBotEvent  = $_hb_woo_LineBotEvent;


      $this->checkLineBot();
    }

    private function checkLineBot(){
        $this->client = new LINEBotTiny($this->ChannelAccessToken, $this->channelSecret);
        if(isset($this->LineBotEvent)&&isset($this->LineBotEvent['checked'])&&$this->LineBotEvent['checked']=='on'){
            $LineUser = $this->client->parseEvents();
            $LineUser = $LineUser[0]['source']['userId'];
            $user_meta      = get_option('_hb_woo_line_user_meta');
            $CustomerID='';

            $CustomerID = $this->switchPluginType($user_meta,$LineUser);

            //don't line login
            if(!$CustomerID){
                $myarray[] = array(
                    'type' => 'text',
                    'text' => $this->LineBotEvent['notlineLogin']
                );
                $this->sendMessage($myarray);
                return;
            }


            $newstatuses = [];
            foreach ($this->LineBotEvent['order_statuses'] as $statuses){
                $newstatuses[] = str_replace("wc-",'', $statuses);
            }
            $args = array(
                'status' => $newstatuses,
                'customer_id' => $CustomerID,
                'limit' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
            );
            $orders = wc_get_orders($args);
            $this->nickname = get_user_meta($CustomerID,'nickname' , true);
            $this->HB_order_statuses = wc_get_order_statuses();

            //no order
            if(!$orders){
                $resultText  = str_replace("{{order.nickname}}",$this->nickname, $this->LineBotEvent['noOrder']);
                $myarray[] = array(
                    'type' => 'text',
                    'text' => $resultText
                );
                $this->sendMessage($myarray);
                return;
            }

            foreach ($orders as $value){
                $this->getOrderMessage($value);
                $HBTitle = $this->Mestonor($this->LineBotEvent['HBTitle']);
                $HBImage = $this->Mestonor($this->LineBotEvent['HBImage']);
                $HBText = $this->Mestonor($this->LineBotEvent['HBText']);
                $buttonTitle = $this->Mestonor($this->LineBotEvent['buttonTitle']);
                $buttonText = $this->Mestonor($this->LineBotEvent['buttonText']);
                $myarray[] = array(
                    'type' => 'template',
                    'altText' => $HBTitle,
                    'template' => array(
                        'type' => 'buttons',
                        "thumbnailImageUrl" => $HBImage,
                        'title' => $HBTitle,
                        'text' => $HBText,
                        'actions' => array(
                            array(
                                'type' => 'uri',
                                'label' => $buttonTitle,
                                'uri' => $buttonText
                            )
                        )
                    )
                );
            }

            $this->sendMessage($myarray);


        }
    }
    private function switchPluginType($user_meta,$LineUser){
        if($user_meta=='SuperSocializer'){
            $CustomerID= get_users(array(
                'meta_value' => $LineUser,
                'meta_key'=>'thechamp_social_id',
            ));

            if(!$CustomerID){
                return;
            }
            $CustomerID = $CustomerID[0]->ID;

            return $CustomerID;
        }else if($user_meta=='NEXTENDSOCIALLOGIN'){
            global $wpdb;


            $sql        =   $wpdb->prepare("SELECT ID FROM `wp_social_users` WHERE `identifier` = '$LineUser' ");
            $social_links   = $wpdb->get_results( $sql );

            if(!$social_links[0]){
                return;
            }
            $CustomerID         =   $social_links[0]->ID;
            return $CustomerID;
        }else if($user_meta=='HBLineLogin'){

            $CustomerID= get_users(array(
                'meta_value' => $LineUser,
                'meta_key'=>'_heiblack_social_line_id',
            ));

            if(!$CustomerID){
                return;
            }
            $CustomerID = $CustomerID[0]->ID;

            return $CustomerID;

        }
    }
    private function getOrderMessage($value){
        $this->payment = $value->get_payment_method_title();
        $this->total =  $value->get_total();
        $this->status =$value->get_status();
        $this->order = $value->get_id();
    }
    private function Mestonor($HBOrder){
        $result = $HBOrder;
        $result  = str_replace("{{order.id}}",$this->order , $result);
        $result  = str_replace("{{order.total}}",$this->total , $result);
        $result  = str_replace("{{order.status}}",$this->getOrderStatuses($this->status) , $result);
        $result  = str_replace("{{order.nickname}}",$this->nickname , $result);



        return $result;
    }
    private function getOrderStatuses($status){
        return $this->HB_order_statuses['wc-'.$status];
    }
    private function sendMessage($myarray){
        foreach ($this->client->parseEvents() as $event) {
            switch ($event['type']) {
                case 'message': //訊息觸發
                    $message = $event['message'];
                    switch ($message['type']) {
                        case 'text': //訊息為文字
                            if (strtolower($message['text']) == $this->LineBotEvent['UserEnter']) {

                                $this->client->replyMessage(array(
                                    'replyToken' => $event['replyToken'],
                                    'messages' =>$myarray
                                ));
                            }
                            break;
                    }
                    break;
            }
        }
    }

}

new  HEIBLACK_LineBotEventSend();

