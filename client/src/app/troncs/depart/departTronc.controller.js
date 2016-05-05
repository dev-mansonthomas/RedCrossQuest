/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('DepartTroncController', DepartTroncController);

  /** @ngInject */
  function DepartTroncController($scope, $log, QueteurResource) {
    var vm = this;

    $scope.$watch('departTronc.current.queteur', function(newValue, oldValue) {

      try
      {
        $scope.departTronc.current.queteurId = newValue.id;
      }
      catch(exception)
      {

      }


    });

    vm.save = function ()
    {
      $log.debug("Saved called");
      $log.debug(vm.current);
    }


    vm.searchQueteur=function(queryString)
    {
      $log.info("search for '"+queryString+"'");


      return QueteurResource.query({"q":queryString}).$promise.then(function(response){

        return response.map(function(queteur)
        {
          queteur.full_name= queteur.first_name+' '+queteur.last_name+' - '+queteur.nivol;
          return queteur;
        });
      });
    }





    vm.onSuccess = function(data)
    {
      if(/*match a tronc*/ 1==1)
      {
        //play sound
         vm.current.troncId=data;
      }
      else if(/*match a queteurId*/ 1==2)
      {
        //play sound
        vm.current.queteurId=data;
      }
      vm.decodedData = data;
      $log.info(data);
    }
    vm.onError = function(error)
    {
      vm.errorMessage=error;
    }
    vm.onVideoError = function(error)
    {
      vm.errorMessageVideo = error;
      $log.error(error);
    }

  }
})();

