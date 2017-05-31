/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('DepartTroncController', DepartTroncController);

  /** @ngInject */
  function DepartTroncController($scope, $log, $location, $uibModal, $timeout,
                                 PointQueteResource  ,
                                 TroncResource  , TroncQueteurResource,
                                 QRDecodeService, DateTimeHandlingService)
  {
    var vm = this;

    vm.current = {};
    vm.current.ul_id=2;
    vm.current.horaireDepartTheorique = new Date();

    vm.current.horaireDepartTheorique.setHours(9) ;
    vm.current.horaireDepartTheorique.setMinutes(0) ;
    vm.current.horaireDepartTheorique.setSeconds(0) ;
    vm.current.horaireDepartTheorique.setMilliseconds(0) ;
    //pointQuete list
    vm.current.pointsQuete = PointQueteResource.query();


    var troncDecodedAndFoundInDB = function(tronc)
    {

      vm.current.tronc = tronc;
      vm.current.tronc.stringView = tronc.id+" - "+tronc.created;
      $scope.departTronc.current.troncId = tronc.id;


      TroncQueteurResource.getTroncQueteurForTroncIdAndSetDepart({'tronc_id':tronc.id},
        function(tronc_queteur)
      {
        $log.debug("Tronc Queteur returned");
        $log.debug(tronc_queteur);

        vm.current.tronc_queteur =  tronc_queteur;

        if(tronc_queteur.depart !== null)
        {
          vm.current.tronc_queteur.depart           =  DateTimeHandlingService.handleServerDate(tronc_queteur.depart).dateInLocalTimeZone;
        }

        if(tronc_queteur.depart_theorique !== null)
        {
          vm.current.tronc_queteur.depart_theorique = DateTimeHandlingService.handleServerDate(tronc_queteur.depart_theorique).dateInLocalTimeZone;
        }

        $log.debug(tronc_queteur);
        $log.debug("deleting troncId to allow a new scan directly");
        delete $scope.departTronc.current.troncId;

        vm.savedSuccessfully=true;
        $timeout(function () { vm.savedSuccessfully=false; vm.current={}; }, 20000);

      });
    };



    //This watch change on queteur variable to update the queteurId field
    $scope.$watch('dt.current.tronc', function(newValue/*, oldValue*/)
    {
      if(newValue !== null)
      {
        try
        {
          $scope.departTronc.current.troncId = newValue.id;
          troncDecodedAndFoundInDB(newValue);
        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });


    function redirectToSlash()
    {
      $location.path('/');
    }

    function onSaveError(errorMessage)
    {

      $log.error(errorMessage);
      vm.errorOnSave = errorMessage.data.exception[0].message;


      $uibModal.open({
        animation: true,
        templateUrl: 'myModalContent.html',
        controller: 'ModalInstanceController',
        size: 'lg',
        resolve: {
          errorOnSave: function () {
            return vm.errorOnSave;
          },
          troncId:function()
          {
            return $scope.departTronc.current.tronc.id;
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
      troncQueteur.queteur_id       = $scope.departTronc.current.queteurId;
      troncQueteur.tronc_id         = $scope.departTronc.current.troncId;
      troncQueteur.point_quete_id   = $scope.departTronc.current.lieuDeQuete;
      troncQueteur.depart_theorique = $scope.departTronc.current.horaireDepartTheorique;

      troncQueteur.$save(redirectToSlash, onSaveError);
      $log.debug("Saved completed");
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



        var checkTroncNotAlreadyDecocededFunction = function()
        {
          var notAlreadyDecoded = (typeof $scope.departTronc.current.troncId === 'undefined');

          if(!notAlreadyDecoded)
          {
            $log.debug("Tronc is already decoded with value '"+$scope.departTronc.current.troncId+"'")
          }
          return notAlreadyDecoded;
        };



        var troncDecodedAndNotFoundInDB=function(reason, troncId, ulId)
        {
          alert("Vous n'êtes pas autorisé à effectuer cette action") ;
          $log.debug(JSON.stringify(reason) +' ' + troncId+' '+ulId);

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

