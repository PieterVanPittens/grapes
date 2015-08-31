<!DOCTYPE html style="height: 100%;">
<!--[if lt IE 7]>      <html lang="en" ng-app="bugeffect" class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html lang="en" ng-app="bugeffect" class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html lang="en" ng-app="bugeffect" class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en" ng-app="grapes" class="no-js"> <!--<![endif]-->

  <head>
  <base href="/ewuki/">
  <title>Grapes</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
    <link rel="icon" type="image/png" sizes="32x32" href="WebClient/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="WebClient/images/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="WebClient/images/favicon-16x16.png">
    
    <link rel="shortcut icon" type="image/x-icon" href="WebClient/images/favicon.ico">
    
<script type="text/javascript" src="WebClient/jquery.min.js"></script>
<script type="text/javascript" src="WebClient/jquery-ui.min.js"></script>
<script type="text/javascript" src="WebClient/angular-1.3.4/angular.min.js"></script>
<script type="text/javascript" src="WebClient/angular-1.3.4/angular-resource.min.js"></script>
<script type="text/javascript" src="WebClient/angular-1.3.4/angular-route.min.js"></script>
<script type="text/javascript" src="WebClient/angular-1.3.4/angular-cookies.min.js"></script>
<script type="text/javascript" src="WebClient/angular-1.3.4/angular-sanitize.min.js"></script>
<script type="text/javascript" src="WebClient/DataTables-1.10.4/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="WebClient/toastr/toastr.min.js"></script>
<script type="text/javascript" src="WebClient/Chart.js-1.0.1/Chart.min.js"></script>
<script type="text/javascript" src="WebClient/bootstrap-3.3.5-dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="WebClient/dropzone.js"></script>
<script type="text/javascript" src="WebClient/infinite-scroll/ng-infinite-scroll.min.js"></script>
<script type="text/javascript" src="WebClient/moment/moment.js"></script>



<link href="WebClient/angular-xeditable-0.1.8/css/xeditable.css" rel="stylesheet">
<script src="WebClient/angular-xeditable-0.1.8/js/xeditable.min.js"></script>


<script type="text/javascript" src="WebClient/gridster/jquery.gridster.with-extras.min.js"></script>
<link rel="stylesheet" href="WebClient/gridster/jquery.gridster.min.css" />

<script type="text/javascript" src="WebClient/grapes.js"></script>


<script type="text/javascript" src="WebClient/app.js"></script>
<script type="text/javascript" src="WebClient/admin-users.js"></script>
<script type="text/javascript" src="WebClient/admin-projects.js"></script>
<script type="text/javascript" src="WebClient/admin-components.js"></script>
<script type="text/javascript" src="WebClient/admin-config.js"></script>
<script type="text/javascript" src="WebClient/home.js"></script>
<script type="text/javascript" src="WebClient/admin-project-dashboard.js"></script>
<script type="text/javascript" src="WebClient/wiki.js"></script>
<script type="text/javascript" src="WebClient/issues.js"></script>
<script type="text/javascript" src="WebClient/issue.js"></script>
<script type="text/javascript" src="WebClient/map.js"></script>
<script type="text/javascript" src="WebClient/action-log.js"></script>
<script type="text/javascript" src="WebClient/project.js"></script>

<script type="text/javascript" src="WebClient/shared/base64Service.js"></script>
<script type="text/javascript" src="WebClient/shared/authService.js"></script>


<link rel="stylesheet" href="WebClient/jquery-ui.css" />
<link rel="stylesheet" href="WebClient/toastr/toastr.min.css">
<link rel="stylesheet" href="WebClient/DataTables-1.10.4/media/css/jquery.dataTables.css">
<link rel="stylesheet" href="WebClient/bootstrap-3.3.5-dist/css/bootstrap.min.css">
<link rel="stylesheet" href="WebClient/bootstrap-3.3.5-dist/css/bootstrap-theme.css">
<link rel="stylesheet" href="WebClient/grapes.css">
</head>
<body>

	<div style="display: table;    position: absolute;    height: 100%;    width: 100%;">
	<div style="display: table-cell;    vertical-align: middle;">
	<div ng-if='!loadingDone' style="width: 150px; margin: 0px auto;">
		
		<img ng-src="WebClient/images/grapes-loading.gif" alt="Loading..."/>
		
	</div>
	</div>
	</div>

<div ng-if='loadingDone' ng-include="'WebClient/shared/navbar-top.html'"></div>

<div id="wrapper" class="toggled" style="height: 100%" ng-if='loadingDone'>



	<div id="sidebar-wrapper" style="height: 100%" ng-controller="StreamController">
	
	<div class="activity-stream">
	<h1>{{stream.title}}</h1>
	
		<div ng-show="hasStreamError">
		Stream could not be loaded
		</div>
		<div class="feed-item" ng-show="isStreamLoaded">
		<div class="feed-content"  ng-if="auth.isLoggedIn() && !auth.isPublicUser()">
			<div class="feed-icon"><a href="#"><img ng-src="{{auth.currentUser.imageMediumUrl}}" alt="{{auth.currentUser.displayName}}"/></a></div>
			<div class="feed-content-text">
			<input id="post-feed" type="text" class="form-control" placeholder="What's up?" ng-model="newFeedItem.feed"><input class="btn btn-default" type="button" value="Post" id="post-feed-button">
			</div>
		</div>
		</div>

		
		
	<div class="feed-item" ng-repeat="feedItem in stream.feedItems" ng-show="isStreamLoaded">
		<div class="feed-content">
			<div class="feed-icon"><a href="#"><img ng-src="{{feedItem.createdBy.imageMediumUrl}}" alt="{{feedItem.createdBy.displayName}}"/></a></div>
			<div class="feed-content-text">
			<a href="#">{{feedItem.createdBy.displayName}}</a> {{feedItem.feed}}
			</div>
		
		</div>
		<!-- 
		<div class="feed-replies">
		<ul>
			<li>31 people like this</li>
			<li ng-repeat="reply in feedItem.replies">
				<div class="reply-content">
				<div class="reply-icon"><a href="#"><img ng-src="{{reply.createdBy.imageSmallUrl}}" alt="{{reply.createdBy.displayName}}"/></a></div>
				<div class="reply-content-text">
				<a href="#">{{reply.createdBy.displayName}}</a> {{reply.feed}}
				</div>
				</div>
			</li>
			<li>
			<div class="reply-content first-reply-content">
				<div class="reply-icon"><a href="#"><img ng-src="{{auth.currentUser.imageMediumUrl}}"/></a></div>
				<div class="reply-content-text"><input type="text"/></div>
			</div>
			</li>
		</ul>
		</div>
		 -->
	</div>
		
	</div>
	</div>

		
	<div class="row">
		<div class="col-lg-12">
			<div ng-view style="height: 100%;" class="container-fluid"></div>
		</div>
	</div>
</div>


</body>
</html>