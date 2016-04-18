var app=angular.module('ui.bootstrap.demo',['ui.bootstrap','ngAnimate']).controller('DatepickerDemoCtrl', function ($scope)
{
  $scope.today = function () {
  $scope.dt = new Date();
};
  $scope.dateformat="MMMM-yyyy";
  $scope.showWeeks = false;
  $scope.today();
  $scope.showcalendar = function ($event) {
    $scope.showdp = true;
  };
  $scope.showdp = false;
  $scope.dtmax = new Date();
  $scope.viewMode= "months";
      $scope.minViewMode= "months";
      $scope.pickTime= false;
});


app.config(function($interpolateProvider){
  $interpolateProvider.startSymbol('[[').endSymbol(']]');
});
