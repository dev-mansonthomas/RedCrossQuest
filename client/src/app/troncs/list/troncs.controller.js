/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('TroncsController', TroncsController);

  /** @ngInject */
  function TroncsController($rootScope, $log, TroncResource) {
    var vm = this;

    $rootScope.$emit('title-updated', 'Liste des Troncs');
    vm.typeTroncList=[
      {id:0,label:'Tout type'},
      {id:1,label:'Tronc'},
      {id:2,label:'Urne chez un commerçant'},
      {id:3,label:'Autre'},
      {id:4,label:'Terminal Carte Bleue'}
    ];

    vm.list = TroncResource.query();

    vm.searchSubmit = function()
    {
      vm.list = TroncResource.query({'active':vm.active, 'type':vm.type===0?'':vm.type, 'q':vm.search});
    };
  }
})();

