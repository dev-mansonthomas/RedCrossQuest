/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('TroncResource', function($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/troncs/:id',
    {
      roleId: function () { return $localStorage.currentUser.roleId},
      ulId  : function () { return $localStorage.currentUser.ulId  },
      id    : '@id'
    },
    {
      update: {
        method: 'PUT' // this method issues a PUT request
      },
      query:{
        isArray: false
      },
      troncForDepart:{
        isArray: false,
        params: {
          id:'depart',
        }
      },
      troncForRetour:{
        isArray: false,
        params: {
          id:'retour',
        }
      },
      troncForComptage:{
        isArray: false,
        params: {
          id:'comptage',
        }
      },

    markAllAsPrinted: {
    method: 'POST',
      params: {
      action: 'cancelDepart'
    }
  }
  });
});
