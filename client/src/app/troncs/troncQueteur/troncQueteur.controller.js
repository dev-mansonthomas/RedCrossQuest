/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('TroncQueteurController', TroncQueteurController);

  /** @ngInject */
  function TroncQueteurController($rootScope, $scope, $log, $routeParams, $timeout, $localStorage, $anchorScroll, $location,
                                  TroncResource, TroncQueteurResource, TroncQueteurHistoryResource, PointQueteResource,
                                  QRDecodeService, moment, MoneyBagResource,
                                  DateTimeHandlingService)
  {
    var vm = this;

    $rootScope.$emit('title-updated', 'Comptage d\'un Tronc');

    vm.onlyNumbers    = /^[0-9]{1,3}$/;
    vm.cbFormat       = /^[0-9]+(\.[0-9]{1,2})?$/;

    vm.coins_order    = $localStorage.guiSettings.coins_order;
    if(!vm.coins_order)
    {//coins ordered by size by default
      vm.coins_order = 1;
    }

    vm.setCoinsOrderInLocalCache=function(coins_order)
    {
      $localStorage.guiSettings.coins_order = coins_order;
    };

    vm.currentUserRole= $localStorage.currentUser.roleId;
    vm.currentUlMode  = $localStorage.currentUser.ulMode;
    vm.ulName         = $localStorage.currentUser.ulName;

    vm.first_name     = $localStorage.guiSettings.user.first_name;
    vm.last_name      = $localStorage.guiSettings.user.last_name;

    vm.use_bank_bag   = $localStorage.guiSettings.ul_settings.use_bank_bag;

    vm.currentDate    = new Date();
    vm.currentYear    = new Date().getFullYear();

    vm.confirmButtonDisabled=false;

    var tronc_queteur_id = $routeParams.id;


    vm.loadData=function(tronc_queteur_id)
    {
      vm.current = {};
      vm.current.readOnlyView  = false;
      vm.current.adminEditMode = false;

      if (angular.isDefined(tronc_queteur_id) &&  tronc_queteur_id !== 0)
      {
        $log.debug("loading data for Tronc Queteur with ID='"+tronc_queteur_id+"' ");
        TroncQueteurResource.get({id:tronc_queteur_id}).$promise.then(handleTroncQueteur).catch(function(e){
          $log.error("error searching for TroncQueteur", e);
        });
      }
    };
    vm.loadData(tronc_queteur_id);


    vm.activateAdminEditMode = function()
    {
      vm.current.adminEditMode=true;
      vm.coinsAndCBAndChequeMandatoryInAdminModeFunction();
    };

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
    }).catch(function(e){
      $log.error("error searching for PointQuete", e);
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

    vm.coinsAndCBAndChequeMandatoryInAdminModeFunction=function()
    {
      if(!vm.current || !vm.current.tronc_queteur)
      {
        return;
      }

      vm.current.coinsAndCBAndChequeMandatoryInAdminMode =
        vm.isEmpty(vm.current.tronc_queteur.euro2          )    +
        vm.isEmpty(vm.current.tronc_queteur.euro1          ) +
        vm.isEmpty(vm.current.tronc_queteur.cents50        ) +
        vm.isEmpty(vm.current.tronc_queteur.cents20        ) +
        vm.isEmpty(vm.current.tronc_queteur.cents10        ) +
        vm.isEmpty(vm.current.tronc_queteur.cents5         ) +
        vm.isEmpty(vm.current.tronc_queteur.cents2         ) +
        vm.isEmpty(vm.current.tronc_queteur.cent1          ) +
        vm.isEmpty(vm.current.tronc_queteur.don_creditcard ) +
        vm.isEmpty(vm.current.tronc_queteur.don_cheque     ) +
        vm.isEmpty(vm.current.tronc_queteur.euro5          ) +
        vm.isEmpty(vm.current.tronc_queteur.euro10         ) +
        vm.isEmpty(vm.current.tronc_queteur.euro20         ) +
        vm.isEmpty(vm.current.tronc_queteur.euro50         ) +
        vm.isEmpty(vm.current.tronc_queteur.euro100        ) +
        vm.isEmpty(vm.current.tronc_queteur.euro200        ) +
        vm.isEmpty(vm.current.tronc_queteur.euro500        ) > 0;
    }

    vm.setNonFilledCoinToZero=function()
    {
      vm.current.tronc_queteur.euro2    = vm.isEmpty(vm.current.tronc_queteur.euro2   );
      vm.current.tronc_queteur.euro1    = vm.isEmpty(vm.current.tronc_queteur.euro1   );
      vm.current.tronc_queteur.cents50  = vm.isEmpty(vm.current.tronc_queteur.cents50 );
      vm.current.tronc_queteur.cents20  = vm.isEmpty(vm.current.tronc_queteur.cents20 );
      vm.current.tronc_queteur.cents10  = vm.isEmpty(vm.current.tronc_queteur.cents10 );
      vm.current.tronc_queteur.cents5   = vm.isEmpty(vm.current.tronc_queteur.cents5  );
      vm.current.tronc_queteur.cents2   = vm.isEmpty(vm.current.tronc_queteur.cents2  );
      vm.current.tronc_queteur.cent1    = vm.isEmpty(vm.current.tronc_queteur.cent1   );

    };

    vm.setCBToZero=function()
    {
      vm.current.tronc_queteur.don_creditcard      = vm.isEmpty(vm.current.tronc_queteur.don_creditcard       );
      vm.current.tronc_queteur.don_cb_total_number = vm.isEmpty(vm.current.tronc_queteur.don_cb_total_number  );
    };

    vm.setChequeToZero=function()
    {
      vm.current.tronc_queteur.don_cheque        = vm.isEmpty(vm.current.tronc_queteur.don_cheque       );
      vm.current.tronc_queteur.don_cheque_number = vm.isEmpty(vm.current.tronc_queteur.don_cheque_number);
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


    vm.cancelDepart=function()
    {
      vm.current.confirmCancelDepart=true;
    };

    vm.closeConfirmCancelDepart=function()
    {
      vm.current.confirmCancelDepart=false;
    };


    vm.doCancelDepart=function()
    {
      vm.current.tronc_queteur.$cancelDepart(function(){vm.loadData(tronc_queteur_id);}, onSaveError);
    };
    vm.doCancelRetour=function()
    {
      vm.current.tronc_queteur.$cancelRetour(function(){vm.loadData(tronc_queteur_id);}, onSaveError);
    };

    vm.cancelRetour=function()
    {
      vm.current.confirmCancelRetour=true;
    };

    vm.closeConfirmCancelRetour=function()
    {
      vm.current.confirmCancelRetour=false;
    };



    vm.confirmSave = function ()
    {
      vm.current.overrideWarning=true;
      vm.save();
    };

    vm.save = function save()
    {

      if(vm.checkInputValues() && vm.current.overrideWarning !== true)
      {
        vm.current.confirmInputValues=true;
      }
      else
      {
        if(hasCoinsBeenModified())
        {
          vm.confirmButtonDisabled=true;
          if(vm.current.adminEditMode && vm.currentUserRole >= 4)
          {
            vm.current.tronc_queteur.$saveCoinsAsAdmin(savedSuccessfully, onSaveError);
          }
          else
          {
            vm.current.tronc_queteur.$saveCoins(savedSuccessfully, onSaveError);
          }
        }
        else
        {//no coins modified, but the rest of the form may has been modified
          savedSuccessfully();
        }
      }
    };

    function hasCoinsBeenModified()
    {
      var listOfFields=[
        'euro5',
        'euro10',
        'euro20',
        'euro50',
        'euro100',
        'euro200',
        'euro500',
        'euro2',
        'cents50',
        'euro1',
        'cents20',
        'cents10',
        'cents05',
        'cents02',
        'cents01',
        'euro2valueSorted',
        'cents50valueSorted',
        'euro1valueSorted',
        'cents20valueSorted',
        'cents10valueSorted',
        'cents05valueSorted',
        'cents02valueSorted',
        'cents01valueSorted',
        'don_creditcard',
        'don_cb_total_number',
        'don_cheque',
        'don_cheque_number',
        'coins_money_bag_id',
        'bills_money_bag_id',
        'notesRetour'
      ];
      var tqForm = $scope.troncQueteurForm;

      for (var i=0;i<listOfFields.length;i++)
      {
        if(tqForm[listOfFields[i]] && !tqForm[listOfFields[i]].$pristine)
        {
          return true;
        }
      }
      return false;
    }

    function onSaveError(error)
    {
      $log.error("error while saving",{"CurrentData":vm,"error":error});

      vm.errorWhileSaving=true;
      vm.errorWhileSavingDetails=JSON.stringify(error);
      vm.confirmButtonDisabled=false;

      $location.hash("ErrorDiv");
      $anchorScroll();

    }

    function savedSuccessfully()
    {
      if(hasTopFormBeenModified() && vm.current.adminEditMode && vm.currentUserRole >= 4)
      {
        vm.current.tronc_queteur.$saveAsAdmin(savedSuccessfullyActions, onSaveError) ;
      }
      else
      {
        savedSuccessfullyActions();
      }
    }

    function hasTopFormBeenModified()
    {
      var listOfFields=[
        'point_quete_id',
        'deleted',
        'horaireDepartTheorique',
        'horaireDepart',
        'retour',
        'notesUpdate'
      ];
      var tqForm = $scope.troncQueteurForm;

      for (var i=0;i<listOfFields.length;i++)
      {
        if(tqForm[listOfFields[i]] && !tqForm[listOfFields[i]].$pristine)
        {
          return true;
        }
      }
      return false;
    }

    function savedSuccessfullyActions()
    {
      if(vm.current.adminEditMode === true)
      {
        vm.loadData();
      }
      else
      {
        vm.current = {};
        vm.savedSuccessfully=true;
        vm.errorWhileSaving = false;
        vm.errorWhileSavingDetails=null;
        vm.confirmButtonDisabled=false;

        $timeout(function () { vm.savedSuccessfully=false; }, 10000);
      }
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
          $log.debug("new value for tronc "+newValue);
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
      vm.current.tronc_queteur        = tronc_queteur;
      vm.current.dateRetourNotFilled  = false;
      vm.current.fillTronc            = false;

      vm.current.coinsMoneyBagDetails = null;
      vm.current.billsMoneyBagDetails = null;


      if(vm.current.tronc_queteur == null )
      {
        vm.current.troncQueteurNotFound = true;
        return;
      }
      else
      {
        vm.current.troncQueteurNotFound = false;
      }

      if(angular.isUndefined(vm.current.tronc))
      {
        //if the tronc is not defined, it means that we've reached this page from the URL http://localhost:3000/#/troncs_queteur/id
        //tronc is initialized when QRCode scan or autocompletion
        // (from the queteur page) to visualize the data rather than editing it.
        vm.current.tronc        = tronc_queteur.tronc;
        vm.current.readOnlyView = true;
      }

      if(vm.current.tronc_queteur.depart !== null)
      {
        var depart = DateTimeHandlingService.handleServerDate(tronc_queteur.depart);
        vm.current.tronc_queteur.departStr =  depart.stringVersion;
        vm.current.tronc_queteur.depart    =  depart.dateInLocalTimeZone;
      }

      if(vm.current.tronc_queteur.depart_theorique !== null)
      {
        var dateDepartTheorique =  DateTimeHandlingService.handleServerDate(tronc_queteur.depart_theorique);
        vm.current.tronc_queteur.depart_theoriqueStr =  dateDepartTheorique.stringVersion;
        vm.current.tronc_queteur.depart_theorique    =  dateDepartTheorique.dateInLocalTimeZone;


        var currentYear      = moment().format('YYYY');
        var troncQueteurYear = dateDepartTheorique.dateInLocalTimeZoneMoment.format('YYYY');

        if(currentYear !== troncQueteurYear)
        {
          vm.current.not_same_year = true;
          vm.current.year_tronc_queteur=troncQueteurYear;
        }
      }

      if(vm.current.tronc_queteur.retour === null)
      {
        vm.current.dateRetourNotFilled=true;
      }
      else
      {
        var retour = DateTimeHandlingService.handleServerDate(tronc_queteur.retour);
        vm.current.tronc_queteur.retour    = retour.dateInLocalTimeZone;
        vm.current.tronc_queteur.retourStr = retour.stringVersion;

        //if the return date is non null, then it's time to fill the number of coins
        vm.current.fillTronc=true;
      }

      //this code is supposed to scroll the page to the form to set the coins
      //but this generate a bug, the first time, it re-init the form, you have to type or scan the qrcode again

      window.setTimeout(function(){
        $location.hash("SaveResult");
        $anchorScroll();
      }, 300);

      $rootScope.$emit('title-updated', moment().format('YYYY-MM-DD') + ' - Tronc '+vm.current.tronc_queteur.id+' - ' +
                                                 vm.current.tronc_queteur.point_quete.name + ' - '+
                                                 vm.current.tronc_queteur.queteur.first_name +' '+vm.current.tronc_queteur.queteur.last_name);



      TroncQueteurHistoryResource.getTroncQueteurHistoryForTroncQueteurId({tronc_queteur_id:tronc_queteur.id}).$promise.then(handleTroncQueteurHistory).catch(function(e){
        $log.error("error searching for TroncQueteurHistoryForTroncQueteurId", e);
      });
    }



    function handleTroncQueteurHistory(tronc_queteur_array)
    {
      for(var i=0, counti = tronc_queteur_array.length;i<counti;i++)
      {
        tronc_queteur_array[i].insert_date      = DateTimeHandlingService.handleServerDate(tronc_queteur_array[i].insert_date     ).stringVersion;
        tronc_queteur_array[i].depart_theorique = DateTimeHandlingService.handleServerDate(tronc_queteur_array[i].depart_theorique).stringVersion;
        tronc_queteur_array[i].depart           = DateTimeHandlingService.handleServerDate(tronc_queteur_array[i].depart          ).stringVersion;
        tronc_queteur_array[i].retour           = DateTimeHandlingService.handleServerDate(tronc_queteur_array[i].retour          ).stringVersion;
        tronc_queteur_array[i].comptage         = DateTimeHandlingService.handleServerDate(tronc_queteur_array[i].comptage        ).stringVersion;
        tronc_queteur_array[i].last_update      = DateTimeHandlingService.handleServerDate(tronc_queteur_array[i].last_update     ).stringVersion;
      }

      vm.current.tronc_queteur_history = tronc_queteur_array;
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
        if(tronc.id)
        {
          vm.current.tronc.stringView = tronc.id;
          TroncQueteurResource.getLastTroncQueteurFromTroncId({'tronc_id':tronc.id}, handleTroncQueteur);
        }
      }
    }



    /**
     * Function used while performing a manual search for a Queteur
     * @param queryString the search string (search is performed on first_name, last_name, nivol)
     * */
    vm.searchTronc=function(queryString)
    {
      $log.info("Tronc: Manual Search for '"+queryString+"'");
      if(parseInt(queryString)<=0)
        return;

      return TroncResource.query({"q":queryString}).$promise.then(function(response)
        {
          return response.rows.map(function(tronc)
          {
            tronc.stringView = tronc.id;
            return tronc;
          });
        },
        function(reason)
        {
          $log.debug("error while searching for tronc with query='"+queryString+"' with reason='"+reason+"'");
        }).catch(function(e){
        $log.error("error searching for Tronc", e);
      });
    };




    vm.checkInputValues=function()
    {
      var displayConfirmDialog=false;
      vm.current.confirmInputValuesMessage="";

      if(vm.current.tronc_queteur.don_cheque>0)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>Un chèque a été saisie</li>";
      }

      if(vm.current.tronc_queteur.euro5 > 20)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 20 billets de 5€</li>";
      }
      if(vm.current.tronc_queteur.euro10 > 10)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 10 billets de 10€ </li>";
      }
      if(vm.current.tronc_queteur.euro20 > 6)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 6 billets de 20€ </li>";
      }
      if(vm.current.tronc_queteur.euro50 > 1)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 1 billet de 50€ </li>";
      }
      if(vm.current.tronc_queteur.euro100 > 0)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 1 billet de 100€ </li>";
      }
      if(vm.current.tronc_queteur.euro200 > 0)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 1 billet de 200€ </li>";
      }
      if(vm.current.tronc_queteur.euro500 > 0)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 1 billet de 500€ </li>";
      }
       //pièces
      if(vm.current.tronc_queteur.euro2 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 2€ </li>";
      }
      if(vm.current.tronc_queteur.euro1 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 1€ </li>";
      }

      if(vm.current.tronc_queteur.cents50 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 50cts </li>";
      }
      if(vm.current.tronc_queteur.cents20 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 20cts </li>";
      }
      if(vm.current.tronc_queteur.cents10 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 10cts </li>";
      }
      if(vm.current.tronc_queteur.cents5 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 5cts </li>";
      }
      if(vm.current.tronc_queteur.cents2 > 120)
      {
        displayConfirmDialog = true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 2cts </li>";
      }
      if(vm.current.tronc_queteur.cent1 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 1cent</li>";
      }


      if(vm.current.tronc_queteur.don_creditcard > 0)
      {
        if(vm.current.tronc_queteur.don_cb_total_number === 0)
        {
          vm.current.tronc_queteur.don_cb_total_number = 1;
          displayConfirmDialog=true;
          vm.current.confirmInputValuesMessage+="<li>Le total des paiements CB est supérieur à 0, mais le nombre total de paiement est égale à 0. " +
            "Le nombre de paiement a été initialisé à 1 </li>";
        }
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

    vm.print=function()
    {
      window.print();
    };


    vm.searchCoinMoneyBagId=function(searchedString)
    {
      var searchResults = vm.searchMoneyBagId(searchedString, 'coin');

      searchResults.then(function(data){
          if(data === null || data.length === 0)
          {
            vm.current.coinsMoneyBagDetails = null;
          }
        }
      );
      //vm.current.coinsMoneyBagDetails
      return  searchResults;
    };

    vm.searchBillMoneyBagId=function(searchedString)
    {
      var searchResults = vm.searchMoneyBagId(searchedString, 'bill');
      searchResults.then(function(data)
        {
          if(data === null || data.length === 0)
          {
            vm.current.billsMoneyBagDetails = null;
          }
        }
      );
      return searchResults;
    };

    vm.searchMoneyBagId=function(searchedString, type)
    {
      return MoneyBagResource.searchMoneyBagId({'q':searchedString, 'type':type}).$promise.then(function success(response)
      {
        return response.map(function(oneResponse)
          {
            return oneResponse;
          },
          function error(reason)
          {
            $log.debug("error while searching for moneybagId query='"+searchedString+"' with reason='"+reason+"'");
          });
      }).catch(function(e){
        $log.error("error searching for MoneyBag", e);
      });
    };

    vm.getBagDetails = function($item, $model, $label, $event, coins)
    {
      //$log.error(JSON.stringify([$item, $model, $label, $event, coins]));
      if(coins)
      {
        vm.current.coinsMoneyBagDetails = MoneyBagResource.coinsMoneyBagDetails({'id':$item});
      }
      else
      {
        vm.current.billsMoneyBagDetails = MoneyBagResource.billsMoneyBagDetails({'id':$item});
      }
    };
  }
})();

