/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('EditPointQueteController', EditPointQueteController);

  /** @ngInject */
  function EditPointQueteController($rootScope, $log, $localStorage, $routeParams, $timeout,
                                    PointQueteResource, DateTimeHandlingService,  GeoCoder)
  {
    var vm = this;

    vm.typePointQueteList=[
      {id:1,label:'Voix Publique'},
      {id:2,label:'Piéton'},
      {id:3,label:'Boutique'},
      {id:4,label:'Base UL'},
      {id:5,label:'Autre'}
    ];

    vm.transportPointQueteList=[
      {id:1,label:'A Pied'},
      {id:2,label:'Voiture'},
      {id:3,label:'Vélo'},
      {id:4,label:'Train/Tram'},
      {id:5,label:'Autre'}
    ];

    vm.currentUserRole = $localStorage.currentUser.roleId;
    vm.settings        = $localStorage.guiSettings;
    var pointQueteId   = $routeParams.id;



    //documentation
    //https://rawgit.com/allenhwkim/angularjs-google-maps/master/build/docs/index.html
    //https://github.com/allenhwkim/angularjs-google-maps/blob/master/services/geo-coder.js
    vm.updateCoordinatesAndAddress = function(event)
    {
      vm.current.latitude  = event.latLng.lat();
      vm.current.longitude = event.latLng.lng();

       GeoCoder.geocode(
        {
          latLng: new google.maps.LatLng(vm.current.latitude, vm.current.longitude)
        }).then(function(results)
       {
         vm.current.errorWhileReverseGeoCoding=false;
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
         vm.current.errorWhileReverseGeoCoding=true;
         vm.current.errorWhileReverseGeoCodingDetails=angular.toJson(fallback);
       });
    };


    vm.geoCodeAddress=function()
    {

      if( !vm.current.address     &&
          !vm.current.postal_code &&
          !vm.current.city )
      {
        return;
      }

      GeoCoder.geocode(
        {
          address: vm.current.address+', '+vm.current.postal_code+', '+vm.current.city+', France'}
        ).then(function(results)
      {
        vm.current.errorWhileGeoCoding=false;
        if(results && results.length>0)
        {
          vm.current.latitude  = results[0].geometry.location.lat();
          vm.current.longitude = results[0].geometry.location.lng();
        }
      }).catch(function(fallback)
      {
        vm.current.errorWhileGeoCoding=true;
        vm.current.errorWhileGeoCodingDetails=angular.toJson(fallback);
      });
    };
    vm.copyGoogleMapsSuggestion=function()
    {
      vm.current.address      = vm.geocoded.address;
      vm.current.postal_code  = vm.geocoded.postal_code;
      vm.current.city         = vm.geocoded.city;
      vm.geocoded = {};
    };


    vm.handleLocationError=function(browserHasGeolocation)
    {
      vm.current.currentUserLocationNotAvailable = browserHasGeolocation?1:2;//2: ne supporte pas la geolocation, 1: autre erreur
    };


    vm.createNewPointQuete=function()
    {
      vm.current          = new PointQueteResource();
      vm.current.ul_id    = $localStorage.currentUser.ulId;
      vm.current.ul_name  = $localStorage.currentUser.ulName;


      if (navigator.geolocation)
      {
        navigator.geolocation.getCurrentPosition(function(position)
        {
          vm.current.latitude   = position.coords.latitude;
          vm.current.longitude  = position.coords.longitude;
        },
          function()
          {
            vm.handleLocationError(true);
        });
      }
      else
      {
        // Browser doesn't support Geolocation
        vm.handleLocationError(false);
      }
    };


    if (angular.isDefined(pointQueteId))
    {
      PointQueteResource.get({ 'id': pointQueteId }).$promise.then(function(pointQuete)
      {
        vm.current = pointQuete;
        vm.current.created      = DateTimeHandlingService.handleServerDate(vm.current.created).stringVersion;
        $rootScope.$emit('title-updated', 'Point de quête - '+vm.current.name);
      });
    }
    else
    {
      vm.createNewPointQuete();
    }

    vm.save = function ()
    {
      //vm.uploadFiles();

      if (angular.isDefined(vm.current.id))
      {
        vm.current.$update(savedSuccessfully, errorWhileSaving);
      }
      else
      {
        vm.current.$save(savedSuccessfully, errorWhileSaving);
      }

    };

    function savedSuccessfully(pointQuete)
    {
      vm.savedSuccessfully= true;
      vm.current          = pointQuete;
      vm.current.created  = DateTimeHandlingService.handleServerDate(vm.current.created).stringVersion;
      $timeout(function () { vm.savedSuccessfully=false; }, 5000);
    }

    function errorWhileSaving(error)
    {
      vm.errorWhileSaving=true;
      vm.errorWhileSavingDetails=error;
    }
  }
})();

