<?php 

namespace Verband\Doctrine;

use Doctrine\Common\Cache\ApcCache;
use Verband\Framework\Util\Nomenclature;

use Verband\Framework\Core;
use Verband\Framework\Structure\Package;
use Verband\Framework\Structure\Context;
use Verband\Framework\Structure\Process;
use Verband\Doctrine\Process\Initialization;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\YamlDriver;

/**
 * The Core package.  Currently only established the root framework.init Context.
 */
class Startup extends Package {

	/**
	 * Package initialization currently does nothing.
	 * @return	void 
	 */
	public function init($contexts) {
		$framework = $contexts->getState('framework');
		$environment = $framework->getEnvironment();

		$inDevelopment =
			$environment == Core::ENVIRONMENT_TEST ||
			$environment == Core::ENVIRONMENT_LOCAL || 
			$environment == Core::ENVIRONMENT_DEV;
	
		$config = new Configuration();
		// @TODO Toggle between APC and Memcache
		$cacheDriver = new ApcCache();
		$entityPaths = array();

/*
		if($inDevelopment) {
			$cacheDriver = new \Doctrine\Common\Cache\ArrayCache;
		} else {
			$cacheDriver = $apcCache;
		}
*/

		foreach($framework->getPackages() as $index => $package) {
			// Load ORMS
			$directory = $package->getDirectory() . Core::PATH_ORM_SETTINGS;
			if(file_exists($directory)) {
				$entityPaths[] = $directory;
			}

			// Register custom query functions
			$queryFunctionDirectory = $package->getDirectory() . '/QueryFunction';
			if(is_dir($queryFunctionDirectory)) {
			    $queryFunctions = array_diff(scandir($queryFunctionDirectory), array('.', '..'));
			    foreach($queryFunctions as $queryFunction) {
			        $functionName = basename($queryFunction, '.php');
			        $config->addCustomNumericFunction(strtoupper($functionName), Nomenclature::getVendorAndPackage($package).'\QueryFunction\\'.$functionName);
			    }
			}
		}

		$config->setMetadataDriverImpl(new YamlDriver($entityPaths));
		$config->setMetadataCacheImpl($cacheDriver);
		$config->setQueryCacheImpl($cacheDriver);
		$config->setResultCacheImpl($cacheDriver);

		// @TODO: Figure this out
		$config->setProxyDir($framework->getPath(Core::PATH_PACKAGES) . '/Doctrine/Proxies');
		$config->setProxyNamespace('Doctrine\Proxies');

		if ($inDevelopment) {
			$config->setAutoGenerateProxyClasses(true);
		} else {
			$config->setAutoGenerateProxyClasses(false);
		}

		$databaseConfig = $framework->getSetting('Application[database]');
		if(!isset($databaseConfig[$environment])) {
		    $environment = 'main';
		}

		$connectionOptions = array(
			'driver'   => $databaseConfig[$environment]['driver'],
			'user'     => $databaseConfig[$environment]['user'],
			'password' => $databaseConfig[$environment]['password'],
			'dbname'   => $databaseConfig[$environment]['name']
		);

		// @TODO: Allow many entity managers
		$contexts->setState('entityManager', EntityManager::create($connectionOptions, $config));
	}

	/**
	 * Set custom namespaces
	 * @see Framework.Package::getNamespaces()
	 * @return array
	 */
	public function getNamespaces($packagesPath) {
		return array(
			'Doctrine\DBAL\Migrations'  => $packagesPath . '/doctrine/migrations/lib/{>-1}',
			'Doctrine'					=> $packagesPath . '/{first.lc}/{1.lc}/lib/{>-1}'
		);
	}
}