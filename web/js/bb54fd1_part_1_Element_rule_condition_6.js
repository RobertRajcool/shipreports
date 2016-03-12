$(document).ready(function(){
    $('.add-condition').remove();
    $('.all-any-none').remove();
    $('.remove').remove();

    $('#add-rule-id').live('click',function(){
        $('.remove').remove();
    });

    var j = 0;
    $('.add-rule').bind('click',function(){
        var div = $('<div>');
        j++;
        $('#operator-id_'+j).hide();
        $('#field-id_'+j).hide();
        $('#text-value-id_'+j).hide();
        div.html(DynamicBox(""));
        $('#actions').append(div);

    });

    function DynamicBox(value){
        return  '<input type = "text" id="ref-condition-id_'+j+'" name="ref-condition-value">'+
            '<input type = "text" name="DynamicBox" id="textbox_'+j+'">'+
            '<input type="button" name="DynamicButton" id="del-remove_'+j+'" class = "del-remove" value ="Remove">'+
            '<input type="button" id="submit_'+j+'" value = "add" class = "dynamic-add" name = "DynamicAdd">'+
            '<input type = "hidden" name="element_details[rules-'+j+']" id="result_'+j+'">'

    }

    $('.del-remove').live("click",function(){
        j--;
    });
    $('#get_value').live("click",function(){
        $('#element_details_rules').val(j);
    });


});

