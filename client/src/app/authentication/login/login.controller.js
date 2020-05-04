(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('LoginController', LoginController);

  /** @ngInject */
  function LoginController($rootScope, $location, $timeout, $window, $routeParams,
                           AuthenticationService) {
    var vm       = this;
    var forceSSL = function ()
    {
      if($location.host() !=='localhost' && $location.host() !=='rcq' && $location.protocol() !== 'https')
      {
        $window.location.href = $location.absUrl().replace('http', 'https');
      }
    };
    forceSSL();


    $rootScope.$emit('title-updated', 'Login');

    vm.timeout  = false;
    vm.username = $routeParams.login;

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


      var doLogin = function(token) {

        AuthenticationService.login(vm.username, vm.password, token,
          function success(result)
          {
            if (result === true)
            {
              $timeout.cancel(loginTimeout);
              $location.path('/');
            }
            else if (result === false)
            {
              vm.errorStr = 'Login ou mot de passe incorrect';
              vm.error    = true;
              vm.loading  = false;
            }
            else
            {
              vm.errorStr = JSON.stringify(result);
              vm.error    = true;
              vm.loading  = false;
            }
            $timeout.cancel(loginTimeout);
          },
          function error(message)
          {
            $timeout.cancel(loginTimeout);
            vm.error    = true;
            vm.errorStr = 'Un erreur s\'est produite: '+JSON.stringify(message.data.error);
            vm.loading  = false;

          }
        );

      };

    //recaptchaKey is defined in index.html
    grecaptcha.execute(recaptchaKey, {action: 'rcq/login'})
      .then(doLogin);



    };
    vm.sendInit = function()
    {
      var regexp = /[0-9]{4,7}[A-Za-z]{1,1}/;

      if(typeof vm.username === "undefined" || vm.username === '' || !regexp.test(vm.username))
      {
        vm.errorStr="Veuillez saisir votre login (nivol) au bon format (sans les premiers 0)";
        return;
      }

      vm.loading = true;

      var doSendInit = function(token) {

        AuthenticationService.sendInit(vm.username, token,
                                       function(success, email)
                                       {
                                         if(success)
                                         {
                                           vm.error=null;
                                           vm.success=true;
                                           vm.email=email;
                                           vm.loading=false;
                                         }
                                         else
                                         {
                                           vm.error = true;
                                           vm.errorStr='Une erreur est survenue. Veuillez contacter support@redcrossquest.com';
                                           vm.success=null;
                                           vm.loading=false;
                                         }
                                       }, function (error)
                                       {
                                         vm.error = true;
                                         vm.errorStr=JSON.stringify(error);
                                         vm.success=null;
                                         vm.loading=false;
                                       }
        );
      };

      //recaptchaKey is defined in index.html
      grecaptcha.execute(recaptchaKey, {action: 'rcq/sendInit'})
      .then(doSendInit);
    };
  }
})();
