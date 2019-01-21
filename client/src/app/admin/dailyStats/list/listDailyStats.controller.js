/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('DailyStatsController', DailyStatsController);

  /** @ngInject */
  function DailyStatsController($rootScope, $log, $timeout, $localStorage,
                                DailyStatsResource, moment)
  {
    var vm = this;
    vm.decimalRegEx = /^[0-9]+(,[0-9]{1,2})?$/;


    var firstRCQYear = (new Date()).getFullYear();
    try
    {//list years starting from the year before they use RCQ
      firstRCQYear = $localStorage.guiSettings.ul.date_demarrage_rcq.date.substring(0,4);
    }
    catch(exception)
    {
      //do nothing
    }

    vm.selectedYear = firstRCQYear-1;

    vm.years = [];

    for(var i=2004;i<firstRCQYear; i++)
      vm.years[vm.years.length]=i;


    vm.doSearch=function()
    {

      DailyStatsResource.query({'year':vm.selectedYear }).$promise.then(function(mylist)
      {
        vm.list = mylist;
        for(var i=0; i<mylist.length; i++)
        {//orinal date format : 2013-06-09 00:00:00.000000
          //  console.log(mylist[i].date.date.substring(0, mylist[i].date.date.length -16 ));
          mylist[i].date   = moment( mylist[i].date.date.substring(0, mylist[i].date.date.length -16 ),"YYYY-MM-DD").toDate();
          mylist[i].amount = parseFloat(mylist[i].amount);
        }

        vm.searchedYear = vm.selectedYear;

        $rootScope.$emit('title-updated', 'Stats Avant RCQ - Année '+vm.selectedYear);
      });
    };

    vm.doSearch();


    vm.createYear=function(year)
    {
      vm.saveInProgress=true;
      var dailyStatsResource = new DailyStatsResource({year:year});
      dailyStatsResource.$createYear(function(){vm.saveInProgress=false;vm.doSearch();}, function(error){vm.saveInProgress=false;$log.error(error);});
    };

    vm.save=function(id, amount)
    {
      var dailyStatsResource = new DailyStatsResource({id:id, amount:amount});
      dailyStatsResource.$update();
    };


    vm.repartitionPercentagesFor9=[30,15,6,4,6,6,8,15,10];
    vm.repartitionPercentagesFor8=[31,15,6,6,6,6,10,20];
    vm.repartitionPercentagesFor7=[35,18,8,8,8,8,15];
    vm.repartitionPercentagesFor2=[60,40];

    vm.repartition=function()
    {
      var length = vm.list.length;
      var repartition = null;

      if(length === 9)
      {
        repartition = vm.repartitionPercentagesFor9;
      }
      else if(length === 8)
      {
        repartition = vm.repartitionPercentagesFor8;
      }
      else if(length === 7)
      {
        repartition = vm.repartitionPercentagesFor7;
      }
      else if(length === 2)
      {
        repartition = vm.repartitionPercentagesFor2;
      }
      else
      {
        //alert('length:'+length);
      }

      for(var i=0; i < length; i++)
      {
        vm.list[i].amount=(vm.totalAmount*repartition[i]/100).toFixed(2);
        vm.save(vm.list[i].id,vm.list[i].amount);
      }

      $timeout(function() {
        vm.doSearch();
      }, 500);
    };

    vm.computeTotal=function()
    {
      if(angular.isUndefined(vm.list))
        return "";
      var total = 0;
      for(var i=0; i < vm.list.length; i++)
      {
        total +=vm.list[i].amount;
      }
      return total;
    }

  }
})();

