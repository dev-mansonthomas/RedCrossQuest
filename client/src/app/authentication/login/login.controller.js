(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('LoginController', LoginController);

  /** @ngInject */
  function LoginController($rootScope, $location, $timeout, $window,
                           AuthenticationService) {
    var vm = this;


    var forceSSL = function ()
    {
      if($location.host() !=='localhost' && $location.protocol() !== 'https')
      {
        $window.location.href = $location.absUrl().replace('http', 'https');
      }
    };
    forceSSL();


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
      var loginTimeout = $timeout(function () {vm.loading=false;vm.timeout=true; }, 10000);

      AuthenticationService.login(vm.username, vm.password,
        function success(result)
        {
          if (result === true)
          {
            $timeout.cancel(loginTimeout);
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
          $timeout.cancel(loginTimeout);
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
          vm.success='Un email vient de vous être envoyé ('+email+') avec un lien pour réinitialiser votre mot de passe. L\'email va arriver dans une minute ! (Checkez votre dossier spam)';
          vm.loading=false;
        }
        else
        {
          vm.error='Une erreur est survenue. Veuillez contacter support@redcrossquest.com';
          vm.success=null;
          vm.loading=false;
        }
      });
    };
  }
})();
