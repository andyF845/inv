<?php
/**
 * CacheProvider interface
 *
 */

/**
 * CacheProvider Interface
  */
interface CacheProvider {
	public function get($key);
	public function set($key,$data,$lifetime = 0);
	public function delete($key);
	public function deleteKeys($keysArray);
}
/**
 * Memecahce implementation for CacheProvider interface.
 */
class MemCacheProvider implements CacheProvider {
	public $active=true;
	private $cache;
	function __construct($server = '127.0.0.1' ,$port = 11211) {
		$this->cache = new MemCache;
		if ($this->cache->connect($server,$port)) {
		} else {
			throw new Exception (ERROR_MEMCACHE_CONNECTION_FAILED);
		}
	}
	function get($key) {
		return $this->cache->get($key);
	}
	function set($key,$data,$lifetime = 0) {
		return $this->cache->set($key,$data,false,$lifetime);
	}
	function delete($key) {
		return $this->cache->delete($key);
	}
	function deleteKeys($keysArray) {
		foreach ($keysArray as $key) {
			$this->delete($key);
		}
	}
	function __destruct() {
		$this->cache->close();
	}	
}

/**
 * Filecache implementation for CacheProvieder interface.
 */
class FileCacheProvider implements CacheProvider {
	public $active=true;
	public $storagePath = './cache/';
	function get($key) {
		if ($this->active) {
			if (!file_exists($this->storagePath)) {
				mkdir($this->storagePath);
			}
			return file_exists($this->storagePath.$key)? file_get_contents($this->storagePath.$key): false;
		} else {
			return "";
		}
	}
	function set($key,$data,$lifetime = 0) {
		if ($this->active)
			return file_put_contents($this->storagePath.$key,$data);
	}
	function delete($key) {
		if ($this->active)
			return file_exists($this->storagePath.$key)? unlink($this->storagePath.$key): false;
	}
	function deleteKeys($keysArray) {
		foreach ($keysArray as $key) {
			$this->delete($key);
		}
	}
}
?>