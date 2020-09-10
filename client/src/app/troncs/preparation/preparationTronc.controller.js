/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('PreparationTroncController', PreparationTroncController);

  /** @ngInject */
  function PreparationTroncController($rootScope, $scope, $log,$uibModal, $timeout, $location,  $localStorage,
                                      QueteurResource, PointQueteResource   ,
                                      TroncResource  , TroncQueteurResource ,
                                      QRDecodeService,
                                      moment)
  {
    var vm = this;

    $rootScope.$emit('title-updated', 'Préparation d\'un Tronc');

    vm.typePointQueteList=[
      {id:1,label:'Voie Publique'},
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

    vm.deploymentType = $localStorage.currentUser.d;

    if($localStorage.guiSettings == null)
    {//clicked too fast and guiSettings are not ready
      $location.path('/').replace();
      return;
    }

    vm.firstDay       = moment($localStorage.guiSettings.FirstDay);
    vm.firstDayStr    = vm.firstDay.format("DD/MM/YYYY HH:mm:ss");

    //return true if the current time is before the first day of quete and the current deployement is production
    vm.isCurrentTimeBefore1stDayOfQuete=function()
    {
      return vm.deploymentType === 'P' && vm.firstDay.diff(moment(), 'secondes')>=0;
    };

    vm.initData = function(lastInsertedId)
    {
      if(vm.current)
      {
        vm.previous     = vm.current;
        vm.previous.id  = lastInsertedId;
      }

      vm.current                = {};
      vm.current.saveInProgress = false;
      vm.current.ul_id          = $localStorage.currentUser.ulId;

      vm.current.horaireDepartTheorique           = new Date();
      vm.current.horaireDepartTheoriqueNotBefore  = vm.current.horaireDepartTheorique  ;
      if(vm.isCurrentTimeBefore1stDayOfQuete())
      {//Production only : tronc can't be prepared or depart before the 1st day of Quete
        vm.current.horaireDepartTheoriqueNotBefore  = vm.firstDay.toDate();
      }

      //on laisse l'heure courante.
      vm.current.horaireDepartTheorique.setMinutes(0) ;
      vm.current.horaireDepartTheorique.setSeconds(0) ;
      vm.current.horaireDepartTheorique.setMilliseconds(0) ;

    };

    vm.initData();


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

    //pointQuete list is normally loaded in localStorage in main.controller.js
    vm.pointsQuete    = $localStorage.pointQuete;
    vm.pointsQueteHash= $localStorage.pointsQueteHash;

    //This watch change on queteur variable to update the queteurId field
    $scope.$watch('pt.current.queteur', function(newValue/*, oldValue*/)
    {
      if(newValue !== null && typeof newValue !==  "string" && typeof newValue !== "undefined")
      {
        try
        {
          $scope.pt.current.queteurId = newValue.id;
          vm.preparationChecks();
        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });

    function savedSuccessfully(returnData)
    {
      vm.current.saveInProgress=false;
      if(returnData.troncInUse === true)
      {//Tronc is in use
        vm.current.troncAlreadyInUse=true;
        vm.current.troncAlreadyInUseInfo=returnData.troncInUseInfo;

        $uibModal.open({
          animation  : true,
          templateUrl: 'myModalContent.html',
          controller : 'ModalInstanceController',
          windowClass: 'troncInUse-modal-dialog',
          resolve    : {
            errorOnSave: function ()
            {
              return vm.current.troncAlreadyInUseInfo;
            },
            troncId:function()
            {
              return $scope.pt.current.tronc.id;
            },
            saveFunction : function()
            {
              return vm.save;
            }
          }
        });
      }
      else if(returnData.queteHasNotStartedYet === true)
      {
        vm.current.queteHasNotStartedYet=true;
      }
      else
      {
        if(vm.current.lieuDeQuete)
        { //Lorsque tronc et queteur sont rempli, un check est fait est cette méthode est appelée (la meme que pour le save)
          //Si le check est successful, pas de tronc en cours d'utilisation, on arrive ici, mais il ne faut pas réinitialise le formulaire
          //car la sauvegarde n'est pas encore faite (heure et point de quete pas encore choisi)
          vm.initData(returnData.lastInsertId);
          vm.savedSuccessfully=true;
          $timeout(function () { vm.savedSuccessfully=false; }, 10000);
        }
      }
    }

    function onSaveError(errorMessage)
    {
      vm.current.saveInProgress=false;
      $log.error(errorMessage);
      vm.errorWhileSaving = true;

      try
      {
        errorMessage.data.exception.xdebug_message=null;
      }
      catch(ex)
      {//do nothing
      }


      vm.errorWhileSavingDetails = JSON.stringify(errorMessage.data.exception);

    }
    /***
     * Save a Départ Tronc
     * if preparationAndDepart is set to true, then it saves the preparation and immediately save the Depart
     * */
    vm.save = function ()
    {
      vm.current.saveInProgres=true;
      var troncQueteur = new TroncQueteurResource();
      troncQueteur.queteur_id             = vm.current.queteur.id;
      troncQueteur.tronc_id               = vm.current.tronc.id;
      troncQueteur.point_quete_id         = vm.current.lieuDeQuete.id;
      troncQueteur.depart_theorique       = vm.current.horaireDepartTheorique;
      troncQueteur.notes_depart_theorique = vm.current.notes_depart_theorique;

      troncQueteur.preparationAndDepart   = vm.current.preparationAndDepart ;

      troncQueteur.$save(savedSuccessfully, onSaveError);
    };

    /**
     * Function used while performing a manual search for a Queteur
     * @param queryString the search string (search is performed on first_name, last_name, nivol)
     * */
    vm.searchQueteur=function(queryString)
    {
      $log.info("Queteur : Manual Search for '"+queryString+"'");
      return QueteurResource.query({"q":queryString, "searchType":3}).$promise.then(function success(response)
      {
        return response.rows.map(function(queteur)
        {
          queteur.full_name= queteur.first_name+' '+queteur.last_name+' - '+queteur.nivol;
          return queteur;
        },
        function error(reason)
        {
          $log.debug("error while searching for queteur with query='"+queryString+"' with reason='"+reason+"'");
        });
      }).catch(function(e){
        $log.error("error searching for Queteur", e);
      });
    };

    /**
     * Function used while performing a manual search for a Queteur
     * @param queryString the search string (search is performed on first_name, last_name, nivol)
     * */
    vm.searchTronc=function(queryString)
    {
      $log.info("Tronc: Manual Search for '"+queryString+"'");
      return TroncResource.query({"q":queryString}).$promise.then(function success(response)
      {
        return response.rows.map(function(tronc)
        {
          tronc.stringView = tronc.id;
          return tronc;
        });
      },
      function error(reason)
      {
        $log.debug("error while searching for tronc with query='"+queryString+"' with reason='"+reason+"'");
      }).catch(function(e){
        $log.error("error searching for Tronc", e);
      });
    };

    vm.isQueteurAllowed=function()
    {
      if(!vm.current.queteur)
        return true;

      if(!vm.current.queteur.birthdate)
        return true;

      if(moment().diff(vm.current.queteur.birthdate, 'years')>=18)
        return true;

      if(vm.current.lieuDeQuete == null || typeof vm.current.lieuDeQuete != 'object')
        return true;

      return vm.pointsQueteHash[vm.current.lieuDeQuete.id].minor_allowed === true;
    };

    /**
     * called when qr-scanner is able to decode something successfully from the webcam
     * What is decoded may be corrupted.
     * The function test if it's a non null string,
     * then it check the size                               TYPE-UL_ID - OBJ_ID
     *  data.length: 24 => it should then be a QUETEUR : QUETEUR-000002-000002500
     *  data.length: 22 => it should then be a TRONC   :   TRONC-000002-000002656
     * if the search has already found something, stop the processing (the qr decoder keeps looking for QR Code, to avoid multiple query on the server, we stop if we have a match)
     * then it applies a RegEx and decode the UL_ID & Object Id
     * then it query the server to find the matching object (with UL_ID and Object ID as search parameters)
     * if it find something, then it plays a sound, and update the corresponding field
     * */
    vm.qrCodeScanOnSuccess = function(data)
    {
      $log.debug("Successfully decoded : '"+data+"'");

      if(data !== null  && angular.isString(data) )
      {
        $log.debug("data is a String of length :" +data.length);

        var checkQueteurNotAlreadyDecocededFunction = function()
        {
          var notAlreadyDecoded = (typeof $scope.pt.current.queteurId === 'undefined');
          if(!notAlreadyDecoded)
          {
              $log.debug("Queteur is already decoded with value '"+$scope.pt.current.queteurId+"'")
          }
          return notAlreadyDecoded;
        };
        var queteurDecodedAndFoundInDB = function(queteur)
        {
          vm.current.queteur = queteur;
          vm.current.queteur.full_name= queteur.first_name+' '+queteur.last_name+' - '+queteur.nivol;
          $scope.pt.current.queteurId = queteur.id;
          vm.preparationChecks();
        };
        var queteurDecodedAndNotFoundInDB=function (reason, queteurId, ulId)
        {

          if(reason === 'ANONYMISED')
          {
            vm.current.QRCodeScanError="Le quêteur dont vous avez scanné le QRCode a été anonymisé et ne peut être utilisé pour quêter";
          }
          else if(reason === 'INACTIVE')
          {
            vm.current.QRCodeScanError="Le quêteur dont vous avez scanné le QRCode est inactif ! Seuls les queteurs actifs peuvent quêter";
          }
          else
          {
            vm.current.QRCodeScanError="Le quêteur n'a pas été trouvé ! <small>Attention un QRCode imprimé depuis la plateforme de <b>TEST</b> ne fonctionnera pas sur la <b>PROD</b></small> !";
          }
          $log.debug("QueteurID="+queteurId+" ulId="+ulId, reason);
        };

        var foundSomething = QRDecodeService.decodeQueteur(data, checkQueteurNotAlreadyDecocededFunction, queteurDecodedAndFoundInDB, queteurDecodedAndNotFoundInDB);

        if(!foundSomething)
        {//if data is not 24 long, try to decode tronc (22)
          var checkTroncNotAlreadyDecocededFunction = function()
          {
            var notAlreadyDecoded = (typeof $scope.pt.current.troncId === 'undefined');

            if(!notAlreadyDecoded)
            {
              $log.debug("Tronc is already decoded with value '"+$scope.pt.current.troncId+"'")
            }
            return notAlreadyDecoded;
          };

          var troncDecodedAndFoundInDB = function(tronc)
          {
            vm.current.tronc = tronc;
            vm.current.tronc.stringView = tronc.id;
            $scope.pt.current.troncId = tronc.id;
            vm.preparationChecks();
          };

          var troncDecodedAndNotFoundInDB=function(reason, troncId, ulId)
          {
            if(reason === 'INACTIVE')
            {
              vm.current.QRCodeScanError="Le tronc dont vous avez scanné le QRCode est inactif ! Seuls les troncs actifs peuvent être utilisés";
            }
            else
            {
              vm.current.QRCodeScanError="Le tronc n'a pas été trouvé !<small>Attention un QRCode imprimé depuis la plateforme de <b>TEST</b> ne fonctionnera pas sur la <b>PROD</b></small> !";
            }
            $log.debug( troncId+' '+ulId,reason);
          };
          QRDecodeService.decodeTronc(data, checkTroncNotAlreadyDecocededFunction, troncDecodedAndFoundInDB, troncDecodedAndNotFoundInDB);

        }
        else
        {
          $log.debug("unrecognized QRCode :'"+data+"'");
        }
      }
      vm.decodedData = data;

    };
    //when tronc & queteur is filled, check if one of them is not already in use
    vm.preparationChecks=function()
    {
      if(vm.current.queteurId >0  && typeof(vm.current.tronc) ==='object' && vm.current.tronc.id > 0)
      {
        TroncQueteurResource.preparationChecks({'tronc_id':vm.current.tronc.id,'queteur_id':vm.current.queteurId}
        ).$promise.then(savedSuccessfully, onSaveError);
      }
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


  angular
    .module('redCrossQuestClient')
    .controller('ModalInstanceController',
      function ($scope, $uibModalInstance, $log,
                TroncQueteurResource, errorOnSave, troncId, saveFunction, DateTimeHandlingService)
  {

    //this can be triggered because the current tronc is already set to someone else
    //or the queteur already has a tronc set. ==>  queteurHasAnotherTronc is here to check that and set the list of tronc_id in an array
    // it's likely that there's only one, so we delete the first of the array
    var queteurHasAnotherTronc=false;
    var otherTroncs=[];
    var previousTQID=null;
    var twoIdenticalRows=false;

    for(var i=0, counti=errorOnSave.length;i<counti;i++)
    {
      errorOnSave[i].depart           = DateTimeHandlingService.handleServerDate(errorOnSave[i].depart          ).stringVersion;
      errorOnSave[i].depart_theorique = DateTimeHandlingService.handleServerDate(errorOnSave[i].depart_theorique).stringVersion;
      if(errorOnSave[i].tronc_id !== troncId)
      {
        queteurHasAnotherTronc=true;
        otherTroncs[otherTroncs.length]= errorOnSave[i].tronc_id;
      }
      if(errorOnSave[i].id === previousTQID)
      {
        twoIdenticalRows=true;
      }
      previousTQID = errorOnSave[i].id;
    }

    $scope.troncInfos=errorOnSave;
    $scope.twoIdenticalRows = twoIdenticalRows;
    $scope.label= {
      'TRONC_IN_USE':'Ce tronc est affecté a un autre quêteur',
      'QUETEUR_ALREADY_HAS_A_TRONC':'Ce quêteur à déjà un tronc'
    };



    $scope.deleteNonReturnedTronc = function ()
    {
      //queteurHasAnotherTronc :  This queteur has been affected another tronc (A) that the one being scanned (B). => mark the tronc_queteur with tronc (A) one as deleted
      //otherwise : mark the tronc_queteur with the scanned tronc as deleted (so other people had a tronc_queteur prepared this tronc, and they are marked as deleted)
      // Mark as deleted occurs only on troncqueteur that have retour or depart set to null.
      TroncQueteurResource.deleteNonReturnedTroncQueteur({'subId':queteurHasAnotherTronc?otherTroncs[0]:troncId}, function()
      {
        saveFunction();
        $uibModalInstance.close();
      },
        function(reason)
      {
        alert(reason);
      });
    };

    $scope.cancel = function ()
    {
      $uibModalInstance.dismiss('cancel');
    };
  });

})();

