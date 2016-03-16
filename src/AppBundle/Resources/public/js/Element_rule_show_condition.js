$(document).ready(function(){
    $('.add').remove();
    $('.add-condition').remove();
    $('.all-any-none').remove();
    $('.remove').remove();
    $('#row_id').hide();

    $('.add-rule').html('Show-Rules');

    var count = 0;
    var num = 0;

    $('.add-rule').live('click',function(){
        count++;
        $('.remove').remove();
        $('.remove-condition').remove();
        $('.field').remove();
        $('.operator').remove();
        $('.value').remove();
        $('#row_id').show();
        var data = {Id : $('#id').val()};
        var condition_text = "";

        $.ajax({
            type: "POST",
            data:data,
            url: "rule",
            success: function(data)
            {
                if(count==1)
                {
                    $.each(data.Rule_Array, function(i)
                    {
                        var rule_obj = JSON.parse( data.Rule_Array[i].rules);

                        $('#rule_obj_text').append($('<input>').attr({'type':'hidden','name':'rule_name['+i+']', 'id':'rule_obj_text-'+i+''}).val(data.Rule_Array[i].rules));
                        $('#rule_obj_text').append($('<br>'));

                        $.each(rule_obj.conditions.all, function(j)
                        {
                            if(i==num)
                            {
                                if(rule_obj.conditions.all[j].operator=='equalTo')
                                {
                                    rule_obj.conditions.all[j].operator='=';
                                }
                                else if(rule_obj.conditions.all[j].operator=='notEqualTo')
                                {
                                    rule_obj.conditions.all[j].operator='!=';
                                }
                                else if(rule_obj.conditions.all[j].operator=='greaterThan')
                                {
                                    rule_obj.conditions.all[j].operator='>';
                                }
                                else if(rule_obj.conditions.all[j].operator=='greaterThanEqual')
                                {
                                    rule_obj.conditions.all[j].operator='>=';
                                }
                                else if(rule_obj.conditions.all[j].operator=='lessThan')
                                {
                                    rule_obj.conditions.all[j].operator='<';
                                }
                                else if(rule_obj.conditions.all[j].operator=='lessThanEqual')
                                {
                                    rule_obj.conditions.all[j].operator='<=';
                                }
                                var condition_text_one =rule_obj.conditions.all[j].operator+rule_obj.conditions.all[j].value;
                                if(j==0)
                                {
                                    condition_text = condition_text_one;
                                }
                                if(j>0)
                                {
                                    condition_text = condition_text+'   ' + '&&' +'   '+condition_text_one;
                                }
                            }
                        });
                        var $tr = $('<tr>').attr({'id':'row_id-'+i}).append(
                            $('<td>').text(condition_text),
                            $('<td>').text(rule_obj.actions.value),
                            $('<td>').append($('<input>').attr({'type':'button', 'id':'edit-'+i+'', 'class':'edit_class'}).val("edit")),
                            $('<td>').append($('<input>').attr({'type':'button', 'id':'delete-'+i+'', 'class':'delete_class'}).val("delete"))
                        ).appendTo('#rule-table');
                        num++;
                    });
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown)
            {
                alert('Error : ' + errorThrown);
            }
        });

        $('#id').val(count);

    });
    var conditions, actions;

    $('.edit_class').live("click",function(){
        var currentId = $(this).attr('id');
        var ans = splitfun(currentId);
        $('#row_id-'+ans).remove();
        var ans1 = $('#rule_obj_text-'+ans).val();
        var object_rule = JSON.parse(ans1);
        $('#rule_edit_button').append($('<input>').attr({'type':'button', 'id':'edit_add-'+ans+'', 'class':'edit_add_class'}).val("add"));
        $('.edit_add_class').hide();
        $('#edit_add-'+ans).show();

        function onReady() {
            conditions = $("#conditions");
            actions = $("#actions");

            initializeConditions();
            initializeActions();
            initializeForm();
        }

        function initializeConditions() {
            conditions.conditionsBuilder({
                fields: [
                    {label: "Value", name: "ageField", operators: [
                        {label: "is present", name: "present", fieldType: "none"},
                        {label: "is blank", name: "blank", fieldType: "none"},
                        {label: "is equal to", name: "equalTo", fieldType: "text"},
                        {label: "is not equal to", name: "notEqualTo", fieldType: "text"},
                        {label: "is greater than", name: "greaterThan", fieldType: "text"},
                        {label: "is greater than or equal to", name: "greaterThanEqual", fieldType: "text"},
                        {label: "is less than", name: "lessThan", fieldType: "text"},
                        {label: "is less than or equal to", name: "lessThanEqual", fieldType: "text"}
                    ]}
                ],
                data: object_rule.conditions
            });
        }
        function initializeActions() {
            actions.actionsBuilder({
                fields: [
                    {label: "Green", name: "Green"},
                    {label: "Red", name: "Red"},
                    {label: "Yellow", name: "Yellow"}
                ],
                data: [object_rule.actions]
            });
        }
        function initializeForm() {
            var edit_condition_text="";

            $('.edit_add_class').live("click",function(e) {
                var currentId1 = $(this).attr('id');
                var ans1 = splitfun(currentId);
                e.preventDefault();
                var engine = new RuleEngine({
                    conditions: conditions.conditionsBuilder("data"),
                    actions: actions.actionsBuilder("data")
                });
                var sample = JSON.stringify(engine);
                $('#rule_obj_text-'+ans1).val(sample);
                //console.log(engine.conditions);
                $.each(engine.conditions.all, function(j)
                {
                    if(engine.conditions.all[j].operator=='equalTo')
                    {
                        engine.conditions.all[j].operator='=';
                    }
                    else if(engine.conditions.all[j].operator=='notEqualTo')
                    {
                        engine.conditions.all[j].operator='!=';
                    }
                    else if(engine.conditions.all[j].operator=='greaterThan')
                    {
                        engine.conditions.all[j].operator='>';
                    }
                    else if(engine.conditions.all[j].operator=='greaterThanEqual')
                    {
                        engine.conditions.all[j].operator='>=';
                    }
                    else if(engine.conditions.all[j].operator=='lessThan')
                    {
                        engine.conditions.all[j].operator='<';
                    }
                    else if(engine.conditions.all[j].operator=='lessThanEqual')
                    {
                        engine.conditions.all[j].operator='<=';
                    }

                    var edit_condition_text_one =engine.conditions.all[j].operator+engine.conditions.all[j].value;

                    if(j==0)
                    {
                        edit_condition_text = edit_condition_text_one;
                    }
                    if(j>0)
                    {
                        edit_condition_text = edit_condition_text+'   ' + '&&' +'   '+edit_condition_text_one;
                    }

                });
                var $tr = $('<tr>').attr({'id':'row_id-'+ans1}).append(
                    $('<td>').text(edit_condition_text),
                    $('<td>').text(engine.actions.value),
                    $('<td>').append($('<input>').attr({'type':'button', 'id':'edit-'+ans1+'', 'class':'edit_class'}).val("edit")),
                    $('<td>').append($('<input>').attr({'type':'button', 'id':'delete-'+ans1+'', 'class':'delete_class'}).val("delete"))
                ).appendTo('#rule-table');

                $('.operator').remove();
                $('.add-rule').remove();
                $('.value').remove();
                $('.add-condition').remove();
                $('.remove').remove();
                $('.field').remove();
                $('.add').remove();
                $('.action-select').remove();
                $(this).remove();
            });
        }
        $(onReady);
        $('#remove-id').remove();
        $('#action_remove').remove();
        $('.all-any-none').hide();
    });

    $('.delete_class').live("click",function(){
        var currentId = $(this).attr('id');
        var id_value = splitfun(currentId);
        $('#row_id-'+id_value).remove();
        var count_row = $('#rule-table tr').length;
        if(count_row<=1)
        {
            $('#row_id').hide();
        }
        //$('#rules-id_'+id_value).val('');
    });

    function splitfun(data){
        var num = data.split('-');
        return num[1];
    }

});

