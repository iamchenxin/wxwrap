function wx_show(context) {
    jQuery(".wx_show",context).click(function(){
        jQuery(".wx_wechat").toggle();
    });
    console.log('here~~');
}

function init_ui(context){ 
  wx_show(context);
}

jQuery(function(){
    // jQuery methods go here...
    init_ui();
    console.log('jQuery~~');
});