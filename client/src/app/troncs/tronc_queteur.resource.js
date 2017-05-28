/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('client').factory('TroncQueteurResource', function ($resource, $localStorage) {
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
      getTroncQueteurForTroncId: {
        method: 'GET',
        params: {
          action: 'getTroncQueteurForTroncId',
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
