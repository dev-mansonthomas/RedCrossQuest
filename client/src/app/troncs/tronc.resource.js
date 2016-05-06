/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('client').factory('TroncResource', function($resource) {
  return $resource('/rest/troncs/:id', { id: '@id' }, {
    update: {
      method: 'PUT' // this method issues a PUT request
    }
  });
});
