/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('TroncEditController', TroncEditController);

  /** @ngInject */
  function TroncEditController($log, $routeParams, $timeout,
                               TroncResource, moment, TroncQueteurResource)
  {
    var vm      = this;
    var troncId = $routeParams.id;

    vm.typeTroncList=[
      {id:1,label:'Tronc'},
      {id:2,label:'Urne chez un commerçant'},
      {id:3,label:'Autre'},
      {id:3,label:'Terminal Carte Bleue'}
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
            data[i].depart            = vm.handleDate(data[i].depart);
            data[i].depart_theorique  = vm.handleDate(data[i].depart_theorique);
            data[i].retour            = vm.handleDate(data[i].retour);

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
    }
    else
    {
      vm.current = new TroncResource();
    }

    vm.save = save;

    function save()
    {
      vm.current.$update(savedSuccessfully, errorWhileSaving);
      $log.debug("Saved called");
    }


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

