/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('QueteurEditController', QueteurEditController);

  /** @ngInject */
  function QueteurEditController($scope, $log, $routeParams, $location, QueteurResource) {
    var vm = this;

    var queteurId = $routeParams.id;

    if (angular.isDefined(queteurId)) {
      vm.current = QueteurResource.get({ 'id': queteurId });
    } else {
      vm.current = new QueteurResource();
    }

    function redirectToList()
    {
      $location.path('/queteurs');
    }


    vm.save = function ()
    {
      $log.debug("Saved called");
      $log.debug(vm.current);

      if (angular.isDefined(vm.current.id))
      {
        vm.current.$update(redirectToList);
      } else
      {
        vm.current.$save(redirectToList);
      }

    };


    // $scope.$watch('queteur.current.secteur', function(newValue/*, oldValue*/)
    // {
    //
    //   $log.debug("secteur change to "+newValue)
    //   try
    //   {
    //     if(newValue > 3)
    //     {
    //       $scope.queteur.current.ul_id = 1;
    //     }
    //     else
    //     {
    //       $scope.queteur.current.ul_id = 2;
    //     }
    //
    //     $log.debug("ul_id change to "+$scope.queteur.current.ul_id);
    //   }
    //   catch(exception)
    //   {
    //
    //   }
    // });


  }
})();

