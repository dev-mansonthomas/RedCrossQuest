(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .directive('acmeNavbar', acmeNavbar);

  /** @ngInject */
  function acmeNavbar() {
    var directive = {
      restrict: 'E',
      templateUrl: 'app/components/navbar/navbar.html',
      scope: {
          creationDate: '='
      },
      controller: NavbarController,
      controllerAs: 'vm',
      bindToController: true
    };

    return directive;

    /** @ngInject */
    function NavbarController($localStorage,  $location, $log, $uibModal,
                              moment, AuthenticationService, QueteurResource, VersionResource)
    {
      var vm = this;
      vm.relativeDate    = moment(vm.creationDate).fromNow();
      vm.currentUserRole = $localStorage.currentUser.roleId;
      vm.currentUlMode   = $localStorage.currentUser.ulMode;
      vm.deploymentType  = $localStorage.currentUser.d;
      vm.pendingQueteurRegistrationCount = 0;
      vm.currentPath     = $location.path();
      vm.frontEndVersion = $localStorage.frontEndVersion;

      if($localStorage.guiSettings)
      {//first display guiSettings is not yet available.
        vm.RCQVersion     = $localStorage.guiSettings.RCQVersion;
      }
      else
      {
        vm.RCQVersion     = "...";
      }

       //timestamp to avoid server cache
      VersionResource.get({"id":(new Date()).getTime()}).$promise.then(function(result)
      {
        if(!vm.frontEndVersion)
        {
          vm.frontEndVersion = result.deployDate;
          $localStorage.frontEndVersion = vm.frontEndVersion;
        }
        else
        {
          if(vm.frontEndVersion !== result.deployDate)
          {
            $uibModal.open({
              animation  : true,
              templateUrl: 'NewVersioNModal.html',
              controller : 'NewVersionModalInstanceController',
              windowClass: 'newVersion-modal-dialog',
              resolve    : {
                versionInfo: function () {
                  return result;
                }
              }
            });
          }
        }


      }).catch(function(e){
        $log.error("error while getting the frontend version", e);
      });

      QueteurResource.countPendingQueteurRegistration().$promise.then(function(result){
        vm.pendingQueteurRegistrationCount = result.count;
      }).catch(function(e){
        $log.error("error searching for Queteur", e);
      });



      vm.logout=function()
      {
        AuthenticationService.logout();
        $location.path('/login').replace();
      };
      vm.goToChangelog=function()
      {
        $location.path('/changelog').replace();
      };
    }
  }


  angular
    .module('redCrossQuestClient')
    .controller('NewVersionModalInstanceController',
      function ($scope, $uibModalInstance, $window, $localStorage, versionInfo)
      {
        $scope.versionInfo = versionInfo;

        $scope.reload = function ()
        {
          $localStorage.frontEndVersion = null;
          //force reload from the server
          $window.location.reload(true);
        };

        $scope.cancel = function ()
        {
          $uibModalInstance.dismiss('cancel');
        };
      });
})();
