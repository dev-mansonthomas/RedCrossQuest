(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('LoginController', LoginController);

  /** @ngInject */
  function LoginController($rootScope, $location, $timeout,
                           AuthenticationService) {
    var vm = this;

    $rootScope.$emit('title-updated', 'Login');


    vm.timeout=false;

    initController();

    function initController()
    {
      // reset login status
      AuthenticationService.logout();
    }

    vm.login=function()
    {
      vm.loading = true;
      $timeout(function () {vm.loading=false;vm.timeout=true; }, 10000);

      AuthenticationService.login(vm.username, vm.password,
        function success(result)
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
        },
        function error(message)
        {
          vm.error = 'Service Indisponible - '+message.data;
          vm.loading = false;

        }
      );







    };
    vm.sendInit = function()
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
    };
  }
})();
