$(document).ready(function(){
    $('#jsid').change(function()
    {
        var id = $('#jsid').val();
        $('#element option:gt(0)').remove();
        var data = {jsid : $('#jsid').val()};
        //alert(data.jsid);
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
    $('.remove').hide();
    $('.all-any-none').hide();

    $('.add-rule').bind('click',function(){
        $('.add').show();
        $('.add-condition').show();
        //$('.remove').show();
        var div = $('<div>');
        j++;
        div.html(DynamicBox(""));
        $('#text_box').append(div);

    });

    function DynamicBox(value){
        return '<input type="button" id="submit_'+j+'" value = "add" class = "dynamic-add" name = "DynamicAdd">'+
            '<input type = "hidden" id="rules-id_'+j+'" name="rules-'+j+'">'/*+
         '<select name="sub-action-select" id="sud-action-select-id_'+j+'"> <option>--Choose color--</option> <option value="Green">Green</option> <option value="Red">Red</option> <option value="Yellow">Yellow</option> </select>'*/
    }


    var k =1;
    $('.add-condition').live("click", function () {
        $('.all-any-none').hide();
        $('#add-rule-id'+k).hide();
        k++;
    });
    $('.add').live("click",function(){
        $('.remove-action').remove();
    });

    var i =0;
    $('.dynamic-add').live("click",function() {
        var currentId = $(this).attr('id');
        id_value = splitfun(currentId);
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
        //alert(con.length);
        var $tr = $('<tr>').append(
            $('<td>').text(con[i].operator),
            $('<td>').text(con[i].value),
            $('<td>').text(rule_obj.actions.value)
        ).appendTo('#rule-table');
        i++;
    });
    /*$('#submit_id').live("click",function(){
     $('#result').val(i);
     });*/

    function splitfun(data){
        var num = data.split('_');
        return num[1];
    }
});

