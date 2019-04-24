/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('QRCodeQueteursController', QRCodeQueteursController);

  /** @ngInject */
  function QRCodeQueteursController($rootScope, $log,$localStorage, $timeout,
                                    QueteurResource)
  {
    var vm = this;

    vm.size=200;
    vm.ulName = $localStorage.currentUser.ulName;
    vm.QRSearchType = 2;
    vm.confirmMarkAllAsRead = false;
    vm.listQueteurIdsRegExp=/^(\d+( )?,( )?)*\d+$/;
    vm.deploymentType = $localStorage.currentUser.d;

    $rootScope.$emit('title-updated', 'Impression des QRCode Quêteurs');


    vm.search=function()
    {
      if(vm.listQueteurIds)
      {//if a list of ids is set, then remove the printed filter
        vm.QRSearchType=0;


        vm.listQueteurIds = vm.listQueteurIds.replace(/\s/g,'');
      }

      QueteurResource.query({'searchType':0, 'QRSearchType':vm.QRSearchType, 'queteurIds':vm.listQueteurIds}).$promise.then(function(response)
      {

        vm.list   = response.map(function(queteur)
          {
            //the Q of Queteur is put in the HTML page, with a font size of 11px to exactly match the same template as TRONC, so that it print exactly on the stickers
            queteur.qr_code="QUETEUR-"+("000000"+queteur.ul_id).slice(-6)+"-"+("00000000"+queteur.id).slice(-9);
            return queteur;
          },
          function(reason)
          {
            $log.debug("error while loading for queteur with reason='"+reason+"'");
          });

        //  $log.debug("There is "+vm.list.length+" queteur, "+Math.ceil(vm.list.length/32)+" tableaux") ;

        var numberOfTable= Math.ceil(vm.list.length/40);
        vm.tables = [];
        var global_i=0;

        for(var table_i=0;table_i<numberOfTable; table_i++)
        {
          vm.tables[table_i]=[];
          for(var table_tr_i=0;table_tr_i<8;table_tr_i++)
          {//ligne
            vm.tables[table_i][table_tr_i]=[];

            for(var table_td_i=0;table_td_i<5;table_td_i++)
            {//colonnes
              var element = vm.list[global_i++];
              if(element != null)
              {
                vm.tables[table_i][table_tr_i][table_td_i] = element;
              }
              else
              {
                return;
              }

            }
          }
        }
      });
    };
    vm.search();





    vm.markAllAsPrinted=function()
    {
      vm.confirmMarkAllAsRead = true;
    };
    vm.doMarkAllAsPrinted=function()
    {
      QueteurResource.markAllAsPrinted(vm.onSaveSuccess, vm.onSaveError);
    };
    vm.cancelMarkAllAsPrinted=function()
    {
      vm.confirmMarkAllAsRead = false;
    };

    vm.onSaveSuccess = function()
    {
      vm.savedSuccessfully=true;
      vm.confirmMarkAllAsRead=false;
      $timeout(function () { vm.savedSuccessfully=false; }, 10000);
    };

    vm.onSaveError = function(error)
    {
      $log.debug(error);
      vm.errorWhileSaving=true;
      vm.errorWhileSavingDetails=JSON.stringify(error);
    };
  }
})();

