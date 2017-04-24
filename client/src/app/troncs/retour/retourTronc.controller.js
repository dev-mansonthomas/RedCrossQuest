/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('RetourTroncController', RetourTroncController);

  /** @ngInject */
  function RetourTroncController($scope, $log, $location, $routeParams,
                                 TroncResource, TroncQueteurResource,
                                 QRDecodeService,
                                 moment)
  {
    var vm = this;
    vm.current = {};

    var tronc_queteur_id = $routeParams.id;

    if (angular.isDefined(tronc_queteur_id))
    {
      $log.debug("loading data for Tronc Queteur with ID='"+tronc_queteur_id+"' ");
      TroncQueteurResource.get({id:tronc_queteur_id}).$promise.then(handleTroncQueteur);
    }

    vm.onlyNumbers = /^\d+$/;

    function savedSuccessfully()
    {
      vm.current = {};
    }

    vm.back=function()
    {
      window.history.back();
    }

    function onSaveError(error)
    {
      $log.error(error)
    }

    //This watch change on tronc variable to update the rest of the form
    $scope.$watch('rt.current.tronc', function(newValue/*, oldValue*/)
    {
      if(newValue != null && !angular.isString(newValue))
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
        // (from the queteur page) to visualize the data rather than editing it.
        vm.current.tronc = tronc_queteur.tronc;
        vm.current.readOnlyView=true;
      }

      if(vm.current.tronc_queteur.depart !== null)
      {
        vm.current.tronc_queteur.depart =  moment( tronc_queteur.depart.date.substring(0, tronc_queteur.depart.date.length -3 ),"YYYY-MM-DD HH:mm:ss.SSS").toDate();
      }

      if(vm.current.tronc_queteur.retour === null)
      {
        vm.current.tronc_queteur.retour = new Date();
      }
      else
      {
        //date store in UTC + Timezone offset with Carbon on php side.
        //this parse the Carbon time without '000' ending in the UTC timezone, and then convert it to Europe/Paris (the value of the tronc_queteur.retour.timezone)
        var tempRetour = moment.tz( tronc_queteur.retour.date.substring(0, tronc_queteur.retour.date.length -3 ),"YYYY-MM-DD HH:mm:ss.SSS", 'UTC');
        vm.current.tronc_queteur.retour = tempRetour.clone().tz(tronc_queteur.retour.timezone)  .toDate();
        //if the return date is non null, then it's time to fill the number of coins
        vm.current.fillTronc=true;
      }
      $log.debug(tronc_queteur);
    }


    //function used when scanning QR Code or using autocompletion
    function troncDecodedAndFoundInDB (tronc, doNotReassingTronc)
    {
      if(vm.current.readOnlyView!=true)
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

    vm.save = function save()
    {
      $log.debug("Saved called");
      $log.debug(vm.current);

      if(vm.current.fillTronc == true)
      {
        $log.debug("Saving the number of coins");
        $log.debug(vm.current.tronc_queteur);
        vm.current.tronc_queteur.$saveCoins(savedSuccessfully, onSaveError);
      }
      else
      {
        $log.debug("Saving the return date");
        vm.current.tronc_queteur.$saveReturnDate(savedSuccessfully, onSaveError);
      }
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
          var notAlreadyDecoded = (typeof vm.current.tronc === 'undefined') || vm.current.tronc == 'undefined';
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

