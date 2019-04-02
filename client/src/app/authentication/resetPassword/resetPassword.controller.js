(function () {
  'use strict';

  /**Load zxcvbn library*/

  (function () {
    var ZXCVBN_SRC = 'bower_components/zxcvbn/dist/zxcvbn.js';

    var async_load = function () {
      var first, s;
      // create a <script> element using the DOM API
      s = document.createElement('script');

      // set attributes on the script element
      s.src = ZXCVBN_SRC;
      s.type = 'text/javascript';
      s.async = true; // HTML5 async attribute

      // Get the first script element in the document
      first = document.getElementsByTagName('script')[0];

      // insert the <script> element before the first in the document
      return first.parentNode.insertBefore(s, first);
    };

    // attach async_load as callback to the window load event
    if (window.attachEvent !== null && typeof window.attachEvent !== 'undefined') {
      window.attachEvent('onload', async_load);
    } else {
      window.addEventListener('load', async_load, false);
    }
  }).call(this);



  /* [Nicolas - 18 may] Peut-etre pas necessaire ce @ngInject vu que j'ai defini l'injection , @ngInject

   */
  function ResetPasswordController($rootScope, $location, $scope,
                                   zxcvbn, AuthenticationService)
  {
    var vm = this;
    vm.key = null;
    vm.rate = 0;
    vm.passwordMatch = false;

    $rootScope.$emit('title-updated', 'Ré-initialisation de votre mot de passe');

    AuthenticationService.logout();

    vm.checkIfPasswordMatch = function ()
    {
      vm.passwordMatch = vm.password === vm.passwordRepeat;
    };

    vm.getPasswordCheckText = function ()
    {
      if (typeof vm.passwordRepeat === 'undefined' || vm.passwordRepeat === '')
        return "";

      return vm.passwordMatch === true ? "Bravo! les mots de passe correspondent!" : "Echec : Les deux mots de passe ne correspondent pas";
    };

    vm.computeStrength = function ()
    {
      if (typeof vm.password === 'undefined' || vm.password === '')
        vm.rate = 0;
      else
        vm.rate = zxcvbn(vm.password).score;
    };

    vm.getTxtFromRating = function ()
    {
      if (typeof vm.password === 'undefined' || vm.password === '')
        return "";

      vm.checkIfPasswordMatch();

      if (vm.rate === 0)
      {
        return "Booouuuuhhh, vraiment... c'est pas un password ca ;)";
      }
      else if (vm.rate === 1)
      {
        return "Trop facile à deviner ! Ayez un peu d'imagination ;)";
      }
      else if (vm.rate === 2)
      {
        return "Devinable sans trop de difficulté... encore un effort !";
      }
      else if (vm.rate === 3)
      {
        return "Difficilement devinable, c'est pas mal !";
      }
      else if (vm.rate === 4)
      {
        return "Très bon password ! Bravo !";
      }
    };


    vm.resetPassword = resetPassword;

    //on slow network, the recaptcha lib is not yet loaded
    addEventListener("load", initController);



    function initController()
    {
      // reset login status
      vm.key     = $location.search()['key'];
      vm.loading = true;

      //recaptchaKey is defined in index.html
      grecaptcha.execute(recaptchaKey, {action: 'rcq/getUserInfoWithUUID'})
        .then(function(token)
        {
          AuthenticationService.getUserInfoWithUUID(
            vm.key, token,
            function (success, info)
            {
              if (success)
              {
                vm.username = info.nivol;
                vm.error    = null;
                vm.loading  = false;
              }
              else
              {
                vm.error = "Votre demande est invalide ou périmée ou votre utilisateur a été désactivé. Veuillez contacter le support.";
              }
            },
            function (errorMessage)
            {
              vm.error = JSON.stringify(errorMessage.data);
            }
          );
        });

    }

    function resetPassword()
    {
      vm.loading = true;

      //recaptchaKey is defined in index.html
      grecaptcha.execute(recaptchaKey, {action: 'rcq/resetPassword'})
        .then(function(token)
        {
          AuthenticationService.resetPassword(vm.key, vm.password, token, function (success, email) {

            if (success) {
              vm.error = null;
              vm.success = 'Un email de confirmation vient de vous être envoyé (' + JSON.stringify(email).slice(1, -1) + ')';
              vm.loading = false;
            }
            else {
              vm.error = 'Une erreur est survenue. Avez vous rempli le formulaire moins d\'une heure après avoir recçue l\'email ? <br/> <strong>🐢</strong> Si ce n\'est pas le cas, retourner sur la page de login et recommencez la procédure. <br/>Si ça ne fonctionne toujours pas, Veuillez contacter votre cadre local ou départemental';
              vm.success = null;
              vm.loading = false;
            }
          });
        });
    }
  }

  angular
    .module('redCrossQuestClient')
    .factory('zxcvbn', function () {
      return window.zxcvbn; // zxcvbn ext charge par le bout de code 'ZXCVBN_SRC'
    })
    .controller('ResetPasswordController', ['$rootScope','$location', '$scope', 'zxcvbn', 'AuthenticationService', ResetPasswordController]);
})();
