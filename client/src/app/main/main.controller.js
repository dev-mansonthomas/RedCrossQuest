(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('MainController', MainController);

  /** @ngInject */
  function MainController($timeout, $localStorage,
                          toastr, SettingsResource)
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

    vm.currentUserRole=$localStorage.currentUser.roleId;

    vm.displayInstructions=false;

//TODO : find a way to load once, before any page. (a refresh a point quete, should query this first to get the google maps API key)
    SettingsResource.get().$promise.then(function(settings)
    {
      $localStorage.guiSettings = settings;
    });

    if(vm.currentUserRole >=4)
    {
      SettingsResource.getSetupStatus().$promise.then(function(result)
      {
        vm.setupStatus = result;
      });

    }

    $(function () {
      $('[data-toggle="popover"]').popover()
    });

    activate();

    function activate() {

      $timeout(function() {
        vm.classAnimation = 'rubberBand';
      }, 4000);
    }

    function showToastr() {
      toastr.info('Fork <a href="https://github.com/Swiip/generator-gulp-angular" target="_blank"><b>generator-gulp-angular</b></a>');
      vm.classAnimation = '';
    }
  }
})();
