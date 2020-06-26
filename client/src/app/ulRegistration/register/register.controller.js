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
    vm.settings.registrationCompletedOpen    = false;
    vm.settings.registrationCompletedVisible = false;
    vm.settings.coordinatesOpen              = false;
    vm.settings.coordinatesVisible           = false;


    vm.register=function()
    {
      var doRegister = function(token) {

        vm.settings.token = token;
        vm.settings.$save().then(function success(returnedData)
        {
          vm.success                               = true;
          vm.settings.registrationCompletedVisible = true;
          vm.settings.registrationCompletedOpen    = true;
          vm.registrationId = returnedData.registrationId;
        }, function onrejected(error)
        {
          vm.error    = true;
          vm.errorStr = JSON.stringify(error.data.error);
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


    /**
     * Function used while performing a manual search for an Unité Locale
     * @param queryString the search string (search is performed on name, postal code, city)
     * */
    vm.searchUL=function(queryString)
    {
      $log.info("UL : Manual Search for '"+queryString+"'");
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
          $scope.ulr.settings.id                 = newValue.id;
          $scope.ulr.settings.ul_name            = newValue.full_name;
          $scope.ulr.settings.coordinatesOpen    = true;
          $scope.ulr.settings.coordinatesVisible = true;
          $scope.ulr.settings.ulSearchOpen       = false;
        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });
  }
})();

