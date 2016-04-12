(function($) {
    $.fn.conditionsBuilder = function(options, answer) {
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

    var count=0;
    var k =0;

    function ConditionsBuilder(element, options) {
        this.element = $(element);
        this.options = options || {};
        this.init();
    }

    $('.dynamic-add').live("click",function() {
        var currentId = $(this).attr('id');
        id_value = splitfun(currentId);
    });


    function splitfun(data){
        var num = data.split('_');
        return num[1];
    }

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
                    name: element.find("#field-id_"+id_value).val(),
                    operator: element.find("#operator-id_"+id_value).val(),
                    value: element.find("#text-value-id_"+id_value).val()
                };
            }
        },

        buildRules: function(ruleData) {
            return this.buildConditional(ruleData) || this.buildRule(ruleData);
        },

        buildConditional: function(ruleData) {
            var kind;
            if(ruleData.all) { kind = "all"; }
            else if(ruleData.any) { kind = "any"; }
            else if (ruleData.none) { kind = "none"; }
            if(!kind) { return; }

            var div = $("<div>", {"class": "conditional kpi_new_style form-group " + kind},{"id":"conditionalid"});
            var selectWrapper = $("<div>", {"class": "all-any-none-wrapper"},{"id": "all-any-none-wrapper-id"});
            var select = $("<select>", {"class": "all-any-none"},{"id":"all-any-none-id"});
            select.append($("<option>", {"value": "all", "text": "All", "selected": kind == "all"}));
            select.append($("<option>", {"value": "any", "text": "Any", "selected": kind == "any"}));
            select.append($("<option>", {"value": "none", "text": "None", "selected": kind == "none"}));
            selectWrapper.append(select);
            div.append(selectWrapper);

            var addRuleLink = $("<a>", {"href": "#", "class": "form-group add-rule","id":"add-rule-id"+k, "text": "Add Rule"});
            var _this = this;
            addRuleLink.click(function(e) {
                count++;
                e.preventDefault();
                var f = _this.fields[0];
                var newField = {name: f.value, operator: f.operators[0], value: null};

                div.append(_this.buildRule(newField));
            });
            div.append(addRuleLink);

            var addConditionLink = $("<a>", {"href": "#", "class": "form-group add-condition","id":"add-condition-id", "text": "Add Sub-Condition"});
            addConditionLink.click(function(e) {
                e.preventDefault();
                k++;
                var f = _this.fields[0];
                var newField = {"all": [{ operator: f.operators[0], value: null}]};
                div.append(_this.buildConditional1(newField));
            });
            div.append(addConditionLink);

            var removeLink = $("<a>", {"class": "remove delete_icon_btn ss-delete", "href": "#"});
            removeLink.click(function(e) {
                e.preventDefault();
                div.remove();
            });
            div.append(removeLink);

            var rules = ruleData[kind];
            for(var i=0; i<rules.length; i++) {
                div.append(this.buildRules(rules[i]));
            }
            return div;
        },
        buildConditional1: function(ruleData) {
            var kind;
            if(ruleData.all) { kind = "all"; }
            else if(ruleData.any) { kind = "any"; }
            else if (ruleData.none) { kind = "none"; }
            if(!kind) { return; }

            var div = $("<div>", {"class": "conditional kpi_new_style " + kind},{"id":"conditionalid"});

            var addConditionLink = $("<a>", {"href": "#", "class": "form-group add-condition","id":"add-condition-id", "text": "Add Sub-Condition"});
            addConditionLink.click(function(e) {
                e.preventDefault();
                k++;
                var f = _this.fields[0];
                var newField = {"all": [{ operator: f.operators[0], value: null}]};
                var designDiv = $("<div>", {"class": "form-group col-xs-16 rule"});
                designDiv.append(_this.buildConditional1(newField));
                div.append(designDiv);
            });

            var removeLink = $("<a>", {"class": "remove delete_icon_btn ss-delete", "href": "#"});
            removeLink.click(function(e) {
                e.preventDefault();
                div.remove();
            });
            div.append(removeLink);

            var rules = ruleData[kind];
            for(var i=0; i<rules.length; i++) {
                div.append(this.buildRules(rules[i]));
            }
            return div;
        },

        buildRule: function(ruleData) {
            var ruleDiv = $("<div>", {"class": "form-group button col-xs-16 rule", "id": "rule-id"});
            var fieldSelect = getFieldSelect(this.fields, ruleData);
            var operatorSelect = getOperatorSelect();

            fieldSelect.change(onFieldSelectChanged.call(this, operatorSelect, ruleData));

            ruleDiv.append(fieldSelect);
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
        var select = $("<select>",  {"class": "field", "id":"field-id_"+count});
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
        var select = $("<select>",  {"class": "operator add_rule_drop","id":"operator-id_"+count});
        select.change(onOperatorSelectChange);
        return select;
    }

    function removeLink() {
        var removeLink = $("<a>", {"class": "remove-condition delete_icon_btn ss-delete","id":"remove-condition-id"+k, "href": "#"});
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
        var j=count;
        switch(option.data("fieldType"))
        {
            case "none":
                $this.after($("<input>", {"type": "hidden", "class": "value", "id":"none-value-id"}));
                break;
            case "text":
                $this.after($("<input>", {"type": "text", "class": "value add_rule_input", "id":"text-value-id_" +j}));
                break;
            case "textarea":
                $this.after($("<textarea>", {"class": "value", "id":"textarea-value-id"}));
            case "select":
                var select = $("<select>", {"class": "value", "id":"select-value-id"});
                var options = fieldSelect.find("> :selected").data("options");
                for(var i=0; i < options.length; i++) {
                    var opt = options[i];
                    select.append($("<option>", {"text": opt.label || opt.name, "value": opt.name}));
                }
                $this.after(select);
                break;
        }
        currentValue.remove();
    }

})(jQuery);