/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('YearlyGoalsController', DailyStatsController);

  /** @ngInject */
  function DailyStatsController($rootScope, $log, $timeout,
                                YearlyGoalsResource)
  {
    var vm = this;

    vm.savedSuccessfully=false;
    vm.errorWhileSaving =false;
    vm.saveInProgress   =false;

    vm.currentYear  = (new Date()).getFullYear();
    vm.selectedYear = vm.currentYear;
    vm.years = [];

    for(var i=2004;i<=vm.currentYear; i++)
      vm.years[vm.years.length]=i;

    vm.doSearch=function()
    {

      YearlyGoalsResource.getByYear({'year':vm.selectedYear }).$promise.then(function(yearlyGoal)
      {
        vm.yearlyGoal   = yearlyGoal;
        vm.searchedYear = vm.selectedYear;
        $rootScope.$emit('title-updated', 'Objectifs de quête pour l\'année '+vm.searchedYear);
      }).catch(function(e){
        $log.error("error searching for YearlyGoals", e);
      });
    };

    vm.doSearch();

    vm.clearScreen=function()
    {
      vm.yearlyGoal={};
    };


    vm.createYear=function(year)
    {
      vm.saveInProgress=true;
      var yearlyGoalsResource = new YearlyGoalsResource({year:year});
      yearlyGoalsResource.$createYear(function()
        {
          vm.saveInProgress=false;
          vm.doSearch();
        },
        function(error)
        {
          vm.saveInProgress=false;
          $log.error(error);
        });
    };

    vm.save=function()
    {
      vm.yearlyGoal.$update(vm.savedSuccessfullyFunction, vm.errorWhileSavingFunction);
    };

    vm.savedSuccessfullyFunction=function()
    {
      vm.savedSuccessfully=true;
      $timeout(function () { vm.savedSuccessfully=false; }, 5000);
    };

    vm.errorWhileSavingFunction=function(error)
    {
      vm.errorWhileSaving=true;
      vm.errorWhileSavingDetails=error;
    };

    vm.computeRemainingPercentage=function()
    {
      if(angular.isUndefined(vm.yearlyGoal) || angular.isUndefined(vm.yearlyGoal.day_1_percentage))
      {
        return 100;
      }

     vm.yearlyGoal.remaining_percentage = 100 -
       ( vm.yearlyGoal.day_1_percentage +
         vm.yearlyGoal.day_2_percentage +
         vm.yearlyGoal.day_3_percentage +
         vm.yearlyGoal.day_4_percentage +
         vm.yearlyGoal.day_5_percentage +
         vm.yearlyGoal.day_6_percentage +
         vm.yearlyGoal.day_7_percentage +
         vm.yearlyGoal.day_8_percentage +
         vm.yearlyGoal.day_9_percentage );
    };

    vm.reset=function()
    {//30% - 15% - 6% - 4% - 6% - 6% - 8% - 15% - 10%
      vm.yearlyGoal.day_1_percentage = 30;
      vm.yearlyGoal.day_2_percentage = 15;
      vm.yearlyGoal.day_3_percentage =  6;
      vm.yearlyGoal.day_4_percentage =  4;
      vm.yearlyGoal.day_5_percentage =  6;
      vm.yearlyGoal.day_6_percentage =  6;
      vm.yearlyGoal.day_7_percentage =  8;
      vm.yearlyGoal.day_8_percentage = 15;
      vm.yearlyGoal.day_9_percentage = 10;
    };

  }
})();

