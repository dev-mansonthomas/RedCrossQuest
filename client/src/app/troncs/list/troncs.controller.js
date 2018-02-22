/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('TroncsController', TroncsController);

  /** @ngInject */
  function TroncsController($log, TroncResource) {
    var vm = this;

    vm.typeTroncList=[
      {id:1,label:'Tronc'},
      {id:2,label:'Urne chez un commerçant'},
      {id:3,label:'Autre'},
      {id:3,label:'Terminal Carte Bleue'}
    ];

    vm.list = TroncResource.query();

    vm.searchSubmit = function()
    {
      vm.list = TroncResource.query({'active':vm.active});
    };
  }
})();

