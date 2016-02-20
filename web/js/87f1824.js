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

  function ActionsBuilder(element, options) {
    this.element = $(element);
    this.options = options || {};
    this.init();
  }

  ActionsBuilder.prototype = {
    init: function() {
      this.fields = this.options.fields;
      this.fields.unshift({label: "Choose an Action...", name: ""});
      this.data = this.options.data || [];
      var actions = this.buildActions(this.data);
      this.element.html(actions);
    },

    buildActions: function(data) {
      var container = $("<div>", {"class": "actions"});
      var buttons = $("<div>", {"class": "action-buttons"});
      var addButton = $("<a>", {"href": "#", "class": "add", "text": "Add Action"});
      var _this = this;

      addButton.click(function(e) {
        e.preventDefault();
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
      var fieldsDiv = $("<div>", {"class": "subfields"});
      var select = $("<select>", {"class": "action-select", "name": "action-select"});

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

      var removeLink = $("<a>", {"href": "#", "class": "remove", "text": "Remove Action"});
      removeLink.click(function(e) {
        e.preventDefault();
        div.remove();
      });

      div.append(select);
      div.append(fieldsDiv);
      div.append(removeLink);
      return div;
    },

    buildField: function(field) {
      var div = $("<div>", {"class": "field"});
      var subfields = $("<div>", {"class": "subfields"});
      var _this = this;

      var label = $("<label>", {"text": field.label});
      div.append(label);

      if(field.fieldType == "select") {
        var label = $("<label>", {"text": field.label});
        var select = $("<select>", {"name": field.name});

        for(var i=0; i < field.options.length; i++) {
          var optionData = field.options[i];
          var option = $("<option>", {"text": optionData.label, "value": optionData.name});
          option.data("optionData", optionData);
          select.append(option);
        }

        select.change(function() {
          var option = $(this).find("> :selected");
          var optionData = option.data("optionData");
          subfields.empty();
          if(optionData.fields) {
            for(var i=0; i < optionData.fields.length; i++) {
              var f = optionData.fields[i];
              subfields.append(_this.buildField(f));
            }
          }
        });

        select.change();
        div.append(select);
      }
      else if(field.fieldType == "text") {
        var input = $("<input>", {"type": "text", "name": field.name});
        div.append(input);
      }
      else if(field.fieldType == "textarea") {
        var id = "textarea-" + Math.floor(Math.random() * 100000);
        var area = $("<textarea>", {"name": field.name, "id": id});
        div.append(area);
      }

      if(field.hint) {
        div.append($("<p>", {"class": "hint", "text": field.hint}));
      }

      div.append(subfields);
      return div;
    },
                        

    collectData: function(fields) {
      var _this = this;
      fields = fields || this.element.find(".action");
      var out = [];
      fields.each(function() {
        var input = $(this).find("> :input, > .jstEditor > :input");
        var subfields = $(this).find("> .subfields > .field");
        var action = {name: input.attr("name"), value: input.val()};
        if(subfields.length > 0) {
          action.fields = _this.collectData(subfields);
        }
        out.push(action);
      });
      return out;
    },

    _findField: function(fieldName) {
      for(var i=0; i < this.fields.length; i++) {
        var field = this.fields[i];
        if(field.name == fieldName) return field;
      }
    }
  };

})(jQuery);

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
      else if(ruleData.any) { kind = "any"; }
      else if (ruleData.none) { kind = "none"; }
      if(!kind) { return; }

      var div = $("<div>", {"class": "conditional " + kind});
      var selectWrapper = $("<div>", {"class": "all-any-none-wrapper"});
      var select = $("<select>", {"class": "all-any-none"});
      select.append($("<option>", {"value": "all", "text": "All", "selected": kind == "all"}));
      select.append($("<option>", {"value": "any", "text": "Any", "selected": kind == "any"}));
      select.append($("<option>", {"value": "none", "text": "None", "selected": kind == "none"}));
      selectWrapper.append(select);
      selectWrapper.append($("<span>", {text: "of the following rules:"}));
      div.append(selectWrapper);

      var addRuleLink = $("<a>", {"href": "#", "class": "add-rule", "text": "Add Rule"});
      var _this = this;
      addRuleLink.click(function(e) {
        e.preventDefault();
        var f = _this.fields[0];
        var newField = {name: f.value, operator: f.operators[0], value: null};
        div.append(_this.buildRule(newField));
      });
      div.append(addRuleLink);

      var addConditionLink = $("<a>", {"href": "#", "class": "add-condition", "text": "Add Sub-Condition"});
      addConditionLink.click(function(e) {
        e.preventDefault();
        var f = _this.fields[0];
        var newField = {"all": [{name: f.value, operator: f.operators[0], value: null}]};
        div.append(_this.buildConditional(newField));
      });
      div.append(addConditionLink);

      var removeLink = $("<a>", {"class": "remove", "href": "#", "text": "Remove This Sub-Condition"});
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
      var ruleDiv = $("<div>", {"class": "rule"});
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
    var select = $("<select>", {"class": "operator"});
    select.change(onOperatorSelectChange);
    return select;
  }

  function removeLink() {
    var removeLink = $("<a>", {"class": "remove", "href": "#", "text": "Remove"});
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
        $this.after($("<input>", {"type": "hidden", "class": "value"}));
        break;
      case "text":
        $this.after($("<input>", {"type": "text", "class": "value"}));
        break;
      case "textarea":
        $this.after($("<textarea>", {"class": "value"}));
      case "select":
        var select = $("<select>", {"class": "value"});
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

/**
 * Created by lawrance on 17/2/16.
 */

var engine=require('./rule-engine.js');
//var user = new engine();

var eng =new engine({
    conditions: {all: [{name: "name", operator: "present", value: ""}, {name: "age", operator: "greaterThanEqual", value: "21"}]}

});

var conditionsAdapter = {name: "Joe", age: 20};
var actionsAdapter = {giveDrink: function(data) { alert("Gave user a " + data.find("drinkType")); } };
console.log(eng.run(conditionsAdapter));

var global = this;

(function() {
  var standardOperators = {
    present: function(actual, target) {
      return !!actual;
    },
    blank: function(actual, target) {
      return !actual;
    },
    equalTo: function(actual, target) {
      return "" + actual === "" + target;
    },
    notEqualTo: function(actual, target) {
      return "" + actual !== "" + target;
    },
    greaterThan: function(actual, target) {
      return parseFloat(actual, 10) > parseFloat(target, 10);
    },
    greaterThanEqual: function(actual, target) {
      return parseFloat(actual, 10) >= parseFloat(target, 10);
    },
    lessThan: function(actual, target) {
      return parseFloat(actual, 10) < parseFloat(target, 10);
    },
    lessThanEqual: function(actual, target) {
      return parseFloat(actual, 10) <= parseFloat(target, 10);
    },
    includes: function(actual, target) {
      return ("" + actual).indexOf("" + target) > -1;
    },
    matchesRegex: function(actual, target) {
      var r = target.replace(/^\/|\/$/g, "");
      var regex = new RegExp(r);
      return regex.test("" + actual);
    }
  };

  var RuleEngine = global.RuleEngine = function RuleEngine(rule) {
    rule = rule || {};
    this.operators = {};
    this.actions = rule.actions || [];
    this.conditions = rule.conditions || {all: []};
    this.addOperators(standardOperators);
  }

  RuleEngine.prototype = {
    run: function(conditionsAdapter, actionsAdapter, cb) {
      var out, error, _this = this;
      this.matches(conditionsAdapter, function(err, result) {
        out = result;
        error = err;
        if (result && !err) _this.runActions(actionsAdapter);
        if (cb) cb(err, result);
      });
      if (error) throw error;
      return out;
    },

    matches: function(conditionsAdapter, cb) {
      var out, err;
      handleNode(this.conditions, conditionsAdapter, this, function(e, result) {
        if (e) {
          err = e;
          console.log("ERR", e.message, e.stack);
        }
        out = result;
        if (cb) cb(e, result);
      });
      if (err) throw err;
      if (!cb) return out;
    },

    operator: function(name) {
      return this.operators[name];
    },

    addOperators: function(newOperators) {
      var _this = this;
      for(var key in newOperators) {
        if(newOperators.hasOwnProperty(key)) {
          (function() {
            var op = newOperators[key];
            // synchronous style operator, needs to be wrapped
            if (op.length == 2) {
              _this.operators[key] = function(actual, target, cb) {
                try {
                  var result = op(actual, target);
                  cb(null, result);
                } catch(e) {
                  cb(e);
                }
              };
            }
            // asynchronous style, no wrapping needed
            else if (op.length == 3) {
              _this.operators[key] = op;
            }
            else {
              throw "Operators should have an arity of 2 or 3; " + key + " has " + op.length;
            }
          })();
        }
      }
    },

    runActions: function(actionsAdapter) {
      for(var i=0; i < this.actions.length; i++) {
        var actionData = this.actions[i];
        var actionName = actionData.value;
        var actionFunction = actionsAdapter[actionName]
        if(actionFunction) { actionFunction(new Finder(actionData)); }
      }
    }
  };

  function Finder(data) {
    this.data = data;
  }

  Finder.prototype = {
    find: function() {
      var currentNode = this.data;
      for(var i=0; i < arguments.length; i++) {
        var name = arguments[i];
        currentNode = findByName(name, currentNode);
        if(!currentNode) { return null; }
      }
      return currentNode.value;
    }
  };

  function findByName(name, node) {
    var fields = node.fields || [];
    for(var i=0; i < fields.length; i++) {
      var field = fields[i];
      if(field.name === name) { return field; }
    }
    return null;
  }

  function handleNode(node, obj, engine, cb) {
    if(node.all || node.any || node.none) {
      handleConditionalNode(node, obj, engine, cb);
    } else {
      handleRuleNode(node, obj, engine, cb);
    }
  }

  function handleConditionalNode(node, obj, engine, cb) {
    try {
      var isAll = !!node.all;
      var isAny = !!node.any;
      var isNone = !!node.none;
      var nodes = isAll ? node.all : node.any;
      if (isNone) { nodes = node.none }
      if (nodes.length == 0) {
        return cb(null, true);
      }
      var currentNode, i = 0;
      var next = function() {
        try {
          currentNode = nodes[i];
          i++;
          if (currentNode) {
            handleNode(currentNode, obj, engine, done);
          }
          else {
            // If we have gone through all of the nodes and gotten
            // here, either they have all been true (success for `all`)
            // or all false (failure for `any`);
            var r = isNone ? true : isAll; 
            cb(null, r);
          }
        } catch(e) {
          cb(e);
        }
      };

      var done = function(err, result) {
        if (err) return cb(err);
        if (isAll && !result) return cb(null, false);
        if (isAny && !!result) return cb(null, true);
        if (isNone && !!result) return cb(null, false);
        next();
      }
      next();
    } catch(e) {
      cb(e);
    }
  }

  function handleRuleNode(node, obj, engine, cb) {
    try {
      var value = obj[node.name];
      if (value && value.call) {
        if (value.length === 1) {
          return value(function(result) {
            compareValues(result, node.operator, node.value, engine, cb);
          });
        }
        else {
          value = value()
        }
      }
      compareValues(value, node.operator, node.value, engine, cb);
    } catch(e) {
      cb(e);
    }
  }

  function compareValues(actual, operator, value, engine, cb) {
    try {
      var operatorFunction = engine.operator(operator);
      if (!operatorFunction) throw "Missing " + operator + " operator";
      operatorFunction(actual, value, cb);
    } catch(e) {
      cb(e);
    }
  }

  if (typeof module !== "undefined") {
    module.exports = RuleEngine;
    delete global.RuleEngine;
  }
})();
