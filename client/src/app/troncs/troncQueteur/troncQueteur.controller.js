/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('TroncQueteurController', TroncQueteurController);

  /** @ngInject */
  function TroncQueteurController($scope, $log, $routeParams, $timeout, $localStorage, // $anchorScroll, $location,
                                  TroncResource, TroncQueteurResource, PointQueteResource,
                                  QRDecodeService,
                                  DateTimeHandlingService)
  {
    var vm = this;
    vm.current = {};
    vm.onlyNumbers = /^[0-9]{1,3}$/;
    vm.cbFormat    = /^[0-9]+(\.[0-9]{1,2})?$/;

    vm.currentUserRole=$localStorage.currentUser.roleId;
    vm.currentUlMode  =$localStorage.currentUser.ulMode;

    vm.current.readOnlyView  = false;
    vm.current.adminEditMode = false;
    vm.activateAdminEditMode = function()
    {
      vm.current.adminEditMode=true;
    };



    var tronc_queteur_id = $routeParams.id;

    if (angular.isDefined(tronc_queteur_id) &&  tronc_queteur_id !== 0)
    {
      $log.debug("loading data for Tronc Queteur with ID='"+tronc_queteur_id+"' ");
      TroncQueteurResource.get({id:tronc_queteur_id}).$promise.then(handleTroncQueteur);
    }


    vm.getTypeLabel=function(id)
    {
      if(id===1)
        return 'Voie Publique';
      else if(id===2)
        return 'Pieton';
      else if(id===3)
        return 'Boutique';
      else
        return 'Base UL';
    };

    //pointQuete list
    vm.pointsQuete = PointQueteResource.query();
    vm.pointsQuete.$promise.then(function success(pointQueteList)
    {
      vm.pointsQueteHash = [];
      pointQueteList.forEach(function(onePointQuete){
        vm.pointsQueteHash[onePointQuete.id]=onePointQuete;
      });
    });



    vm.isEmpty=function(value)
    {
      if(typeof value === "undefined" || value === null || value === "" || value === 0)
      {
        return 0;
      }
      else
      {
        return value;
      }

    };

    vm.setNonFilledBillToZero=function()
    {

      vm.current.tronc_queteur.euro5   = vm.isEmpty(vm.current.tronc_queteur.euro5  );
      vm.current.tronc_queteur.euro10  = vm.isEmpty(vm.current.tronc_queteur.euro10 );
      vm.current.tronc_queteur.euro20  = vm.isEmpty(vm.current.tronc_queteur.euro20 );
      vm.current.tronc_queteur.euro50  = vm.isEmpty(vm.current.tronc_queteur.euro50 );
      vm.current.tronc_queteur.euro100 = vm.isEmpty(vm.current.tronc_queteur.euro100);
      vm.current.tronc_queteur.euro200 = vm.isEmpty(vm.current.tronc_queteur.euro200);
      vm.current.tronc_queteur.euro500 = vm.isEmpty(vm.current.tronc_queteur.euro500);
    };


    vm.fillForDeletion = function()
    {

      if(typeof vm.current.tronc_queteur.retour ==='undefined' || vm.current.tronc_queteur.retour === null)
      {
        vm.current.tronc_queteur.retour = new Date();
      }
      if(typeof vm.current.tronc_queteur.depart ==='undefined' || vm.current.tronc_queteur.depart === null)
      {
        vm.current.tronc_queteur.depart = new Date();
      }

      vm.current.tronc_queteur.euro5   = vm.isEmpty(vm.current.tronc_queteur.euro5  );
      vm.current.tronc_queteur.euro10  = vm.isEmpty(vm.current.tronc_queteur.euro10 );
      vm.current.tronc_queteur.euro20  = vm.isEmpty(vm.current.tronc_queteur.euro20 );
      vm.current.tronc_queteur.euro50  = vm.isEmpty(vm.current.tronc_queteur.euro50 );
      vm.current.tronc_queteur.euro100 = vm.isEmpty(vm.current.tronc_queteur.euro100);
      vm.current.tronc_queteur.euro200 = vm.isEmpty(vm.current.tronc_queteur.euro200);
      vm.current.tronc_queteur.euro500 = vm.isEmpty(vm.current.tronc_queteur.euro500);

      vm.current.tronc_queteur.euro2   = vm.isEmpty(vm.current.tronc_queteur.euro2  );
      vm.current.tronc_queteur.euro1   = vm.isEmpty(vm.current.tronc_queteur.euro1  );
      vm.current.tronc_queteur.cents50 = vm.isEmpty(vm.current.tronc_queteur.cents50);
      vm.current.tronc_queteur.cents20 = vm.isEmpty(vm.current.tronc_queteur.cents20);
      vm.current.tronc_queteur.cents10 = vm.isEmpty(vm.current.tronc_queteur.cents10);
      vm.current.tronc_queteur.cents5  = vm.isEmpty(vm.current.tronc_queteur.cents5 );
      vm.current.tronc_queteur.cents2  = vm.isEmpty(vm.current.tronc_queteur.cents2 );
      vm.current.tronc_queteur.cent1   = vm.isEmpty(vm.current.tronc_queteur.cent1  );

    };

    vm.confirmSave = function ()
    {
      vm.current.overrideWarning=true;
      vm.save();
    };

    vm.save = function save()
    {
      $log.debug("Saving the number of coins");
      $log.debug(vm.current.tronc_queteur);

      if(vm.checkInputValues() && vm.current.overrideWarning !== true)
      {
        vm.current.confirmInputValues=true;
      }
      else
      {

        //savedSuccessfully function handle the save of data in adminMode
        if(vm.current.tronc.type == 4)
        {
          vm.current.tronc_queteur.$saveCreditCard(savedSuccessfully, onSaveError);
        }
        else
        {
          vm.current.tronc_queteur.$saveCoins(savedSuccessfully, onSaveError);
        }
      }
    };

    function onSaveError(error)
    {
      $log.debug(error);
      vm.errorWhileSaving=true;
      vm.errorWhileSavingDetails=error;
    }
    function savedSuccessfully()
    {
      if(vm.current.adminEditMode && vm.currentUserRole >= 4)
      {
        vm.current.tronc_queteur.$saveAsAdmin(savedSuccessfullyActions, onSaveError) ;
      }
      else
      {
        savedSuccessfullyActions();
      }
    }

    function savedSuccessfullyActions()
    {
      vm.current = {};
      vm.savedSuccessfully=true;
      $timeout(function () { vm.savedSuccessfully=false; }, 10000);
    }



    vm.back=function()
    {
      vm.current = {};
    };



    //This watch change on tronc variable to update the rest of the form
    $scope.$watch('tq.current.tronc', function(newValue/*, oldValue*/)
    {
      if(newValue !== null && typeof newValue !==  "string" && typeof newValue !== "undefined")
      {
        try
        {
          $log.debug("new value for tronc");
          troncDecodedAndFoundInDB (newValue, true);
        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });


    function handleTroncQueteur(tronc_queteur)
    {
      vm.current.tronc_queteur =  tronc_queteur;

      if(angular.isUndefined(vm.current.tronc))
      {
        //if the tronc is not defined, it means that we've reached this page from the URL http://localhost:3000/#/troncs/retour/820
        //tronc is initialized when QRCode scan or autocompletion
        // (from the queteur page) to visualize the data rather than editing it.
        vm.current.tronc = tronc_queteur.tronc;
        vm.current.readOnlyView=true;
      }

      if(vm.current.tronc_queteur.depart !== null)
      {
        vm.current.tronc_queteur.departStr =  DateTimeHandlingService.handleServerDate(tronc_queteur.depart).stringVersion;
        vm.current.tronc_queteur.depart    =  DateTimeHandlingService.handleServerDate(tronc_queteur.depart).dateInLocalTimeZone;
      }

      if(vm.current.tronc_queteur.depart_theorique !== null)
      {
        vm.current.tronc_queteur.depart_theoriqueStr =  DateTimeHandlingService.handleServerDate(tronc_queteur.depart_theorique).stringVersion;
        vm.current.tronc_queteur.depart_theorique    =  DateTimeHandlingService.handleServerDate(tronc_queteur.depart_theorique).dateInLocalTimeZone;
      }



      if(vm.current.tronc_queteur.retour === null)
      {
        vm.current.dateRetourNotFilled=true;
      }
      else
      {
        vm.current.tronc_queteur.retour = DateTimeHandlingService.handleServerDate(tronc_queteur.retour).dateInLocalTimeZone;

        //if the return date is non null, then it's time to fill the number of coins
        vm.current.fillTronc=true;
      }
      $log.debug(tronc_queteur);

      //this code is supposed to scroll the page to the form to set the coins
      //but this generate a bug, the first time, it re-init the form, you have to type or scan the qrcode again
      //$location.hash('anchorForm');
      //$anchorScroll();
    }


    //function used when scanning QR Code or using autocompletion
    function troncDecodedAndFoundInDB (tronc, doNotReassingTronc)
    {
      if(vm.current.readOnlyView !== true)
      { //if true, it means we're coming from the queteur form just to view the data.
        // So we're not using the QRDecode function to get a tronc_id, and get the last tronc_queteur from it

        if( !doNotReassingTronc )
        {//vm.current.tronc is watch, in some case we must not modify it to not enter in an endless loop.
          vm.current.tronc = tronc;
        }

        vm.current.tronc.stringView = tronc.id;

        TroncQueteurResource.getTroncQueteurForTroncId({'tronc_id':tronc.id}, handleTroncQueteur);
      }
    }



    /**
     * Function used while performing a manual search for a Queteur
     * @param queryString the search string (search is performed on first_name, last_name, nivol)
     * */
    vm.searchTronc=function(queryString)
    {
      $log.info("Tronc: Manual Search for '"+queryString+"'");
      return TroncResource.query({"q":queryString}).$promise.then(function(response)
        {
          return response.map(function(tronc)
          {
            tronc.stringView = tronc.id+" - "+tronc.created;
            return tronc;
          });
        },
        function(reason)
        {
          $log.debug("error while searching for tronc with query='"+queryString+"' with reason='"+reason+"'");
        });
    };




    vm.checkInputValues=function()
    {
      var displayConfirmDialog=false;
      vm.current.confirmInputValuesMessage="";
      if(vm.current.tronc_queteur.euro5 > 20)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Billet de 5€</li>";
      }
      if(vm.current.tronc_queteur.euro10 > 10)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Billet de 10€ </li>";
      }
      if(vm.current.tronc_queteur.euro20 > 6)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Billet de 20€ </li>";
      }
      if(vm.current.tronc_queteur.euro50 > 1)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Billet de 50€ </li>";
      }
      if(vm.current.tronc_queteur.euro100 > 0)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Billet de 100€ </li>";
      }
      if(vm.current.tronc_queteur.euro200 > 0)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Billet de 200€ </li>";
      }
      if(vm.current.tronc_queteur.euro500 > 0)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Billet de 500€ </li>";
      }
       //pièces
      if(vm.current.tronc_queteur.euro2 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Pièce de 2€ </li>";
      }
      if(vm.current.tronc_queteur.euro1 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Pièce de 1€ </li>";
      }

      if(vm.current.tronc_queteur.cents50 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Pièce de 50cts </li>";
      }
      if(vm.current.tronc_queteur.cents20 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Pièce de 20cts </li>";
      }
      if(vm.current.tronc_queteur.cents10 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Pièce de 10cts </li>";
      }
      if(vm.current.tronc_queteur.cents5 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Pièce de 5cts </li>";
      }
      if(vm.current.tronc_queteur.cents2 > 120)
      {
        displayConfirmDialog = true;
        vm.current.confirmInputValuesMessage+="<li>Pièce de 2cts </li>";
      }
      if(vm.current.tronc_queteur.cent1 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Pièce de 1cent</li>";
      }

      return displayConfirmDialog;
    };


    /**
     * called when qr-scanner is able to decode something successfully from the webcam
     * What is decoded may be corrupted.
     * The function test if it's a non null string,
     * then it check the size                               TYPE-UL_ID - OBJ_ID
     *  data.length: 22 => it should then be a TRONC   :   TRONC-000002-000002656
     * if the search has already found something, stop the processing (the qr decoder keeps looking for QR Code, to avoid multiple query on the server, we stop if we have a match)
     * then it applies a RegEx and decode the UL_ID & Object Id
     * then it query the server to find the matching object (with UL_ID and Object ID as search parameters)
     * if it find something, then it plays a sound, and update the corresponding field
     * */
    vm.qrCodeScanOnSuccess = function(data)
    {
      $log.debug("Successfully decoded : '"+data+"'");

      if(data !== null  && angular.isString(data))
      {
        $log.debug("data is a String of length :" +data.length);

        var checkTroncNotAlreadyDecocededFunction = function()
        {
          var notAlreadyDecoded = (typeof vm.current.tronc === 'undefined') || typeof vm.current.tronc === 'undefined';
          if(!notAlreadyDecoded)
          {
            $log.debug("Tronc is already decoded with value '"+vm.current.tronc.id+"'")
          }
          return notAlreadyDecoded;
        };

        var troncDecodedAndNotFoundInDB=function(reason, troncId, ulId)
        {
          alert(reason +' ' + troncId+' '+ulId) ;
        };
        QRDecodeService.decodeTronc(data, checkTroncNotAlreadyDecocededFunction, troncDecodedAndFoundInDB, troncDecodedAndNotFoundInDB);

      }
      vm.decodedData = data;

    };

    /**
     * called when there's an error on qr-scanner directive
     * */
    vm.qrCodeScanOnError = function(error)
    {
      $log.debug(error);
    };
    /**
     * called when there's a video error on qr-scanner directive
     * */
    vm.qrCodeScanOnVideoError = function(error)
    {
      $log.debug(error);
    };

  }
})();

