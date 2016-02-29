/**
 * Created by lawrance on 17/2/16.
 */

var engine=require('./87f1824_part_1_rule-engine_4.js');
//var user = new engine();
var rule = process.argv[2];
var value= process.argv[3];
var obj=JSON.parse(rule);
var conditionsAdapter = {
    ageField: value
};
//var samp=new engine(obj.conditions);
var samp = new engine({
    conditions: obj.conditions
});
var res = samp.run(conditionsAdapter);
if(res==true)
{
console.log(obj.actions);
}
else
{
    console.log(res);
}

/*

var eng =new engine({
    conditions: {all: [{name: "name", operator: "present", value: ""}, {name: "age", operator: "greaterThanEqual", value: "21"}]}

});

var conditionsAdapter = {name: "Joe", age: 20};
var actionsAdapter = {giveDrink: function(data) { alert("Gave user a " + data.find("drinkType")); } };
console.log(eng.run(conditionsAdapter));
*/