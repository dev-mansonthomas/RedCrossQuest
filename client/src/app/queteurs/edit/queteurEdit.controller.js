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

    vm.youngestBirthDate=moment().subtract(10 ,'years').toDate();
    vm.oldestBirthDate  =moment().subtract(100,'years').toDate();


    if (angular.isDefined(queteurId))
    {
      QueteurResource.get({ 'id': queteurId }).$promise.then(function(queteur)
      {
        vm.current = queteur;

        TroncQueteurResource.getTroncsOfQueteur({'queteur_id': queteurId}).$promise.then(
          function success(data)
          {
            var dataLength = data.length;
            for(var i=0;i<dataLength;i++)
            {

              var handleDate = function (theDate)
              {
                if(theDate ===null)
                  return null;

                 var dateAsString = theDate.date;
                 return moment( dateAsString.substring (0, dateAsString.length  - 3 ),"YYYY-MM-DD  HH:mm:ss.SSS");
              }

              data[i].depart            = handleDate(data[i].depart);
              data[i].depart_theorique  = handleDate(data[i].depart_theorique);
              data[i].retour            = handleDate(data[i].retour);

              if(data[i].retour !==null && data[i].depart !== null)
              {
                data[i].duration = moment.duration(data[i].retour.diff(data[i].depart)).asMinutes();
              }
            }

            vm.current.troncs_queteur  = data;
          },
          function error(error)
          {
            $log.error(error);
          }

        );

        if(vm.current.birthdate != null)
        {
          vm.current.birthdate = moment( queteur.birthdate.date.substring(0, queteur.birthdate.date.length -16 ),"YYYY-MM-DD").toDate();
          vm.computeAge();
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

    vm.computeAge=function()
    {
      vm.current.age       = moment().diff(vm.current.birthdate, 'years');
    }

    vm.capitalize = function($event)
    {
      $event.currentTarget.value = $event.currentTarget.value.replace(/\w\S*/g,
        function(txt)
        {
          return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        }
      );
    }


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

