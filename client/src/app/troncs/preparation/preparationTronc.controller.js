/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('PreparationTroncController', PreparationTroncController);

  /** @ngInject */
  function PreparationTroncController($rootScope, $scope, $log,$uibModal, $timeout,
                                      QueteurResource, PointQueteResource   ,
                                      TroncResource  , TroncQueteurResource ,
                                      QRDecodeService, $localStorage        ,
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


    vm.initData = function()
    {
      vm.current = {};
      vm.current.saveInProgress=false;
      vm.current.ul_id=$localStorage.currentUser.ulId;

      vm.current.horaireDepartTheorique = new Date();

      //on laisse l'heure courante.
      vm.current.horaireDepartTheorique.setMinutes(0) ;
      vm.current.horaireDepartTheorique.setSeconds(0) ;
      vm.current.horaireDepartTheorique.setMilliseconds(0) ;
      vm.current.horaireDepartTheoriqueNotBefore = vm.current.horaireDepartTheorique;
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

    //pointQuete list
    vm.pointsQuete = PointQueteResource.query();
    vm.pointsQuete.$promise.then(function success(pointQueteList)
    {
      vm.pointsQueteHash = [];
      pointQueteList.forEach(function(onePointQuete){
        vm.pointsQueteHash[onePointQuete.id]=onePointQuete;
      });
    });

    //This watch change on queteur variable to update the queteurId field
    $scope.$watch('pt.current.queteur', function(newValue/*, oldValue*/)
    {
      if(newValue !== null && typeof newValue !==  "string" && typeof newValue !== "undefined")
      {
        try
        {
          $scope.pt.current.queteurId = newValue.id;
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
      else
      {
        vm.initData();
        vm.savedSuccessfully=true;
        $timeout(function () { vm.savedSuccessfully=false; }, 10000);
      }
    }

    function onSaveError(errorMessage)
    {
      vm.current.saveInProgress=false;
      $log.error(errorMessage);
      vm.errorWhileSaving = true;

      errorMessage.data.exception.xdebug_message=null;

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
      troncQueteur.point_quete_id         = vm.current.lieuDeQuete;
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
      return QueteurResource.query({"q":queryString}).$promise.then(function success(response)
      {
        return response.map(function(queteur)
        {
          queteur.full_name= queteur.first_name+' '+queteur.last_name+' - '+queteur.nivol;
          return queteur;
        },
        function error(reason)
        {
          $log.debug("error while searching for queteur with query='"+queryString+"' with reason='"+reason+"'");
        });
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
        return response.map(function(tronc)
        {
          tronc.stringView = tronc.id+" - "+tronc.created;
          return tronc;
        });
      },
      function error(reason)
      {
        $log.debug("error while searching for tronc with query='"+queryString+"' with reason='"+reason+"'");
      });
    };

    vm.isQueteurAllowed=function()
    {
      if(moment().diff(vm.current.queteur.birthdate.date, 'years')>=18)
        return true;

      return vm.pointsQueteHash[vm.current.lieuDeQuete].minor_allowed === true;
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
        };
        var queteurDecodedAndNotFoundInDB=function (reason, queteurId, ulId)
        {
          //TODO display a message to the user
          alert("Quêteur Non trouvé ! Attention un QRCode imprimé depuis la plateforme de TEST ne fonctionnera pas sur la PROD !") ;
          $log.debug(JSON.stringify(reason) +' ' + queteurId+' '+ulId);
          //JSON.stringify(reason) +' ' + queteurId+' '+ulId
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
            vm.current.tronc.stringView = tronc.id+" - "+tronc.created;
            $scope.pt.current.troncId = tronc.id;
          };

          var troncDecodedAndNotFoundInDB=function(reason, troncId, ulId)
          {
            alert("Tronc Non trouvé ! Attention un QRCode imprimé depuis la plateforme de TEST ne fonctionnera pas sur la PROD !") ;
            $log.debug(JSON.stringify(reason) +' ' + troncId+' '+ulId);
            //reason +' ' + troncId+' '+ulId
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

    for(var i=0, counti=errorOnSave.length;i<counti;i++)
    {
      errorOnSave[i].depart           = DateTimeHandlingService.handleServerDate(errorOnSave[i].depart          ).stringVersion;
      errorOnSave[i].depart_theorique = DateTimeHandlingService.handleServerDate(errorOnSave[i].depart_theorique).stringVersion;
    }

    $scope.troncInfos=errorOnSave;


    $scope.deleteNonReturnedTronc = function ()
    {
      TroncQueteurResource.deleteNonReturnedTroncQueteur({'id':troncId}, function()
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

