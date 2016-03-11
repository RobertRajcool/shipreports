$(document).ready(function(){
    $('.add').remove();
    $('.add-condition').remove();
    $('.all-any-none').remove();
    $('.remove').remove();
    $('#row_id').hide();

    $('.add-rule').html('Show-Rules');

    var count = 0;
    var num = 0;
    var edit_count = 0;

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

                        $('#rule_obj_text').append($('<input>').attr({'type':'text','name':'rule_name['+i+']', 'id':'rule_obj_text-'+i+''}).val(data.Rule_Array[i].rules));
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
                        var $tr = $('<tr>').append(
                            $('<td>').text(condition_text),
                            $('<td>').text(rule_obj.actions.value),
                            $('<td>').append($('<input>').attr({'type':'button', 'id':'edit-'+i+'', 'class':'edit_class'}).val("edit"))
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
        edit_count=parseInt(ans);
        var ans1 = $('#rule_obj_text-'+ans).val();
        var object_rule = JSON.parse(ans1);
        if(edit_count==parseInt(ans))
        {
            $('#rule_edit_button').append($('<input>').attr({'type':'button', 'id':'edit_add-'+ans+'', 'class':'edit_add_class'}).val("add"));
        }

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

            $('.edit_add_class').live("click",function(e) {
                var currentId1 = $(this).attr('id');
                var ans1 = splitfun(currentId);
                //alert(ans1);
                e.preventDefault();
                var engine = new RuleEngine({
                    conditions: conditions.conditionsBuilder("data"),
                    actions: actions.actionsBuilder("data")
                });
                var sample = JSON.stringify(engine);
                $('#rule_obj_text-'+ans1).val(sample);
            });
        }
        $(onReady);
        $('#remove-id').remove();
        $('#action_remove').remove();
        $('.all-any-none').hide();
    });

    function splitfun(data){
        var num = data.split('-');
        return num[1];
    }

});




