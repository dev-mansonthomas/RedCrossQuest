(function() {
  'use strict';

  angular
    .module('client')
    .controller('ResetPasswordController', ResetPasswordController);

  /** @ngInject */
  function ResetPasswordController($location, AuthenticationService) {
    var vm = this;
    vm.key = null;


    vm.resetPassword=resetPassword;

    initController();

    function initController() {
      // reset login status
      vm.key = $location.search()['key'];
      AuthenticationService.getUserInfoWithUUID(vm.key, function(success, info){
        if(success)
        {
          vm.info=[info.first_name, info.last_name, info.email, info.mobile, info.nivol];
          vm.username = info.nivol;

          vm.error=null;
          vm.loading=false;


        }
        else
        {
          vm.error="Votre demande est invalide ou périmée";
        }

      });


    }


    function resetPassword()
    {
      vm.loading = true;
      AuthenticationService.resetPassword(vm.key, vm.password, function(success, email){

        if(success)
        {
          vm.error=null;
          vm.success='Un email de confirmation vient de vous être envoyé ('+email+')';
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
