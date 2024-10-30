<?php
if (!defined('ABSPATH') || !current_user_can('administrator')) {
    http_response_code(404);
    die();
}

if (isset($_POST['ChannelAccessToken']) && isset($_POST['userIDMeta'])){
    try{
        update_option('_hb_woo_line_channel_access_token', $value = sanitize_text_field($_POST['ChannelAccessToken']),  $autoload = 'yes');
        update_option('_hb_woo_line_channelSecret', $value = sanitize_html_class($_POST['channelSecret']),  $autoload = 'yes');
        update_option('_hb_woo_line_user_meta', $value = sanitize_html_class($_POST['userIDMeta']),  $autoload = 'yes');
        echo "<script>alert('success');location.reload()</script>";
    }catch (Exception $e){
        echo "<script>alert('fail');location.reload()</script>";
    }
}
?>
<p></p>
<p></p>
<p></p>
<div id="HB-LINE">
    <div class="stuffbox">
        <h3><?php esc_html_e("Setting","hb-line-tools-for-woocommerce");?></h3>
        <p style="color: red"><b><?php esc_html_e("All Required","hb-line-tools-for-woocommerce");?></b></p>
        <div class="inside">
            <form class="sign-form1"  method="post">
                <h2>Channel access token</h2>
                <input type="text" style="width: 99%" name="ChannelAccessToken" value="<?php echo esc_html($user_ChannelAccessToken); ?>">
                <h2>channelSecret</h2>
                <input type="text" style="width: 99%" name="channelSecret" value="<?php echo esc_html($user_channelSecret); ?>">
                <h2><b>LINE ID</b></h2>
                    <p>Please select the Line login plugin to use</p>
                    <select name="userIDMeta" id="">
                            <option value="SuperSocializer" <?php if($user_meta=='SuperSocializer') echo 'selected="selected"';?>>Super Socializer</option>
                            <option value="NEXTENDSOCIALLOGIN" <?php if($user_meta=='NEXTENDSOCIALLOGIN') echo 'selected="selected"';?>>Nextend Social Login</option>
                            <option value="HBLineLogin" <?php if($user_meta=='HBLineLogin') echo 'selected="selected"';?>>HB Line Login Tiny</option>
                    </select>

                    <p></p>
                    <input id="the_champ_enable_fblike"  type="submit" name="save" class="button button-primary" value="Save">
            </form>
        </div>
    </div>
</div>
<p></p>
<p></p>
<div id="HB-LINE-explain">
    <div class="stuffbox" style="padding: 10px">
        <a target='_blank' href='https://piglet.me/hb-line-tools-for-woocommerce/'><?php echo __('Have Bug or suggest','hb-line-tools-for-woocommerce')?></a>
        <h2><?php esc_html_e("Send Test","hb-line-tools-for-woocommerce");?></h2>
        <p>Your Line user ID</p>
        <input type="text" id="HB-send-text" style="width: 100%" v-model="ULINE">
        <br><br>
        <input type="button" @click="sendtest" :disabled="isDisabled" class="button button-primary" value="send">
    </div>
</div>
<?php


?>
<script>
    const app = Vue.createApp({
        data() {
            return {
                ULINE:'',
                isDisabled: false
            }
        },
        created(){

        },
        methods:{
            sendtest:function (){

                vm.isDisabled = true;
                var data = new FormData();
                data.append('action', 'simpleTest_action');
                data.append('ULINE', vm.ULINE);
                axios.post('admin-ajax.php', data)
                    .then(function (response) {
                        alert('Please confirm whether you have received the message');
                        vm.isDisabled = false;
                    })
                    .catch(function (error) {

                    })
                ;
            },
        }
    })
    const vm = app.mount('#HB-LINE-explain');
</script>
<style>
    .stuffbox
    {
        width: 50%;
        margin: 0 auto;
        padding: 10px;
        border-radius: 10px;
        border: 0;
    }
    #HB-LINE-explain h2{

    }

    .stbox > div.order-status{
        padding: 15px;
        margin-bottom: 15px;
        background: #fff;
        border-radius: 10px;
    }
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        height: 30px;
    }
</style>
