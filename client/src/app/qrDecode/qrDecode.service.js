/**
 * Created by tmanson on 03/05/2016.
 */

angular
  .module('redCrossQuestClient')
  .factory('QRDecodeService', function ($resource, $log, QueteurResource, TroncResource, ngAudio) {

  var instance = {};

  instance.sound = ngAudio.load("assets/sounds/bip_scanner.mp3");
  instance.queteurQRCodeLength = 24;
  instance.troncQRCodeLength   = 22;


  /**
   * Look for queteurData in the decoded data produced by qr-scanner library
   *
   * @param data: the data decoded by the qr-scanner library (it may be corrupted)
   * @param checkQueteurNotAlreadyDecocededFunction : function that must return true if the queteur is already decoded.
   *                  (if you leave the QRCode a few second in front of the camera, it will decode several time the QRCode)
   * @param queteurDecodedAndFoundInDB : function called when the QRCode is a queteur and it's found in DB (it takes a queteur in parameter)
   * @param queteurDecodedAndNotFoundInDB : function called when the decodeded queteur id is not found is DB. Used to display an error to the user
   *
   * @return boolean true : if the decodeded data length is 24, meaning it's potentially a queteur (but might not match math the regex or not be found in db)
   *                 false: if the length is not 24
   * */
  instance.decodeQueteur=function(data, checkQueteurNotAlreadyDecocededFunction, queteurDecodedAndFoundInDB, queteurDecodedAndNotFoundInDB )
  {
    var local = this;

    if(data.length === local.queteurQRCodeLength)
    {//queteur
      $log.debug("data length is 24 => QUETEUR");

      if(checkQueteurNotAlreadyDecocededFunction())
      {
        var queteurRegEx = /^QUETEUR-([0-9]{6})-([0-9]{9})$/g
        var match = queteurRegEx.exec(data);
        if(match !== null)
        {
          $log.debug("Queteur data match RegEx");
          var ulId       = parseInt(match[1]);
          var queteurId  = parseInt(match[2]);

          $log.debug("ulId:"+ulId );
          $log.debug("queteurId:"+queteurId);

          QueteurResource.get({'id':queteurId}).$promise.then(function(queteur)
            {
              $log.debug("queteurId:"+queteurId +" found in Database", queteur);

              if(queteur.anonymization_token !== "")
              {//The queteur has been anonymised and can't be used to prepare a tronc
                queteurDecodedAndNotFoundInDB("ANONYMISED", queteurId, ulId);
                return;
              }

              if(queteur.active !== true)
              {//The queteur has been anonymised and can't be used to prepare a tronc
                queteurDecodedAndNotFoundInDB("INACTIVE", queteurId, ulId);
                return;
              }

              //play scanner beep
              local.sound.play();

              queteurDecodedAndFoundInDB(queteur);
            },
            function(reason)
            {
              $log.error("Error while fetching queteur queteurId='"+queteurId+"' ulId='"+ulId+"' in database: '"+ JSON.stringify(reason)+"'");
              queteurDecodedAndNotFoundInDB(reason, queteurId, ulId);
            }
          );
        }
        else
        {
          $log.info("data do not match RegEx, data='"+data+"'");
        }
      }
      return true;
    }
    else
    {
      return false;
    }
  };




  /**
   * Look for troncData in the decoded data produced by qr-scanner library
   *
   * @param data : the data decoded by the qr-scanner library (it may be corrupted)
   * @param checkTroncNotAlreadyDecocededFunction : function that must return true if the queteur is already decoded.
   *                  (if you leave the QRCode a few second in front of the camera, it will decode several time the QRCode)
   * @param troncDecodedAndFoundInDB : function called when the QRCode is a tronc and it's found in DB (it takes a tronc in parameter)
   * @param troncDecodedAndNotFoundInDB : function called when the decodeded tronc id is not found is DB. Used to display an error to the user
   *
   * @return boolean true : if the decodeded data length is 22, meaning it's potentially a tronc (but might not match math the regex or not be found in db)
   *                 false: if the length is not 22
   * */
  instance.decodeTronc=function(data, checkTroncNotAlreadyDecocededFunction, troncDecodedAndFoundInDB, troncDecodedAndNotFoundInDB )
  {
    var local = this;

    if(data.length === local.troncQRCodeLength)
    {//queteur
      $log.debug("data length is 22 => TRONC");

      if(checkTroncNotAlreadyDecocededFunction())
      {
        var troncRegEx = /^TRONC-([0-9]{6})-([0-9]{9})$/g
        var match = troncRegEx.exec(data);
        if(match != null)
        {
          $log.debug("Tronc data match RegEx");
          var ulId     = parseInt(match[1]);
          var troncId  = parseInt(match[2]);

          $log.debug("ulId:"+ulId );
          $log.debug("troncId:"+troncId);

          TroncResource.get({'id':troncId}).$promise.then(function(tronc)
            {
              $log.debug("troncId:"+troncId +" found in Database", tronc);

              if(tronc.enabled !== true)
              {
                troncDecodedAndNotFoundInDB("INACTIVE", troncId, ulId);
                return;
              }
              //play scanner beep
              local.sound.play();

              troncDecodedAndFoundInDB(tronc);
            },
            function(reason)
            {
              $log.error("Error while fetching queteur troncId='"+troncId+"' ulId='"+ulId+"' in database: '"+reason+"'");
              troncDecodedAndNotFoundInDB(reason, troncId, ulId);
            }
          );
        }
        else
        {
          $log.info("data do not match RegEx, data='"+data+"'");
        }
      }
      return true;
    }
    else
    {
      return false;
    }
  };


  return instance;


});
