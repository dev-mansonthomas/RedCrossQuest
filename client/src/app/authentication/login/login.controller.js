(function() {
  'use strict';

  angular
    .module('client')
    .controller('LoginController', LoginController);

  /** @ngInject */
  function LoginController($location, AuthenticationService) {
    var vm = this;

    vm.login    = login   ;
    vm.sendInit = sendInit;

    initController();

    function initController()
    {
      // reset login status
      AuthenticationService.logout();
    }

    function login()
    {
      vm.loading = true;
      AuthenticationService.login(vm.username, vm.password,
        function (result)
        {
          if (result === true)
          {
            $location.path('/');
          }
          else
          {
            vm.error = 'Login ou mot de passe incorrect';
            vm.loading = false;
          }
        }
        );
    }
    function sendInit()
    {
      var regexp = /[0-9]{4,7}[A-Z]{1,1}/;

      if(typeof vm.username === "undefined" || vm.username === '' || !regexp.test(vm.username))
      {
        vm.error="Veuillez saisir votre login (nivol)";
        return;
      }

      vm.loading = true;
      AuthenticationService.sendInit(vm.username, function(success, email){

        if(success)
        {
          vm.error=null;
          vm.success='Un email vient de vous être envoyé ('+email+') avec un lien pour réinitialiser votre mot de passe. (checker votre dossier spam)';
          vm.loading=false;
        }
        else
        {
          vm.error='Une erreur est survenue. Veuillez contacter votre cadre local ou départemental';
          vm.success=null;
          vm.loading=false;
        }
      });
    }
  }
})();
