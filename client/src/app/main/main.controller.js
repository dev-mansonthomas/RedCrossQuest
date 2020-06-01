(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('MainController', MainController);

  /** @ngInject */
  function MainController($timeout, $localStorage, $scope, $rootScope,
                          toastr, SettingsResource, PointQueteService)
  {
    var vm = this;

    vm.awesomeThings  = [];
    vm.classAnimation = '';
    vm.creationDate   = 1456333782311;
    vm.showToastr     = showToastr;

    vm.username       = $localStorage.currentUser.username;
    vm.ulName         = $localStorage.currentUser.ulName;
    vm.ulId           = $localStorage.currentUser.ulId;
    vm.deploymentType = $localStorage.currentUser.d;
    vm.currentUserRole= $localStorage.currentUser.roleId;


    //load in local storage the list of points de quete
    //used in preparationQuete for autocomplete
    PointQueteService.loadPointQuete();

    vm.displayInstructions=false;

    SettingsResource.getAllSettings().$promise.then(function(settings)
    {
      $localStorage.guiSettings = settings;
      vm.first_name = $localStorage.guiSettings.user.first_name;
      $rootScope.$emit('title-updated', 'Bienvenue '+vm.first_name);
    });

    if(vm.currentUserRole >=4)
    {
      SettingsResource.getSetupStatus().$promise.then(function(result)
      {
        vm.setupStatus = result;
      });

    }

    $(function () {
      $('[data-toggle="popover"]').popover();
    });

    function activate()
    {
      $timeout(function() {
        vm.classAnimation = 'rubberBand';
      }, 4000);
    }

    activate();



    function showToastr() {
      toastr.info('Fork <a href="https://github.com/Swiip/generator-gulp-angular" target="_blank"><b>generator-gulp-angular</b></a>');
      vm.classAnimation = '';
    }
  }
})();
