$(document).ready(function(){

    $('#row_id').hide();
    var j = 0;

    $('.remove').live("click",function(){
        j--;
        $('.action-value').remove();
        $('.dynamic-add').remove();
    });

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
            var action_value = $("#action-value_"+a).val();
            return action_value;
        }
    })(jQuery);


    var conditions, actions, submit, ans;

    function onReady() {
        conditions = $("#conditions");
        actions = $("#actions");
        submit = $("#submit");

        initializeConditions();
        initializeActions();
        initializeForm();
    }
    function initializeConditions() {
        conditions.conditionsBuilder({
            fields: [
                {label: "Value", name: "ageField", operators: [
                    {label: "is equal to", name: "equalTo", fieldType: "text"}

                ]}
            ]
        });
    }
    function initializeActions() {
        actions.actionsBuilder({
            fields: []
        });
    }
    function initializeForm() {

        $('.dynamic-add').live("click",function(e) {

            e.preventDefault();
            var currentId = $(this).attr('id');
            ans = splitfun(currentId);

            var engine = new RuleEngine({
                conditions: conditions.conditionsBuilder("data"),
                actions: actions.actionsBuilder("data")
            });

            var first = JSON.stringify(engine);
            $('#rules-id_'+ans).val(first);

            var rule_obj = $('#rules-id_'+ans).val();
            rule_obj = JSON.parse(rule_obj);
            var con = rule_obj.conditions.all;
            var new_rule_obj = con.filter(filterEmpty);
            console.log(new_rule_obj);
            rule_obj.conditions.all = new_rule_obj;
            var final_rule = JSON.stringify(rule_obj);
            $('#rules-id_'+ans).val(final_rule);

            $('.action-value').remove();
            $('.remove').remove();
            $('.value').remove();
            $('.field').remove();
            $('.operator').remove();
            $(this).remove();
            $('#row_id').show();

            var obj = rule_obj;
            if(obj.conditions.all[0].operator=='equalTo')
            {
                obj.conditions.all[0].operator = '=';
            }

            var condition_text = obj.conditions.all[0].operator +''+ obj.conditions.all[0].value;
            var $tr = $('<tr>').append(
                $('<td>').text(condition_text),
                $('<td>').text(obj.actions)
            ).appendTo('#rule-table');

        });

        function splitfun(data){
            var num = data.split('_');
            return num[1];
        }

        function filterEmpty(obj) {
            if(jQuery.isEmptyObject(obj)) {
                return false;
            } else {
                return true;
            }
        }

    }
    $(onReady);

    $('.add-condition').hide();
    $('.remove').hide();
    $('.all-any-none').hide();
    $('#remove-id').remove();


    $('.add-rule').bind('click',function(){
        $('.field').hide();
        $('.remove').show();
        var div = $('<div>');
        j++;
        $('#operator-id_'+j).hide();
        $('#field-id_'+j).hide();
        $('#text-value-id_'+j).hide();
        div.html(DynamicBox(""));
        $('#actions').append(div);

    });

    function DynamicBox(value){
        return '<input type = "text" name="action_value" id="action-value_'+j+'" class="action-value">'+
            '<input type="button" id="submit_'+j+'" value = "add" class = "dynamic-add" name = "DynamicAdd">'+
            '<input type = "hidden" id="rules-id_'+j+'" name="rules-'+j+'">'
    }

    $('.del-remove').live("click",function(){
        j--;
    });
    $('#get_value').live("click",function(){
        $('#element_details_rules').val(j);
    });


});

