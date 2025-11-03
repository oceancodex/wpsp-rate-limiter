<?php

namespace WPSPCORE\RateLimiter;

use Symfony\Component\Cache\Adapter\DoctrineDbalAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use WPSPCORE\Base\BaseInstances;
use WPSPCORE\Cache\Adapter;

/**
 * @property DoctrineDbalAdapter|FilesystemAdapter|MemcachedAdapter|RedisAdapter|null $adapter
 */
class RateLimiter extends BaseInstances {

	protected $adapter          = null;
	protected $limiters         = null;
	protected $key              = null;
	protected $store            = null;
	protected $connectionParams = null;

	/*
	 *
	 */

	public function prepare() {
		$configs = $this->funcs->_config('rate-limiter');

//		if (!$this->adapter) {
//			$this->adapter = (new Adapter(
//				$this->funcs->_getMainPath(),
//				$this->funcs->_getRootNamespace(),
//				$this->funcs->_getPrefixEnv()
//			))->init($this->store, $this->connectionParams);
//		}

		foreach ($configs as $configKey => $configData) {
			$this->limiters[$configKey] = (new RateLimiterFactory(
				$configData,
				new CacheStorage($this->adapter)
			))->create($this->getKey());
		}

		return $this;
	}

	/*
	 *
	 */

	public function global() {
		$globalRateLimiter = $this->funcs->_getAppShortName();
		$globalRateLimiter = $globalRateLimiter . '_rate_limiter';
		global ${$globalRateLimiter};
		${$globalRateLimiter} = $this;
		return $this;
	}

	/*
	 *
	 */

	public function getKey() {
		return $this->key ?? $this->request->getClientIp();
	}

	public function setKey($key = null) {
		if ($key) $this->key = $key;
	}

}