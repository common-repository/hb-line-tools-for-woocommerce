<?php
if (!defined('ABSPATH') || !current_user_can('administrator')) {
    http_response_code(404);
    die();
}

if($_POST){
    $verifyHtml = [];
    foreach ($_POST as $key=>$value){
        if($key=='order_statuses'){
            $verifyHtml[$key] = $value;
        }else{
            $verifyHtml[$key] = sanitize_textarea_field($value);
        }
    }

    update_option('_hb_woo_LineBotEvent_2',$verifyHtml);
    echo "<script>alert('success');</script>";
}
$array = wc_get_order_statuses();
$_hb_woo_LineBotEvent =  get_option('_hb_woo_LineBotEvent_2');

?>
<div id="HB-LINE-explain">
    <div class="stuffbox" style="box-sizing: content-box;padding-left: 20px;"  >
        <p>{{order.id}}  <?php esc_html_e("Order Number","hb-line-tools-for-woocommerce");?></p>
        <p>{{order.nickname}} <?php esc_html_e("Order Customer Nickname","hb-line-tools-for-woocommerce");?></p>
        <p>{{order.total}} <?php esc_html_e("Total Order Price","hb-line-tools-for-woocommerce");?></p>
        <p>{{order.status}} <?php esc_html_e("Order Status","hb-line-tools-for-woocommerce");?></p>
    </div>
    <p></p>
    <form method="post">
        <div class="stuffbox" style="padding: 10px">
            <h3><?php esc_html_e("Enable","hb-line-tools-for-woocommerce");?></h3>
                <?php if(isset($_hb_woo_LineBotEvent['checked']) && $_hb_woo_LineBotEvent['checked']=='on'): ?>
                    <input type="checkbox" class="Hb_checkbox" name="checked" checked="checked">
                <?php else: ?>
                    <input type="checkbox" class="Hb_checkbox" name="checked">
                <?php endif; ?>

                <h3><?php esc_html_e("When the user enters make an order query :","hb-line-tools-for-woocommerce");?></h3>
                <input type="text"  name="UserEnter" value="<?php echo @esc_html($_hb_woo_LineBotEvent['UserEnter']);?>">
            <h3><?php esc_html_e("Orders allowed for inquiries :","hb-line-tools-for-woocommerce");?></h3>
                <?php foreach ($array as $key2=>$value2): ?>
                        <?php if(isset($_hb_woo_LineBotEvent['order_statuses'])&&in_array($key2,$_hb_woo_LineBotEvent['order_statuses'])): ?>
                            <input type="checkbox" value="<?php echo esc_html($key2); ?>" name="order_statuses[]" checked="checked">
                        <?php else: ?>
                            <input type="checkbox" value="<?php echo esc_html($key2); ?>" name="order_statuses[]">
                        <?php endif; ?>
                    <?php echo esc_html($value2); ?>
                <?php endforeach;?>
                <p></p>
                    <h3><?php esc_html_e("Title Text:","hb-line-tools-for-woocommerce");?></h3>
                    <input type="text" name="HBTitle" value="<?php echo @esc_html($_hb_woo_LineBotEvent['HBTitle']);?>">

                <p></p>
                    <h3><?php esc_html_e("Picture:","hb-line-tools-for-woocommerce");?></h3>
                    <input type="text"  name="HBImage" value="<?php echo @esc_html($_hb_woo_LineBotEvent['HBImage']);?>">

                <p></p>
                    <h3><?php esc_html_e("Text:","hb-line-tools-for-woocommerce");?></h3>
                    <textarea style="width: 100%" name="HBText"><?php echo @esc_html($_hb_woo_LineBotEvent['HBText']);?></textarea>

                <p></p>
                    <h3><?php esc_html_e("Button Text:","hb-line-tools-for-woocommerce");?></h3>
                    <input type="text"  name="buttonTitle" value="<?php echo @esc_html($_hb_woo_LineBotEvent['buttonTitle']);?>">
                    <h3><?php esc_html_e("Link:","hb-line-tools-for-woocommerce");?></h3>
                    <input type="text"  name="buttonText" value="<?php echo @esc_html($_hb_woo_LineBotEvent['buttonText']);?>">

                <hr>
                <h3><?php esc_html_e("When there is no matching order status :","hb-line-tools-for-woocommerce");?></h3>
                <p></p>
                    <textarea style="width: 100%" name="noOrder"><?php echo @esc_html($_hb_woo_LineBotEvent['noOrder'])?></textarea>

                <h3><?php esc_html_e("Not Line member :","hb-line-tools-for-woocommerce");?></h3>
                <p></p>
                    <textarea style="width: 100%" name="notlineLogin"><?php echo @esc_html($_hb_woo_LineBotEvent['notlineLogin'])?></textarea>
        </div>
        <p></p>
        <div class="stbox">
            <div class="HB-button-container">
                <input type="submit"  value="<?php esc_html_e("Save","hb-line-tools-for-woocommerce");?>" class="button button-primary" >
            </div>
        </div>
    </form>
</div>
<style>
    #HB-LINE,
    #HB-LINE-explain
    {
        padding: 15px;
    }
    #HB-LINE-explain input[type=text]{
        width: 100%;
    }
    .stuffbox
    {
        width: 50%;
        margin: 0 auto;
        border: 0;
        border-radius: 10px;
        overflow: hidden;
    }
    .stuffboxChild{

    }
    .stbox > div.HB-button-container{
        text-align: center;

    }
</style>



