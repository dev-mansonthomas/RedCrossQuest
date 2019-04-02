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
    function NavbarController($localStorage,  $location,
                              moment, AuthenticationService, QueteurResource)
    {
      var vm = this;
      vm.relativeDate   = moment(vm.creationDate).fromNow();
      vm.currentUserRole= $localStorage.currentUser.roleId;
      vm.currentUlMode  = $localStorage.currentUser.ulMode;
      vm.deploymentType = $localStorage.currentUser.d;
      vm.pendingQueteurRegistrationCount = 0;

      QueteurResource.countPendingQueteurRegistration().$promise.then(function(result){
        vm.pendingQueteurRegistrationCount = result[0];
      });



      vm.logout=function()
      {
        AuthenticationService.logout();
        $location.path('/login').replace();
      }
    }
  }
})();
