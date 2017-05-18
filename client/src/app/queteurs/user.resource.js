/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('client').factory('UserResource', function($resource, $localStorage) {
  return $resource('/rest/:roleId/ul/:ulId/users/:id',
    {
      roleId: $localStorage.currentUser.roleId,
      ulId  : $localStorage.currentUser.ulId,
      id    : '@id'
    }, {
    update: {
      method: 'PUT', // this method issues a PUT request
      params:{
        action:'update'
      }
    },
    reInitPassword: {
      method: 'PUT', // this method issues a PUT request
      params:{
        action:'reInitPassword'
      }
    }
  });
});
