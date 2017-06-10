/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('TroncsController', TroncsController);

  /** @ngInject */
  function TroncsController($log, TroncResource) {
    var vm = this;

    vm.list = TroncResource.query();

    vm.searchSubmit = function()
    {
      vm.list = TroncResource.query({'active':vm.active});
    }


  }
})();

