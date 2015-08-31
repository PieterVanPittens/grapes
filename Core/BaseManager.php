<?php
class BaseManager {
	/**
	 *
	 * @var MySqlRepository
	 */
	protected $repository;

	function __construct($repository) {
		$this->repository = $repository;
	}

}
?>