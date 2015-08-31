<?php

class GrapesException extends Exception {
}
class ServiceException extends GrapesException {
}
class ModelException extends GrapesException {

	public $modelErrors = array();

	public function addModelError($propertyName, $message) {
		$this->modelErrors[$propertyName] = $message;
	}

	public function hasModelErrors() {
		return count($this->modelErrors) > 0;
	}
}
class RepositoryException extends GrapesException {
}
class ManagerException extends GrapesException {
}
class PluginException extends GrapesException {
}
class WebApiException extends GrapesException {
}
class ParameterException extends GrapesException {
}
/**
 * Ressource was not found
 * this exception will result in 404 error code in API Response
 *
 */
class NotFoundException extends GrapesException {
}
class UnauthorizedException extends GrapesException {
	
}

class ApiError {
	
	public $message;
	public $code;
	public $file;
	public $line;
	public $trace;
	public $modelErrors = null;

	/**
	 * creates an ApiError out of an exception
	 * @param Exception $exception
	 * @return ApiError
	 */
	public static function createByException($exception) {
		$apiError = new ApiError();
		$apiError->message = $exception->getMessage();
		$apiError->code = $exception->getCode();
		$apiError->file = $exception->getFile();
		$apiError->line = $exception->getLine();
		$apiError->trace = $exception->getTraceAsString();
		
		switch (get_class($exception)) {
			case "ModelException":
				$apiError->modelErrors = $exception->modelErrors;
				break;
		}
		return $apiError;
	}
}



/**
 * interface for all models
 *
 */
interface iModel {

	/**
	 * checks if this model is new:
	 * the model does not have a database id yet
	 */
	public function isNew();

	/**
	 * returns id of this model
	 */
	public function getId();

	/**
	 * returns objecttype of this model
	 * @return ObjectTypeEnum
	 */
	public function getObjectType();
	
}

/**
 * abstract base Model
 *
 */
class BaseModel {

	public $isNew;
	
	/**
	 * constructor
	 */
	function __construct() {
		$this->isNew = true;
	}
					
	/**
	 * getter
	 * @param unknown $property
	 */
	public function __get($property) {
            if (property_exists($this, $property)) {
                return $this->$property;
            }
    }

    /**
     * setter
     * @param unknown $property
     * @param unknown $value
     */
    public function __set($property, $value) {
        if (property_exists($this, $property)) {
			$this->$property = $value;
        }
    }

    /**
     * converts object to json
     * @return string
     */
	public function toJson() {
		$array = (array) $this;
		$array["isNew"] = $this->isNew();
		return json_encode($array);
	
	}
	
	/**
	 * Creates a model and populates it with data from a repository row
	 */
	public static function createModelFromRepositoryArray($array) {	
		$rc = new ReflectionClass(get_called_class());
		$model = $rc->newInstance();
		
		foreach($model as $key => $value) {
			if (array_key_exists($key, $array)) {
				$model->$key = $array[$key];
			}
		}
		$model->isDirty = false;
		return $model;
	}
	
	/**
	 * Creates a model and populates it with data from json
	 */
	public static function createModelFromJson($json) {	
		$rc = new ReflectionClass(get_called_class());
		$model = $rc->newInstance();
		
		if ($json == "") {
			return null;
		}
				
		$jsonObject = json_decode($json);
		if ($jsonObject == null) { // json cannot be parsed
			throw new WebApiException("Request does not contain a valid JSON String");
		}
		foreach ($jsonObject AS $key => $value) {
			$model->{$key} = $value;
		}
		$model->isDirty = false;
		return $model;
	}
	
	/**
	 * retrieves objectname including name of class
	 */
	public function getObjectName() {
		return get_called_class().$this->name;		
	}

	/**
	 * creates wikiname for this object
	 */
	public function createWikiName() {
		$wikiName = get_called_class();
		$wikiName .= preg_replace("/[^\d\w-_]/", "", $this->name);
		return $wikiName;		
	}
}

/**
 * represents preview of an object
 *
 */
class ObjectPreview {
	/**
	 * 
	 * @var ObjectType
	 */
	public $objectType;
	public $objectName;
	
	
	public $properties = array();

	/**
	 * adds a property to this preview
	 * @param ObjectPreviewProperty $property
	 */
	public function addProperty($name, $caption, $value) {
		$property = new ObjectPreviewProperty();
		$property->name = $name;
		$property->caption = $caption;
		$property->value = $value;
		$this->properties[] = $property;
	}
}

/**
 * property of an object in a preview
 *
 */
class ObjectPreviewProperty {
	public $name;
	public $caption;
	public $value;
}

class ApiResponse {
	/**
	 * 
	 * @var ValidationState
	 */
	public $validationState;
	
	/**
	 * contains one single object, e.g. project
	 * @var unknown
	 */
	public $object;

	/**
	 * contains list of objects
	 * needs to be named "data" because of datatables control in web client
	 * @var array
	 */
	public $data;
}

/**
 * ActionResult
 */
class ActionResult {

	public function __construct($object, $pointsUpdate, $pointsNewTotal, $message) {
		$this->object = $object;
		$this->pointsUpdate = $pointsUpdate;
		$this->pointsNewTotal = $pointsNewTotal;	
		$this->message = $message;
	}
	
	/**
	 * the points received for this action
	 * @var int
	 */
	public $pointsUpdate = 0;

	/**
	 * new total number of points of the user that triggered this action
	 * @var int
	 */
	public $pointsNewTotal = 0;

	/**
	 * the object that this action was performed on
	 * i.e. the object that was created, updated, deleted
	 * @var unknown
	 */
	public $object;
	
	public $message;
}

/**
 * ValidationState
 *
 */
class ValidationState {

	/**
	 * 
	 * @var ValidationStateType
	 */
	public $validationStateType = ValidationStateType::Success;
	/**
	 * 
	 * @var ValidationResult
	 */
	public $validationResult = ValidationResult::OK;
	public $messages = array();

	public $points = array("update" => 0, "newTotal" => 0);

	/**
	 * adds an error to this ValidationState
	 * @param unknown $propertyName
	 * @param unknown $propertyValue
	 * @param unknown $messageText
	 */
	public function addError($propertyName, $propertyValue, $messageText) {
		$message = new ValidationMessage();
		$message->propertyName = $propertyName;
		$message->propertyValue = $propertyValue;
		$message->message = $messageText;
		$this->validationStateType = ValidationStateType::Error;
		$this->addMessage($message);
	}
	
	public function setPointsUpdate($points) {
		$this->points["update"] = $points;
	}

	public function setPointsNewTotal($points) {
		$this->points["newTotal"] = $points;
	}
	
	/**
	 * checks if this validationstate has errors
	 * @return boolean
	 */
	public function hasErrors() {
		return $this->validationStateType == ValidationStateType::Error;
	}

	/**
	 * 
	 * @param ValidationMessage $message
	 */
	public function addMessage($message) {
		//$this->messages[$message->propertyName] = $message;
		$this->messages[] = $message;
	}
	
	public static function createObjectCreated($messageText, $pointsUpdate, $pointsNewTotal) {
		$validationState = new ValidationState();
		$validationState->validationResult = ValidationResult::OKCreated;
		$validationState->validationStateType = ValidationStateType::Success;
		$validationState->points["update"] = $pointsUpdate;
		$validationState->points["newTotal"] = $pointsNewTotal;
		
		$message = new ValidationMessage();
		//$message->propertyName = $propertyName;
		//$message->propertyValue = $propertyValue;
		$message->message = $messageText;
		$validationState->addMessage($message);		
		return $validationState;
	}
}

/**
 * Validation Results
 * enum
 */
abstract class ValidationResult {
	const OK = 200;
	const OKCreated = 201;
	const OKDeleted = 204;
	const NotModified = 304;
	const BadRequest = 400;
	const Unauthorized = 401;
	const Forbidden = 403;
	const NotFound = 404;
	const Unprocessable = 422;
	const Fatal = 500;
}

/**
 * Validation States
 * enum
 */
abstract class ValidationStateType {
	/**
	 * Error
	 * @var unknown
	 */
	const Error = 1;
	/**
	 * Warning
	 * @var unknown
	 */
	const Warning = 2;
	/**
	 * Success
	 * @var unknown
	 */
	const Success = 3;
	/**
	 * Info
	 * @var unknown
	 */
	const Info = 4;
}


class ValidationMessage {
	public $propertyName;
	public $propertyValue;
	public $message;
}

/**
 * Dashboard
 * @package Core\Models
 *
 */
class Dashboard extends BaseModel implements iModel {
	public $dashboardId;
	public $name;
	public $createdAt;
	public $createdByUserId;
	/**
	 * 
	 * @var ObjectTypeEnum
	 */
	public $objectTypeId;
	public $objectId;
	
	/**
	 * Dashboard or Map
	 * @var ObjectTypeEnum
	 */
	public $dashboardTypeId = ObjectTypeEnum::Dashboard;

	public $parameterValues;
	
	public $tiles;
		
	public function isNew() {
		return !($this->dashboardId > 0);
	}
	
	public function validate(&$validationState) {	
	}
	
	public function getId() {
		return $this->dashboardId;
	}
	public function getObjectType() {
		return ObjectTypeEnum::Dashboard;
	}
}

/**
 * DashboardTile
 * represents an actual Tile on a Dashboard
 * @package Core\Models
 *
 */
class DashboardTile extends BaseModel implements iModel {
	public $dashboardTileId;
	public $tileId;
	public $dashboardId;
	public $width;
	public $height;
	public $col;
	public $row;
	
	
	// join fields
	public $customCssFile;
	public $name;
	
	public $parameterValues = array();
	
	public function isNew() {
		return !($this->dashboardTileId > 0);
	}
	
	public function validate(&$validationState) {	
	}

	public function getId() {
		return $this->dashboardTileId;
	}
	public function getObjectType() {
		return ObjectTypeEnum::DashboardTile;
	}	
}

class Project extends BaseModel implements iModel {
	public $projectId;
	public $identifier;
	public $name;
	public $wikiName;
	public $description;
	public $createdByUserId;
	public $defaultComponentId;
	
	public function isNew() {
		return !($this->projectId > 0);
	}

	public function validate(&$validationState) {

	}
	
	public function getId() {
		return $this->projectId;
	}
	public function getObjectType() {
		return ObjectTypeEnum::Project;
	}
}

class Release extends BaseModel implements iModel {
	public $projectId;
	public $releaseId;
	public $name;
	public $wikiName;
	
	public function isNew() {
		return !($this->releaseId > 0);
	}

	public function validate(&$validationState) {

	}

	public function getId() {
		return $this->releaseId;
	}
	public function getObjectType() {
		return ObjectTypeEnum::Release;
	}
}


class Component extends BaseModel implements iModel {
	public $componentId;
	public $projectId;
	public $name;
	public $wikiName;
	public $description;

	public function isNew() {
		return !($this->componentId > 0);
	}
	
	public function getId() {
		return $this->componentId;
	}
	public function getObjectType() {
		return ObjectTypeEnum::Component;
	}
	
}

class Role extends BaseModel implements iModel {
	public $roleId;
	public $name;
	public $description;

	public function isNew() {
		return !($this->roleId > 0);
	}

	public function validate(&$validationState) {
	}
	
	public function getId() {
		return $this->roleId;
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
	
}

class UserStats extends BaseModel {
	public $numberOfUsers;
}


class Tile extends BaseModel implements iModel {
	public $tileId;
	public $name;
	public $wikiName;
	public $title;
	public $description;
	public $version;
	public $author;
	public $defaultWidth;
	public $defaultHeight;
	public $customCssFile;
		
	/**
	 * parameters of this tile
	 * @var array
	 */
	public $parameters = array();
	
	public function isNew() {
		return !($this->tileId > 0);
	}
	public function getId() {
		return $this->tileId;
	}
	public function getObjectType() {
		return ObjectTypeEnum::Tile;
	}	
}

abstract class ParameterType {
	const Text = 1;
	const YesNo = 2;
}

abstract class ObjectTypeEnum {
	const User = 1;
	const Issue = 2;
	const Project = 3;
	const Component = 4;
	const Story = 5;
	const Badge = 6;
	const Document = 7;
	const Tile = 8;
	const Dashboard = 9;
	const DashboardTile = 10;
	const Release = 11;
	const Generic = 12;
	const BadgeType = 13;
	const Date = 14;
	const Universe = 15;
	const FeedItem = 16;
	const Map = 17;
}

abstract class DocumentTypeEnum {
	const Wiki = 1;
	const Docx = 2;
}

abstract class RoleEnum {
	const Admin = 1;
	const Lead = 2;
	const Member = 3;
}

abstract class StatusTypeEnum {
	const Open = 1;
	const Closed = 2;
}

abstract class StatusEnum {
	const Neww = 1;
	const InProgress= 2;
	const Resolved = 3;
	const Confirmed = 4;
}


abstract class ResolutionEnum {
	const Unresolved = 1;
	const Resolved = 2;
	const Invalid = 3;
	const Declined = 4;
	const Duplicate = 5;
}

/**
 * ObjectType
 */
class ObjectType extends BaseModel {
	public $objectTypeId;
	public $name;
	public $description;
}

/**
 * an object recently touched by a user
 */
class RecentObject extends BaseModel {
	public $recentId;
	public $objectType;
	public $objectId;
	public $userId;
	public $createdAt;
	
	// join fields
	public $description;
	public $name;
	public $subject;
	public $issueNr;
	public $issueTypeId;
	public $statusId;
	
	
	public function __construct() {
		$this->createdAt = time();		
	}
}

class SearchItem extends BaseModel {
	public $searchItemId;
	/**
	 * 
	 * @var ObjectTypeEnum
	 */
	public $objectTypeId;
	public $objectId;
	public $itemText;
}

class SearchResult extends BaseModel {
	public $itemId;
	/**
	 *
	 * @var ObjectTypeEnum
	 */
	public $objectType;
	public $objectId;
	public $text;
}

/**
 * Parameter
 */
class Parameter extends BaseModel implements iModel {
	public $parameterId;
	public $name;
	
	/**
	 * 
	 * @var ParameterType
	 */
	public $type;
	public $defaultValue;

	/**
	 * 
	 * @var ObjectTypeEnum
	 */
	public $objectTypeId;
	
	public function isNew() {
		return !($this->parameterId > 0);
	}
	public function getId() {
		return $this->parameterId;
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}	
}

/**
 * Universe
 */
class Universe extends BaseModel implements iModel {
	public $name;
	public $wikiName;
	
	public function isNew() {
		return false;
	}
	public function getId() {
		return 1;
	}
	public function getObjectType() {
		return ObjectTypeEnum::Universe;
	}
}


/**
 * ParameterValue
 */
class ParameterValue extends BaseModel implements iModel {
	public $parameterValueId;
	public $parameterId;
	/**
	 *
	 * @var ObjectTypeEnum
	 */
	public $objectTypeId;
	public $objectId;

	public $value;
	/**
	 * 
	 * @var Parameter
	 */	
	public $parameter;
	
	public function isNew() {
		return !($this->parameterValueId > 0);
	}
	public function getId() {
		return $this->parameterValueId;
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
}

abstract class IssueTypeEnum {
	const Issue = 1;
	const Story = 2;
	const Incident = 3;
	const Task = 4;
}


/**
 * Issue
 */
class Issue extends BaseModel implements iModel {
	public $issueId;
	public $issueNr;
	public $subject;
	public $wikiName;
	public $description;
	public $projectId;
	public $componentId;
	/**
	 * @var IssueTypeEnum
	 */
	public $issueTypeId;
	/**
	 * @var ResolutionEnum
	 */
	public $resolutionId;

	public $statusId;
	public $createdByUserId;
	public $createdAt;
	public $assignedToUserId;
	
	
	public $projectName;
	public $project;
	public $componentName;
	public $component;
	public $createdBy;
	public $assignedTo;
	public $issueType;
	public $status;
	
	public function isNew() {
		return !($this->issueId > 0);
	}
	public function getId() {
		return $this->issueId;
	}
	public function getObjectType() {
		return ObjectTypeEnum::Issue;
	}
}

class User extends BaseModel implements iModel {
	public $userId;
	public $name;
	public $wikiName;
	public $createdAt;
	public $createdByUserId;
	public $password;
	public $email;
	public $displayName;
	public $points;
	public $isConfirmed;
	public $confirmationKey;
	public $homeDashboardId;
	
	public $imageMicroUrl;
	public $imageSmallUrl;
	public $imageMediumUrl;
	public $imageLargeUrl;

	public function updateImageUrls() {
		$this->imageMicroUrl = "upload/profiles/profile-".$this->userId."-small.jpg";
		$this->imageSmallUrl = "upload/profiles/profile-".$this->userId."-small.jpg";
		$this->imageMediumUrl = "upload/profiles/profile-".$this->userId."-medium.jpg";
		$this->imageLargeUrl = "upload/profiles/profile-".$this->userId."-large.jpg";
	}
	
	public function toJson() {
		$this->updateImageUrls();
		return parent::toJson();
	}
	
	public static function createModelFromRepositoryArray($array) {
		$model = parent::createModelFromRepositoryArray($array);
		$model->updateImageUrls();
		return $model;
	}
	
	
	public function isNew() {
		return !($this->userId > 0);
	}
	public function getId() {
		return $this->userId;
	}
	
	public function validate(&$validationState) {	
	}
	public function getObjectType() {
		return ObjectTypeEnum::User;
	}
}

class ActivityStream {
	
	public $title;
	
	public $feedItems;
}

class FeedItem extends BaseModel implements iModel {
	public $feedItemId;
	public $feed;
	public $createdAt;
	public $createdByUserId;
	public $objectTypeId;
	public $objectId;
	public $targetObjectTypeId;
	public $targetObjectId;
	public $replyToId;
	public $sortParents;
	public $sortReplies;
	public $verb;
	public $numReplies;
	
	public $replies = array();
	
	/**
	 * @var User
	 */
	public $createdBy;
	
	public function isNew() {
		return !($this->feedItemId > 0);
	}
	public function getId() {
		return $this->feedItemId;
	}
	
	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::FeedItem;
	}
}

class UserBadge extends BaseModel {
	public $userId;
	public $badgeId;
	public $actionLogItemId;	
	/**
	 * 
	 * @var Badge
	 */
	public $badge;
	
	/**
	 * 
	 * @var ActionLogItem
	 */
	public $actionLogItem;
	
	public function validate(&$validationState) {	
	}
}

class Action extends BaseModel implements iModel {
	public $actionId;
	public $name;
	public $description;
	public $points;
	/**
	 * Object Type that this Action is related to
	 * @var int
	 */
	public $objectTypeId;

	public function isNew() {
		return !($this->actionId > 0);
	}
	public function getId() {
		return $this->actionId;
	}
	
	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
	
}

class ActionLogItem extends BaseModel implements iModel {
	public $logId;
	public $actionId;
	/**
	 * Object Type that this Action is related to
	 * @var int
	 */
	public $objectTypeId;
	public $objectId;
	public $userId;
	public $timestamp;
	public $points;
	public $isProcessed = 0;

	
	public $action;
	public $user;
	
	
	public function isNew() {
		return !($this->logId > 0);
	}
	public function getId() {
		return $this->logId;
	}
	
	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
	
}

class ActionType extends BaseModel implements iModel {
	public $actionTypeId;
	public $name;
	public $description;

	public function isNew() {
		return !($this->actionTypeIdId > 0);
	}
	public function getId() {
		return $this->actionTypeId;
	}
	
	public function validate(&$validationState) {	
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
}

class Badge extends BaseModel implements iModel {
	public $badgeId;
	public $name;
	public $wikiName;
	public $description;
	public $badgeTypeId;
	public $points;
	public $createdByUserId;
	
	public function isNew() {
		return !($this->badgeId > 0);
	}
	public function getId() {
		return $this->badgeId;
	}
	
	public function validate(&$validationState) {	
	}
	public function getObjectType() {
		return ObjectTypeEnum::Badge;
	}
}

class BadgeIssuer extends BaseModel implements iModel {
	public $issuerId;
	public $name;
	public $description;
	public $objectTypeId;

	public function isNew() {
		return !($this->issuerId > 0);
	}
	public function getId() {
		return $this->issuerId;
	}
	
	public function validate(&$validationState) {	
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
}

class BadgeType extends BaseModel implements iModel {
	public $badgeTypeId;
	public $name;
	public $description;
	
	public function isNew() {
		return !($this->badgeTypeId > 0);
	}
	public function getId() {
		return $this->badgeTypeId;
	}
	
	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::BadgeType;
	}
	
}


/**
 * Document
 *
 */
class Document extends BaseModel implements iModel {
	public $documentId;
	public $objectTypeId;
	public $objectId;
	public $documentTypeId;
	public $version;
	public $latestContentId;
	public $createdByUserId;
	public $createdAt;
	public $name;
	public $wikiName;
	
	/**
	 * not persistent
	 * @var string
	 */
	public $content;
	
	
	public function isNew() {
		return !($this->documentId > 0);
	}
	public function getId() {
		return $this->documentId;
	}

	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::Document;
	}
}

/**
 * Document Content
 *
 */
class DocumentContent extends BaseModel implements iModel {
	public $documentContentId;
	public $createdByUserId;
	public $createdAt;
	public $version;
	public $contentReference;
	public $content;
	public $documentId;

	public function isNew() {
		return !($this->documentContentId > 0);
	}
	public function getId() {
		return $this->documentContentId;
	}

	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
}

/**
 * Document Type
 *
 */
class DocumentType extends BaseModel implements iModel {
	public $documentTypeId;
	public $name;
	public $description;
	
	public function isNew() {
		return !($this->documentTypeId > 0);
	}
	public function getId() {
		return $this->documentTypeId;
	}

	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
}

/**
 * Date
 */
class Date extends BaseModel implements iModel {
	public $dateId;
	/**
	 * ObjectTypeId
	 * @var ObjectTypeEnum
	 */
	public $objectTypeId;
	public $objectId;
	public $summary;
	public $description;
	public $dateStart;
	public $guid;
	
	public function isNew() {
		return !($this->dateId > 0);
	}
	public function getId() {
		return $this->dateId;
	}
	
	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::Date;
	}
}

/**
 * AccessControlItem
 */
class AccessControlItem extends BaseModel implements iModel {
	public $aclId;
	public $projectId;
	public $userId;
	/**
	 * Role
	 * @var RoleEnum
	 */
	public $roleId;

	public function isNew() {
		return !($this->aclId > 0);
	}
	public function getId() {
		return $this->aclId;
	}

	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
}

/**
 * Status
 */
class Status extends BaseModel implements iModel {
	public $statusId;
	public $name;
	public $description;
	/**
	 * @var StatusTypeEnum
	 */
	public $statusType;
	public $sequence;

	
	public function isNew() {
		return !($this->statusId > 0);
	}
	public function getId() {
		return $this->statusId;
	}

	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
}

/**
 * Resolution
 */
class Resolution extends BaseModel implements iModel {
	public $resolutionId;
	public $name;
	public $description;
	
	public function isNew() {
		return !($this->resolutionId > 0);
	}
	public function getId() {
		return $this->resolutionId;
	}

	public function validate(&$validationState) {
	}
	public function getObjectType() {
		return ObjectTypeEnum::Generic;
	}
}



?>