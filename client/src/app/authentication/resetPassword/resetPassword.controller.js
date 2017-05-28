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
  function ResetPasswordController($location, zxcvbn, AuthenticationService) {
    var vm = this;
    vm.key = null;
    vm.rate = 0;
    vm.passwordMatch = false;

    AuthenticationService.logout();

    vm.checkIfPasswordMatch = function () {
      vm.passwordMatch = vm.password === vm.passwordRepeat;
    };

    vm.getPasswordCheckText = function () {
      if (typeof vm.passwordRepeat === 'undefined' || vm.passwordRepeat === '')
        return "";

      return vm.passwordMatch === true ? "Bravo! les mots de passe correspondent!" : "Echec : Les deux mots de passe ne correspondent pas";
    };

    vm.computeStrength = function () {
      if (typeof vm.password === 'undefined' || vm.password === '')
        vm.rate = 0;
      else
        vm.rate = zxcvbn(vm.password).score;
    };

    vm.getTxtFromRating = function () {
      if (typeof vm.password === 'undefined' || vm.password === '')
        return "";

      vm.checkIfPasswordMatch();

      if (vm.rate === 0) {
        return "Booouuuuhhh, vraiment... c'est pas un password ca ;)";
      }
      else if (vm.rate === 1) {
        return "Trop facile à deviner ! Ayez un peu d'imagination ;)";
      }
      else if (vm.rate === 2) {
        return "Devinable sans trop de difficulté... encore un effort !";
      }
      else if (vm.rate === 3) {
        return "Difficilement devinable, c'est pas mal !";
      }
      else if (vm.rate === 4) {
        return "Très bon password ! Bravo !";
      }
    };


    vm.resetPassword = resetPassword;

    initController();

    function initController() {
      // reset login status
      vm.key = $location.search()['key'];
      AuthenticationService.getUserInfoWithUUID(vm.key, function (success, info) {
        if (success) {
          vm.info = [info.first_name, info.last_name, info.email, info.mobile, info.nivol];
          vm.username = info.nivol;

          vm.error = null;
          vm.loading = false;


        }
        else {
          vm.error = "Votre demande est invalide ou périmée";
        }

      });


    }

    function resetPassword() {
      vm.loading = true;
      AuthenticationService.resetPassword(vm.key, vm.password, function (success, email) {

        if (success) {
          vm.error = null;
          vm.success = 'Un email de confirmation vient de vous être envoyé (' + email + ')';
          vm.loading = false;
        }
        else {
          vm.error = 'Une erreur est survenue. Veuillez contacter votre cadre local ou départemental';
          vm.success = null;
          vm.loading = false;
        }
      });
    }

  }

  angular
    .module('client')
    .factory('zxcvbn', function () {
      return window.zxcvbn; // zxcvbn ext charge par le bout de code 'ZXCVBN_SRC'
    })
    .controller('ResetPasswordController', ['$location', 'zxcvbn', 'AuthenticationService', ResetPasswordController]);
})();
