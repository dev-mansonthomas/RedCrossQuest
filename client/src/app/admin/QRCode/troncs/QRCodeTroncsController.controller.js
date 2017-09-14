/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('client')
    .controller('QRCodeTroncsController', QRCodeTroncsController);

  /** @ngInject */
  function QRCodeTroncsController($log,
                                  TroncResource)
  {
    var vm = this;

    vm.size=200;

    TroncResource.query().$promise.then(function(response)
    {
      vm.list = response.map(function(tronc)
        {
          tronc.qr_code="TRONC-"+("000000"+tronc.ul_id).slice(-6)+"-"+("00000000"+tronc.id).slice(-9);
          return tronc;
        },
        function(reason)
        {
          $log.debug("error while loading for tronc with reason='"+reason+"'");
        });

      $log.debug("There is "+vm.list.length+" troncs, "+Math.ceil(vm.list.length/32)+" tableaux") ;

      var numberOfTable= Math.ceil(vm.list.length/40);
      vm.tables = [];
      var global_i=0;

      for(var table_i=0;table_i<numberOfTable; table_i++)
      {
        vm.tables[table_i]=[];
        for(var table_tr_i=0;table_tr_i<8;table_tr_i++)
        {
          vm.tables[table_i][table_tr_i]=[];

          for(var table_td_i=0;table_td_i<5;table_td_i++)
          {
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

  }
})();

