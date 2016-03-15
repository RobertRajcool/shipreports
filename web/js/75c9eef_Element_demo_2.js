var global = this;
var conditions, actions, ageField, submit, result, ans;


(function($) {


    function onReady() {
        conditions = $("#conditions");
        actions = $("#actions");
        ageField = $("#ageField");
        submit = $("#submit");
        result = $("#element_details_rules");

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
            var sample = JSON.stringify(engine);

            $('#result_'+ans).val(sample);
        });

        $('.del-remove').live("click",function(){
            var currentId = $(this).attr('id');
            ans = splitfun(currentId);
            $('#field-id_'+ans).remove();
            $('#operator-id_'+ans).remove();
            $('#text-value-id_'+ans).remove();
            $('#submit_'+ans).remove();
            $(this).remove();
            $('#textbox_'+ans).remove();
            $('#result_'+ans).remove();
            /*var dummy = $('#element_details_rules').val()-1;
            $('#element_details_rules').val(dummy);*/

        });

        function splitfun(data){
            var num = data.split('_');
            return num[1];
        }

    }
    $(onReady);
})(jQuery);
