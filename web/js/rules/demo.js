var conditions, actions, ageField, submit;

(function($) {


    function onReady() {
        conditions = $("#conditions");
        actions = $("#actions");
        ageField = $("#ageField");
        submit = $('.dynamic-add');

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
            ]
        });
    }
    function initializeActions() {
        actions.actionsBuilder({
            fields: [
                {label: "Green", name: "Green"},
                {label: "Red", name: "Red"},
                {label: "Yellow", name: "Yellow"}
            ]
        });
    }
    function initializeForm() {

        submit.live("click",function(e) {
            e.preventDefault();
            var currentId = $(this).attr('id');
            var id_value = splitFun(currentId);
            var engine = new RuleEngine({
                conditions: conditions.conditionsBuilder("data"),
                actions: actions.actionsBuilder("data")
            });
            var sample = JSON.stringify(engine);
            $('#rules-id_'+id_value).val(sample);
            $('#result').val(id_value);
        });

        function splitFun(data){
            var num = data.split('_');
            return num[1];
        }
    }
    $(onReady);
})(jQuery);