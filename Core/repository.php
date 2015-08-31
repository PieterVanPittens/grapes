<?php
/**
 * MySQL Repository Implementation
 *
 */
class MySqlRepository {

	public $mysqli;

	function __construct($host, $userName, $password, $database) {
		$this->mysqli = new mysqli($host, $userName, $password, $database);
	}

	/**
	 * opens a new transaction
	 */
	public function beginTransaction() {
		$this->mysqli->autocommit(FALSE);
	}
	
	/**
	 * commits current transaction
	 */
	public function commit() {
		$this->mysqli->commit();
		$this->mysqli->autocommit(TRUE);
	}
	
	private function getUser($stmt) {
		$stmt->execute();
		$a = array();
		$stmt->bind_result($a["userId"], $a["name"], $a["createdAt"], $a["password"], $a["created_by_user_id"], $a["displayName"], $a["email"], $a["points"]);
		if ($stmt->fetch()) {
			return User::CreateModelFromRepositoryArray($a);
		} else {
			return null;		
		}	
	}

	/**
	 * gets User by name
	 * @param string $name
	 * @return User
	 */
	public function getUserByName($name) {
		$query = "SELECT user_id, name, wiki_name, created_at, password, display_name, email, points, is_confirmed, confirmation_key, created_by_user_id, home_dashboard_id FROM users where name = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("s", $name);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["userId"], $a["name"], $a["wikiName"], $a["createdAt"], $a["password"], $a["displayName"], $a["email"], $a["points"], $a["isConfirmed"], $a["confirmationKey"], $a["createdByUserId"], $a["homeDashboardId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		if ($stmt->fetch()) {
			return User::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}

	/**
	 * get User by id
	 * @param int $id
	 * @return User
	 */
	public function getUserById($id) {
		$query = "SELECT user_id, name, wiki_name, created_at, password, display_name, email, points, is_confirmed, confirmation_key, created_by_user_id, home_dashboard_id FROM users where user_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["userId"], $a["name"], $a["wikiName"], $a["createdAt"], $a["password"], $a["displayName"], $a["email"], $a["points"], $a["isConfirmed"], $a["confirmationKey"], $a["createdByUserId"], $a["homeDashboardId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return User::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}

	/**
	 * retrieves number of points of user
	 * @param unknown $userId
	 * @return int
	 */
	public function getUserTotalPointsById($userId) {
		$stmt = $this->mysqli->prepare("SELECT points FROM users where user_id = ?");
		$stmt->bind_param("i", $userId);
		$stmt->execute();
		$a = array();
		$stmt->bind_result($a["points"]);
		if ($stmt->fetch()) {
			return $a["points"];
		} else {
			return null;		
		}	
	}
		

	/**
	 * retrieves all Users that have access to a project
	 * @param int $projectId
	 * @return Array
	 */
	public function getProjectUsers($projectId) {
		$query = "select u.user_id, u.name, u.wiki_name, u.display_name, acl.role_id, r.name from access_control_items acl, users u, roles r where r.role_id  = acl.role_id and project_id = ? and u.user_id = acl.user_id ORDER BY u.display_name";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $projectId);
		if ($rc === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$rc = $stmt->bind_result($userId, $name, $wikiName, $displayName, $roleId, $roleName);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$a = array();
			$a["userId"] = $userId;
			$a["name"] = $name;
			$a["wikiName"] = $wikiName;
			$a["displayName"] = $displayName;
			$a["roleId"] = $roleId;
			$a["roleName"] = $roleName;
			$models[] = $a;
		}
		return $models;
	}

	/**
	 * retrieves all Users and their rights in a project
	 * @param int $projectId
	 * @return Array
	 */
	public function getProjectUsersAll($projectId) {
		$query = "select u.user_id, u.name, u.wiki_name, u.display_name, acl.role_id, r.name ";
		$query .= "FROM users u ";
		$query .= "LEFT JOIN (SELECT * FROM access_control_items WHERE project_id = ?) acl on (acl.user_id = u.user_id) ";
		$query .= "LEFT JOIN roles r on (acl.role_id = r.role_id) ";
		$query .= "ORDER BY u.display_name";
		
		
		
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $projectId);
		if ($rc === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$rc = $stmt->bind_result($userId, $name, $wikiName, $displayName, $roleId, $roleName);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$a = array();
			$a["userId"] = $userId;
			$a["name"] = $name;
			$a["wikiName"] = $wikiName;
			$a["displayName"] = $displayName;
			$a["roleId"] = $roleId;
			$a["roleName"] = $roleName;
			$models[] = $a;
		}
		return $models;
	}
	
	
	/**
	 * retrieves all projects that a user has access to
	 * @param int $userId
	 * @return Array
	 */
	public function getUserProjects($userId) {
		$query = "select p.project_id, p.name, p.wiki_name, p.identifier, acl.role_id from access_control_items acl, projects p where p.project_id = acl.project_id and acl.user_id = ? ORDER BY p.name";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $userId);
		if ($rc === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$rc = $stmt->bind_result($projectId, $name, $wikiName, $identifier, $roleId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$a = array();
			$a["projectId"] = $projectId;
			$a["name"] = $name;
			$a["wikiName"] = $wikiName;
			$a["identifier"] = $identifier;
			$a["roleId"] = $roleId;
			$models[] = $a;
		}
		return $models;
	}
	
	
	
	/**
	 * retrieves all Users
	 * @return Array
	 */
	public function getUsers() {
		$query = "SELECT user_id, name, wiki_name, created_at, password, display_name, email, points, is_confirmed, confirmation_key, created_by_user_id, home_dashboard_id FROM users";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["userId"], $a["name"], $a["wikiName"], $a["createdAt"], $a["password"], $a["displayName"], $a["email"], $a["points"], $a["isConfirmed"], $a["confirmationKey"], $a["createdByUserId"], $a["homeDashboardId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$models[] = User::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	/**
	 * updates User home dashboard
	 * @param User $model
	 * @return User
	 */
	public function updateUserHomeDashboardId($model) {
		$query = "UPDATE users SET home_dashboard_id = ? WHERE user_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ii"
				, $model->homeDashboardId
				, $model->userId	);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		return $model;
	}

	/**
	 * creates User
	 * @param User $model
	 * @return User
	 */
	public function createUser($model) {
		$query = "INSERT INTO users (name, wiki_name, created_at, password, display_name, email, points, is_confirmed, confirmation_key, created_by_user_id, home_dashboard_id) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ssisssiisii"
				, $model->name
				, $model->wikiName
				, $model->createdAt
				, $model->password
				, $model->displayName
				, $model->email
				, $model->points
				, $model->isConfirmed
				, $model->confirmationKey
				, $model->createdByUserId
				, $model->homeDashboardId
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->userId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}

	/**
	 * confirms a user
	 * @param User $model
	 * @return User
	 */
	public function confirmUser($model) {
		$query = "UPDATE users SET is_confirmed = 1 WHERE user_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i"
				, $model->userId
				);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		return $model;
	}
	
	/**
	 * adds points to user
	 * @param User $user
	 * @param points $points
	 */
	public function addUserPoints($userId, $points) {
		$query = "UPDATE users set points = points + ? where user_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ii",
				$points, $userId
			);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	}
	
	/**
	 * creates Parameter
	 * @param Parameter $model
	 * @return Parameter
	 */
	public function createParameter($model) {
	
		$query = "INSERT INTO parameters (name, type, default_value, object_type_id) VALUES (?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("sisi"
				, $model->name
				, $model->type
				, $model->defaultValue
				, $model->objectTypeId
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->parameterId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	/**
	 * updates ParameterValue
	 * @param ParameterValue $model
	 * @return ParameterValue
	 */
	public function updateParameterValue($model) {
		$query = "UPDATE parameter_values SET parameter_id = ?, object_type_id = ?, object_id = ?, value = ? WHERE parameter_value_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iiisi"
				, $model->parameterId
				, $model->objectTypeId
				, $model->objectId
				, $model->value
	
				, $model->parameterValueId	);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		return $model;
	}
	
	
	/**
	 * get ParameterValue
	 * @param int $parameterId
	 * @param int $objectTypeId
	 * @param int $id
	 * @return ParameterValue
	 */
	public function getObjectParameterValue($parameterId, $objectTypeId, $id) {
		$stmt = $this->mysqli->prepare("SELECT parameter_value_id, parameter_id, object_type_id, object_id, value FROM parameter_values where parameter_id = ? and object_type_id = ? and object_id = ?");
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iii", $parameterId, $objectTypeId, $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$a = array();
			$stmt->bind_result($a["parameterValueId"], $a["parameterId"], $a["objectTypeId"], $a["objectId"], $a["value"]);
			if ($stmt->fetch()) {
				return ParameterValue::CreateModelFromRepositoryArray($a);
			} else {
				return null;
			}
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	}
	
	
	
	
	/**
	 * retrieves dashboard by name
	 * @param unknown $name
	 * @return Dashboard
	 */
	public function getDashboardByName($name) {
		$stmt = $this->mysqli->prepare("SELECT dashboard_id, name, created_at, created_by_user_id, object_type_id, object_id, dashboard_type_id FROM dashboards where name = ?");
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("s", $name);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $this->getDashboard($stmt);
	}

	/**
	 * retrieves dashboard by id
	 * @param int $id
	 * @return Dashboard
	 */
	public function getDashboardById($id) {
		$stmt = $this->mysqli->prepare("SELECT dashboard_id, name, created_at, created_by_user_id, object_type_id, object_id, dashboard_type_id FROM dashboards where dashboard_id= ?");
		$stmt->bind_param("i", $id);
		
		return $this->getDashboard($stmt);
	}
	
	private function getDashboard($stmt) {
		if ($stmt->execute()) {
			$a = array();
			$stmt->bind_result($a["dashboardId"], $a["name"], $a["createdAt"], $a["createdByUserId"], $a["objectTypeId"], $a["objectId"], $a["dashboardTypeId"]);
			if ($stmt->fetch()) {
				$dashboard = Dashboard::CreateModelFromRepositoryArray($a);
				$stmt->store_result();
				$stmt->close();
				
				// getdashboard tiles
				$query = "SELECT dashboard_tile_id, dashboard_id, t.tile_id, width, height, col, row, name, description, version, author, custom_css_file FROM dashboard_tiles dt, tiles t where t.tile_id = dt.tile_id and dashboard_id = ?";
			
				$stmt2 = $this->mysqli->prepare($query);
				if ($stmt2 === false) {
					throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
				}
				$rc = $stmt2->bind_param("i", $dashboard->dashboardId);
				if ($rc === false) {
					throw new RepositoryException($stmt->error, $stmt->errno);
				}
				if ($stmt2->execute()) {
					$a = array();
					$stmt2->bind_result($a["dashboardTileId"], $a["dashboardId"], $a["tileId"], $a["width"], $a["height"], $a["col"],$a["row"], $a["name"], $a["description"], $a["version"], $a["author"], $a["customCssFile"]);
					$dashboard->tiles = array();
					while ($stmt2->fetch()) {
						$dashboard->tiles[] = DashboardTile::CreateModelFromRepositoryArray($a);
					}
				} else {
					throw new RepositoryException($stmt2->error, $stmt2->errno);
				}
				
				// get dashboard parameters and values
				$parameterValues = $this->getParameterValuesOfObject(ObjectTypeEnum::Dashboard, $dashboard->dashboardId);
				$dashboard->parameterValues = $parameterValues;
				
				return $dashboard;
				
			
			} else {
				return null;
			}
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}

	}

	/**
	 * gets Parameter by name
	 * @param string $name
	 * @return Parameter
	 */
	public function getParameterByName($name) {
		$query = "SELECT parameter_id, name, type, default_value, object_type_id FROM parameters where name = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("s", $name);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["parameterId"], $a["name"], $a["type"], $a["defaultValue"], $a["objectTypeId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		if ($stmt->fetch()) {
			return Parameter::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	
	/**
	 * get user stats
	 * @return UserStats
	 */
	public function getUserStats() {
		$stmt = $this->mysqli->prepare("SELECT COUNT(*) FROM users");
		$stmt->execute();
		$a = array();
		$stmt->bind_result($a["numberOfUsers"]);
		if ($stmt->fetch()) {
			return UserStats::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	
	/**
	 * get dashboardtile by id
	 * @param int $dashboardTileId
	 * @return DashboardTile
	 */
	public function getDashboardTileById($dashboardTileId) {
		$stmt = $this->mysqli->prepare("SELECT * FROM dashboard_tiles where dashboard_tile_id = ?");
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $dashboardTileId);
		if ($rc === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$stmt->bind_result($a["dashboardTileId"], $a["dashboardId"], $a["tileId"], $a["width"], $a["height"], $a["col"], $a["row"]);
		if ($stmt->fetch()) {
			$dashboardTile = DashboardTile::CreateModelFromRepositoryArray($a);
			
			$stmt->store_result();
			$stmt->close();
			$dashboardTile->parameterValues = $this->getParameterValuesOfObject($dashboardTile->getObjectType(), $dashboardTile->getId());
			
			return $dashboardTile;
		} else {
			return null;
		}
	}
	
	/**
	 * updates position and size of a dashboardtile
	 * @param DashboardTile $dashboardTile
	 * @return DashboardTile
	 */
	public function updateDashboardTilePosition($dashboardTile) {
		$stmt = $this->mysqli->prepare("UPDATE dashboard_tiles set height = ?, width = ?, row = ?, col = ? where dashboard_tile_id = ?");
		$stmt->bind_param("iiiii", $dashboardTile->height, $dashboardTile->width, $dashboardTile->row, $dashboardTile->col, $dashboardTile->dashboardTileId);
		$stmt->execute();
		
		return $dashboardTile;
	}

	/**
	 * updates Document 
	 * @param Document $model
	 * @return Document 
	 */
	public function updateDocument($model) {
		$query = "UPDATE documents SET object_type_id = ?, object_id = ?, document_type_id = ?, version = ?, latest_content_id = ?, created_by_user_id = ?, created_at = ?, name = ? WHERE document_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iiiiiiisi"
			, $model->objectTypeId
	, $model->objectId
	, $model->documentTypeId
	, $model->version
	, $model->latestContentId
	, $model->createdByUserId
	, $model->createdAt
	, $model->name
	 
			, $model->documentId	);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		
		return $model;
	}
		
	/**
	 * creates Dashboard
	 * @param Dashboard $model
	 * @return Dashboard
	 */
	public function createDashboard($model) {
	
		$query = "INSERT INTO dashboards (name, created_at, created_by_user_id, object_type_id, object_id, dashboard_type_id) VALUES (?, ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("siiiii"
				, $model->name
				, $model->createdAt
				, $model->createdByUserId
				, $model->objectTypeId
				, $model->objectId
				, $model->dashboardTypeId
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->dashboardId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}

	/**
	 * logs that an object was touched
	 * @param RecentObject $recentObject
	 * @return RecentObject
	 */
	public function createRecentObject($recentObject) {
		
		// check if this object has recently been touched by user anyway
		$stmt = $this->mysqli->prepare("SELECT recent_id FROM recent_objects where object_type = ? and object_id = ? and user_id = ?");
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iii", $recentObject->objectType, $recentObject->objectId, $recentObject->userId);
		if ($rc === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if ($stmt->execute()) {
			$a = array();
			$stmt->bind_result($a["recentId"]);
			if ($stmt->fetch()) {
				$stmt->store_result();
				$stmt->close();
				$stmt2 = $this->mysqli->prepare("DELETE FROM recent_objects where recent_id = ?");
				if ($stmt2 === false) {
					throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
				}
				$rc = $stmt2->bind_param("i", $a["recentId"]);
				if ($rc === false) {
					throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
				}
				if (!$stmt2->execute()) {
					throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
				}
			}
		} else {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		
		
		// insert		
		$query = "INSERT INTO recent_objects (object_type, object_id, user_id, created_at) Values (?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);			
		}
		$rc = $stmt->bind_param("iiii", $recentObject->objectType, $recentObject->objectId, $recentObject->userId, $recentObject->createdAt);
		if ($rc === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);			
		}
		if ($stmt->execute()) {
			$recentObject->recentId = $this->mysqli->insert_id;
			$stmt->store_result();
		} else {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		
		// purge

		// maximum number of recent objects per objecttype per user
		$maxNumberOfRecents = 8; // todo: make it configurable

		$query = "SELECT recent_id FROM recent_objects WHERE object_type = ? AND user_id = ? ORDER BY created_at DESC LIMIT $maxNumberOfRecents, 10000";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);			
		}
		$rc = $stmt->bind_param("ii", $recentObject->objectType, $recentObject->userId);
		if ($rc === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}		
		if ($stmt->execute()) {
			$a = array();
			$stmt->bind_result($a["recentId"]);
			$recentIds = array();
			while ($stmt->fetch()) {
				$recentIds[] = $a["recentId"];
			}
			foreach ($recentIds as $recentId) {
				$stmt = $this->mysqli->prepare("DELETE FROM recent_objects where recent_id = ?");
				$stmt->bind_param("i", $recentId);
				$stmt->execute();
			}
			
		} else {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		
		return $recentObject;
	}
	
	/**
	 * retrieve recent projects
	 * @param unknown $userId
	 * @return multitype:
	 */
	public function getRecentProjects($userId) {
		$recentProjects = array();
		$query = "SELECT o.recent_id, o.object_type, o.object_id, o.user_id, o.created_at, p.name, p.description FROM recent_objects o, projects p WHERE o.object_id = p.project_id and object_type = ? AND o.user_id = ? ORDER BY o.created_at DESC";
		$stmt = $this->mysqli->prepare($query);
		$o = ObjectTypeEnum::Project;
		$stmt->bind_param("ii", $o, $userId);
		if ($stmt->execute()) {
			$a = array();
			$stmt->bind_result($a["recentId"], $a["objectType"], $a["objectId"], $a["userId"], $a["createdAt"], $a["name"], $a["description"]);
			while ($stmt->fetch()) {
				$recentProjects[] = RecentObject::CreateModelFromRepositoryArray($a);
			}
		}
		return $recentProjects;
	}

	/**
	 * retrieve recent issues
	 * @param unknown $userId
	 * @return multitype:
	 */
	public function getRecentIssues($userId) {
		$recents = array();
		$query = "SELECT o.recent_id, o.object_type, o.object_id, o.user_id, o.created_at, i.subject, i.description, i.issue_nr, i.issue_type_id, i.status_id FROM recent_objects o, issues i WHERE o.object_id = i.issue_id and object_type = ? AND o.user_id = ? ORDER BY o.created_at DESC";
		$stmt = $this->mysqli->prepare($query);
		$o = ObjectTypeEnum::Issue;
		$stmt->bind_param("ii", $o, $userId);
		if ($stmt->execute()) {
			$a = array();
			$stmt->bind_result($a["recentId"], $a["objectType"], $a["objectId"], $a["userId"], $a["createdAt"], $a["subject"], $a["description"], $a["issueNr"], $a["issueTypeId"], $a["statusId"]);
			while ($stmt->fetch()) {
				$recents[] = RecentObject::CreateModelFromRepositoryArray($a);
			}
		}
		return $recents;
	}
	
	/**
	 * get Components of a project
	 * @param int $projectId
	 * @return array
	 */
	public function getProjectComponents($projectId) {
		$query = "SELECT component_id, project_id, name, wiki_name, description FROM components where project_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $projectId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["componentId"], $a["projectId"], $a["name"], $a["wikiName"], $a["description"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$r = array();
		while ($stmt->fetch()) {
			$r[] = Component::CreateModelFromRepositoryArray($a);
		}
		return $r;
	}
	
	/**
	 * retrieves all Badges
	 * @return Array
	 */
	public function getBadges() {
		$query = "SELECT badge_id, name, wiki_name, description, badge_type_id, points, created_by_user_id FROM badges";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["badgeId"], $a["name"], $a["wikiName"], $a["description"], $a["badgeTypeId"], $a["points"], $a["createdByUserId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$models[] = Badge::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}	
	
	/**
	 * get Releases by id
	 * @param int $projectId
	 * @return array
	 */
	public function getProjectReleases($projectId) {
		$query = "SELECT release_id, project_id, name, wiki_name FROM releases where project_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $projectId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["releaseId"], $a["projectId"], $a["name"], $a["wikiName"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$r = array();
		while ($stmt->fetch()) {
			$r[] = Release::CreateModelFromRepositoryArray($a);
		}
		return $r;
	}
	
	
	/**
	 * creates Project
	 * @param Project $model
	 * @return Project
	 */
	public function createProject($model) {
		$query = "INSERT INTO projects (identifier, name, wiki_name, description, created_by_user_id) VALUES ( ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ssssi"
				, $model->identifier
				, $model->name
				, $model->wikiName
				, $model->description
				, $model->createdByUserId
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->projectId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	/**
	 * creates Component
	 * @param Component $model
	 * @return Component
	 */
	public function createComponent($model) {
		$query = "INSERT INTO components (project_id, name, wiki_name, description) VALUES ( ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("isss"
				, $model->projectId
				, $model->name
				, $model->wikiName
				, $model->description
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->componentId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
		
	/**
	 * updates a project
	 * @param Project $project
	 * @return Project
	 */
	public function updateProject($project) {
		$query = "UPDATE projects SET name = ?, description = ?, default_component_id = ? WHERE project_id = ?";
		$stmt = $this->mysqli->prepare($query);
		$stmt->bind_param("ssii", $project->name, $project->description, $project->defaultComponentId, $project->projectId);
	
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $project;
	}

	/**
	 * updates Component
	 * @param Component $model
	 * @return Component
	 */
	public function updateComponent($model) {
		$query = "UPDATE components SET name = ?, wiki_name = ?, description = ? WHERE component_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("sssi"
				, $model->name
				, $model->wikiName
				, $model->description
	
				, $model->componentId	);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		return $model;
	}
	
	/**
	 * creates Role
	 * @param Role $model
	 * @return Role
	 */
	public function createRole($model) {
		$query = "INSERT INTO roles (name, description) VALUES ( ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ss"
				, $model->name
				, $model->description
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->roleId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	/**
	 * assigns a role to a user in a project
	 * @param ProjectRole $projectRole
	 * @return ProjectRole
	 */
	public function createProjectRole($projectRole, &$validationState) {
		
		
	}
	
	/**
	 * removes tile from dashboard
	 * @param DashboardTile $dashboardTile
	 */
	public function removeTileFromDashboard($dashboardTile) {
		$query = "DELETE FROM dashboard_tiles WHERE dashboard_tile_id = ?";
		$stmt = $this->mysqli->prepare($query);
		$stmt->bind_param("i", $dashboardTile->dashboardTileId);
		
		$stmt->execute();
		
		// todo: proper error handling
	}

	/**
	 * creates DashboardTile 
	 * @param DashboardTile $model
	 * @return DashboardTile 
	 */	
	public function createDashboardTile($model) {
	
		$query = "INSERT INTO dashboard_tiles (dashboard_id, tile_id, width, height, col, row) VALUES ( ?, ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iiiiii"
			, $model->dashboardId
	, $model->tileId
	, $model->width
	, $model->height
	, $model->col
	, $model->row
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->dashboardTileId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	

	/**
	 * get Component by id
	 * @param int $id
	 * @return Component 
	 */	
	public function getComponentById($id) {
		$query = "SELECT component_id, project_id, name, wiki_name, description FROM components where component_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["componentId"], $a["projectId"], $a["name"], $a["wikiName"], $a["description"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return Component::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}

	/**
	 * get component by key (name + project_id)
	 * @param string $name
	 * @param int $projectId
	 * @return Component
	 */
	public function getComponentByKey($name, $projectId) {
		$query = "SELECT component_id, project_id, name, wiki_name, description FROM components where name = ? and project_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("si", $name, $projectId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["componentId"], $a["projectId"], $a["name"], $a["wikiName"], $a["description"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		if ($stmt->fetch()) {
			return Component::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	

	/**
	 * get Project by id
	 * @param int $id
	 * @return Project 
	 */	
	public function getProjectById($id) {
		$query = "SELECT project_id, identifier, name, wiki_name, description, created_by_user_id, default_component_id FROM projects where project_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["projectId"], $a["identifier"], $a["name"], $a["wikiName"], $a["description"], $a["createdByUserId"], $a["defaultComponentId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return Project::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}

	/**
	 * gets project status
	 * @param int $id
	 * @return array
	 */
	public function getProjectStatus($id) {
		$query = "SELECT count(*), status_id FROM issues where project_id = ? group by status_id";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["count"], $a["statusId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$r = array();
		while ($stmt->fetch()) {
			$r[$a["statusId"]] = array("count" => $a["count"]);
		}
		return $r;
	}
	
	
	/**
	 * get userbadge
	 * @param int $userId
	 * @param int $badgeId
	 * @return UserBadge
	 */
	public function getUserBadge($userId, $badgeId) {
		$stmt = $this->mysqli->prepare("SELECT user_id, badge_id FROM user_badges where user_id = ? and badge_id = ?");
		$stmt->bind_param("ii", $userId, $badgeId);
		$stmt->execute();
		$a = array();
		$stmt->bind_result($a["userId"], $a["badgeId"]);
		if ($stmt->fetch()) {
			return UserBadge::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	
	/**
	 * get UserBadges by userId
	 * @param int $userId
	 * @return UserBadge
	 */
	public function getUserBadges($userId) {
		$query = "SELECT user_id, badge_id, action_log_item_id FROM user_badges where user_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $userId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["userId"], $a["badgeId"], $a["actionLogItemId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		
		$models = array();
		while ($stmt->fetch()) {
			$userBadge = UserBadge::CreateModelFromRepositoryArray($a);
			$models[] = $userBadge;
		}
		
		foreach($models as $userBadge) {
			$userBadge->badge = $this->getBadgeById($userBadge->badgeId);
			$userBadge->actionLogItem = $this->getActionLogItemById($userBadge->actionLogItemId);
		}
	
		return $models;
	}
	
	/**
	 * get Badge by id
	 * @param int $id
	 * @return Badge
	 */
	public function getBadgeById($id) {
		$query = "SELECT badge_id, name, description, badge_type_id, points, created_by_user_id FROM badges where badge_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["badgeId"], $a["name"], $a["description"], $a["badgeTypeId"], $a["points"], $a["createdByUserId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return Badge::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	/**
	 * get ActionLogItem by id
	 * @param int $id
	 * @return ActionLogItem
	 */
	public function getActionLogItemById($id) {
		$query = "SELECT log_id, action_id, object_type_id, object_id, user_id, timestamp, points, is_processed FROM action_log_items where log_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["logId"], $a["actionId"], $a["objectTypeId"], $a["objectId"], $a["userId"], $a["timestamp"], $a["points"], $a["isProcessed"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return ActionLogItem::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	/**
	 * get badge by name
	 * @param string $name
	 * @return Badge
	 */
	public function getBadgeByName($name) {
		$stmt = $this->mysqli->prepare("SELECT badge_id, name, description, badge_type_id, points FROM badges where name = ?");
		$stmt->bind_param("s", $name);
		$stmt->execute();
		$a = array();
		$stmt->bind_result($a["badgeId"], $a["name"], $a["description"], $a["badgeTypeId"], $a["points"]);
		if ($stmt->fetch()) {
			return Badge::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	
	/**
	 * get action by name
	 * @param string $actionName
	 * @return Action
	 */
	public function getActionByName($actionName) {
		$stmt = $this->mysqli->prepare("SELECT * FROM actions where name = ?");
		$stmt->bind_param("s", $actionName);
		$stmt->execute();
		$a = array();
		$stmt->bind_result($a["actionId"], $a["name"], $a["description"], $a["points"], $a["objectTypeId"]);
		if ($stmt->fetch()) {
			return Action::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	

	/**
	 * gets Project by name
	 * @param string $name
	 * @return Project
	 */
	public function getProjectByName($name) {
		$query = "SELECT project_id, identifier, name, wiki_name, description, created_by_user_id, default_component_id FROM projects where name = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("s", $name);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["projectId"], $a["identifier"], $a["name"], $a["wikiName"], $a["description"], $a["createdByUserId"], $a["defaultComponentId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		if ($stmt->fetch()) {
			return Project::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	/**
	 * gets Project by identifier
	 * @param string $identifier
	 * @return Project
	 */
	public function getProjectByIdentifier($identifier) {
		$query = "SELECT project_id, identifier, name, wiki_name, description, created_by_user_id, default_component_id FROM projects where identifier = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("s", $identifier);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["projectId"], $a["identifier"], $a["name"], $a["wikiName"], $a["description"], $a["createdByUserId"], $a["defaultComponentId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		if ($stmt->fetch()) {
			return Project::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	
	
	/**
	 * retrieves all Projects
	 * @return Array
	 */
	public function getProjects() {
		$query = "SELECT project_id, identifier, name, wiki_name, description, created_by_user_id FROM projects";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["projectId"], $a["identifier"], $a["name"], $a["wikiName"], $a["description"], $a["createdByUserId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$models[] = Project::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	
	
	/**
	 * get Tile by id
	 * @param int $id
	 * @return Tile 
	 */	
	public function getTileById($id) {
		$query = "SELECT tile_id, name, wiki_name, title, description, version, author, default_height, default_width, custom_css_file FROM tiles where tile_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["tileId"], $a["name"], $a["wikiName"], $a["title"], $a["description"], $a["version"], $a["author"], $a["defaultHeight"], $a["defaultWidth"], $a["customCssFile"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return Tile::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	

	/**
	 * retrieves all FeedItems of an object
	 * @return Array
	 */
	public function getObjectFeedItems($objectTypeId, $objectId) {
		$query = "SELECT u.name, u.display_name, feed_item_id, feed, object_type_id, object_id, target_object_type_id, target_object_id, fi.created_at, fi.created_by_user_id, reply_to_id, sort_parents, sort_replies, verb FROM feed_items fi, users u where fi.created_by_user_id = u.user_id and fi.target_object_type_id = ? and fi.target_object_id = ? order by sort_parents desc, sort_replies asc";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ii", $objectTypeId, $objectId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["createdByUserName"], $a["createdByUserDisplayName"], $a["feedItemId"], $a["feed"], $a["objectTypeId"], $a["objectId"], $a["targetObjectTypeId"], $a["targetObjectId"], $a["createdAt"], $a["createdByUserId"], $a["replyToId"], $a["sortParents"], $a["sortReplies"], $a["verb"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$feedItem = FeedItem::CreateModelFromRepositoryArray($a);
			$createdBy = new User();
			$createdBy->userId = $a["createdByUserId"];
			$createdBy->displayName = $a["createdByUserDisplayName"];
			$createdBy->name = $a["createdByUserName"];
			$feedItem->createdBy = $createdBy;
	
			$models[] = $feedItem;
		}
		return $models;
	}
	
	
	/**
	 * updates number of replies of feed item
	 * @param int $feedItemId
	 */
	public function updateFeedItemNumReplies($feedItemId) {
		$query = "UPDATE feed_items SET num_replies = num_replies + 1 WHERE feed_item_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i"
				, $feedItemId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	}

	/**
	 * updates sort key for item
	 * @param int $feedItemId
	 * @param string $sortKey
	 */
	public function updateFeedItemSorting($feedItemId, $sortKey) {
		$query = "UPDATE feed_items SET sort_parents = ?, sort_replies = ? WHERE feed_item_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ssi"
				, $sortKey
				, $sortKey
				, $feedItemId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	}
	
	
	/**
	 * creates FeedItem
	 * @param FeedItem $model
	 * @return FeedItem
	 */
	public function createFeedItem($model) {
		$query = "INSERT INTO feed_items (feed, object_type_id, object_id, target_object_type_id, target_object_id, created_at, created_by_user_id, reply_to_id, sort_parents, sort_replies, verb, num_replies) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("siiiiiiiissi"
				, $model->feed
				, $model->objectTypeId
				, $model->objectId
				, $model->targetObjectTypeId
				, $model->targetObjectId
				, $model->createdAt
				, $model->createdByUserId
				, $model->replyToId
				, $model->sortParents
				, $model->sortReplies
				, $model->verb
				, $model->numReplies
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->feedItemId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	

	/**
	 * get FeedItem created_at by id
	 * when creating a reply we need to know information about the parent
	 * @param int $id
	 * @return int
	 */
	public function getFeedItemParentInfoById($id) {
		$query = "SELECT created_at, num_replies FROM feed_items where feed_item_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["createdAt"], $a["numReplies"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return $a;
		} else {
			return null;
		}
	}
	
	/**
	 * get Parameter by id
	 * @param int $id
	 * @return Parameter
	 */
	public function getParameterById($id) {
		$query = "SELECT parameter_id, name, type, default_value, object_type_id FROM parameters where parameter_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["parameterId"], $a["name"], $a["type"], $a["defaultValue"], $a["objectTypeId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return Parameter::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	
	/**
	 * gets Tile by name
	 * @param string $name
	 * @return Tile
	 */
	public function getTileByName($name) {
		$query = "SELECT tile_id, name, title, description, version, author, default_height, default_width, custom_css_file FROM tiles where name = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("s", $name);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["tileId"], $a["name"], $a["title"], $a["description"], $a["version"], $a["author"], $a["defaultHeight"], $a["defaultWidth"], $a["customCssFile"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		if ($stmt->fetch()) {
			$tile = Tile::CreateModelFromRepositoryArray($a);

			$stmt->store_result();
			$stmt->close();
			$tile->parameters = $this->getParametersOfObjectType($tile->getObjectType());
				
			return $tile;
		} else {
			return null;
		}
	}
	
	
	
	
	/**
	 * creates ParameterValue
	 * @param ParameterValue $model
	 * @return ParameterValue
	 */
	public function createParameterValue($model) {	
		$query = "INSERT INTO parameter_values (parameter_id, object_type_id, object_id, value) VALUES (?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iiis"
				, $model->parameterId
				, $model->objectTypeId
				, $model->objectId
				, $model->value
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->parameterValueId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	public function getParametersOfObjectType($objectTypeId) {
		$stmt = $this->mysqli->prepare("SELECT parameter_id, name, type, default_value, object_type_id FROM parameters where object_type_id = ?");
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $objectTypeId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
					throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$stmt->bind_result($a["parameterId"], $a["name"], $a["type"], $a["defaultValue"], $a["objectTypeId"]);
		$parameters = array();
		while ($stmt->fetch()) {
			$parameters[] = Parameter::CreateModelFromRepositoryArray($a);
		}
		return $parameters;
	}
	
	private function getParameterValuesOfObject($objectTypeId, $objectId) {
		$stmt = $this->mysqli->prepare("SELECT parameter_value_id, parameter_id, object_type_id, object_id, value FROM parameter_values where object_type_id = ? AND object_id = ?");
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ii", $objectTypeId, $objectId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$a = array();
			$stmt->bind_result($a["parameterValueId"], $a["parameterId"], $a["objectTypeId"], $a["objectId"], $a["value"]);
			$parameterValues = array();
			$stmt->store_result();
			while ($stmt->fetch()) {
				$parameterValue =  ParameterValue::CreateModelFromRepositoryArray($a);
				$parameter = $this->getParameterById($parameterValue->parameterId);
				$parameterValue->parameter = $parameter;
				$parameterValues[$parameter->name] =$parameterValue;
			}
			return $parameterValues;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	}
		
	
	/**
	 * gets number of issues of a project
	 * @param int $projectId
	 * @return int
	 */
	public function getProjectIssueCount($projectId) {
		$stmt = $this->mysqli->prepare("SELECT COUNT(*) FROM issues where project_id = ?");
		$stmt->bind_param("i", $projectId);
		$stmt->execute();
		$a = array();
		$stmt->bind_result($count);
		if ($stmt->fetch()) {
			return $count;
		} else {
			return null;
		}
	}	
	
	/**
	 * retrieves all Tiles
	 * @return Array
	 */
	public function getTiles() {
		$query = "SELECT tile_id, name, wiki_name, title, description, version, author, default_height, default_width, custom_css_file FROM tiles";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["tileId"], $a["name"], $a["wikiName"], $a["title"], $a["description"], $a["version"], $a["author"], $a["defaultHeight"], $a["defaultWidth"], $a["customCssFile"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$models[] = Tile::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

	/**
	 * retrieves all issuers associated with an object type
	 * @param int $objectTypeId
	 * @return multitype:
	 */
	public function getIssuersByObjectTypeId($objectTypeId) {
		$issuers = array();
		$stmt = $this->mysqli->prepare("SELECT * FROM badge_issuers where object_type_id = ?");
		$stmt->bind_param("i", $objectTypeId);
		$stmt->execute();
		$a = array();
		$stmt->bind_result($a["issuerId"], $a["name"], $a["description"], $a["objectTypeId"]);
		$issuers = array();
		while ($stmt->fetch()) {
			$issuers[] = BadgeIssuer::CreateModelFromRepositoryArray($a);
		}
		return $issuers;
	}
	
	/**
	 * creates an actionlogitem
	 * @param ActionLogItem $actionLogItem
	 * @param ValidationState $validationState
	 * @return ActionLogItem
	 */
	public function createActionLogItem($actionLogItem, &$validationState) {
		if ($actionLogItem->isNew()) {
			$query = "INSERT INTO action_log_items (action_id, object_type_id, object_id, user_id, timestamp, points, is_processed) VALUES (?, ?, ?, ?, ?, ?, ?)";
			$stmt = $this->mysqli->prepare($query);
			$stmt->bind_param("iiiiiii"
					, $actionLogItem->actionId
					, $actionLogItem->objectTypeId
					, $actionLogItem->objectId
					, $actionLogItem->userId
					, $actionLogItem->timestamp
					, $actionLogItem->points
					, $actionLogItem->isProcessed
			);
	
			if ($stmt->execute()) {
				$actionLogItem->logId = $this->mysqli->insert_id;
			} else {
				$validationState->addError("repository", $stmt->errno, $stmt->error);
			}
		} else {
			$query = "";
		}
		return $actionLogItem;
	}

	/**
	 * retrieves all ActionLogItems that have not been processed yet
	 * @return Array
	 */
	public function getUnprocessedActionLogItems() {
		$query = "SELECT log_id, action_id, object_type_id, object_id, user_id, timestamp, points, is_processed FROM action_log_items where is_processed = 0";
		$stmt = $this->mysqli->prepare($query);
		$stmt->execute();
		$a = array();
		$stmt->bind_result($a["logId"], $a["actionId"], $a["objectTypeId"], $a["objectId"], $a["userId"], $a["timestamp"], $a["points"], $a["isProcessed"]);
		$models = array();
		while ($stmt->fetch()) {
			$models[] = ActionLogItem::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	/**
	 * sets ActionLogItem to processed
	 * @param ActionLogItem $model
	 * @param ValidationState
	 * @return ActionLogItem
	 */
	public function setActionLogItemToProcessed($model, &$validationState) {
		$query = "UPDATE action_log_items SET is_processed = 1 WHERE log_id = ?";
		$stmt = $this->mysqli->prepare($query);
		$stmt->bind_param("i"
				, $model->logId
				);
		if (!$stmt->execute()) {
			$validationState->addError("repository", $stmt->errno, $stmt->error);
		}
		return $model;
	}
	
	
	/**
	 * creates a userbadge
	 * @param UserBadge $userBadge
	 * @return UserBadge
	 */
	public function createUserBadge($userBadge) {
		$query = "INSERT INTO user_badges (badge_id, user_id, action_log_item_id) VALUES (?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		
		$stmt->bind_param("iii"
				, $userBadge->badgeId
				, $userBadge->userId
				, $userBadge->actionLogItemId
		);

		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $userBadge;
	}
		
	/**
	 * creates a new ObjectType
	 * @param ObjectType $model
	 * @return ObjectType
	 */
	public function createObjectType($model) {
		$query = "INSERT INTO object_types (name, description) VALUES ( ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		$stmt->bind_param("ss"
				, $model->name
				, $model->description
		);
	
		if ($stmt->execute()) {
			$model->objectTypeId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	

	/**
	 * registers a new action
	 * @param Action $model
	 * @return Action
	 */
	public function createAction($model) {
		$query = "INSERT INTO actions (name, description, points, object_type_id) VALUES ( ?, ?, ?, ?)";
		$rc = $stmt = $this->mysqli->prepare($query);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$stmt->bind_param("ssii"
				, $model->name
				, $model->description
				, $model->points
				, $model->objectTypeId
		);
	
		if ($stmt->execute()) {
			$model->actionId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	
	/**
	 * creates BadgeType 
	 * @param BadgeType $model
	 * @return BadgeType 
	 */	
	public function createBadgeType($model) {
	
		$query = "INSERT INTO badge_types (name, description) VALUES ( ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ss"
			, $model->name
	, $model->description
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->badgeTypeId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}

	/**
	 * creates Badge
	 * @param Badge $model
	 * @return Badge
	 */
	public function createBadge($model) {
		$query = "INSERT INTO badges (name, wiki_name, description, badge_type_id, points, created_by_user_id) VALUES ( ?, ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("sssiii"
				, $model->name
				, $model->wikiName
				, $model->description
				, $model->badgeTypeId
				, $model->points
				, $model->createdByUserId
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->badgeId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	/**
	 * creates BadgeIssuer 
	 * @param BadgeIssuer $model
	 * @return BadgeIssuer 
	 */	
	public function createBadgeIssuer($model) {
	
		$query = "INSERT INTO badge_issuers (name, description, object_type_id) VALUES ( ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ssi"
			, $model->name
	, $model->description
	, $model->objectTypeId
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->badgeIssuerId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	/**
	 * creates Tile
	 * @param Tile $model
	 * @return Tile
	 */
	public function createTile($model) {
		$query = "INSERT INTO tiles (name, wiki_name, title, description, version, author, default_height, default_width, custom_css_file) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ssssssiis"
				, $model->name
				, $model->wikiName
				, $model->title
				, $model->description
				, $model->version
				, $model->author
				, $model->defaultHeight
				, $model->defaultWidth
				, $model->customCssFile
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->tileId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}	

	/**
	 * creates Issue
	 * @param Issue $model
	 * @return Issue
	 */
	public function createIssue($model) {
		$query = "INSERT INTO issues (issue_nr, subject, wiki_name, description, issue_type_id, project_id, component_id, resolution_id, status_id, created_by_user_id, created_at, assigned_to_user_id) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ssssiiiiiiii"
				, $model->issueNr
				, $model->subject
				, $model->wikiName
				, $model->description
				, $model->issueTypeId
				, $model->projectId
				, $model->componentId
				, $model->resolutionId
				, $model->statusId
				, $model->createdByUserId
				, $model->createdAt
				, $model->assignedToUserId
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->issueId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}

	/**
	 * creates SearchItem
	 * @param SearchItem $model
	 * @return SearchItem
	 */
	public function createSearchItem($model) {
		$query = "INSERT INTO search_items (object_type_id, object_id, item_text) VALUES ( ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iii"
				, $model->objectTypeId
				, $model->objectId
				, $model->itemText
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->searchItemId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	/**
	 * get Document by id
	 * @param int $id
	 * @return Document
	 */
	public function getDocumentById($id) {
		$query = "SELECT document_id, object_type_id, object_id, document_type_id, version, latest_content_id, created_by_user_id, created_at, name, wiki_name FROM documents where document_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["documentId"], $a["objectTypeId"], $a["objectId"], $a["documentTypeId"], $a["version"], $a["latestContentId"], $a["createdByUserId"], $a["createdAt"], $a["name"], $a["wikiName"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return Document::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	/**
	 * gets Document by name
	 * @param string $name
	 * @return Document
	 */
	public function getDocumentByName($name) {
		$query = "SELECT document_id, object_type_id, object_id, document_type_id, version, latest_content_id, created_by_user_id, created_at, name, wiki_name FROM documents where name = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("s", $name);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["documentId"], $a["objectTypeId"], $a["objectId"], $a["documentTypeId"], $a["version"], $a["latestContentId"], $a["createdByUserId"], $a["createdAt"], $a["name"], $a["wikiName"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		if ($stmt->fetch()) {
			return Document::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}

	/**
	 * get DocumentContent by id
	 * @param int $id
	 * @return DocumentContent 
	 */	
	public function getDocumentContentById($id) {
		$query = "SELECT document_content_id, created_by_user_id, created_at, version, content_reference, content, document_id FROM document_contents where document_content_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["documentContentId"], $a["createdByUserId"], $a["createdAt"], $a["version"], $a["contentReference"], $a["content"], $a["documentId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return DocumentContent::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	/**
	 * creates Document 
	 * @param Document $model
	 * @return Document 
	 */	
	public function createDocument($model) {
		$query = "INSERT INTO documents (object_type_id, object_id, document_type_id, version, latest_content_id, created_by_user_id, created_at, name, wiki_name) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iiiiiiiss"
			, $model->objectTypeId
	, $model->objectId
	, $model->documentTypeId
	, $model->version
	, $model->latestContentId
	, $model->createdByUserId
	, $model->createdAt
	, $model->name
	, $model->wikiName
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->documentId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	

	/**
	 * creates DocumentContent
	 * @param DocumentContent $model
	 * @return DocumentContent
	 */
	public function createDocumentContent($model) {
		$query = "INSERT INTO document_contents (created_by_user_id, created_at, version, content_reference, content, document_id) VALUES ( ?, ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iiissi"
				, $model->createdByUserId
				, $model->createdAt
				, $model->version
				, $model->contentReference
				, $model->content
				, $model->documentId
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->documentContentId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	
	/**
	 * creates DocumentType 
	 * @param DocumentType $model
	 * @return DocumentType 
	 */	
	public function createDocumentType($model) {
	
		$query = "INSERT INTO document_types (name, description) VALUES ( ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		$stmt->bind_param("ss"
			, $model->name
	, $model->description
		);
	
		if ($stmt->execute()) {
			$model->documentTypeId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	/**
	 * creates Release
	 * @param Release $model
	 * @return Release
	 */
	public function createRelease($model) {
		$query = "INSERT INTO releases (project_id, name, wiki_name) VALUES ( ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iss"
				, $model->projectId
				, $model->name
				, $model->wikiName
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->releaseId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	/**
	 * creates Date
	 * @param Date $model
	 * @return Date
	 */
	public function createDate($model) {
		$query = "INSERT INTO dates (object_type_id, object_id, date_start, summary, description, guid) VALUES ( ?, ?, ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iiisss"
				, $model->objectTypeId
				, $model->objectId
				, $model->dateStart
				, $model->summary
				, $model->description
				, $model->guid
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->dateId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	/**
	 * retrieves all Dates
	 * @return Array
	 */
	public function getDates() {
		$query = "SELECT date_id, object_type_id, object_id, date_start, summary, description, guid FROM dates";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["dateId"], $a["objectTypeId"], $a["objectId"], $a["dateStart"], $a["summary"], $a["description"], $a["guid"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$models[] = Date::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	/**
	 * creates AccessControlItem
	 * @param AccessControlItem $model
	 * @return AccessControlItem
	 */
	public function createAccessControlItem($model) {
		$query = "INSERT INTO access_control_items (project_id, user_id, role_id) VALUES ( ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iii"
				, $model->projectId
				, $model->userId
				, $model->roleId
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->aclId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}
	
	/**
	 * checks access control
	 * does this user have this role in this project? 
	 * @param int $userId
	 * @param int $roleId
	 * @param int $projectId
	 * @return boolean
	 */
	public function checkAccessControl($userId, $roleId, $projectId) {
		$query = "SELECT acl_id FROM access_control_items where user_id = ? AND role_id = ? AND project_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iii", $userId, $roleId, $projectId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["aclId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * creates Status
	 * @param Status $model
	 * @return Status
	 */
	public function createStatus($model) {
		$query = "INSERT INTO statuses (name, description, status_type, sequence) VALUES ( ?, ?, ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ssii"
				, $model->name
				, $model->description
				, $model->statusType
				, $model->sequence
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->statusId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}

	/**
	 * creates Resolution
	 * @param Resolution $model
	 * @return Resolution
	 */
	public function createResolution($model) {
		$query = "INSERT INTO resolutions (name, description) VALUES ( ?, ?)";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ss"
				, $model->name
				, $model->description
		);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->execute()) {
			$model->resolutionId = $this->mysqli->insert_id;
		} else {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		return $model;
	}

	/**
	 * gets first status according to sequence
	 * a new issue will be set to this status
	 * @param int $id
	 * @return Status
	 */
	public function getFirstStatus() {
		$query = "SELECT status_id, name, description, status_type, sequence FROM statuses order by sequence";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["statusId"], $a["name"], $a["description"], $a["statusType"], $a["sequence"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return Status::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}

	
	/**
	 * updates issue meta data
	 * @param Issue $model
	 * @return Issue
	 */
	public function updateIssueMetadata($model) {
		$query = "UPDATE issues SET subject = ?, description = ?, project_id = ?, component_id = ? WHERE issue_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ssiii"
				, $model->subject
				, $model->description
				, $model->projectId
				, $model->componentId
	
				, $model->issueId	);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		return $model;
	}
	
	/**
	 * get Issue by id
	 * @param int $id
	 * @return Issue
	 */
	public function getIssueById($id) {
		$query = "SELECT issue_id, issue_nr, subject, wiki_name, description, issue_type_id, project_id, component_id, resolution_id, status_id, created_by_user_id, created_at, assigned_to_user_id FROM issues where issue_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["issueId"], $a["issueNr"], $a["subject"], $a["wikiName"], $a["description"], $a["issueTypeId"], $a["projectId"], $a["componentId"], $a["resolutionId"], $a["statusId"], $a["createdByUserId"], $a["createdAt"], $a["assignedToUserId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return Issue::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	/**
	 * updates status of Issue
	 * @param Issue $model
	 * @return Issue
	 */
	public function updateIssueStatus($model) {
		$query = "UPDATE issues SET status_id = ? WHERE issue_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ii"
				, $model->statusId
	
				, $model->issueId	);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		return $model;
	}

	/**
	 * updates status + resolution of Issue
	 * @param Issue $model
	 * @return Issue
	 */
	public function updateIssueStatusAndResolution($model) {
		$query = "UPDATE issues SET resolution_id = ?, status_id = ? WHERE issue_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("iii"
				, $model->resolutionId
				, $model->statusId
	
				, $model->issueId	);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	
		return $model;
	}
	
	
	/**
	 * retrieves all Issues that a user has access to
	 * @param int $userId
	 * @param string $filter
	 * @return Array
	 */
	public function getIssues($userId, $filter = "") {
		$query = "SELECT issue_id, issue_nr, subject, wiki_name, description, issue_type_id, i.project_id, component_id, resolution_id, status_id, created_by_user_id, created_at, assigned_to_user_id FROM issues i, access_control_items acl WHERE acl.user_id = ? and acl.project_id = i.project_id ";

		
		
		$query = "SELECT ";
		$query .= "i.issue_id, ";
		$query .= "i.issue_nr, ";
		$query .= "i.subject, ";
		$query .= "i.wiki_name, ";
		$query .= "issue_type_id, ";
		$query .= "i.project_id, ";
		$query .= "i.component_id, ";
		$query .= "i.resolution_id, ";
		$query .= "i.status_id, ";
		$query .= "i.created_by_user_id, ";
		$query .= "i.created_at, ";
		$query .= "i.assigned_to_user_id, ";
		$query .= "p.name, ";
		$query .= "c.name, ";
		$query .= "creators.display_name as createdBy, ";
		$query .= "a.display_name as assignedTo, ";
		$query .= "s.name ";
		$query .= "FROM issues i ";
		$query .= "LEFT JOIN users a on (a.user_id = i.assigned_to_user_id) ";
		$query .= "INNER JOIN access_control_items acl on (acl.project_id = i.project_id) ";
		$query .= "INNER JOIN projects p on (p.project_id = i.project_id) ";
		$query .= "INNER JOIN components c on (c.component_id = i.component_id) ";
		$query .= "INNER JOIN users creators on (creators.user_id = i.created_by_user_id) ";
		$query .= "INNER JOIN statuses s on (s.status_id = i.status_id) ";
		
		$query .= "WHERE acl.user_id = ?";
		if ($filter != "") {
			$query .= " and ($filter)";
		}
		//echo $query;
				
		
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $userId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result(
				$a["issueId"], 
				$a["issueNr"], 
				$a["subject"], 
				$a["wikiName"], 
				$a["issueTypeId"], 
				$a["projectId"], 
				$a["componentId"], 
				$a["resolutionId"], 
				$a["statusId"], 
				$a["createdByUserId"], 
				$a["createdAt"], 
				$a["assignedToUserId"], 
				$a["projectName"], 
				$a["componentName"], 
				$a["createdBy"], 
				$a["assignedTo"],
				$a["status"]
				
				);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$model = Issue::CreateModelFromRepositoryArray($a);
			// resolve issuetype
			switch($a["issueTypeId"]) {
				case 1:
					$model->issueType = "Issue";
					break;
				case 2:
					$model->issueType = "Story";
					break;
				case 3:
					$model->issueType = "Incident";
					break;
				case 4:
					$model->issueType = "Task";
					break;
			}
			
			$models[] = $model;
		}
		return $models;
	}

	/**
	 * retrieves all ActionLogItems
	 * @return Array
	 */
	public function getActionLogItems($chunk) {

		$chunkLength = 10; // todo: configurable
		$offset = $chunk * $chunkLength;
		$count = $chunkLength;
		
		$query = "SELECT ";
		$query .= "ali.log_id, ";
		$query .= "ali.action_id, ";
		$query .= "ali.object_type_id, ";
		$query .= "ali.object_id, ";
		$query .= "ali.user_id, ";
		$query .= "ali.timestamp, ";
		$query .= "ali.points, ";
		$query .= "ali.is_processed, ";
		$query .= "a.name, ";
		$query .= "u.name, ";
		$query .= "u.display_name ";
		$query .= "FROM action_log_items ali, actions a, users u ";
		$query .= "WHERE ali.action_id = a.action_id and ali.user_id = u.user_id ";
		$query .= "order by ali.timestamp DESC, ali.log_id DESC ";
		$query .= "LIMIT $offset, $count";
		
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["logId"], $a["actionId"], $a["objectTypeId"], $a["objectId"], $a["userId"], $a["timestamp"],
				$a["points"],
				$a["isProcessed"],
				$a["actionName"],
				$a["userName"],
				$a["userDisplayName"]
				
				
				);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$model = ActionLogItem::CreateModelFromRepositoryArray($a);
			$action["name"] = $a["actionName"];
			$model->action = $action;

			$user["name"] = $a["userName"];
			$user["displayName"] = $a["userDisplayName"];
			$model->user = $user;
				
			$models[] = $model;
		}
		return $models;
	}
	
	
	
	/**
	 * gets all issues filtered by filter
	 * @param int $userId
	 * @return Array
	 */
	/*
	public function getIssues($userId, $filter = "") {
		$query = "SELECT issue_id, issue_nr, subject, wiki_name, description, issue_type_id, i.project_id, component_id, resolution_id, status_id, created_by_user_id, created_at, assigned_to_user_id FROM issues i, access_control_items acl WHERE acl.user_id = ? and acl.project_id = i.project_id ";

		
		
		$query = "SELECT ";
		$query .= "i.issue_id, ";
		$query .= "i.issue_nr, ";
		$query .= "i.subject, ";
		$query .= "i.wiki_name, ";
		$query .= "issue_type_id, ";
		$query .= "i.project_id, ";
		$query .= "i.component_id, ";
		$query .= "i.resolution_id, ";
		$query .= "i.status_id, ";
		$query .= "i.created_by_user_id, ";
		$query .= "i.created_at, ";
		$query .= "i.assigned_to_user_id, ";
		$query .= "p.name, ";
		$query .= "c.name, ";
		$query .= "creators.display_name as createdBy, ";
		$query .= "assignees.display_name as assignedTo, ";
		$query .= "s.name ";
		$query .= "FROM issues i, access_control_items acl, projects p, components c, users creators, users assignees, statuses s ";
		$query .= "WHERE i.status_id = s.status_id and i.created_by_user_id = creators.user_id and i.assigned_to_user_id = assignees.user_id and c.component_id = i.component_id and p.project_id = i.project_id and acl.user_id = ? and acl.project_id = i.project_id";
		//echo $query;
		
		if ($filter != "") {
			$query .= " and ($filter)";
		}
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $userId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["issueId"], $a["issueNr"], $a["subject"], $a["wikiName"], $a["description"], $a["issueTypeId"], $a["projectId"], $a["componentId"], $a["resolutionId"], $a["statusId"], $a["createdByUserId"], $a["createdAt"], $a["assignedToUserId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$models = array();
		while ($stmt->fetch()) {
			$models[] = Issue::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	*/
	
	
	/**
	 * get Issue by key
	 * @param string $key
	 * @return Issue
	 */
	public function getIssueByKey($key) {
		$query = "SELECT issue_id, issue_nr, subject, wiki_name, description, issue_type_id, project_id, component_id, resolution_id, status_id, created_by_user_id, created_at, assigned_to_user_id FROM issues where issue_nr = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("s", $key);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["issueId"], $a["issueNr"], $a["subject"], $a["wikiName"], $a["description"], $a["issueTypeId"], $a["projectId"], $a["componentId"], $a["resolutionId"], $a["statusId"], $a["createdByUserId"], $a["createdAt"], $a["assignedToUserId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return Issue::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	/**
	 * get AccessControlItem by id
	 * @param int $id
	 * @return AccessControlItem
	 */
	public function getAccessControlItemById($id) {
		$query = "SELECT acl_id, project_id, user_id, role_id FROM access_control_items where acl_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["aclId"], $a["projectId"], $a["userId"], $a["roleId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return AccessControlItem::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	/**
	 * get AccessControlItem
	 * @param int $projectId
	 * @param int $userId
	 * @return AccessControlItem
	 */
	public function getAccessControlItem($projectId, $userId) {
		$query = "SELECT acl_id, project_id, user_id, role_id FROM access_control_items where project_id = ? and user_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("ii", $projectId, $userId);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["aclId"], $a["projectId"], $a["userId"], $a["roleId"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return AccessControlItem::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
	
	/**
	 * deletes AccessControlItem
	 * @param int $id
	 */
	public function deleteAccessControlItem($id) {
		$query = "DELETE FROM access_control_items WHERE acl_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
	}
	
	/**
	 * get Status by id
	 * @param int $id
	 * @return Status
	 */
	public function getStatusById($id) {
		$query = "SELECT status_id, name, description, status_type, sequence FROM statuses where status_id = ?";
		$stmt = $this->mysqli->prepare($query);
		if ($stmt === false) {
			throw new RepositoryException($this->mysqli->error, $this->mysqli->errno);
		}
		$rc = $stmt->bind_param("i", $id);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if (!$stmt->execute()) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		$a = array();
		$rc = $stmt->bind_result($a["statusId"], $a["name"], $a["description"], $a["statusType"], $a["sequence"]);
		if ($rc === false) {
			throw new RepositoryException($stmt->error, $stmt->errno);
		}
		if ($stmt->fetch()) {
			return Status::CreateModelFromRepositoryArray($a);
		} else {
			return null;
		}
	}
}

?>