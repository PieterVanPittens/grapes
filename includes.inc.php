<?php

/* core */
require(dirname(__FILE__) . "/Core/BaseManager.php");
require(dirname(__FILE__) . "/Core/SecurityManager.php");

require(dirname(__FILE__) . "/Core/repository.php");
require(dirname(__FILE__) . "/Core/model.php");
require(dirname(__FILE__) . "/Core/plugins.php");
require(dirname(__FILE__) . "/Core/service.php");
require(dirname(__FILE__) . "/Core/DocumentService.php");
require(dirname(__FILE__) . "/Core/DateService.php");
require(dirname(__FILE__) . "/Core/DashboardService.php");
require(dirname(__FILE__) . "/Core/ParameterService.php");
require(dirname(__FILE__) . "/Core/ProjectService.php");
require(dirname(__FILE__) . "/Core/IssueService.php");
require(dirname(__FILE__) . "/Core/ActivityStreamService.php");
require(dirname(__FILE__) . "/Core/SystemManager.php");
require(dirname(__FILE__) . "/Core/UserService.php");


/* markdown */
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/block/CodeTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/block/FencedCodeTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/block/HeadlineTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/block/HtmlTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/block/ListTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/block/QuoteTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/block/RuleTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/block/TableTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/inline/CodeTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/inline/EmphStrongTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/inline/LinkTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/inline/StrikeoutTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/inline/UrlLinkTrait.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/Parser.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/Markdown.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/GithubMarkdown.php");
require(dirname(__FILE__) . "/3rdparty/markdown-1.1.0/MarkdownExtra.php");
require(dirname(__FILE__) . "/Core/GrapesMarkdown.php");

/* REST API */
require(dirname(__FILE__) . "/WebClient/3rdparty/AltoRouter-1.1.0/AltoRouter.php");
require(dirname(__FILE__) . "/WebApi/BaseWebApiController.php");
require(dirname(__FILE__) . "/WebApi/DashboardsApiController.php");
require(dirname(__FILE__) . "/WebApi/UsersApiController.php");
require(dirname(__FILE__) . "/WebApi/ProjectsApiController.php");
require(dirname(__FILE__) . "/WebApi/DocumentsApiController.php");
require(dirname(__FILE__) . "/WebApi/ActivityStreamApiController.php");
require(dirname(__FILE__) . "/WebApi/IssuesApiController.php");





?>