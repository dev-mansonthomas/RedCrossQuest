/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('ListRecuFiscalController', ListRecuFiscalController);

  /** @ngInject */
  function ListRecuFiscalController($rootScope, $scope, $log, $localStorage, $location,
                                    RecuFiscalResource, UniteLocaleResource, DateTimeHandlingService)
  {
    var vm = this;
    vm.currentUserRole=$localStorage.currentUser.roleId;
    vm.pageNumber       = 1;
    vm.rowCount         = 0;

    $rootScope.$emit('title-updated', 'Reçu Fiscaux');


    var currentYear = (new Date()).getFullYear();
    vm.year  = currentYear;
    vm.years = [];
    for(var i=2004;i<=currentYear; i++)
      vm.years[vm.years.length]=i;

    vm.deleted = false;




    vm.doSearch=function()
    {
      var searchParams = {'action':'search','q':vm.search, 'year':vm.year, 'deleted':vm.deleted, 'pageNumber':vm.pageNumber};

      if(vm.currentUserRole === 9 && vm.admin_ul_id !== null)
      {
        searchParams['admin_ul_id']=vm.admin_ul_id;
      }

      vm.recuFiscal = RecuFiscalResource.query(searchParams).$promise.then(handleResult).catch(function(e){
        $log.error("error searching for RecuFiscal", e);
      });

    };

    vm.doSearch();

    vm.createNewRecuFiscal=function()
    {
      $location.path("/recu_fiscal/edit").replace();
    };



    function handleResult (pageableResponse)
    {
      vm.recuFiscal  = pageableResponse.rows;
      vm.rowCount    = pageableResponse.count;

      var counti = vm.recuFiscal.length;
      var i=0;
      for(i=0;i<counti;i++)
      {
        var oneRecu = vm.recuFiscal[i];
        oneRecu.donation_date = DateTimeHandlingService.handleServerDate(oneRecu.donation_date     ).stringVersion;
        oneRecu.total_amount  = oneRecu.euro2   * 2    +
                                oneRecu.euro1   * 1    +
                                oneRecu.cents50 * 0.5  +
                                oneRecu.cents20 * 0.2  +
                                oneRecu.cents10 * 0.1  +
                                oneRecu.cents5  * 0.05 +
                                oneRecu.cents2  * 0.02 +
                                oneRecu.cent1   * 0.01 +
                                oneRecu.euro5   * 5    +
                                oneRecu.euro10  * 10   +
                                oneRecu.euro20  * 20   +
                                oneRecu.euro50  * 50   +
                                oneRecu.euro100 * 100  +
                                oneRecu.euro200 * 200  +
                                oneRecu.euro500 * 500  +
                                oneRecu.don_cheque     +
                                oneRecu.don_creditcard;
        vm.recuFiscal[i]=oneRecu;
      }
    }



    /**
     * Function used while performing a manual search for an Unité Locale
     * @param queryString the search string (search is performed on name, postal code, city)
     * */
    vm.searchUL=function(queryString)
    {
      $log.info("UL : Manual Search for '"+queryString+"'");
      return UniteLocaleResource.query({"q":queryString}).$promise.then(function success(response)
      {
        return response.map(function(ul)
          {
            ul.full_name= ul.id + ' - ' + ul.name+' - '+ul.postal_code+' - '+ul.city;
            return ul;
          },
          function error(reason)
          {
            $log.debug("error while searching for ul with query='"+queryString+"' with reason='"+reason+"'");
          });
      }).catch(function(e){
        $log.error("error searching for UL", e);
      });
    };

    //This watch change on queteur variable to update the queteurId field
    $scope.$watch('rf.admin_ul', function(newValue/*, oldValue*/)
    {
      if(newValue !== null && typeof newValue !==  "string" && typeof newValue !== "undefined")
      {
        try
        {
          $scope.rf.admin_ul_id = newValue.id;
        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });


  }
})();

