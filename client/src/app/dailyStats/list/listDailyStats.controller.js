/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('client')
    .controller('DailyStatsController', DailyStatsController);

  /** @ngInject */
  function DailyStatsController($log, DailyStatsResource, moment)
  {
    var vm = this;

    var currentYear = (new Date()).getFullYear();

    vm.selectedYear = currentYear;

    vm.years = [];

    for(var i=2002;i<=currentYear; i++)
      vm.years[vm.years.length]=i;

    vm.doSearch=function()
    {

      DailyStatsResource.query({'year':vm.selectedYear }).$promise.then(function(mylist)
      {
        vm.list = mylist;
        for(var i=0; i<mylist.length; i++)
        {//orinal date format : 2013-06-09 00:00:00.000000
          //  console.log(mylist[i].date.date.substring(0, mylist[i].date.date.length -16 ));
          mylist[i].date = moment( mylist[i].date.date.substring(0, mylist[i].date.date.length -16 ),"YYYY-MM-DD").toDate();
          mylist[i].amount= parseFloat(mylist[i].amount);
        }

        vm.searchedYear = vm.selectedYear;
      });


    }

    vm.doSearch();


    vm.createYear=function(year)
    {
      var dailyStatsResource = new DailyStatsResource({year:year});
      dailyStatsResource.$createYear(function(){vm.doSearch();}, function(error){console.log(error);});

    }

    vm.save=function(id, amount)
    {
      var dailyStatsResource = new DailyStatsResource({id:id, amount:amount});
      dailyStatsResource.$update();
    }

  }
})();

