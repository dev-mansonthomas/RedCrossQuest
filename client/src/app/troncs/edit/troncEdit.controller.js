/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('TroncEditController', TroncEditController);

  /** @ngInject */
  function TroncEditController($rootScope, $log, $routeParams, $timeout,$localStorage,
                               TroncResource, moment, TroncQueteurResource, DateTimeHandlingService)
  {
    var vm      = this;
    var troncId = $routeParams.id;

    vm.currentUserRole = $localStorage.currentUser.roleId;
    vm.pointQueteHash  = $localStorage.pointsQueteHash;

    vm.typeTroncList=[
      {id:1,label:'Tronc'},
      {id:2,label:'Tronc chez un commerçant'},
      {id:3,label:'Autre'}
    ];

    vm.handleDate = function (theDate)
    {
      return DateTimeHandlingService.handleServerDate(theDate).dateInLocalTimeZoneMoment;
    };

    if (angular.isDefined(troncId))
    {
       TroncResource.get({ 'id': troncId }).$promise.then(function success(data)
      {
        vm.current = data;
        vm.current.created = DateTimeHandlingService.handleServerDate(vm.current.created).stringVersion;
        vm.current.saveInProgress=false;
        loadTroncQueteurForTronc();
      }).catch(function(e){
         $log.error("error searching for Tronc", e);
       });

      $rootScope.$emit('title-updated', 'Edition du tronc - '+troncId);
    }
    else
    {
      vm.current = new TroncResource();
      vm.current.saveInProgress=false;
      vm.current.type=1;
      vm.current.enabled=true;
      $rootScope.$emit('title-updated', 'Création d\'un nouveau Tronc');
    }

    vm.save =function save()
    {
      vm.current.saveInProgress=true;
      if (angular.isDefined(troncId))
      {
        vm.current.$update(savedSuccessfully, errorWhileSaving);
        $log.debug("Saved called");
      }
      else
      {
        vm.current.$save(savedSuccessfully, errorWhileSaving);
      }
    };


    function savedSuccessfully()
    {
      vm.savedSuccessfully=true;
      vm.current.saveInProgress=false;
      $timeout(function () { vm.savedSuccessfully=false; }, 5000);
    }

    function errorWhileSaving(error)
    {
      vm.current.saveInProgress=false;
      vm.errorWhileSaving=true;
      vm.errorWhileSavingDetails=error;
    }

    function loadTroncQueteurForTronc()
    {
      TroncQueteurResource.getTroncsQueteurForTroncId({'tronc_id': troncId}).$promise.then(
        function success(data)
        {
          var dataLength = data.length;
          for(var i=0;i<dataLength;i++)
          {
            var oneRow = data[i];
            oneRow.depart            = vm.handleDate(oneRow.depart);
            oneRow.depart_theorique  = vm.handleDate(oneRow.depart_theorique);
            oneRow.retour            = vm.handleDate(oneRow.retour);

            if(oneRow.retour !==null && oneRow.depart !== null && oneRow.retour !=="" && oneRow.depart !== "")
            {
              oneRow.duration = moment.duration(oneRow.retour.diff(oneRow.depart)).asMinutes();
            }
          }

          vm.current.troncs_queteur  = data;
        },
        function error(error)
        {
          $log.error(error);
        }
      );
    }

  }
})();

