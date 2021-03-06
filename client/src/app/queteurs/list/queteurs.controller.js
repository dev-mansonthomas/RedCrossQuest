/**
 * Created by tmanson on 15/04/2016.
 */

(function () {
  'use strict';

  angular
    .module('redCrossQuestClient')
    .controller('QueteursController', QueteursController);

  /** @ngInject */
  function QueteursController($rootScope, $scope, $log, $localStorage,
                              QueteurResource, UniteLocaleResource, DateTimeHandlingService)
  {
    var vm = this;
    vm.searchType           = 0;
    vm.redquest_registered  = 3;
    vm.rcqUser              = 0;
    vm.user_role            = null;
    vm.rcqUserActif         = 1;
    vm.currentUserRole      = $localStorage.currentUser.roleId;
    vm.pageNumber           = 1;
    vm.rowCount             = 0;
    vm.list                 = [];
    vm.yearsOfInactivity    = 1;
    vm.inactiveQueteurCount    = null;
    vm.inactiveQueteurDisabled = null;

    $rootScope.$emit('title-updated', 'Liste des quêtêurs');

    vm.yearsOfInactivityList=[
      {id:1 ,label:'1 an'},
      {id:2 ,label:'2 ans'},
      {id:3 ,label:'3 ans'},
      {id:4 ,label:'4 ans'}
    ];

    vm.typeBenevoleList=[
      {id:1 ,label:'Action Sociale'},
      {id:2 ,label:'Secours'},
      {id:3 ,label:'Bénévole 1j'},
      {id:4 ,label:'Ancien Bénévole, Inactif ou Adhérent'},
      {id:5 ,label:'Commerçant'},
      {id:6 ,label:'Spécial'}
    ];

    vm.roleList=[
      {id:1,label:'Lecture Seule' },
      {id:2,label:'Opérateur'     },
      {id:3,label:'Compteur'      },
      {id:4,label:'Administrateur'}
    ];

    vm.typeBenevoleHash=[];
    for(var i=0;i< vm.typeBenevoleList.length;i++)
    {
      vm.typeBenevoleHash[vm.typeBenevoleList[i].id]=vm.typeBenevoleList[i].label;
    }


    vm.handleDate = function (theDate)
    {
      return DateTimeHandlingService.handleServerDate(theDate).stringVersion;
    };

    function handleSearchResults(pageableResponse)
    {
      vm.rowCount = pageableResponse.count;
      vm.list     = pageableResponse.rows ;

      var dataLength = vm.list.length;

      for(var i=0;i<dataLength;i++)
      {
        vm.list[i].depart            = vm.handleDate(vm.list[i].depart);
        vm.list[i].depart_theorique  = vm.handleDate(vm.list[i].depart_theorique);
        vm.list[i].retour            = vm.handleDate(vm.list[i].retour);
      }
    }

    vm.doSearch=function()
    {
      $log.debug("search with searchType:'"+vm.searchType+"' admin_ul_id:"+vm.admin_ul_id);

      var searchParams = {
        'q'                   : vm.search       ,
        'searchType'          : vm.searchType   ,
        'secteur'             : vm.secteur      ,
        'active'              : vm.active       ,
        'rcqUser'             : vm.rcqUser      ,
        'rcqUserActif'        : vm.rcqUserActif ,
        'anonymization_token' : vm.anonymization_token,
        'pageNumber'          : vm.pageNumber   ,
        'redquest_registered' : vm.redquest_registered,
        'user_role'           : vm.user_role};


      if(vm.currentUserRole === 9 && vm.admin_ul_id !== null)
      {
        searchParams['admin_ul_id']=vm.admin_ul_id;
      }

      QueteurResource.query(searchParams).$promise.then(handleSearchResults).catch(function(e){
        $log.error("error searching for Queteur", e);
      });
    };

    //initial search with type 0 (all queteur)
    vm.doSearch();


    vm.countInactiveQueteurs=function()
    {
      QueteurResource.countInactiveQueteurs({'yearsOfInactivity': vm.yearsOfInactivity}).$promise.then(function(response){

        vm.inactiveQueteurCount    = response.count;
        vm.inactiveQueteurDisabled = null;

      }).catch(function(e){
        $log.error("error countInactiveQueteurs", e);
      });
    };

    vm.disableInactiveQueteurs=function()
    {
      QueteurResource.disableInactiveQueteurs({'yearsOfInactivity': vm.yearsOfInactivity}).$promise.then(function(response){
        vm.inactiveQueteurCount    = null;
        vm.inactiveQueteurDisabled = response.count;

      }).catch(function(e){
        $log.error("error countInactiveQueteurs", e);
      });
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

