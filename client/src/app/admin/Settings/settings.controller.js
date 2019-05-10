/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('SettingsController', SettingsController);

  /** @ngInject */
  function SettingsController($rootScope, $log, $localStorage, $routeParams, $timeout, $location,
    SettingsResource, DateTimeHandlingService, GeoCoder)
  {
    var vm = this;
    vm.currentUserRole=$localStorage.currentUser.roleId;
    vm.latlongRegExp=/^-?(\d+\.)*\d+$/;
    $rootScope.$emit('title-updated', 'Paramètres');

    //load the local stoarge version first
    vm.settings                     = $localStorage.guiSettings.ul;
    vm.applicationSettings          = $localStorage.guiSettings.ul_settings;
    vm.settings.date_demarrage_rcq  = DateTimeHandlingService.handleServerDate(vm.settings.date_demarrage_rcq).stringVersion;
    vm.mapKey                       = $localStorage.guiSettings.mapKey;
    vm.deploymentType               = $localStorage.currentUser.d;
    //update it with current DB Values


    vm.reload=function()
    {
      SettingsResource.query().$promise.then(handleResult);
      SettingsResource.getULSettings().$promise.then(handleResultAppSettings);
    };

    vm.reload();



    function handleResultAppSettings(settings)
    {
      vm.settings.applicationSettings = settings;
    }

    function handleResult (settings)
    {
      vm.settings = settings;
      vm.settings.date_demarrage_rcq=DateTimeHandlingService.handleServerDate(vm.settings.date_demarrage_rcq).stringVersion;

      /*
      $log.info("Find '"+settings.length+"' settings");
      vm.settings = settings;
      var counti = settings.length;
      var i=0;
      for(i=0;i<counti;i++)
      {
        vm.settings[i].created      = DateTimeHandlingService.handleServerDate(vm.settings[i].created     ).stringVersion;
        vm.settings[i].updated      = DateTimeHandlingService.handleServerDate(vm.settings[i].updated     ).stringVersion;
      }*/
    }


    vm.save = function ()
    {
      vm.settings.$update(savedSuccessfully, errorWhileSaving);
    };

    vm.updateRedQuestSettings = function()
    {
      vm.settings.$updateRedQuestSettings(savedSuccessfully, errorWhileSaving);
    };

    function savedSuccessfully()
    {
      vm.savedSuccessfully= true;
      vm.errorWhileSaving = false;
      $timeout(function () { vm.savedSuccessfully=false; }, 5000);
      SettingsResource.query().$promise.then(function(ulSettings)
        {
          $localStorage.guiSettings.ul = ulSettings;
        });
    }

    function errorWhileSaving(error)
    {
      vm.errorWhileSaving        = true;
      vm.errorWhileSavingDetails = error;
    }

    //documentation
    //https://rawgit.com/allenhwkim/angularjs-google-maps/master/build/docs/index.html
    //https://github.com/allenhwkim/angularjs-google-maps/blob/master/services/geo-coder.js
    vm.updateCoordinatesAndAddress = function(event)
    {
      vm.settings.latitude  = event.latLng.lat();
      vm.settings.longitude = event.latLng.lng();

      GeoCoder.geocode(
        {
          latLng: new google.maps.LatLng(vm.settings.latitude, vm.settings.longitude)
        }).then(function(results)
      {
        vm.settings.errorWhileReverseGeoCoding=false;
        if(results && results.length>0)
        {
          vm.geocoded = {};
          vm.geocoded.display_prompt=true;
          vm.geocoded.formatted_address = results[0].formatted_address ;

          var address_components = results[0].address_components;

          vm.geocoded.address    = address_components[0].long_name+' '+
            address_components[1].long_name;
          vm.geocoded.postal_code= address_components[6].long_name;
          vm.geocoded.city       = address_components[2].long_name;
        }
      }).catch(function(fallback)
      {
        vm.settings.errorWhileReverseGeoCoding=true;
        vm.settings.errorWhileReverseGeoCodingDetails=angular.toJson(fallback);
      });
    };


    vm.geoCodeAddress=function()
    {

      if( !vm.settings.address     &&
          !vm.settings.postal_code &&
          !vm.settings.city )
      {
        return;
      }

      GeoCoder.geocode({address: vm.settings.address+', '+vm.settings.postal_code+', '+vm.settings.city+', France'}).then(function(results)
      {
        vm.settings.errorWhileGeoCoding=false;

        if(results && results.length>0)
        {
          vm.settings.latitude  = results[0].geometry.location.lat();
          vm.settings.longitude = results[0].geometry.location.lng();
        }
      }).catch(function(fallback)
      {
        vm.settings.errorWhileGeoCoding=true;
        vm.settings.errorWhileGeoCodingDetails=angular.toJson(fallback);
      });
    };

    vm.copyGoogleMapsSuggestion=function()
    {
      vm.settings.address      = vm.geocoded.address;
      vm.settings.postal_code  = vm.geocoded.postal_code;
      vm.settings.city         = vm.geocoded.city;
      vm.geocoded             = {};
    };


    vm.handleLocationError=function(browserHasGeolocation)
    {
      vm.settings.currentUserLocationNotAvailable = browserHasGeolocation?1:2;//2: ne supporte pas la geolocation, 1: autre erreur
    };

  }
})();

