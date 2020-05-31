/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('TroncsController', TroncsController);

  /** @ngInject */
  function TroncsController($rootScope, $log, TroncResource, DateTimeHandlingService) {
    var vm = this;

    vm.rowCount   = 0;
    vm.list       = [];
    vm.pageNumber = 1;

    $rootScope.$emit('title-updated', 'Liste des Troncs');
    vm.typeTroncList=[
      {id:0,label:'Tout type'},
      {id:1,label:'Tronc'},
      {id:2,label:'Tronc chez un commerçant'},
      {id:3,label:'Autre'}
    ];

    function handleSearchResults(pageableResponse)
    {
      vm.rowCount = pageableResponse.count;
      vm.list     = pageableResponse.rows;

      var dataLength = vm.list.length;

      for(var i=0;i<dataLength;i++)
      {
        vm.list[i].created = vm.handleDate(vm.list[i].created);
      }
    }

    vm.searchSubmit = function()
    {
      TroncResource.query({'pageNumber': vm.pageNumber, 'active':vm.active, 'type':vm.type===0?'':vm.type, 'q':vm.search!= null? vm.search.trim():null}).$promise.then(handleSearchResults).catch(function(e){
        $log.error("error searching for Queteur", e);
      });
    };

    vm.handleDate = function (theDate)
    {
      return DateTimeHandlingService.handleServerDate(theDate).stringVersion;
    };

    vm.searchSubmit();
  }
})();

