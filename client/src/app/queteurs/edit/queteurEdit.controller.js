/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('QueteurEditController', QueteurEditController);

  /** @ngInject */
  function QueteurEditController($scope, $log, $routeParams, $location, $localStorage,
                                 QueteurResource, TroncQueteurResource, moment)
  {
    var vm = this;

    var queteurId = $routeParams.id;

    vm.ulId   = $localStorage.currentUser.ulId;
    vm.ulName = $localStorage.currentUser.ulName;



    vm.youngestBirthDate=moment().subtract(10 ,'years').toDate();
    vm.oldestBirthDate  =moment().subtract(100,'years').toDate();


    if (angular.isDefined(queteurId))
    {
      QueteurResource.get({ 'id': queteurId }).$promise.then(function(queteur)
      {
        vm.current = queteur;
        if(typeof vm.current.mobile === "string")
        {
          if(vm.current.mobile === "N/A")
          {
            vm.current.mobile = null;
          }
          try
          {
            vm.current.mobile = parseInt(vm.current.mobile.slice(1));
          }
          catch(e)
          {
            vm.current.mobile = null;
          }

        }

        /*lack of data with previous model (minor instead of birthdate), only for ULParisIV, minor and major where set fixed birthdate
        * if editing one of these ==> set birthdate to null to force user to update the data*/

        var birthdate = vm.current.birthdate.date.toLocaleString().substr(0,10);

        if(birthdate === '1902-02-02' || birthdate === '2007-07-07')
        {
          vm.current.birthdate = null;
        }


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

        if(vm.current.birthdate !== null)
        {
          vm.current.birthdate = moment( queteur.birthdate.date.substring(0, queteur.birthdate.date.length -16 ),"YYYY-MM-DD").toDate();
          vm.computeAge();
        }

      });

    }
    else
    {
      vm.current = new QueteurResource();
      vm.current.ul_id = vm.ulId;
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

    /**
     * Set the queteur.id of the selected queteur in the model
     * */
    $scope.$watch('queteur.current.referent_volunteerQueteur', function(newValue/*, oldValue*/)
    {
      if(newValue !== null && typeof newValue === "object")
      {
        try
        {
          $log.info("queteurID set to "+newValue.id);
          $scope.queteur.current.referent_volunteer = newValue.id;
        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });

    /**
     * Function used while performing a manual search for a Queteur
     * @param queryString the search string (search is performed on first_name, last_name, nivol)
     * */
    vm.searchQueteur=function(queryString)
    {
      $log.info("Queteur : Manual Search for '"+queryString+"'");
      return QueteurResource.query({"q":queryString}).$promise.then(function success(response)
      {
        return response.map(function success(queteur)
          {
            queteur.full_name= queteur.first_name+' '+queteur.last_name+' - '+queteur.nivol;
            return queteur;
          },
          function error(reason)
          {
            $log.debug("error while searching for queteur with query='"+queryString+"' with reason='"+reason+"'");
          });
      });
    };


  }
})();

