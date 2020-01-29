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
    function NavbarController($localStorage,  $location, $log,
                              moment, AuthenticationService, QueteurResource)
    {
      var vm = this;
      vm.relativeDate   = moment(vm.creationDate).fromNow();
      vm.currentUserRole= $localStorage.currentUser.roleId;
      vm.currentUlMode  = $localStorage.currentUser.ulMode;
      vm.deploymentType = $localStorage.currentUser.d;
      vm.pendingQueteurRegistrationCount = 0;

      if($localStorage.guiSettings)
      {//first display guiSettings is not yet available.
        vm.RCQVersion     = $localStorage.guiSettings.RCQVersion;
      }
      else
      {
        vm.RCQVersion     = "...";
      }


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
})();
