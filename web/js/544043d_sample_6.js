$(document).ready(function(){
    $('#row_id').hide();
    $('#jsid').change(function()
    {
        var id = $('#jsid').val();
        $('#element option:gt(0)').remove();
        var data = {jsid : $('#jsid').val()};
        if($(this).val())
        {

            $.ajax({
                type: "POST",
                data: data,
                url: "new_temp",
                success: function(data)
                {
                    $.each(data.kpiNameArray, function(i, listkpi) {

                        $('#element').append($('<option>', {
                            value: listkpi.id, text : listkpi.elementName
                        }));
                    });


                },
                error: function(XMLHttpRequest, textStatus, errorThrown)
                {
                    alert('Error : ' + errorThrown);
                }
            });
        }
    });

    var j=0;

    $('.add').hide();
    $('.add-condition').hide();
    $('.remove').remove();
    $('.all-any-none').hide();

    $('.add-rule').bind('click',function(){
        $('.add').show();
        $('.add-condition').show();
        var div = $('<div>');
        j++;
        div.html(DynamicBox(""));
        $('#text_box').append(div);

    });

    function DynamicBox(value){
        return '<input type="button" id="submit_'+j+'" value = "add" class = "dynamic-add" name = "DynamicAdd">'+
            '<input type = "hidden" id="rules-id_'+j+'" name="rules-'+j+'">'
    }

    var k =1;
    $('.add-condition').live("click", function () {
        $('.all-any-none').hide();
        $('#add-rule-id'+k).remove();
        $('#remove-condition-id'+k).remove();
        k++;
    });
    $('.add').live("click",function(){
        $('.remove-action').remove();
    });

    var i =0;
    var condition_text = "";
    $('.dynamic-add').live("click",function() {
        var currentId = $(this).attr('id');
        var id_value = splitfun(currentId);

        $('.add').hide();
        $('.add-condition').hide();
        $('.remove').remove();
        $('.remove-condition').hide();
        $('.value').remove();
        $('.field').remove();
        $('.operator').remove();
        $('.action-select').hide();
        $(this).remove();

        var rule = $('#rules-id_'+id_value).val();
        var rule_obj = JSON.parse(rule);
        var con = rule_obj.conditions.all;
        var new_rule_obj = con.filter(filterByID);
        rule_obj.conditions.all = new_rule_obj;
        var final_rule = JSON.stringify(rule_obj);
        $('#rules-id_'+id_value).val(final_rule);
        $('#row_id').show();

        $.each(new_rule_obj, function(j)
        {
            if(new_rule_obj[j].operator=='equalTo')
            {
                new_rule_obj[j].operator = '=';
            }
            else if(new_rule_obj[j].operator=='notEqualTo')
            {
                new_rule_obj[j].operator = '!=';
            }
            else if(new_rule_obj[j].operator=='greaterThan')
            {
                new_rule_obj[j].operator = '>';
            }
            else if(new_rule_obj[j].operator=='greaterThanEqual')
            {
                new_rule_obj[j].operator = '>=';
            }
            else if(new_rule_obj[j].operator=='lessThan')
            {
                new_rule_obj[j].operator = '<';
            }
            else if(new_rule_obj[j].operator=='lessThanEqual')
            {
                new_rule_obj[j].operator = '<=';
            }

            var condition_text_one = new_rule_obj[j].operator+new_rule_obj[j].value;
            if(j==0)
            {
                condition_text = condition_text_one;
            }
            if(j>0)
            {
                condition_text = condition_text+'   ' + '&&' +'   '+condition_text_one;
            }
        });
        i++;
        var $tr = $('<tr>').attr({'id':'row-id_'+i}).append(
            $('<td>').text(condition_text),
            $('<td>').text(rule_obj.actions.value),
            $('<td>').append($('<input>').attr({'type':'button', 'id':'delete_'+j+'', 'class':'delete_class'}).val("delete"))
        ).appendTo('#rule-table');
    });

    $('.remove-condition').live("click",function(){
        $('.dynamic-add').remove();
        $('.add').hide();
        $('.add-condition').hide();
        $('.action-select').hide();
        $(this).remove();
    });

    $('.delete_class').live("click",function(){
        var currentId = $(this).attr('id');
        var id_value = splitfun(currentId);
        $('#row-id_'+id_value).remove();
        var count_row = $('#rule-table tr').length;
        if(count_row<=1)
        {
            $('#row_id').hide();
        }
        $('#rules-id_'+id_value).val('');
    });

    function splitfun(data){
        var num = data.split('_');
        return num[1];
    }

    function filterByID(obj) {
        if(jQuery.isEmptyObject(obj)) {
            return false;
        } else {
            return true;
        }
    }

});

