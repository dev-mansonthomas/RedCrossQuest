/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('client').factory('QueteurResource', function($resource) {
  return $resource('/rest/queteurs/:id', { id: '@id' }, {
    update: {
      method: 'PUT' // this method issues a PUT request
    }
  });
});
