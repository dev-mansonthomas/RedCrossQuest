/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('ListPointQueteController', ListPointQueteController);

  /** @ngInject */
  function ListPointQueteController($rootScope, $scope, $log, $localStorage, $location,
                                    PointQueteResource, DateTimeHandlingService, UniteLocaleResource)
  {
    var vm = this;
    vm.currentUserRole=$localStorage.currentUser.roleId;
    vm.ul = $localStorage.guiSettings.ul;
    $rootScope.$emit('title-updated', 'Liste des points de quête');

    vm.pageNumber       = 1;
    vm.rowCount         = 0;

    vm.typePointQueteList=[
      {id:null,label:''},
      {id:1,label:'Voie Publique / Feux Rouge'},
      {id:2,label:'Piéton'},
      {id:3,label:'Commerçant'},
      {id:4,label:'Base UL'},
      {id:5,label:'Autre'}
    ];

    vm.doSearch=function()
    {
      var searchParams = {'action':'search','q':vm.search, 'point_quete_type':vm.point_quete_type, 'active':vm.active, 'pageNumber': vm.pageNumber   };

      if(vm.currentUserRole === 9 && vm.admin_ul_id !== null)
      {
        searchParams['admin_ul_id']=vm.admin_ul_id;
      }

      vm.pointsQuete = PointQueteResource.search(searchParams).$promise.then(handleResult).catch(function(e){
        $log.error("error searching for PointQuete", e);
      });

    };

    vm.doSearch();

    vm.createNewPointQuete=function()
    {
      $location.path("/pointsQuetes/edit").replace();
    };



    function handleResult (pointsQueteResponse)
    {
      vm.pointsQuete = pointsQueteResponse.rows;
      vm.rowCount    = pointsQueteResponse.count;

      var counti     = vm.pointsQuete.length;
      for(var i=0;i<counti;i++)
      {
        vm.pointsQuete[i].created      = DateTimeHandlingService.handleServerDate(vm.pointsQuete[i].created).stringVersion;
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
    $scope.$watch('pq.admin_ul', function(newValue/*, oldValue*/)
    {
      if(newValue !== null && typeof newValue !==  "string" && typeof newValue !== "undefined")
      {
        try
        {
          $scope.pq.admin_ul_id = newValue.id;
        }
        catch(exception)
        {
          $log.debug(exception);
        }
      }
    });



  }
})();

