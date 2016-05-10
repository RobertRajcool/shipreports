(function($) {
    $.fn.conditionsBuilder = function(options) {
        if(options == "data") {
            var builder = $(this).eq(0).data("conditionsBuilder");
            return builder.collectData();
        } else {
            return $(this).each(function() {
                var builder = new ConditionsBuilder(this, options);
                $(this).data("conditionsBuilder", builder);
            });
        }
    };

    function ConditionsBuilder(element, options) {
        this.element = $(element);
        this.options = options || {};
        this.init();
    }

    var addRuleStatus = 0;
    var dynamicAdd = $('.dynamic-add');
    dynamicAdd.live("click",function($e) {
        $e.preventDefault();
        addRuleStatus = 1;
    });

    ConditionsBuilder.prototype = {
        init: function() {
            this.fields = this.options.fields;
            this.data = this.options.data || {"all": []};
            var rules = this.buildRules(this.data);
            this.element.html(rules);
        },

        collectData: function() {
            return this.collectDataFromNode(this.element.find("> .conditional"));
        },

        collectDataFromNode: function(element) {
            var klass = null;
            var _this = this;
            if(element.is(".conditional")) {
                klass = element.find("> .all-any-none-wrapper > .all-any-none").val();
            }

            if(klass) {
                var out = {};
                out[klass] = [];
                element.find("> .conditional, > .rule").each(function() {
                    out[klass].push(_this.collectDataFromNode($(this)));
                });
                return out;
            }
            else {
                return {
                    name: element.find(".field").val(),
                    operator: element.find(".operator").val(),
                    value: element.find(".value").val()
                };
            }
        },

        buildRules: function(ruleData) {
            return this.buildConditional(ruleData) || this.buildRule(ruleData);
        },

        buildConditional: function(ruleData) {
            var kind;
            if(ruleData.all) { kind = "all"; }
            if(!kind) { return; }

            var div = $("<div>", {"class": "conditional " + kind});
            var selectWrapper = $("<div>", {"class": "all-any-none-wrapper"});
            var select = $("<select>", {"class": "all-any-none"});
            select.append($("<option>", {"value": "all", "text": "All", "selected": kind == "all"}));
            selectWrapper.append(select);
            div.append(selectWrapper);
            selectWrapper.hide();

            var addRuleLink = $("<a>", {"href": "#", "class": "add-rule", "text": "Add Rule"});
            var _this = this;
            addRuleLink.click(function(e) {
                e.preventDefault();
                if(addRuleStatus==0){
                    addRuleStatus=1;
                    var f = _this.fields[0];
                    var newField = {name: f.value, operator: f.operators[0], value: null};
                    div.append(_this.buildRule(newField));
                    addConditionLink.show();
                }
            });
            div.append(addRuleLink);
            div.append(" ");

            var addConditionLink = $("<a>", {"href": "#", "class": "add-condition", "text": "Add Sub-Condition"});
            addConditionLink.click(function(e) {
                e.preventDefault();
                var f = _this.fields[0];
                var newField = {"all": [{name: f.value, operator: f.operators[0], value: null}]};
                div.append(_this.subCondition(newField));
            });
            div.append(addConditionLink);
            addConditionLink.hide();

            var rules = ruleData[kind];
            for(var i=0; i<rules.length; i++) {
                div.append(this.buildRules(rules[i]));
            }
            return div;
        },

        subCondition: function(ruleData) {
            var kind;
            if(ruleData.all) { kind = "all"; }
            if(!kind) { return; }

            var div = $("<div>", {"class": "conditional " + kind});

            var addRuleLink = $("<a>", {"href": "#", "class": "add-rule", "text": "Add Rule"});
            var _this = this;
            addRuleLink.click(function(e) {
                e.preventDefault();
                var f = _this.fields[0];
                var newField = {name: f.value, operator: f.operators[0], value: null};
                div.append(_this.buildRule(newField));
            });
            var rules = ruleData[kind];
            for(var i=0; i<rules.length; i++) {
                div.append(this.buildRules(rules[i]));
            }
            return div;
        } ,

        buildRule: function(ruleData) {
            var ruleDiv = $("<div>", {"class": "rule "});
            var fieldSelect = getFieldSelect(this.fields, ruleData);
            var operatorSelect = getOperatorSelect();

            fieldSelect.change(onFieldSelectChanged.call(this, operatorSelect, ruleData));

            //ruleDiv.append(fieldSelect);
            ruleDiv.append(operatorSelect);
            ruleDiv.append(removeLink());

            fieldSelect.change();
            ruleDiv.find("> .value").val(ruleData.value);
            return ruleDiv;
        },

        operatorsFor: function(fieldName) {
            for(var i=0; i < this.fields.length; i++) {
                var field = this.fields[i];
                if(field.name == fieldName) {
                    return field.operators;
                }
            }
        }
    };

    function getFieldSelect(fields, ruleData) {
        var select = $("<select>", {"class": "field"});
        for(var i=0; i < fields.length; i++) {
            var field = fields[i];
            var option = $("<option>", {
                text: field.label,
                value: field.name,
                selected: ruleData.name == field.name
            });
            option.data("options", field.options);
            select.append(option);
        }
        return select;
    }

    function getOperatorSelect() {
        var select = $("<select>", {"class": "operator add_rule_drop"});
        select.append(" ");
        select.change(onOperatorSelectChange);
        return select;
    }

    function removeLink() {
        var removeLink = $("<a>", {"class": "remove delete_icon_btn ss-delete", "href": "#"});
        removeLink.click(onRemoveLinkClicked);
        return removeLink;
    }

    function onRemoveLinkClicked(e) {
        e.preventDefault();
        $(this).parents(".rule").remove();
    }

    function onFieldSelectChanged(operatorSelect, ruleData) {
        var builder = this;
        return function(e) {
            var operators = builder.operatorsFor($(e.target).val());
            operatorSelect.empty();
            for(var i=0; i < operators.length; i++) {
                var operator = operators[i];
                var option = $("<option>", {
                    text: operator.label || operator.name,
                    value: operator.name,
                    selected: ruleData.operator == operator.name
                });
                option.data("fieldType", operator.fieldType);
                operatorSelect.append(option);
            }
            operatorSelect.change();
        }
    }

    function onOperatorSelectChange(e) {
        var $this = $(this);
        var option = $this.find("> :selected");
        var container = $this.parents(".rule");
        var fieldSelect = container.find(".field");
        var currentValue = container.find(".value");
        var val = currentValue.val();

        switch(option.data("fieldType")) {
            case "none":
                $this.after($("<input>", {"type": "hidden", "class": "value "}));
                break;
            case "text":
                $this.after($("<input>", {"type": "text", "class": "value add_rule_input"}));
                break;
        }
        currentValue.remove();
    }

})(jQuery);
