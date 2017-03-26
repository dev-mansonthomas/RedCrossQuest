/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('QueteurEditController', QueteurEditController);

  /** @ngInject */
  function QueteurEditController($scope, $log, $routeParams, $location,
                                 QueteurResource, TroncQueteurResource, moment)
  {
    var vm = this;

    var queteurId = $routeParams.id;

    if (angular.isDefined(queteurId))
    {
      QueteurResource.get({ 'id': queteurId }).$promise.then(function(queteur)
      {
        vm.current = queteur;
        vm.current.troncs_queteur =  TroncQueteurResource.getTroncsOfQueteur({'queteur_id': queteurId});

        if(vm.current.birthdate != null)
        {
          vm.current.birthdate =  moment( queteur.birthdate.date.substring(0, queteur.birthdate.date.length -3 ),"YYYY-MM-DD HH:mm:ss.SSS").toDate();
        }

      });

    }
    else
    {
      vm.current = new QueteurResource();
    }

    function savedSuccessfully()
    {
      $location.path('/queteurs');
    }


    vm.save = function ()
    {
      $log.debug("Saved called");
      $log.debug(vm.current);

      if (angular.isDefined(vm.current.id))
      {
        vm.current.$update(savedSuccessfully);
      } else
      {
        vm.current.$save(savedSuccessfully);
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

