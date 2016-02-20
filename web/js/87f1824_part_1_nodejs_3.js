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
