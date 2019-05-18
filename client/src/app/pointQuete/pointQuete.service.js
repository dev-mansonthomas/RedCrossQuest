/**
 * Created by tmanson on 03/05/2016.
 */

angular
  .module('redCrossQuestClient')
  .factory('PointQueteService', PointQueteService);

function PointQueteService ($localStorage, PointQueteResource, moment)
{
  var service = {};

  service.loadPointQuete      = loadPointQuete;

  return service;

  function loadPointQuete(callback)
  {
    //refresh the cache every minute max
    //if(
    //  angular.isUndefined($localStorage.pointQueteLastUpdate) ||
    //  moment().diff($localStorage.pointQueteLastUpdate, 'seconds') <= 60
    //)
    // {
      PointQueteResource.
      query().
      $promise.
      then(function success(pointQueteList)
      {
        updateCache(pointQueteList);
        $localStorage.pointQueteLastUpdate = moment();
        if(callback)
          callback();
      });
    //}
  }
  
  function updateCache(pointQueteList)
  {
    $localStorage.pointQuete=pointQueteList;

    $localStorage.pointsQueteHash = [];
    pointQueteList.forEach(function(onePointQuete){
      $localStorage.pointsQueteHash[onePointQuete.id]=onePointQuete;
    });
  }
}
