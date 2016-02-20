if (process.argv.length <= 2)
 {

    console.log("Usage: " + __filename + " SOME_PARAM");
    process.exit(-1);
}
var param = process.argv[2]; 
var circle=require("./demo");
// console.log(circle);
 console.log("The area of a circle of radius 4 is" + circle.area(param));
