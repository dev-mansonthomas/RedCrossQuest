/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('TroncEditController', TroncEditController);

  /** @ngInject */
  function TroncEditController($log, $routeParams, TroncResource) {
    var vm = this;
    var troncId = $routeParams.id;


    if (angular.isDefined(troncId)) {
      vm.current = TroncResource.get({ 'id': troncId });
    } else {
      vm.current = new TroncResource();
    }
    vm.save = save;

    function save()
    {
      $log.debug("Saved called");
      $log.debug(vm.current);
    }

  }
})();

