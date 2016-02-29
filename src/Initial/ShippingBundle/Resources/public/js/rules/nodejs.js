/**
 * Created by lawrance on 17/2/16.
 */

var engine=require('./rule-engine.js');
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