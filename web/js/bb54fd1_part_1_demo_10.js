var conditions, actions, ageField, submit, result;

(function($) {


    function onReady() {
        conditions = $("#conditions");
        actions = $("#actions");
        ageField = $("#ageField");
        submit = $("#submit");
        result = $("#result");

        exports.valueexcelsheet = function (r)
        {
           return r;
        };
        initializeConditions();
        initializeActions();
        initializeForm();
    }
    function assignvalue()
    {

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

        submit.click(function(e) {
            e.preventDefault();
            var engine = new RuleEngine({
                conditions: conditions.conditionsBuilder("data"),
                actions: actions.actionsBuilder("data")
            });
            var engine1 = new RuleEngine({
                conditions: conditions.conditionsBuilder("data")
                //actions: actions.actionsBuilder("data")
            });

            var engine2 = new RuleEngine({
                //conditions: conditions.conditionsBuilder("data"),
                actions: actions.actionsBuilder("data")
            });
            var conditionsAdapter =
            {
                ageField: ageField.val()
            };


           //alert(engine1);
            var conditionsAdapter = {
                ageField: ageField.val()
            };

            var res = engine1.run(conditionsAdapter);
            alert(res);

            var actionValue = engine.actions[0].value;
            alert(actionValue);


            var sample = JSON.stringify(engine);
            result.val(sample);
        });
    }

    $(onReady);
})(jQuery);
