<?php
error_reporting ( 0 );

register_shutdown_function("fatal_handler");
function fatal_handler() {
	$error = error_get_last();
	if (is_array($error)) {	
		$errorMessage = $error["message"]. " in ". $error["file"]. " in line " . $error["line"];
		header("HTTP/1.0 500 Grapes in fatal trouble!");
		
		$actionResult = new ActionResult(null, 0,0, $errorMessage);
		echo json_encode($actionResult);
	}
	
}

error_reporting ( E_ALL );

require ("config.inc.php.conf");
require("includes.inc.php");
require("init.inc.php");







$router = new AltoRouter ();
$router->setBasePath ( $config ["BasePath"] );

/**
 * Web API
 */

// todo: change code: use method to set all routes in one array


$router->map ( 'POST', '/Api/issues/', array (
		'c' => 'IssuesApiController',
		'a' => 'createIssue'
) );
$router->map ( 'POST', '/Api/issues', array (
		'c' => 'IssuesApiController',
		'a' => 'createIssue'
) );

$router->map ( 'GET', '/Api/dashboards/[i:id]', array (
		'c' => 'DashboardsApiController',
		'a' => 'getDashboardById' 
) );
$router->map ( 'POST', '/Api/dashboards/[i:id]/tiles', array (
		'c' => 'DashboardsApiController',
		'a' => 'addTileToDashboard' 
) );
$router->map ( 'GET', '/Api/users', array (
		'c' => 'UsersApiController',
		'a' => 'Get' 
) );
$router->map ( 'GET', '/Api/users/', array (
		'c' => 'UsersApiController',
		'a' => 'Get' 
) );
$router->map ( 'GET', '/Api/users/[i:userId]', array (
		'c' => 'UsersApiController',
		'a' => 'getUserById' 
) );
$router->map ( 'GET', '/Api/users/[i:userId]/recentObjects', array (
		'c' => 'UsersApiController',
		'a' => 'getAllRecentObjects' 
) );
$router->map ( 'POST', '/Api/users', array (
		'c' => 'UsersApiController',
		'a' => 'create' 
) );
$router->map ( 'POST', '/Api/users/[i:userId]/picture', array (
		'c' => 'UsersApiController',
		'a' => 'uploadPicture' 
) );
$router->map ( 'POST', '/Api/dashboardtiles/[i:id]/position', array (
		'c' => 'DashboardsApiController',
		'a' => 'UpdatePosition'
) );
$router->map ( 'DELETE', '/Api/dashboardtiles/[i:id]', array (
		'c' => 'DashboardsApiController',
		'a' => 'removeTileFromDashboard'
) );            
$router->map ( 'GET', '/Api/tiles', array (
		'c' => 'DashboardsApiController',
		'a' => 'GetTiles'
) );
$router->map ( 'GET', '/Api/projects/[i:id]', array (
		'c' => 'ProjectsApiController',
		'a' => 'GetProjectById'
) );
$router->map ( 'POST', '/Api/projects/[i:id]/users', array (
		'c' => 'ProjectsApiController',
		'a' => 'saveProjectAci'
) );
$router->map ( 'GET', '/Api/projects/[i:id]/preview', array (
		'c' => 'ProjectsApiController',
		'a' => 'GetProjectPreview'
) );
$router->map ( 'GET', '/Api/projects/name/[a:name]', array (
		'c' => 'ProjectsApiController',
		'a' => 'GetProjectByName'
) );
$router->map ( 'GET', '/Api/projects/identifier/[a:identifier]', array (
		'c' => 'ProjectsApiController',
		'a' => 'getProjectByIdentifier'
) );
$router->map ( 'GET', '/Api/projects', array (
		'c' => 'ProjectsApiController',
		'a' => 'GetProjects'
) );
$router->map ( 'POST', '/Api/projects', array (
		'c' => 'ProjectsApiController',
		'a' => 'CreateProject'
) );
$router->map ( 'PUT', '/Api/projects/[i:id]', array (
		'c' => 'ProjectsApiController',
		'a' => 'UpdateProject'
) );
$router->map ( 'GET', '/Api/projects/[i:id]/components', array (
		'c' => 'ProjectsApiController',
		'a' => 'GetProjectComponents'
));
$router->map ( 'GET', '/Api/projects/[i:id]/releases', array (
		'c' => 'ProjectsApiController',
		'a' => 'getProjectReleases'
));
$router->map ( 'POST', '/Api/components', array (
		'c' => 'ProjectsApiController',
		'a' => 'createProjectComponent'
));
$router->map ( 'GET', '/Api/projects/[i:id]/activities', array (
		'c' => 'ActivityStreamApiController',
		'a' => 'getProjectActivities'
));
$router->map ( 'GET', '/Api/dashboards/[i:id]/activities', array (
		'c' => 'ActivityStreamApiController',
		'a' => 'getDashboardActivities'
));
$router->map ( 'GET', '/Api/users/[i:id]/activities', array (
		'c' => 'ActivityStreamApiController',
		'a' => 'getUserActivities'
));
$router->map ( 'GET', '/Api/components/[i:id]', array (
		'c' => 'ProjectsApiController',
		'a' => 'GetComponent'
) );
$router->map ( 'PUT', '/Api/components/[i:id]', array (
		'c' => 'ProjectsApiController',
		'a' => 'UpdateComponent'
) );
$router->map ( 'GET', '/Api/components/[i:id]/preview', array (
		'c' => 'ProjectsApiController',
		'a' => 'GetComponentPreview'
) );
$router->map ( 'GET', '/Api/documents/[i:id]', array (
		'c' => 'DocumentsApiController',
		'a' => 'getDocumentById'
));
$router->map ( 'GET', '/Api/documents/name/[a:name]', array (
		'c' => 'DocumentsApiController',
		'a' => 'getDocumentByName'
));
$router->map ( 'GET', '/Api/contents/[i:id]', array (
		'c' => 'DocumentsApiController',
		'a' => 'getDocumentContentById'
));
$router->map ( 'POST', '/Api/documents', array (
		'c' => 'DocumentsApiController',
		'a' => 'createDocument'
));
$router->map ( 'POST', '/Api/documents', array (
		'c' => 'DocumentsApiController',
		'a' => 'createDocument'
));
$router->map ( 'GET', '/Api/contents/[i:id]/wiki', array (
		'c' => 'DocumentsApiController',
		'a' => 'getDocumentContentWikiById'
));
$router->map ( 'POST', '/Api/contents', array (
		'c' => 'DocumentsApiController',
		'a' => 'createDocumentContent'
));
$router->map ( 'GET', '/Api/users/[i:id]/badges', array (
		'c' => 'UsersApiController',
		'a' => 'getUserBadges'
));
$router->map ( 'GET', '/Api/badges', array (
		'c' => 'ProjectsApiController',
		'a' => 'getBadges'
));
$router->map ( 'GET', '/Api/projects/[i:id]/progress', array (
		'c' => 'ProjectsApiController',
		'a' => 'getProjectProgress'
));
$router->map ( 'GET', '/Api/projects/[i:id]/users', array (
		'c' => 'ProjectsApiController',
		'a' => 'getProjectUsers'
));
$router->map ( 'GET', '/Api/projects/[i:id]/users/all', array (
		'c' => 'ProjectsApiController',
		'a' => 'getProjectUsersAll'
));
$router->map ( 'GET', '/Api/users/[i:id]/projects', array (
		'c' => 'UsersApiController',
		'a' => 'getUserProjects'
));
$router->map ( 'GET', '/Api/users/[i:id]/preview', array (
		'c' => 'UsersApiController',
		'a' => 'getUserPreview'
));
$router->map( 'POST', '/Api/activities', array (
		'c' => 'ActivityStreamApiController',
		'a' => 'createFeedItem'
));
$router->map( 'POST', '/Api/authenticate', array (
		'c' => 'UsersApiController',
		'a' => 'authenticate'
));
$router->map( 'GET', '/Api/issues', array (
		'c' => 'IssuesApiController',
		'a' => 'getIssues'
));
$router->map ( 'GET', '/Api/issues/key/[:key]', array (
		'c' => 'IssuesApiController',
		'a' => 'getIssueByKey'
) );
$router->map ( 'GET', '/Api/issues/[i:id]/activities', array (
		'c' => 'ActivityStreamApiController',
		'a' => 'getIssueActivities'
) );
$router->map ( 'PATCH', '/Api/issues/key/[:key]', array (
		'c' => 'IssuesApiController',
		'a' => 'updateIssueField'
) );
$router->map ( 'GET', '/Api/actionlog', array (
		'c' => 'ActivityStreamApiController',
		'a' => 'getActionLog'
) );
$router->map ( 'POST', '/Api/projects/[i:id]/createIssuesFromFile', array (
		'c' => 'IssuesApiController',
		'a' => 'createIssuesFromFile'
) );

// match current request
$match = $router->match ();
if (!$match) {
	header("HTTP/1.0 400 Bad Request");
	$requestUri = $_SERVER["REQUEST_URI"];
	echo "{\"error\": \"No matching route found for '".$requestUri."'\"}";
	die();
}
$controller = new $match ["target"] ["c"] ();
$controller->repository = $repository;
// $controller->config = $config;
$controller->contextUser = $contextUser;

// prepare for executing controller
//ob_end_clean();
header('Content-Type: application/json');
header("Connection: close");
ignore_user_abort(true); // just to be safe
set_time_limit(0);
ob_start();

// execute controller
$httpCode = "HTTP/1.0 ";

try {
	$result = $controller->$match["target"]["a"]($match["params"]);
	
	switch($_SERVER['REQUEST_METHOD']) {
		case "POST":
			$httpCode = "HTTP/1.0 201 A new object was born";
			break;
		case "PUT":
			$httpCode = "HTTP/1.0 200 updated";
			break;
		case "DELETE":
			// returning 200 instead of 204 because an entity is returned
			$httpCode = "HTTP/1.0 200 It's gone now...";
			break;
		default: // GET
			$httpCode = "HTTP/1.0 200 There you go";
			break;
			
	}
}
catch (ServiceException $ex)
{
	$httpCode = "HTTP/1.0 500 ServiceException";
	$result = ApiError::createByException($ex);
}
catch (ModelException $ex)
{
	$httpCode = "HTTP/1.0 422 Can't process that object";	
	$result = ApiError::createByException($ex);
}
catch (RepositoryException $ex)
{
	$httpCode = "HTTP/1.0 500 Repository Exception";	
	$result = ApiError::createByException($ex);
}
catch (ManagerException $ex)
{
	$httpCode = "HTTP/1.0 500 Manager Exception";	
	$result = ApiError::createByException($ex);
}
catch (PluginException $ex)
{
	$httpCode = "HTTP/1.0 500 Plugin is causing trouble";	
	$result = ApiError::createByException($ex);
}
catch (WebApiException $ex)
{
	$httpCode = "HTTP/1.0 500 WebApi Exception";	
	$result = ApiError::createByException($ex);
}
catch (ParameterException $ex)
{
	$httpCode = "HTTP/1.0 400 Something's bad about your request";	
	$result = ApiError::createByException($ex);
}
catch (NotFoundException $ex)
{
	$httpCode = "HTTP/1.0 404 Not Found";	
	$result = ApiError::createByException($ex);
}
catch (UnauthorizedException $ex)
{
	$httpCode = "HTTP/1.0 403 This is above your pay grade";	
	$result = ApiError::createByException($ex);
}
catch (Exception $ex)
{
	$httpCode = "HTTP/1.0 500 Grapes in trouble!";	
	$result = ApiError::createByException($ex);
}
	
header($httpCode);
if (is_string($result)) {
	echo $result;
} else {
	if (get_parent_class($result) == "BaseModel") {
		echo $result->toJson();
	} else {
		echo json_encode($result);
	}
}


// done, now do the stuff that the user is not supposed to see
$size = ob_get_length();
header("Content-Length: $size");
ob_end_flush(); // Strange behaviour, will not work
flush(); // Unless both are called !

// and now issue badges which might take a while because of many many badge issuer plugins with lots of custom code
$badgeService = new BadgeService($contextUser, $repository);
$badgeService->issueUnprocessedActionLogItems();
//$myfile = fopen(time().".txt", "w");

?>
