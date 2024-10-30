<?php
if (!defined('ABSPATH') || !current_user_can('administrator')) {
    http_response_code(404);
    die();
}

if($_POST){
    if(!isset($_POST['order'])){
        update_option('_hb_woo_new_order_2',$result ='');
    }else{
        $verifyHtml = [];
        foreach ($_POST['order'] as $key=>$value){
            $verifyHtml[$key] = array_map("sanitize_textarea_field", $value);
        }
        update_option('_hb_woo_new_order_2',$verifyHtml);
    }
}
?>
<div class="stbox">
    <h2>&ensp;<?php esc_html_e("New Order Event","hb-line-tools-for-woocommerce");?></h2>
</div>
<div class="stbox" style="box-sizing: content-box;padding-left: 20px;"  v-pre>
    <p>{{order.id}}  <?php esc_html_e("Order Number","hb-line-tools-for-woocommerce");?></p>
    <p>{{order.nickname}} <?php esc_html_e("Order Customer Nickname","hb-line-tools-for-woocommerce");?></p>
    <p>{{order.total}} <?php esc_html_e("Total Order Price","hb-line-tools-for-woocommerce");?></p>
    <p>{{order.payment}} <?php esc_html_e("Order Payment Method","hb-line-tools-for-woocommerce");?></p>
</div>
<div id="app">
    <form action="" method="post">
        <div class="stbox" v-for="(item,index) in orderEvent" v-bind:key="item.id">
            <div class="order-status" :id="item.id">
                <input type="checkbox" :name=" 'order[' + item.id + '][checked]' " class="Hb-checkbox"  :checked="item.checked" checked="false">
                <span><?php esc_html_e("Enable","hb-line-tools-for-woocommerce");?></span>
                <p>
                    <select :name=" 'order[' + item.id + '][type]' " v-model="item.type" >
                        <option value="text"><?php esc_html_e("Word","hb-line-tools-for-woocommerce");?></option>
                        <option value="image"><?php esc_html_e("Picture","hb-line-tools-for-woocommerce");?></option>
                        <option value="template"><?php esc_html_e("Template","hb-line-tools-for-woocommerce");?></option>
                    </select>
                </p>
                <p>
                    <textarea :name=" 'order[' + item.id + '][text]' "  rows="2" style="width: 100%"  v-if="item.type === 'text'">{{item.text}}</textarea>
                    <input type="text" :name=" 'order[' + item.id + '][text]' " v-if="item.type === 'image'" style="width: 100%" :value="item.text">

                    <template v-if="item.type === 'template'">
                        <p><?php esc_html_e("Title Text","hb-line-tools-for-woocommerce");?></p>
                        <input type="text" :name=" 'order[' + item.id + '][title]' " style="width: 100%" :value="item.title">
                        <p><?php esc_html_e("Picture","hb-line-tools-for-woocommerce");?></p>
                        <input type="text" :name=" 'order[' + item.id + '][image]' " style="width: 100%" :value="item.image">
                        <p><?php esc_html_e("Text","hb-line-tools-for-woocommerce");?></p>
                        <textarea  :name=" 'order[' + item.id + '][text]' "  rows="1" style="width: 100%" :value="item.text">{{item.text}}</textarea>
                        <h3><?php esc_html_e("Button","hb-line-tools-for-woocommerce");?></h3>
                        <div class="HB-button-box">
                            <p><?php esc_html_e("Button Text","hb-line-tools-for-woocommerce");?></p>
                            <input type="text" :name=" 'order[' + item.id + '][button]' " style="width: 100%" :value="item.buttonText">
                            <p><?php esc_html_e("Link","hb-line-tools-for-woocommerce");?></p>
                            <input type="text" :name=" 'order[' + item.id + '][url]' " style="width: 100%" :value="item.buttonUrl">
                        </div>
                    </template>
                </p>
                <p>
                    <input type="button" @click="deleteEvent(index)" class="button hb-del" value="<?php esc_html_e("Delete","hb-line-tools-for-woocommerce");?>">
                </p>
            </div>
        </div>
        <input type="text" name="heiblack" style="display:none" value="heiblack">
        <div class="stbox">
            <div class="HB-button-container">
                <input type="submit"  value="<?php esc_html_e("Save","hb-line-tools-for-woocommerce");?>" class="button button-primary" >
                &ensp;&ensp;
                <input type="button" @click="addEvent" value="<?php esc_html_e("Add","hb-line-tools-for-woocommerce");?>" class="button">
            </div>
        </div>
    </form>
</div>
<script>
    const app = Vue.createApp({
        data() {
            return {
                orderEvent:[],
                boxButton:[],
                hbId:1,
                buttonId:1,
                hbId2:1,
            }
        },
        created(){
           this.getOrderEvent();
        },
        methods:{
            addEvent:function (){
                this.orderEvent.push({id:vm.hbId,type:'text'});
                vm.hbId++
            },
            deleteEvent: function(index) {
                this.orderEvent.splice(index, 1);
            },
            deleteButtonBoxEvent:function (index){
               if ( this.boxButton.length>1){
                this.boxButton.splice(index, 1);
               }
            },
            addbutton:function (index){
                if ( this.boxButton.length<=4){
                    this.buttonId++;
                    this.boxButton.push({id:this.buttonId});
                    console.log(this.buttonId);
                }
            },
            getOrderEvent:function (){

                var data = new FormData();
                data.append('action', 'get_NewOrder_action');
                axios.post('admin-ajax.php', data)
                    .then(function (response) {

                        var response_data = response.data.data;
                        if (response_data == "" )  return;

                        var array = Object.values(response_data);
                        for (let i = 0; i < array.length; i++) {
                           if(array[i]['type']!='template'){
                               var hasChecked = array[i]['checked'];
                               var isChecked = false;
                               if(JSON.stringify(hasChecked)) isChecked = true;
                               vm.orderEvent.push({
                                   id:vm.hbId,
                                   checked:isChecked,
                                   type:array[i]['type'],
                                   text:array[i]['text']
                               });

                           }else{
                               var hasChecked = array[i]['checked'];
                               var isChecked = false;
                               if(JSON.stringify(hasChecked)) isChecked = true;

                               vm.orderEvent.push({
                                   id:vm.hbId,
                                   checked:isChecked,
                                   title:array[i]['title'],
                                   image:array[i]['image'],
                                   type:array[i]['type'],
                                   text:array[i]['text'],
                                   buttonText:array[i]['button'],
                                   buttonUrl:array[i]['url'],
                               });
                           }
                            vm.hbId++
                        }
                    });
            }
        }
    })
    const vm = app.mount('#app');
</script>







<style>
    #app{

    }

    .stbox
    {
        width: 50%;
        margin: 0 auto;


    }
    .stbox > div.HB-button-container{
        text-align: center;

    }
    .stbox > div.order-status{
        padding: 15px;
        margin-bottom: 15px;
        background: #fff;
        border-radius: 10px;
    }
    .HB-button-box{

    }


</style>







