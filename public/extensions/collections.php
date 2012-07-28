<?php

if(extensions::isSelected('collections')) {
	/**
	* Collections Extension
	*
	* @package Scabbia
	* @subpackage UtilityExtensions
	*
	* @todo revisions
	*/
	class collections {
		public static function extension_info() {
			return array(
				'name' => 'collections',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}
	}

	/**
	* Collection Class
	*
	* @package Scabbia
	* @subpackage UtilityExtensions
	*/
	class collection implements ArrayAccess, IteratorAggregate {
		public $id;
		public $tag;

		public function __construct($tArray = null) {
			$this->id = null;
			$this->tag = array();

			$this->tag['items'] = is_array($tArray) ? $tArray : array();
			$this->tag['class'] = get_class($this);
		}

		public function add($uItem) {
			$this->tag['items'][] = $uItem;
		}

		public function addKey($uKey, $uItem) {
			$this->tag['items'][$uKey] = $uItem;
		}

		public function addRange($uItems) {
			foreach($uItems as &$tItem) { //SPD (array)$uItems cast
				$this->add($tItem);
			}
		}

		public function addKeyRange($uItems) {
			foreach($uItems as $tKey => &$tItem) { //SPD (array)$uItems cast
				$this->addKey($tKey, $tItem);
			}
		}

		public function keyExists($uKey, $uNullValue = true) {
			if($uNullValue) {
				return array_key_exists($uKey, $this->tag['items']);
			}

			return isset($this->tag['items'][$uKey]);
		}

		public function contains($uItem) {
			foreach($this->tag['items'] as &$tItem) {
				if($uItem == $tItem) {
					return true;
				}
			}

			return false;
		}


		public function count($uItem = null) {
			if(!isset($uItem)) {
				return count($this->tag['items']);
			}

			$tCounted = 0;
			foreach($this->tag['items'] as &$tItem) {
				if($uItem != $tItem) {
					continue;
				}

				$tCounted++;
			}

			return $tCounted;
		}

		public function countRange($uItems) {
			$tCounted = 0;

			foreach($uItems as &$tItem) { //SPD (array)$uItems cast
				$tCounted += $this->count($tItem);
			}

			return $tCounted;
		}

		public function remove($uItem, $uLimit = null) {
			$tRemoved = 0;

			foreach($this->tag['items'] as $tKey => &$tVal) {
				if($uItem != $tVal) {
					continue;
				}

				$tRemoved++;
				unset($this->tag['items'][$tKey]);

				if(isset($uLimit) && $uLimit >= $tRemoved) {
					break;
				}
			}

			return $tRemoved;
		}

		public function removeRange($uItems, $uLimitEach = null, $uLimitTotal = null) {
			$tRemoved = 0;

			foreach($uItems as &$tItem) { //SPD (array)$uItems cast
				$tRemoved += $this->remove($tItem, $uLimitEach);

				if(isset($uLimitTotal) && $uLimitTotal >= $tRemoved) {
					break;
				}
			}

			return $tRemoved;
		}

		public function removeKey($uKey) {
			if(!$this->keyExists($uKey, true)) {
				return 0;
			}

			unset($this->tag['items'][$uKey]);
			return 1;
		}

		public function removeIndex($uIndex) {
		// todo: seek with iterator
			if($this->count < $uIndex) {
				return 0;
			} //SPD (int)$uIndex cast

			reset($this->tag['items']);
			for($i = 0;$i < $uIndex;$i++) {
				next($this->tag['items']);
			}

			unset($this->tag['items'][key($this->tag['items'])]);

			return 1;
		}

		public function chunk($uSize, $uPreserveKeys = false) {
			$tArray = array_chunk($this->tag['items'], $uSize, $uPreserveKeys);

			return new $this->tag['class'] ($tArray);
		}

		public function combineKeys($uArray) {
			if(is_subclass_of($uArray, 'collection')) {
				$uArray = $uArray->toArrayRef();
			}

			$tArray = array_combine($uArray, $this->tag['items']);

			return new $this->tag['class'] ($tArray);
		}

		public function combineValues($uArray) {
			if(is_subclass_of($uArray, 'collection')) {
				$uArray = $uArray->toArrayRef();
			}

			$tArray = array_combine($this->tag['items'], $uArray);

			return new $this->tag['class'] ($tArray);
		}

		public function countValues() {
			$tArray = array_count_values($this->tag['items']);

			return new $this->tag['class'] ($tArray);
		}

		public function diff() {
			$uParms = array(&$this->tag['items']);
			foreach(func_get_args() as $tItem) {
				if(is_subclass_of($tItem, 'collection')) {
					$uParms[] = $tItem->toArrayRef();
				}
				else {
					$uParms[] = $tItem;
				}
			}

			$tArray = call_user_func_array('array_diff', $uParms);

			return new $this->tag['class'] ($tArray);
		}

		public function filter($uCallback) {
			$tArray = array_filter($this->tag['items'], $uCallback);

			return new $this->tag['class'] ($tArray);
		}

		public function flip() {
			$tArray = array_flip($this->tag['items']);

			return new $this->tag['class'] ($tArray);
		}

		public function intersect() {
			$uParms = array(&$this->tag['items']);

			foreach(func_get_args() as $tItem) {
				if(is_subclass_of($tItem, 'collection')) {
					$uParms[] = $tItem->toArrayRef();
				}
				else {
					$uParms[] = $tItem;
				}
			}

			$tArray = call_user_func_array('array_intersect', $uParms);

			return new $this->tag['class'] ($tArray);
		}

		public function keys() {
			$tArray = array_keys($this->tag['items']);

			return new $this->tag['class'] ($tArray);
		}

		public function map($uCallback) {
			$tArray = array_map($uCallback, $this->tag['items']);

			return new $this->tag['class'] ($tArray);
		}

		public function mergeRecursive() {
			$uParms = array(&$this->tag['items']);

			foreach(func_get_args() as $tItem) {
				if(is_subclass_of($tItem, 'collection')) {
					$uParms[] = $tItem->toArrayRef();
				}
				else {
					$uParms[] = $tItem;
				}
			}

			$tArray = call_user_func_array('array_merge_recursive', $uParms);

			return new $this->tag['class'] ($tArray);
		}

		public function merge() {
			$uParms = array(&$this->tag['items']);
			foreach(func_get_args() as $tItem) {
				if(is_subclass_of($tItem, 'collection')) {
					$uParms[] = $tItem->toArrayRef();
				}
				else {
					$uParms[] = $tItem;
				}
			}

			$tArray = call_user_func_array('array_merge', $uParms);

			return new $this->tag['class'] ($tArray);
		}

		public function pad($uSize, $uValue) {
			$tArray = array_pad($this->tag['items'], $uSize, $uValue);

			return new $this->tag['class'] ($tArray);
		}

		public function pop() {
			return array_pop($this->tag['items']);
		}

		public function product() {
			return array_product($this->tag['items']);
		}

		public function push() {
			$uParms = array(&$this->tag['items']);

			foreach(func_get_args() as $tItem) {
				$uParms[] = $tItem;
			}

			return call_user_func_array('array_push', $uParms);
		}

		public function first() {
			reset($this->tag['items']);

			return $this->current();
		}

		public function last() {
			return end($this->tag['items']);
		}

		public function current() {
			$tValue = current($this->tag['items']);

			if($tValue === false) {
				return null;
			}

			return $tValue;
		}

		public function next() {
			$tValue = $this->current();
			next($this->tag['items']);

			return $tValue;
		}

		public function clear() {
			$this->tag['items'] = array();
//			$this->internalIterator->rewind();
		}

	// for array access, $items
		public function offsetExists($uId) {
			return $this->keyExists($uId);
		}

		public function offsetGet($uId) {
			return $this->tag['items'][$uId];
		}

		public function offsetSet($uId, $uValue) {
			$this->tag['items'][$uId] = $uValue;
		}

		public function offsetUnset($uId) {
			$this->removeKey($uId);
		}

	// for iteration access
		public function getIterator() {
			return new ArrayIterator($this->tag['items']);
		}

		public function toCollection() {
			return new collection($this->tag['items']);
		}

		public function toArray() {
			return $this->tag['items'];
		}

		public function &toArrayRef() {
			return $this->tag['items'];
		}

		public function toString($uSeperator = '') {
			return implode($uSeperator, $this->tag['items']);
		}
	}
	
	/**
	* Xml Collection Class
	*
	* @package Scabbia
	* @subpackage UtilityExtensions
	*/
	class xmlCollection extends collection {
		public static function fromString($uString) {
			$tTemp = new xmlCollection();
			$tTemp->add(simplexml_load_string($uString));

			return $tTemp;
		}

		public static function fromFile($uFile) {
			$tTemp = new xmlCollection();
			$tTemp->add(simplexml_load_file($uFile));

			return $tTemp;
		}

		public static function fromFiles() {
			$uFiles = func_get_args();
			if(is_array($uFiles[0])) {
				$uFiles = $uFiles[0];
			}

			$tTemp = new xmlCollection();
			foreach($uFiles as &$tFile) {
				$tTemp->add(simplexml_load_file($tFile));
			}

			return $tTemp;
		}

		public static function fromFileScan($uPattern) {
			$tSep = quotemeta(DIRECTORY_SEPARATOR);
			$tPos = strrpos($uPattern, $tSep);

			if($tSep != '/' && $tPos === false) {
				$tSep = '/';
				$tPos = strrpos($uPattern, $tSep);
			}

			if($tPos !== false) {
				$tPattern = substr($uPattern, $tPos + strlen($tSep));
				$tPath = substr($uPattern, 0, $tPos + strlen($tSep));
			}
			else {
				$tPath = $uPattern;
				$tPattern = '';
			}

			$tTemp = new xmlCollection();
			$tHandle = new DirectoryIterator($tPath);
			$tPatExists = (strlen($uPattern) > 0);

			for(;$tHandle->valid();$tHandle->next()) {
				if(!($tHandle->isFile())) {
					continue;
				}

				$tFile = $tHandle->current();
				if($tPatExists && !fnmatch($tPattern, $tFile)) {
					continue;
				}

				$tTemp->add(simplexml_load_file($tPath . $tFile));
			}

			return $tTemp;
		}

		public static function fromSimplexml($uObject) {
			$tTemp = new xmlCollection();
			$tTemp->add($uObject);

			return $tTemp;
		}

		public static function fromDom($uDom) {
			$tTemp = new xmlCollection();
			$tTemp->add(simplexml_import_dom($uDom));

			return $tTemp;
		}
	}

	/**
	* File Collection Class
	*
	* @package Scabbia
	* @subpackage UtilityExtensions
	*/
	class fileCollection extends collection {
		public static function fromFile($uFile) {
			$tTemp = new fileCollection();
			$tTemp->add($uFile);

			return $tTemp;
		}

		public static function fromFiles() {
			$uFiles = func_get_args();
			if(is_array($uFiles[0])) {
				$uFiles = $uFiles[0];
			}

			$tTemp = new fileCollection();

			foreach($uFiles as &$tFile) {
				$tTemp->add($tFile);
			}

			return $tTemp;
		}

		public static function fromFileScan($uPattern) {
			$tSep = quotemeta(DIRECTORY_SEPARATOR);
			$tPos = strrpos($uPattern, $tSep);

			if($tSep != '/' && $tPos === false) {
				$tSep = '/';
				$tPos = strrpos($uPattern, $tSep);
			}

			if($tPos !== false) {
				$tPattern = substr($uPattern, $tPos + strlen($tSep));
				$tPath = substr($uPattern, 0, $tPos + strlen($tSep));
			}
			else {
				$tPath = $uPattern;
				$tPattern = '';
			}

			$tTemp = new fileCollection();
			$tHandle = new DirectoryIterator($tPath);
			$tPatExists = (strlen($uPattern) > 0);

			for(;$tHandle->valid();$tHandle->next()) {
				if(!($tHandle->isFile())) {
					continue;
				}

				$tFile = $tHandle->current();

				if($tPatExists && !fnmatch($tPattern, $tFile)) {
					continue;
				}

				$tTemp->add($tPath . $tFile);
			}

			return $tTemp;
		}
	}
}

?>