(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('RegisterULController', RegisterULController);

  /** @ngInject */
  function RegisterULController($rootScope, $log, $scope,
                                RegisterULResource)
  {
    var vm = this;

    $rootScope.$emit('title-updated', 'Enregistrement RedCrossQuest');

    //show debug info on how the countdown is calculated
    vm.showDebug                             = false;
    vm.settings                              = new RegisterULResource();
    vm.settings.ulSearchOpen                 = true;
    vm.settings.registrationSavedOpen        = false;
    vm.settings.registrationSavedVisible     = false;
    vm.settings.coordinatesOpen              = false;
    vm.settings.coordinatesVisible           = false;
    vm.validateRegistrationButtonReadOnly    = false;
    vm.loading                               = false;

    var errorMsg=Array();
    errorMsg["1"]="La recherche sur RedCall avec le NIVOL du président a retrouné un autre NIVOL. Contactez le support";
    errorMsg["2"]="L'email du président entré dans ce formulaire ne correspond pas à un des emails renseigné dans Pegass. Modifiez l'email du président dans le formulaire ci-dessus (prenom.nom@croix-rouge.fr), ou demandez au président de mettre à jour la fiche pégass du président ou contactez le support.";
    errorMsg["3"]="L'unité Locale saisie dans le formulaire ci-dessus ne correspond pas à l'unité Locale associé au président dans Pegass. Contactez le support.";
    errorMsg["4"]="Le président n'a pas la nomination 'Elu Local' dans Pegass. Contactez le support RedCrossQuest pour valider votre inscription, et faites une demande au support Gaia/Pegass pour corriger ce problème.";
    errorMsg["5"]="Le NIVOL entré pour l'administrateur a déjà un compte actif dans RedCrossQuest";

    vm.register=function()
    {
      var doRegister = function(token) {
        vm.settings.token = token;
        vm.settings.$save().then(function success(returnedData)
        {
          vm.success                           = true;
          vm.settings.registrationSavedVisible = true;
          vm.settings.registrationSavedOpen    = true;
          vm.registrationId                    = returnedData.registrationId;
        }, function onrejected(error)
        {
          var message = "";

          if ( !isNaN(error.data.error) && angular.isNumber(+error.data.error) && error.data.error >0  && error.data.error < 10)
          {
            message = errorMsg[error.data.error];
          }
          else
          {
            message = JSON.stringify(error.data.error);
          }

          vm.error    = true;
          vm.errorStr = message;
          vm.success  = null;
        });
      };

      //recaptchaKey is defined in index.html
      grecaptcha.execute(recaptchaKey, {action: 'rcq/registerNewUL'})
        .then(doRegister, function onrejected(error){
        vm.error    = true;
        vm.errorStr = JSON.stringify(error);
        vm.success  = null;
      });
    };


    vm.validateRegistrationCode=function()
    {
      var doValidateRegistrationCode = function(token) {
        vm.settings.token = token;
        vm.validateRegistrationButtonReadOnly = true;
        vm.loading = true;
        RegisterULResource.validateUlRegistration(
          {
            'token'             : token,
            'ul_id'             : vm.ul_id,
            'registration_id'   : vm.registrationId,
            'registration_token': vm.registrationToken
          },
          function success(returnedData)
          {
            vm.loading = false;
            if(returnedData.success === true)
            {
              vm.success                               = true;
              vm.settings.registrationCompletedVisible = true;
              vm.settings.registrationCompletedOpen    = true;
              vm.settings.registrationSavedOpen        = false;
              if(returnedData.message)
              {
                vm.error    = true;
                vm.errorStr = JSON.stringify(returnedData.message);
              }
            }
            else
            {
              vm.error    = true;
              vm.errorStr = JSON.stringify(returnedData.message);
              vm.settings.registrationCompletedVisible = false;
              vm.settings.registrationCompletedOpen    = false;
              vm.success  = null;
            }
          },
          function onrejectedValidation(error)
          {
            vm.error    = true;
            vm.loading  = false;
            vm.errorStr = JSON.stringify(error.data);
            vm.settings.registrationCompletedVisible = false;
            vm.settings.registrationCompletedOpen    = false;

            vm.success  = null;
          });
      };

      //recaptchaKey is defined in index.html
      grecaptcha.execute(recaptchaKey, {action: 'rcq/validateULRegistration'})
        .then(doValidateRegistrationCode, function onrejectedValidation(error){
          vm.error    = true;
          vm.errorStr = JSON.stringify(error);
          vm.success  = null;
        });
    };

    /**
     * Function used while performing a manual search for an Unité Locale
     * @param queryString the search string (search is performed on name, postal code, city)
     * */
    vm.searchUL=function(queryString)
    {
      //$log.info("UL : Manual Search for '"+queryString+"'");
      return RegisterULResource.query({"q":queryString}).$promise.then(function success(response)
      {
        return response.map(function(ul)
          {
            ul.full_name=  ul.name+' - '+ul.postal_code+' - '+ul.city;
            return ul;
          },
          function error(reason)
          {
            $log.debug("error while searching for ul with query='"+queryString+"' with reason='"+reason+"'");
          });
      });
    };

    //This watch change on queteur variable to update the queteurId field
    $scope.$watch('ulr.settings.ul_name', function(newValue/*, oldValue*/)
    {
      if(newValue !== null && typeof newValue !==  "string" && typeof newValue !== "undefined")
      {
        try
        {
          $scope.ulr.ul_id                       = newValue.id;
          //settings is a resources and get overwritten by the Register call.
          $scope.ulr.settings.id                 = newValue.id;
          $scope.ulr.settings.ul_name            = newValue.full_name;
          $scope.ulr.registration_in_progress    = newValue.registration_in_progress ===36;

          if($scope.ulr.registration_in_progress)
          {
            $scope.ulr.settings.coordinatesOpen    = false;
            $scope.ulr.settings.coordinatesVisible = false;
            $scope.ulr.settings.ulSearchOpen       = false;
            $scope.ulr.settings.registrationSavedVisible = true;
            $scope.ulr.settings.registrationSavedOpen    = true;
            $scope.ulr.registrationId                    = newValue.registration_id;

          }
          else
          {
            $scope.ulr.settings.coordinatesOpen    = true;
            $scope.ulr.settings.coordinatesVisible = true;
            $scope.ulr.settings.ulSearchOpen       = false;
          }

        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });
  }
})();

