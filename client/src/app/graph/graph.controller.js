/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('GraphController', GraphController);

  /** @ngInject */
  function GraphController($log, $interval, $localStorage,
                           GraphResource, DateTimeHandlingService, moment)
  {
    var vm = this;

    vm.currentUserRole = $localStorage.currentUser.roleId;
    vm.currentUlId     = $localStorage.currentUser.ulId;
    //show debug info on how the countdown is calculated
    vm.showDebug = false;

    vm.tokenAndExpirationDate = GraphResource.get();
    vm.tokenAndExpirationDate.$promise.then(function success(data){
      vm.showGraphs     = true;
      vm.tokenAndExpirationDate.token_expiration_local = DateTimeHandlingService.handleServerDate(data.token_expiration).stringVersion;
      vm.token = data.token;
    });



    vm.grantAccessToGraph=function()
    {
      var graphResource = new GraphResource();

      graphResource.$create().then(
        function success(creationDateAndToken)
        {
          /**
           * Spotfire reports are updated every 2 minutes, every 2 minutes, at the 50th second.
           * This call create a new token and get the creation time.
           * It compares the creation time and compute when the next update of spotfire report will happen
           * and make the user wait after that moment, so that when they click on the link, the spotfire report
           * have the data updated (the token is now in the embedded data) and when the token is provided in the URL,
           * it can match a user and display data.
           *
           * */
          vm.token        = creationDateAndToken.token;


          vm.orignalDate = creationDateAndToken.creationTime;
          vm.nextUpdate  = DateTimeHandlingService.handleServerDate(creationDateAndToken.creationTime).dateInLocalTimeZoneMoment;

          vm.minutes = vm.nextUpdate.minutes();
          vm.seconds = vm.nextUpdate.seconds();

          if (vm.minutes % 2 === 0)
          {
            vm.pair = true;
            vm.nextUpdate.add(2, 'minutes');
            vm.nextUpdate.seconds(0);
          }
          else
          {
            vm.pair = false;
            vm.nextUpdate.add(vm.seconds>50?3:1, 'minutes');
            vm.nextUpdate.seconds(0);
          }

          vm.now = moment();
          vm.numberOfSecondsUntilUpdate = vm.nextUpdate.diff(vm.now, 'seconds');

          vm.countDown = vm.numberOfSecondsUntilUpdate;


          vm.countDownFn = function()
          {
            vm.countDown --;
            if(vm.countDown === 0)
            {
              $interval.cancel(vm.countDownInterval);
              vm.showGraphs     = true;
              vm.showCountDown  = false;
            }
          };

          vm.showCountDown     = true;
          vm.countDownInterval = $interval(vm.countDownFn, 1000, vm.numberOfSecondsUntilUpdate);

        },
      function error(error)
      {
        $log.error(error);
      });
    }
  }
})();

