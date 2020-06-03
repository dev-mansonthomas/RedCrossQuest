/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('TroncQueteurResource', function ($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/tronc_queteur/:id/:action/:subId',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  },
      id: '@id'
    }, {
      update: {
        method: 'PUT' // this method issues a PUT request
      },
      preparationChecks:{
        method: 'GET',
        params: {
          action: 'preparationChecks'
        }
      },
      deleteNonReturnedTroncQueteur: {
        method: 'DELETE',
        params: {
          action: 'nonReturnedTroncQueteur',
          subId:'@subId'
        }
      },
      searchMoneyBagId:{
        method: 'GET',
        isArray: true,
        params: {
          action: 'searchMoneyBagId'
        }
      },
      moneyBagDetails:{
        method: 'GET',
        isArray: false,
        params: {
          action: 'moneyBagDetails'
        }
      },
      //get the last tronc_queteur
      getLastTroncQueteurFromTroncId: {
        method: 'GET',
        params: {
          action: 'getLastTroncQueteurFromTroncId',
          tronc_id: '@tronc_id'
        }
      },
      //get all tronc_queteur for tronc_id
      getTroncsQueteurForTroncId: {
        method: 'GET',
        isArray: true,
        params: {
          action: 'getTroncsQueteurForTroncId',
          tronc_id: '@tronc_id'
        }
      },
      getTroncQueteurForTroncIdAndSetDepart: {
        method: 'PATCH',
        params: {
          action: 'getTroncQueteurForTroncIdAndSetDepart',
          tronc_id: '@tronc_id'
        }
      },
      saveReturnDate: {
        method: 'PATCH',
        params: {
          action: 'saveReturnDate'
        }
      },
      saveCoins: {
        method: 'PATCH',
        params: {
          action: 'saveCoins'
        }
      },
      saveCoinsAsAdmin: {
        method: 'PATCH',
        params: {
          action: 'saveCoins',
          adminMode: true
        }
      },
      saveAsAdmin: {
        method: 'PATCH',
        params: {
          action: 'saveAsAdmin'
        }
      },
      cancelRetour: {
        method: 'PATCH',
        params: {
          action: 'cancelRetour'
        }
      },
      cancelDepart: {
        method: 'PATCH',
        params: {
          action: 'cancelDepart'
        }
      },
      getTroncsOfQueteur: {
        method: 'GET',
        isArray: true,
        params: {
          action: 'getTroncsOfQueteur',
          queteur_id: '@queteur_id'
        }
      }
    });
});
