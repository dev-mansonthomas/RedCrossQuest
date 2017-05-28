(function() {
  'use strict';

  angular
    .module('client')
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
                              moment, AuthenticationService)
    {
      var vm = this;
      // "vm.creation" is avaible by directive option "bindToController: true"
      vm.relativeDate   = moment(vm.creationDate).fromNow();
      vm.currentUserRole=$localStorage.currentUser.roleId;

      vm.logout=function()
      {
        AuthenticationService.logout();
        $location.path('/login').replace();
      }
    }
  }
})();
