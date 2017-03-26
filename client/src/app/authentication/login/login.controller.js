(function() {
  'use strict';

  angular
    .module('client')
    .controller('LoginController', LoginController);

  /** @ngInject */
  function LoginController($location, AuthenticationService) {
    var vm = this;

    vm.login = login;

    initController();

    function initController() {
      // reset login status
      AuthenticationService.logout();
    }

    function login() {
      vm.loading = true;
      AuthenticationService.login(vm.username, vm.password, function (result) {
        if (result === true)
        {
          $location.path('/');
        }
        else
        {
          vm.error = 'Username or password is incorrect';
          vm.loading = false;
        }
      });
    }
  }
})();
