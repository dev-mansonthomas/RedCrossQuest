/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('QRCodeTroncsController', QRCodeTroncsController);

  /** @ngInject */
  function QRCodeTroncsController($log,
                                  TroncResource)
  {
    var vm = this;
    vm.size=100;
    vm.list = TroncResource.query();

  }
})();

