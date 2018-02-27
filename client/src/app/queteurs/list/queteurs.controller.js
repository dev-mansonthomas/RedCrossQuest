/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('QueteursController', QueteursController);

  /** @ngInject */
  function QueteursController($scope, $log, $localStorage,
                              QueteurResource, UniteLocaleResource)
  {
    var vm = this;
    vm.searchType = 0;

    vm.currentUserRole=$localStorage.currentUser.roleId;

    vm.typeBenevoleList=[
      {id:1,label:'Action Sociale'},
      {id:2,label:'Secours'},
      {id:3,label:'Non Bénévole'},
      {id:4,label:'Ancien Bénévole, Inactif ou Adhérent'},
      {id:5,label:'Commerçant'},
      {id:6,label:'Spécial'}
    ];


    //initial search with type 0 (all queteur)
    vm.list = QueteurResource.query({'searchType':0});

    vm.doSearch=function()
    {
      $log.debug("search with type:'"+vm.searchType+"' "+vm.admin_ul_id);

      var searchParams = {'q':vm.search, 'searchType':vm.searchType,  'active':vm.active,  'rcqUser':vm.rcqUser};


      if(vm.currentUserRole === '9' && vm.admin_ul_id !== null)
      {
        searchParams['admin_ul_id']=vm.admin_ul_id;
      }

      vm.list = QueteurResource.query(searchParams);
    };


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
      });
    };

    //This watch change on queteur variable to update the queteurId field
    $scope.$watch('q.admin_ul', function(newValue/*, oldValue*/)
    {
      if(newValue !== null && typeof newValue !==  "string" && typeof newValue !== "undefined")
      {
        try
        {
          $scope.q.admin_ul_id = newValue.id;
        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });


  }
})();

