/**
 * Created by lawrance on 10/3/16.
 */
var engine=require('./87f1824_part_1_rule-engine_4.js');

//var user = new engine();
var rule = process.argv[2];
var value= process.argv[3];
var obj=JSON.   parse(rule);

var conditons=obj.conditions;
var conditionsAdapter = {
    ageField: value
};
var samp = new engine({
    conditions: obj.conditions
});

var res = samp.run(conditionsAdapter);

if(res==true)
{
    console.log(obj.actions.value);
}

else
{
    console.log(res);
}