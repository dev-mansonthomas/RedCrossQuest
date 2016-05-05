/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('QueteursController', QueteursController);

  /** @ngInject */
  function QueteursController(QueteurResource) {
    var vm = this;
    vm.list = QueteurResource.query();


  }
})();

