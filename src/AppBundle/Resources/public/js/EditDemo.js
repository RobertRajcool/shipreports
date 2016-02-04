var conditions, actions, ageField, submit, result, result1, result2, obj1, obj2, obj3;

(function($) {


    function onReady() {
        conditions = $("#conditionsedit");
        actions = $("#actionsedit");
        ageField = $("#ageFieldEdit");
        submit = $("#submitedit");
        result = $("#result");
        //result1 = $("#calculation_rules_ruleConditions");
        //result2 = $("#calculation_rules_ruleActions");
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
            data:
                obj2
           // {"all":[{"name":"ageField","operator":"greaterThan","value":"10"}]}
            //{"all":[{"name":"ageField","operator":"equalTo","value":"5"}]}
                //JSON.parse(result1.val())
            //$(calculation_rules_ruleConditions).value
            //{"all":[{"name":"ageField","operator":"notEqualTo","value":"4"},{"all":[{"name":"ageField","operator":"greaterThan","value":"3"}]}]},

        });
    }
    function initializeActions() {
        actions.actionsBuilder({
            fields: [
                {label: "Green", name: "Green"},
                {label: "Red", name: "Red"},
                {label: "Yellow", name: "Yellow"}
            ],
            data:
                    obj3
                //{"name":"action-select","value":"Red"}
                //{"name":"action-select","value":"Yellow"}
                //JSON.parse(result2.val())
                //{"name":"action-select","value":"Yellow"}

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
            var conditionsAdapter = {
                ageField: ageField.val()
            };
            /*var actionsAdapter = {
             alert: function(data) { alert(data.find("message"));
             },
             updateField: function(data)
             {
             alert(data);
             console.log("data", data);
             var fieldId = data.find("fieldId");
             console.log("fieldId", fieldId);
             var field = $("#" + fieldId);
             var val = data.find("fieldId", "newValue");
             field.val(val);
             }
             };*/
            //alert(conditionsAdapter);
            //alert(actionsAdapter);
            //JSON.stringify(myObj)
            //var temp = engine.run(conditionsAdapter);
            //alert(temp);
            var sample = JSON.stringify(engine);
            result.val(sample);
            var sample1 = JSON.stringify(engine1);
            var len1 = sample1.length;
            var condition = sample1.substring(42,len1-1);
            result1.val(condition);
            var sample2 = JSON.stringify(engine2);
            var len2 = sample2.length;
            var action = sample2.substring(27,len2-26);
            result2.val(action);
        });
    }
    $(onReady);
})(jQuery);
