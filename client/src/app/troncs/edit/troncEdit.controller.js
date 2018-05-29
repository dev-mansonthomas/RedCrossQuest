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
                               TroncResource, moment, TroncQueteurResource)
  {
    var vm      = this;
    var troncId = $routeParams.id;

    vm.currentUserRole=$localStorage.currentUser.roleId;

    vm.typeTroncList=[
      {id:1,label:'Tronc'},
      {id:2,label:'Urne chez un commerçant'},
      {id:3,label:'Autre'},
      {id:4,label:'Terminal Carte Bleue'}
    ];

    vm.handleDate = function (theDate)
    {
      if(theDate ===null)
        return null;

      var dateAsString = theDate.date;
      return moment( dateAsString.substring (0, dateAsString.length  - 3 ),"YYYY-MM-DD  HH:mm:ss.SSS");
    };

    if (angular.isDefined(troncId))
    {
      vm.current = TroncResource.get({ 'id': troncId });

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

            if(oneRow.retour !==null && oneRow.depart !== null)
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
      $rootScope.$emit('title-updated', 'Edition du tronc - '+troncId);
    }
    else
    {
      vm.current = new TroncResource();
      vm.current.type=1;
      vm.current.enabled=true;
      $rootScope.$emit('title-updated', 'Création d\'un nouveau Tronc');
    }

    vm.save =function save()
    {
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

      $timeout(function () { vm.savedSuccessfully=false; }, 5000);
    }

    function errorWhileSaving(error)
    {
      vm.errorWhileSaving=true;
      vm.errorWhileSavingDetails=error;
    }

  }
})();

