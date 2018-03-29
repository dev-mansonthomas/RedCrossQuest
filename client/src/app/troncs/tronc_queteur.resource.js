/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('TroncQueteurResource', function ($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/tronc_queteur/:id',
    {
      roleId: $localStorage.currentUser.roleId,
      ulId: $localStorage.currentUser.ulId,
      id: '@id'
    }, {
      update: {
        method: 'PUT' // this method issues a PUT request
      },
      deleteNonReturnedTroncQueteur: {
        method: 'DELETE',
        params: {id: '@id'}
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
        method: 'POST',
        params: {
          action: 'getTroncQueteurForTroncIdAndSetDepart',
          tronc_id: '@tronc_id'
        }
      },
      saveReturnDate: {
        method: 'POST',
        params: {
          action: 'saveReturnDate'
        }
      },
      saveCoins: {
        method: 'POST',
        params: {
          action: 'saveCoins'
        }
      },
      saveCoinsAsAdmin: {
        method: 'POST',
        params: {
          action: 'saveCoins',
          adminMode: true
        }
      },
      saveCreditCard: {
        method: 'POST',
        params: {
          action: 'saveCreditCard'
        }
      },
      saveCreditCardAsAdmin: {
        method: 'POST',
        params: {
          action: 'saveCreditCard',
          adminMode: true
        }
      },
      saveAsAdmin: {
        method: 'POST',
        params: {
          action: 'saveAsAdmin'
        }
      },
      cancelRetour: {
        method: 'POST',
        params: {
          action: 'cancelRetour'
        }
      },
      cancelDepart: {
        method: 'POST',
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
