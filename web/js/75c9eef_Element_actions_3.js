
(function($) {
    var a=0;
    $('.dynamic-add').live("click",function() {
        var currentId = $(this).attr('id');
        a = splitfun(currentId);
    });
    function splitfun(data){
        var num = data.split('_');
        return num[1];
    }
    $.fn.actionsBuilder = function(options) {
        var ans = $("#textbox_"+a).val();
        //console.log(ans);
        return ans;
    }
})(jQuery);

