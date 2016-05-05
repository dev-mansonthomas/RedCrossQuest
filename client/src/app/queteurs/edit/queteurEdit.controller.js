/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('QueteurEditController', QueteurEditController);

  /** @ngInject */
  function QueteurEditController($log, $routeParams, $location, QueteurResource) {
    var vm = this;


    var queteurId = $routeParams.id;


    if (angular.isDefined(queteurId)) {
      vm.current = QueteurResource.get({ id: queteurId });
    } else {
      vm.current = new QueteurResource();
    }

    $log.debug(vm.current);

    //vm.current.notes= vm.current.notes + " added automatically";
    //vm.current.save();


    function redirectToList() {
      $location.path('/queteurs');
    }


    vm.save = save;

    function save()
    {
      $log.debug("Saved called");
      $log.debug(vm.current);

      if (angular.isDefined(vm.current.id)) {
        vm.current.$update(redirectToList);
      } else {
        vm.current.$save(redirectToList);
      }

    }

  }
})();

