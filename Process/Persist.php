<?php 

namespace Verband\Doctrine\Process;

use Verband\Framework\Structure\Process;
use Verband\Framework\Structure\Context;

/**
 * Prepares Doctrine to integrate with the Framework
 */
class Persist implements Process {

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
		$context->getState('entityManager')->flush();
		return $lastResult;
	}
}