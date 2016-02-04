var conditions, actions, ageField, submit, result, obj1, obj2, obj3;

(function($) {

    function onReady() {
        conditions = $("#conditionsedit");
        actions = $("#actionsedit");
        ageField = $("#ageFieldEdit");
        submit = $("#submitedit");
        result = $("#result");
        obj1 = JSON.parse(result.val());
        obj2 = obj1.conditions;
        obj3 = obj1.actions;

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
            data: obj2
        });
    }
    function initializeActions() {
        actions.actionsBuilder({
            fields: [
                {label: "Green", name: "Green"},
                {label: "Red", name: "Red"},
                {label: "Yellow", name: "Yellow"}
            ],
            data: obj3
        });
    }
    function initializeForm() {

        submit.click(function(e) {
            e.preventDefault();
            var engine = new RuleEngine({
                conditions: conditions.conditionsBuilder("data"),
                actions: actions.actionsBuilder("data")
            });
            var sample = JSON.stringify(engine);
            result.val(sample);

        });
    }
    $(onReady);
})(jQuery);
