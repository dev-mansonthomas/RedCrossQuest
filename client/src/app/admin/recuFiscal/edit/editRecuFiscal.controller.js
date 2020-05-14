/**
 * Created by tmanson on 15/04/2016.
 */

(function() {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('EditRecuFiscalController', EditRecuFiscalController);

  /** @ngInject */
  function EditRecuFiscalController ($rootScope, $scope, $log, $routeParams, $timeout, $localStorage, $location,
                                     RecuFiscalResource, TroncQueteurResource, MoneyBagResource, moment, DateTimeHandlingService)
  {
    var vm = this;

    $rootScope.$emit('title-updated', 'Edition d\' Reçu Fiscal');

    vm.onlyNumbers = /^[0-9]{1,5}$/;
    vm.cbFormat    = /^[0-9]+(\.[0-9]{1,2})?$/;

    vm.currentUserRole= $localStorage.currentUser.roleId;
    vm.currentUlMode  = $localStorage.currentUser.ulMode;
    vm.ulName         = $localStorage.currentUser.ulName;

    vm.first_name     = $localStorage.guiSettings.user.first_name;
    vm.last_name      = $localStorage.guiSettings.user.last_name;

    vm.use_bank_bag   = $localStorage.guiSettings.ul_settings.use_bank_bag;

    vm.currentYear    = new Date().getFullYear();


    vm.formDonList=[

      {id:1,label:'Déclaration de don manuel'},
      {id:2,label:'Acte sous seing privé'}

    ];

    vm.natureDonList=[
      {id:1,label:'Espèce'},
      {id:2,label:'Chèque'},
      {id:3,label:'Virement, Prélèvement, Carte Bancaire'}
      /*,
      {id:4,label:'Don en matériel'},
      {id:5,label:'Don en préstation'}*/
    ];


    vm.currentDate = new Date();

    var recu_fiscal_id = $routeParams.id;


    vm.loadData=function(recu_fiscal_id)
    {
      vm.current = {};
      vm.current.saveInProgress = false;

      if (angular.isDefined(recu_fiscal_id) &&  recu_fiscal_id !== 0)
      {
        $log.debug("loading data for named_donation with ID='"+recu_fiscal_id+"' ");
        RecuFiscalResource.get({id:recu_fiscal_id}).$promise.then(handleRecuFiscal).catch(function(e){
          $log.error("error searching for RecuFiscal", e);
        });
      }
      else
      {
        vm.current.recu_fiscal = new RecuFiscalResource();
        vm.current.recu_fiscal.donation_date    = new Date();
        vm.current.donation_dateMax = new Date();
      }
    };
    vm.loadData(recu_fiscal_id);





    vm.isEmpty=function(value)
    {
      if(typeof value === "undefined" || value === null || value === "" || value === 0)
      {
        return 0;
      }
      else
      {
        return value;
      }

    };

    vm.setNonFilledBillToZero=function()
    {

      vm.current.recu_fiscal.euro5   = vm.isEmpty(vm.current.recu_fiscal.euro5  );
      vm.current.recu_fiscal.euro10  = vm.isEmpty(vm.current.recu_fiscal.euro10 );
      vm.current.recu_fiscal.euro20  = vm.isEmpty(vm.current.recu_fiscal.euro20 );
      vm.current.recu_fiscal.euro50  = vm.isEmpty(vm.current.recu_fiscal.euro50 );
      vm.current.recu_fiscal.euro100 = vm.isEmpty(vm.current.recu_fiscal.euro100);
      vm.current.recu_fiscal.euro200 = vm.isEmpty(vm.current.recu_fiscal.euro200);
      vm.current.recu_fiscal.euro500 = vm.isEmpty(vm.current.recu_fiscal.euro500);
    };

    vm.setNonFilledCoinToZero=function()
    {
      vm.current.recu_fiscal.euro2   = vm.isEmpty(vm.current.recu_fiscal.euro2  );
      vm.current.recu_fiscal.euro1   = vm.isEmpty(vm.current.recu_fiscal.euro1  );
      vm.current.recu_fiscal.cents50 = vm.isEmpty(vm.current.recu_fiscal.cents50);
      vm.current.recu_fiscal.cents20 = vm.isEmpty(vm.current.recu_fiscal.cents20);
      vm.current.recu_fiscal.cents10 = vm.isEmpty(vm.current.recu_fiscal.cents10);
      vm.current.recu_fiscal.cents5  = vm.isEmpty(vm.current.recu_fiscal.cents5 );
      vm.current.recu_fiscal.cents2  = vm.isEmpty(vm.current.recu_fiscal.cents2 );
      vm.current.recu_fiscal.cent1   = vm.isEmpty(vm.current.recu_fiscal.cent1 );
    };

    vm.confirmSave = function ()
    {
      vm.current.overrideWarning=true;
      vm.save();
    };

    vm.save = function save()
    {
      vm.current.saveInProgress = true;
      if(vm.checkInputValues() && vm.current.overrideWarning !== true)
      {
        vm.current.confirmInputValues=true;
      }
      else
      {
        if (angular.isDefined(vm.current.recu_fiscal.id))
        {
          vm.fillEmptyValues();
          vm.current.recu_fiscal.$update(savedSuccessfully, onSaveError);
        }
        else
        {
          //depending on the type of donation, not all field are filled (ex: credit card donation only fill the credit_card field, leaving empty bills, coins and cheque that are still mandatory in the DB
          vm.fillEmptyValues();
          vm.current.recu_fiscal.$save(savedSuccessfully, onSaveError);
        }
      }
    };

    function onSaveError(error)
    {
      vm.current.saveInProgress = false;
      $log.debug(error);
      vm.errorWhileSaving=true;
      vm.errorWhileSavingDetails=JSON.stringify(error);
    }


    function savedSuccessfully(response)
    {
      vm.current = {};
      vm.current.saveInProgress = false;
      vm.savedSuccessfully=true;

      if(response && typeof response.namedDonationId ==='number')
      {
        $location.path('/recu_fiscal/edit/'+response.namedDonationId).replace();
        return;
      }

      $timeout(function () { vm.savedSuccessfully=false; }, 10000);
      $location.path('/recu_fiscal/').replace();
    }

    vm.back=function()
    {
      vm.current = {};
      vm.current.saveInProgress = false;
      $location.path('/recu_fiscal/').replace();
    };






    function handleRecuFiscal(recu_fiscal)
    {
      vm.current.recu_fiscal =  recu_fiscal;
      vm.current.donation_dateMax = new Date();


      if(vm.current.recu_fiscal.donation_date !== null)
      {
        var donationDate =  DateTimeHandlingService.handleServerDate(recu_fiscal.donation_date);
        vm.current.recu_fiscal.donation_dateStr =  donationDate.stringVersion;
        vm.current.recu_fiscal.donation_date    =  donationDate.dateInLocalTimeZone;


        var currentYear    = moment().format('YYYY');
        var recuFiscalYear = donationDate.dateInLocalTimeZoneMoment.format('YYYY');

        if(currentYear !== recuFiscalYear)
        {
          vm.current.not_same_year    = true;
          vm.current.year_recu_fiscal = recuFiscalYear;
        }
      }

      $rootScope.$emit('title-updated', 'Reçu Fiscal '+vm.current.recu_fiscal.id+' - ');
    }



    vm.checkInputValues=function()
    {
      var displayConfirmDialog=false;
      vm.current.confirmInputValuesMessage="";

      if(vm.current.recu_fiscal.euro5 > 20)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 20 billets de 5€</li>";
      }
      if(vm.current.recu_fiscal.euro10 > 10)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 10 billets de 10€ </li>";
      }
      if(vm.current.recu_fiscal.euro20 > 6)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 6 billets de 20€ </li>";
      }
      if(vm.current.recu_fiscal.euro50 > 1)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 1 billet de 50€ </li>";
      }
      if(vm.current.recu_fiscal.euro100 > 0)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 1 billet de 100€ </li>";
      }
      if(vm.current.recu_fiscal.euro200 > 0)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 1 billet de 200€ </li>";
      }
      if(vm.current.recu_fiscal.euro500 > 0)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 1 billet de 500€ </li>";
      }
       //pièces
      if(vm.current.recu_fiscal.euro2 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 2€ </li>";
      }
      if(vm.current.recu_fiscal.euro1 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 1€ </li>";
      }

      if(vm.current.recu_fiscal.cents50 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 50cts </li>";
      }
      if(vm.current.recu_fiscal.cents20 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 20cts </li>";
      }
      if(vm.current.recu_fiscal.cents10 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 10cts </li>";
      }
      if(vm.current.recu_fiscal.cents5 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 5cts </li>";
      }
      if(vm.current.recu_fiscal.cents2 > 120)
      {
        displayConfirmDialog = true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 2cts </li>";
      }
      if(vm.current.recu_fiscal.cent1 > 120)
      {
        displayConfirmDialog=true;
        vm.current.confirmInputValuesMessage+="<li>plus de 120 pièces de 1cent</li>";
      }

      return displayConfirmDialog;
    };

    vm.searchCoinMoneyBagId=function(searchedString)
    {
      return vm.searchMoneyBagId(searchedString, 'coin');
    };

    vm.searchBillMoneyBagId=function(searchedString)
    {
      return vm.searchMoneyBagId(searchedString, 'bill');
    };

    vm.searchMoneyBagId=function(searchedString, type)
    {
      return MoneyBagResource.searchMoneyBagId({'q':searchedString, 'type':type}).$promise.then(function success(response)
      {
        return response.map(function(oneResponse)
          {
            return oneResponse;
          },
          function error(reason)
          {
            $log.debug("error while searching for moneybagId query='"+searchedString+"' with reason='"+reason+"'");
          });
      }).catch(function(e){
        $log.error("error searching for MoneyBag", e);
      });
    };

    vm.getBagDetails = function($item, $model, $label, $event, coins)
    {
      //$log.error(JSON.stringify([$item, $model, $label, $event, coins]));
      if(coins)
      {
        vm.current.coinsMoneyBagDetails = MoneyBagResource.coinsMoneyBagDetails({'id':$item});
      }
      else
      {
        vm.current.billsMoneyBagDetails = MoneyBagResource.billsMoneyBagDetails({'id':$item});
      }
    };


    vm.fillEmptyValues = function()
    {
      if(typeof vm.current.recu_fiscal.donation_date ==='undefined' || vm.current.recu_fiscal.donation_date === null)
      {
        vm.current.recu_fiscal.donation_date = new Date();
      }

      vm.current.recu_fiscal.euro5   = vm.isEmpty(vm.current.recu_fiscal.euro5  );
      vm.current.recu_fiscal.euro10  = vm.isEmpty(vm.current.recu_fiscal.euro10 );
      vm.current.recu_fiscal.euro20  = vm.isEmpty(vm.current.recu_fiscal.euro20 );
      vm.current.recu_fiscal.euro50  = vm.isEmpty(vm.current.recu_fiscal.euro50 );
      vm.current.recu_fiscal.euro100 = vm.isEmpty(vm.current.recu_fiscal.euro100);
      vm.current.recu_fiscal.euro200 = vm.isEmpty(vm.current.recu_fiscal.euro200);
      vm.current.recu_fiscal.euro500 = vm.isEmpty(vm.current.recu_fiscal.euro500);

      vm.current.recu_fiscal.euro2   = vm.isEmpty(vm.current.recu_fiscal.euro2  );
      vm.current.recu_fiscal.euro1   = vm.isEmpty(vm.current.recu_fiscal.euro1  );
      vm.current.recu_fiscal.cents50 = vm.isEmpty(vm.current.recu_fiscal.cents50);
      vm.current.recu_fiscal.cents20 = vm.isEmpty(vm.current.recu_fiscal.cents20);
      vm.current.recu_fiscal.cents10 = vm.isEmpty(vm.current.recu_fiscal.cents10);
      vm.current.recu_fiscal.cents5  = vm.isEmpty(vm.current.recu_fiscal.cents5 );
      vm.current.recu_fiscal.cents2  = vm.isEmpty(vm.current.recu_fiscal.cents2 );
      vm.current.recu_fiscal.cent1   = vm.isEmpty(vm.current.recu_fiscal.cent1  );

      vm.current.recu_fiscal.don_creditcard = vm.isEmpty(vm.current.recu_fiscal.don_creditcard  );
      vm.current.recu_fiscal.don_cheque     = vm.isEmpty(vm.current.recu_fiscal.don_cheque      );
      vm.current.recu_fiscal.notes = '';

    };

    vm.computeTotalDonation=function()
    {
      if(angular.isDefined(vm.current.recu_fiscal))
      {
        return  vm.isEmpty(vm.current.recu_fiscal.euro2   ) * 2    +
                vm.isEmpty(vm.current.recu_fiscal.euro1   ) * 1    +
                vm.isEmpty(vm.current.recu_fiscal.cents50 ) * 0.5  +
                vm.isEmpty(vm.current.recu_fiscal.cents20 ) * 0.2  +
                vm.isEmpty(vm.current.recu_fiscal.cents10 ) * 0.1  +
                vm.isEmpty(vm.current.recu_fiscal.cents5  ) * 0.05 +
                vm.isEmpty(vm.current.recu_fiscal.cents2  ) * 0.02 +
                vm.isEmpty(vm.current.recu_fiscal.cent1   ) * 0.01 +
                vm.isEmpty(vm.current.recu_fiscal.euro5   ) * 5    +
                vm.isEmpty(vm.current.recu_fiscal.euro10  ) * 10   +
                vm.isEmpty(vm.current.recu_fiscal.euro20  ) * 20   +
                vm.isEmpty(vm.current.recu_fiscal.euro50  ) * 50   +
                vm.isEmpty(vm.current.recu_fiscal.euro100 ) * 100  +
                vm.isEmpty(vm.current.recu_fiscal.euro200 ) * 200  +
                vm.isEmpty(vm.current.recu_fiscal.euro500 ) * 500  +
                vm.isEmpty(vm.current.recu_fiscal.don_cheque)      +
                vm.isEmpty(vm.current.recu_fiscal.don_creditcard);
      }
      return '';
    };

  }
})();

