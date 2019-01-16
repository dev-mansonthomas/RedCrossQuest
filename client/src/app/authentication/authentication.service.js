/**
 * Created by tmanson on 03/05/2016.
 */

angular
  .module('redCrossQuestClient')
  .factory('AuthenticationService', AuthenticationService);

function AuthenticationService ($http, $localStorage, jwtHelper, $log)
{
  var service = {};

  service.login               = login;
  service.logout              = logout;
  service.sendInit            = sendInit;
  service.resetPassword       = resetPassword;
  service.getUserInfoWithUUID = getUserInfoWithUUID;

  return service;

  function sendInit(username, token, callback, errorCallback)
  {
    $http.post('/rest/sendInit', { username: username, token: token })
      .then(function success(response) {
        // login successful if there's a token in the response
        if(response.data.success)
        {
          // execute callback with true to indicate successful login
          callback(true, response.data.email);
        }
        else
        {
          // execute callback with false to indicate failed login
          callback(false);
        }
      },
      function error(error)
      {
        errorCallback(error);
      });
  }

  function getUserInfoWithUUID(uuid, token, callback, errorCallback)
  {
    $http.get('/rest/getInfoFromUUID', { params:{uuid: uuid, token:token} })
      .then(
        function successCallback(response)
        {
          // login successful if there's a token in the response
          if(response.data.success)
          {
            // execute callback with true to indicate successful login
            callback(true, response.data);
          }
          else
          {
            // execute callback with false to indicate failed login
            callback(false);
          }
        },
        function (error)
        {
          errorCallback(error);
          $log.error(error);
        });
  }



  function resetPassword(uuid, password, token, callback)
  {
    $http.post('/rest/resetPassword', { uuid: uuid, password: password, token:token })
      .then(function successCallback(response) {
        // login successful if there's a token in the response
        if(response.data.success)
        {
          // execute callback with true to indicate successful login
          callback(true, response.data.email);
        }
        else
        {
          // execute callback with false to indicate failed login
          callback(false);
        }
      }, function errorCallback(error)
      {
        $log.error(error);
      });
  }


  function login(username, password, token, callback, errorCallback)
  {
    $http.post('/rest/authenticate', { username: username, password: password, token:token })
      .then(function successCallback(response) {
        // login successful if there's a token in the response
        if (response.data.token)
        {

          var tokenPayload = jwtHelper.decodeToken(response.data.token);

          // store username and token in local storage to keep user logged in between page refreshes
          $localStorage.currentUser = {
            username  : username              ,
            id        : tokenPayload.id       ,
            queteurId : tokenPayload.queteurId,
            ulId      : tokenPayload.ulId     ,
            ulName    : tokenPayload.ulName   ,
            ulMode    : tokenPayload.ulMode   ,
            roleId    : tokenPayload.roleId   ,
            d         : tokenPayload.d
          };
          $localStorage.RCQ_JWT_Token = response.data.token;

          // execute callback with true to indicate successful login
          callback(true);
        }
        else
        {
          // execute callback with false to indicate failed login
          callback(false);
        }
      },
      function errorCallBack(error){
        $log.error(error);
        errorCallback(error);
      });
  }

  function logout() {
    // remove user from local storage and clear http auth header
    //console.log("loging out "+$localStorage.currentUser)
    delete $localStorage.currentUser;
    delete $localStorage.guiSettings;
    delete $localStorage.RCQ_JWT_Token;
    $http.defaults.headers.common.Authorization = '';
  }


}
