/**
 * Created by tmanson on 03/05/2016.
 */

angular.module('redCrossQuestClient').factory('VersionResource', function ($resource)
{
  return $resource('/deploy.json');
});
