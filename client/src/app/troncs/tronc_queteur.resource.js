/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('client').factory('TroncQueteurResource', function($resource) {
  return $resource('/rest/tronc_queteur/:id', { id: '@id' }, {
    update: {
      method: 'PUT' // this method issues a PUT request
    },
    deleteNonReturnedTroncQueteur:{
      method:'DELETE',
      params:{id:'@id'}
    },
    getTroncQueteurForTroncId:{
      method:'GET',
      params:{
        action:'getTroncQueteurForTroncId',
        tronc_id:'@tronc_id'
      }
    }
  });
});
