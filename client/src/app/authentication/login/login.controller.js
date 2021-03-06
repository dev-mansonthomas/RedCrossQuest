(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('LoginController', LoginController);

  /** @ngInject */
  function LoginController($rootScope, $location, $timeout, $window, $routeParams, $log,
                           AuthenticationService) {
    var vm       = this;
    var forceSSL = function ()
    {
      if($location.protocol() !== 'https'      &&
         $location.host    () !== 'localhost'  &&
         $location.host    () !== 'rcq'        )
      {
        $window.location.href = $location.absUrl().replace('http', 'https').replace('redcrossquest.com', 'redcrossquest.croix-rouge.fr');
      }

      if($location.host    ().indexOf('redcrossquest.com')>-1)
      {
        $window.location.href = $location.absUrl().replace('redcrossquest.com', 'redcrossquest.croix-rouge.fr');
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
      if(!vm.username ||  !vm.password || vm.username.trim() ==='' ||vm.password.trim() ==='')
      {
        vm.errorStr = 'Login ou mot de passe incorrect';
        vm.error    = true;
        return;
      }
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
              vm.success  = null;
            }
            else
            {
              vm.errorStr = JSON.stringify(result);
              vm.error    = true;
              vm.loading  = false;
              vm.success  = null;
            }
            $timeout.cancel(loginTimeout);
          },
          function error(errorResponse)
          {
            $timeout.cancel(loginTimeout);
            $log.error(errorResponse);
            vm.error    = true;//do not display the exception as it contains the password

            if(errorResponse.data.error==='username or password error. Code 2.1')
              vm.errorStr = '<strong>Login ou Mot de passe incorrect!</strong> <br/>' +
                'Vérifier votre NIVOL sur Pegass/Gaia.<br/>' +
                'Votre compte n\'est peut être pas activé comme étant utilisateur de RedCrossQuest. <br/><br/>' +
                'Si vous avez oublié votre mot de passe, saisissez votre NIVOL et cliquer sur le bouton <br>"<strong>Réinitialiser mon Mot de Passe</strong>".<br/><br/>' +
                '<strong>Attention</strong> : le lien pour réinitialiser son mot de passe n\'est valable que 4 heures et ne peut être utilisé qu\'une fois.' +
                'Vérifier le dossier SPAM';
            else
              vm.errorStr = 'Un erreur s\'est produite à : '+JSON.stringify(new Date());

            vm.loading  = false;
            vm.success  = null;
          }
        );

      };

    //recaptchaKey is defined in index.html
      try
      {
        grecaptcha.execute(recaptchaKey, {action: 'rcq/login'})
          .then(doLogin);
      }
      catch(Exception)
      {
        vm.error    = true;
        vm.errorStr = 'Un erreur s\'est produite: '+JSON.stringify(Exception.message);
        vm.loading  = false;
        vm.success  = null;
        $timeout.cancel(loginTimeout);
      }



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
                                           vm.errorStr='Une erreur est survenue. Veuillez contacter support.redcrossquest@croix-rouge.fr';
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

      try
      {
        //recaptchaKey is defined in index.html
        grecaptcha.execute(recaptchaKey, {action: 'rcq/sendInit'})
          .then(doSendInit);
      }
      catch(Exception)
      {
        vm.error    = true;
        vm.errorStr = 'Un erreur s\'est produite: '+JSON.stringify(Exception.message);
        vm.loading  = false;
        vm.success=null;
      }
    };
  }
})();
