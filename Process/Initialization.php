<?php 

namespace Doctrine\Process;

use Framework\Process;
use Framework\Context;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\ORM\EntityManager;

/**
 * Prepares Doctrine to integrate with the Framework
 */
class Initialization implements Process {

	/**
	 * An empty initialization.
	 * @param	\Framework\Context
	 * @return	void
	 */
	public function init(Context $context) {}
	
	/**
	 * Ties Doctrine to the Framework
	 * @param	\Framework\Context
	 * @param	mixed
	 * @return	mixed
	 */
	public function execute(Context $context, $lastResult) {
		return $lastResult;
	}
}