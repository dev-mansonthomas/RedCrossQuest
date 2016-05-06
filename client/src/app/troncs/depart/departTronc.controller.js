/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('DepartTroncController', DepartTroncController);

  /** @ngInject */
  function DepartTroncController($scope, $log, QueteurResource, TroncResource) {
    var vm = this;
    vm.current = {};
    vm.current.ul_id=2;
    $scope.$watch('departTronc.current.queteur', function(newValue/*, oldValue*/)
    {
      try
      {
        $scope.departTronc.current.queteurId = newValue.id;
      }
      catch(exception)
      {
        $log.debug(exception);
      }
    });

    /***
     * Save a Départ Tronc
     * */
    vm.save = function ()
    {
      $log.debug("Saved called");
      $log.debug(vm.current);
    }

    /**
     * Function used while performing a manual search for a Queteur
     * @param queryString the search string (search is performed on first_name, last_name, nivol)
     * */
    vm.searchQueteur=function(queryString)
    {
      $log.info("Manual Search for '"+queryString+"'");
      return QueteurResource.query({"q":queryString}).$promise.then(function(response)
      {
        return response.map(function(queteur)
        {
          queteur.full_name= queteur.first_name+' '+queteur.last_name+' - '+queteur.nivol;
          return queteur;
        });
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

      if(data != null /* && data instanceof String */)
      {
        $log.debug("data is a String of length :" +data.length);
        if(data.length == 24)
        {//queteur
          $log.debug("data length is 24 => QUETEUR");

          var queteurRegEx = /^QUETEUR-([0-9]{6})-([0-9]{9})$/g
          var match = queteurRegEx.exec(data);
          if(match != null)
          {
            $log.debug("Queteur data match RegEx");
            var ulId       = parseInt(match[1]);
            var queteurId  = parseInt(match[2]);

            $log.debug("ulId:"+ulId );
            $log.debug("queteurId:"+queteurId);

            QueteurResource.get({'id':queteurId}).$promise.then(function(queteur)
              {
                $log.debug("queteurId:"+queteurId +" found in Database");
                $log.debug(queteur);


                //TODO play sound
                vm.current.queteur = queteur;
                vm.current.queteur.full_name= queteur.first_name+' '+queteur.last_name+' - '+queteur.nivol;
                $scope.departTronc.current.queteurId = queteur.id;
              },
              function(reason)
              {
                //TODO display an error to the user
                $log.error(reason);
              }
            );
          }
          else
          {
            $log.info("data do not match RegEx");

          }
        }
        else if(data.length == 22)
        {//tronc
          $log.debug("data length is 22 => TRONC");
          var troncrRegEx = /^TRONC-([0-9]{6})-([0-9]{9})$/g
          var matchTronc = troncrRegEx.exec(data);
          if(matchTronc != null)
          {
            $log.debug("TRONC data match RegEx");
            var ulIdTronc = parseInt(matchTronc[1]);
            var troncId   = parseInt(matchTronc[2]);

            $log.debug("ulId:"+ulIdTronc );
            $log.debug("troncId:"+troncId);


          }
        }
        //else: erreur de scan
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

