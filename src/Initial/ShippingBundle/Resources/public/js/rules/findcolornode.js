/**
 * Created by lawrance on 10/3/16.
 */
var engine=require('./rule-engine.js');
//var user = new engine();
var rule = process.argv[2];
var value= process.argv[3];
var obj=JSON.parse(rule);