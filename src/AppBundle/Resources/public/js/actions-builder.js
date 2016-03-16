
(function($) {

    $.fn.actionsBuilder = function(options) {
        if(options == "data") {
            var builder = $(this).eq(0).data("actionsBuilder");
            return builder.collectData();
        } else {
            return $(this).each(function() {
                var builder = new ActionsBuilder(this, options);
                $(this).data("actionsBuilder", builder);
            });
        }
    };

    var count = 0;
    var num = 0;

    function ActionsBuilder(element, options) {
        this.element = $(element);
        this.options = options || {};
        this.init();
    }

    $('.add-rule').live("click",function(){
        num++;
    });
    $('.remove-condition').live("click",function(){
        num--;
    });


    ActionsBuilder.prototype = {
        init: function() {
            this.fields = this.options.fields;
            this.fields.unshift({label: "Choose an Action...", name: ""});
            this.data = this.options.data || [];
            var actions = this.buildActions(this.data);
            this.element.html(actions);
        },

        buildActions: function(data) {
            var num1 = num;
            num1++;
            var container = $("<div>", {"class": "actions"});
            var buttons = $("<div>", {"class": "action-buttons"});
            var addButton = $("<a>", {"href": "#", "class": "add","id":"add-id"+num1, "text": "Add Action"});
            var _this = this;

            addButton.click(function(e) {
                e.preventDefault();
                count++;
                container.append(_this.buildAction({}));
            });

            buttons.append(addButton);
            container.append(buttons);

            for(var i=0; i < data.length; i++) {
                var actionObj = data[i];
                var actionDiv = this.buildAction(actionObj);

                // Add values to fields
                var fields = [actionObj];
                var field;
                while(field = fields.shift()) {
                    actionDiv.find(":input[name='" + field.name + "']").val(field.value).change();
                    if(field.fields) fields = fields.concat(field.fields);
                }
                container.append(actionDiv);
            }
            return container;
        },

        buildAction: function(data) {
            var field = this._findField(data.name);
            var div = $("<div>", {"class": "action"});
            var fieldsDiv = $("<div>", {"class": "subfields","id":"subfields-id"});
            var select = $("<select>", {"class": "action-select","id":"action-select-id"+count, "name": "action-select"});

            for(var i=0; i < this.fields.length; i++) {
                var possibleField = this.fields[i];
                var option = $("<option>", {"text": possibleField.label, "value": possibleField.name});
                select.append(option);
            }

            var _this = this;
            select.change(function() {
                var val = $(this).val();
                var newField = _this._findField(val);
                fieldsDiv.empty();

                if(newField.fields) {
                    for(var i=0; i < newField.fields.length; i++) {
                        fieldsDiv.append(_this.buildField(newField.fields[i]));
                    }
                }

                div.attr("class", "action " + val);
            });

            var removeLink = $("<a>", {"href": "#", "class": "remove-action","id":"action_remove", "text": "Remove Action"});
            removeLink.click(function(e) {
                e.preventDefault();
                div.remove();
            });

            div.append(select);
            div.append(fieldsDiv);
            div.append(removeLink);
            return div;
        },

        collectData: function(fields) {
            var _this = this;
            fields = fields || this.element.find(".action");
            var out = [];
            fields.each(function() {
                var input = $(this).find("> :input, > .jstEditor > :input");
                var subfields = $(this).find("> #subfields-id > .field");
                var action = {name: input.attr("name"), value: input.val()};
                if(subfields.length > 0) {
                    action.fields = _this.collectData(subfields);
                }
                out.push(action);
            });
            return {
                name:'action-select',
                value: fields.find("#action-select-id"+count).val()
            };
        },

        _findField: function(fieldName) {
            for(var i=0; i < this.fields.length; i++) {
                var field = this.fields[i];
                if(field.name == fieldName) return field;
            }
        }
    };

})(jQuery);