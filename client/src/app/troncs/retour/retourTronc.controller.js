/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('RetourTroncController', RetourTroncController);

  /** @ngInject */
  function RetourTroncController($scope, $log,
                                 TroncResource,
                                 TroncQueteurResource,
                                 QRDecodeService,
                                 moment)
  {
    var vm = this;
    vm.current = {};


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

    vm.save = function save()
    {
      $log.debug("Saved called");
      $log.debug(vm.current);
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

      if(data != null  && angular.isString(data))
      {
        $log.debug("data is a String of length :" +data.length);

        var checkTroncNotAlreadyDecocededFunction = function()
        {
          var notAlreadyDecoded = (typeof vm.current.tronc === 'undefined');
          if(!notAlreadyDecoded)
          {
            $log.debug("Tronc is already decoded with value '"+vm.current.tronc.id+"'")
          }
          return notAlreadyDecoded;
        };

        var troncDecodedAndFoundInDB = function(tronc)
        {
          vm.current.tronc = tronc;
          vm.current.tronc.stringView = tronc.id;

          TroncQueteurResource.getTroncQueteurForTroncId({'tronc_id':tronc.id},function(tronc_queteur)
          {
            vm.current.tronc_queteur =  tronc_queteur;




            if(vm.current.tronc_queteur.depart != null)
            {
              vm.current.tronc_queteur.depart =  moment( tronc_queteur.depart.date.substring(0, tronc_queteur.depart.date.length -3 ),"YYYY-MM-DD HH:mm:ss.SSS").format();
            }
            if(vm.current.tronc_queteur.retour == null)
            {
              vm.current.tronc_queteur.retour = new Date();
              vm.current.tronc_queteur.retour.setHours        (0) ;
              vm.current.tronc_queteur.retour.setMinutes      (0) ;
              vm.current.tronc_queteur.retour.setSeconds      (0) ;
              vm.current.tronc_queteur.retour.setMilliseconds (0) ;
            }




            $log.debug(tronc_queteur);
          });



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

