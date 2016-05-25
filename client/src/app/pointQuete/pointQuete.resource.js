/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('client').factory('PointQueteResource', function($resource) {
  return $resource('/rest/pointQuetes/:id', { id: '@id' }, {
    update: {
      method: 'PUT' // this method issues a PUT request
    }
  });
});
