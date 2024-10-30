<form>
    <p><b>客戶暱稱:</b><?php echo esc_html($nickname)?></p>
    <p><b>客戶綁定之LINEID:</b><?php echo esc_html($this->socialId); ?></p>
    <input type="text" id="HBuserID"  style="display:none" value="<?php echo esc_html($this->socialId); ?>">
    <input type="text" id="PostID" style="display:none" value="<?php echo esc_html($post->ID) ?>">
    <textarea  id="HBSendLineText" name="ooo"  style="width: 100%" cols="25"></textarea>
    <p></p>
    <input type="text" style="display:none" id="HBurl" value="<?php echo plugins_url( 'HB-SimpleSendEvent-API.php' , __FILE__ ); ?>">
    <input id="HBSendLine"  type="button" name="save" class="button button-primary" value="發送">
</form>
<script>
    window.onload = function () {
        var HBSendLine = document.getElementById('HBSendLine');
        HBSendLine.onclick = function(){

            var HBuserID = document.getElementById('HBuserID').value;
            var HBSendLineText = document.getElementById('HBSendLineText').value;
            var HBurl =  document.getElementById('HBurl').value;
            var PostID =  document.getElementById('PostID').value;


            var data = new FormData();
            data.append('HBuserID', HBuserID);
            data.append('HBSendLineText', HBSendLineText);
            data.append('HBurl', HBurl);
            data.append('PostID', PostID);

            data.append('action', 'simpleSend_action');


            axios.post('admin-ajax.php', data)
                .then(function (response) {
                    console.log(response.data);
                })

        }
    }

</script>

<!--axios.post(HBurl,-->
<!--{  data: HBSendLineText,HBuserID:HBuserID,PostID:PostID,'headers': { 'Content-Type': 'application/json', 'Accept': 'application/json' } })-->
<?php



