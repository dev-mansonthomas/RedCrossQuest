/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('PreparationTroncController', PreparationTroncController);

  /** @ngInject */
  function PreparationTroncController($scope         , $log                 ,
                                      $uibModal      , $timeout             ,
                                      QueteurResource, PointQueteResource   ,
                                      TroncResource  , TroncQueteurResource ,
                                      QRDecodeService, $localStorage        ,
                                      moment)
  {
    var vm = this;

    vm.initData = function()
    {
      vm.current = {};
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


    function savedSuccessfully()
    {
      vm.initData();

      vm.savedSuccessfully=true;

      $timeout(function () { vm.savedSuccessfully=false; }, 10000);

    }

    function onSaveError(errorMessage)
    {

      $log.error(errorMessage);
      vm.errorOnSave = errorMessage.data.exception[0].message;


      $uibModal.open({
        animation  : true,
        templateUrl: 'myModalContent.html',
        controller : 'ModalInstanceController',
        size       : 'lg',
        resolve    : {
          errorOnSave: function () {
            return vm.errorOnSave;
          },
          troncId:function()
          {
            return $scope.pt.current.tronc.id;
          },
          saveFunction : function(){
            return vm.save;
          }
        }
      });





    }
    /***
     * Save a Départ Tronc
     * */
    vm.save = function ()
    {
      $log.debug("Saved called");
      $log.debug(vm.current);

      var troncQueteur = new TroncQueteurResource();
      troncQueteur.queteur_id       = $scope.pt.current.queteur.id;
      troncQueteur.tronc_id         = $scope.pt.current.tronc.id;
      troncQueteur.point_quete_id   = $scope.pt.current.lieuDeQuete;
      troncQueteur.depart_theorique = $scope.pt.current.horaireDepartTheorique;

      troncQueteur.$save(savedSuccessfully, onSaveError);
      $log.debug("Saved completed");
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

      return vm.pointsQueteHash[vm.current.lieuDeQuete].minor_allowed === '1';
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
          alert("Vous n'êtes pas autorisé à effectuer cette action");
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
            alert("Vous n'êtes pas autorisé à effectuer cette action") ;
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
                TroncQueteurResource, errorOnSave, troncId, saveFunction)
  {
    $scope.message=errorOnSave;


    $scope.deleteNonReturnedTronc = function ()
    {
      TroncQueteurResource.deleteNonReturnedTroncQueteur({'id':troncId}, function()
      {
        saveFunction();
        $uibModalInstance.close();
      }, function(reason){
        alert(reason);
      });
    };

    $scope.cancel = function ()
    {
      $uibModalInstance.dismiss('cancel');
    };
  });



})();

