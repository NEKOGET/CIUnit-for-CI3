<?php

/*
* fooStack, CIUnit for CodeIgniter
* Copyright (c) 2008-2009 Clemens Gruenberger
* Released under the MIT license, see:
* http://www.opensource.org/licenses/mit-license.php
*/

/*
* CodeIgniter source modified for fooStack / CIUnit
*/

/**
 * ============================================================================
 * Note that you will need to update the parent class if you have the class
 * MY_Loader.
 * ============================================================================
 */
class CIU_Loader extends CI_Loader
{

	public $_ci_loaded_files = array();
	public $session = NULL;

	/**
	 * Load class
	 * This function loads the requested class.
	 *
	 * @access  private
	 * @param   string  the item that is being loaded
	 * @param   mixed   any additional parameters
	 * @return  void
	 */
	function _ci_load_class($class, $params = NULL, $object_name = NULL)
	{
		// Get the class name, and while we're at it trim any slashes.
		// The directory path can be included as part of the class name,
		// but we don't want a leading slash
		$class = str_replace(EXT, '', trim($class, '/'));
		// Was the path included with the class name?
		// We look for a slash to determine this
		$subdir = '';
		if(($last_slash = strrpos($class, '/')) !== FALSE){
			// Extract the path
			$subdir = substr($class, 0, $last_slash + 1);

			// Get the filename from the path
			$class = substr($class, $last_slash + 1);
		}
		// We'll test for both lowercase and capitalized versions of the file name
		foreach (array(ucfirst($class), strtolower($class)) as $class) {
			$subclass = APPPATH . 'libraries/' . $subdir . config_item('subclass_prefix') . $class . EXT;
			// Is this a class extension request?
			if(file_exists($subclass)){
				$baseclass = BASEPATH . 'libraries/' . ucfirst($class) . EXT;

				if(!file_exists($baseclass)){
					log_message('error', "Unable to load the requested class: " . $class);
					show_error("Unable to load the requested class: " . $class);
				}

				// Safety:  Was the class already loaded by a previous call?
				if(in_array($subclass, $this->_ci_loaded_files)){
					// Before we deem this to be a duplicate request, let's see
					// if a custom object name is being supplied.  If so, we'll
					// return a new instance of the object
					if(!is_null($object_name)){
						$CI =& get_instance();
						if(!isset($CI->$object_name)){
							return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
						}
					}

					$is_duplicate = TRUE;
					log_message('debug', $class . " class already loaded. Second attempt ignored.");

					return;
				}
				include_once($baseclass);
				include_once($subclass);
				$this->_ci_loaded_files[] = $subclass;

				return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
			}

			// Lets search for the requested library file and load it.
			$is_duplicate = FALSE;
			foreach ($this->_ci_library_paths as $path) {
				$filepath = $path . 'libraries/' . $subdir . $class . EXT;
				// Does the file exist?  No?  Bummer...
				if(!file_exists($filepath)){
					continue;
				}

				// Safety:  Was the class already loaded by a previous call?
				if(in_array($filepath, $this->_ci_loaded_files)){
					// Before we deem this to be a duplicate request, let's see
					// if a custom object name is being supplied.  If so, we'll
					// return a new instance of the object
					if(!is_null($object_name)){
						$CI =& get_instance();
						if(!isset($CI->$object_name)){
							return $this->_ci_init_class($class, '', $params, $object_name);
						}
					}

					$is_duplicate = TRUE;
					log_message('debug', $class . " class already loaded. Second attempt ignored.");
					return;
				}

				include_once($filepath);
				$this->_ci_loaded_files[] = $filepath;
				return $this->_ci_init_class($class, '', $params, $object_name);
			}

		} // END FOREACH
		// One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
		if($subdir == ''){
			$path = strtolower($class) . '/' . $class;

			return $this->_ci_load_class($path, $params);
		}

		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if($is_duplicate == FALSE){
			log_message('error', "Unable to load the requested class: " . $class);
			show_error("Unable to load the requested class: " . $class);
		};
	}

	/**
	 * Instantiates a class
	 *
	 * @access  private
	 * @param   string
	 * @param   string
	 * @return  null
	 */
	function _ci_init_class($class, $prefix = '', $config = FALSE, $object_name = NULL)
	{
		// Is there an associated config file for this class?
		if($config === NULL){
			foreach (array(ucfirst($class), strtolower($class)) as $clsName) {
				if(file_exists(APPPATH . 'config/' . $clsName . EXT)){
					include(APPPATH . 'config/' . $clsName . EXT);
				}
			}
		}

		if($prefix == ''){
			$name = (class_exists('CI_' . $class)) ? 'CI_' . $class : $class;
		} else {
			$name = $prefix . $class;
		}

		// Set the variable name we will assign the class to
		$class = strtolower($class);
		if(is_null($object_name)){
			$classvar = (!isset($this->_ci_varmap[$class])) ? $class : $this->_ci_varmap[$class];
		} else {
			$classvar = $object_name;
		}
		// Instantiate the class
		$CI =& get_instance();
		if($config !== NULL){
			if(!defined('CIUnit_Version')){
				$CI->$classvar = new $name($config);
			} elseif(!isset($CI->$classvar)) {
				//redesignme: check if we have got one already..
				$CI->$classvar = new $name($config);
			}
		} else {
			if(!defined('CIUnit_Version')){
				$CI->$classvar = new $name;
			} elseif(!isset($CI->$classvar)) {
				//redesignme: check if we have got one already..
				$CI->$classvar = new $name($config);
			}
		}
		$this->_ci_classes[$class] = $classvar;
	}

	// --------------------------------------------------------------------

	/**
	 * Autoloader
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, plugins, and helpers to be loaded automatically.
	 *
	 * @access  private
	 * @param   array
	 * @return  void
	 */
	/*
	function _ci_autoloader()
	{
		//enable multiple autoload during tests
		include(APPPATH.'config/autoload'.EXT);
		//include_once(APPPATH.'config/autoload'.EXT);

		if ( ! isset($autoload))
		{
			return FALSE;
		}

		// Autoload packages
		if (isset($autoload['packages']))
		{
			foreach ($autoload['packages'] as $package_path)
			{
				$this->add_package_path($package_path);
			}
		}

		// Load any custom config file
		if (count($autoload['config']) > 0)
		{
			$CI =& get_instance();
			foreach ($autoload['config'] as $key => $val)
			{
				$CI->config->load($val);
			}
		}

		// Autoload helpers and languages
		foreach (array('helper', 'language') as $type)
		{
			if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
			{
				$this->$type($autoload[$type]);
			}
		}

		// A little tweak to remain backward compatible
		// The $autoload['core'] item was deprecated
		if ( ! isset($autoload['libraries']) AND isset($autoload['core']))
		{
			$autoload['libraries'] = $autoload['core'];
		}

		// Load libraries
		if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}

			// Load all other libraries
			foreach ($autoload['libraries'] as $item)
			{
				$this->library($item);
			}
		}

		// Autoload models
		if (isset($autoload['model']))
		{
			$this->model($autoload['model']);
		}
	}
*/

	/*
	* Can load a view file from an absolute path and
	* relative to the CodeIgniter index.php file
		* Handy if you have views outside the usual CI views dir
		*/
	function viewfile($viewfile, $vars = array(), $return = FALSE)
	{
		return $this->_ci_load(
			array(
				'_ci_path' => $viewfile,
				'_ci_vars' => $this->_ci_object_to_array($vars),
				'_ci_return' => $return
			)
		);
	}

	/**
	 * Driver Loader
	 * Loads a driver library.
	 *
	 * @param    string|string[] $library Driver name(s)
	 * @param    array $params            Optional parameters to pass to the driver
	 * @param    string $object_name      An optional object name to assign to
	 * @return    object|bool    Object or FALSE on failure if $library is a string
	 *                                    and $object_name is set. CI_Loader instance otherwise.
	 */
	public function driver($library, $params = NULL, $object_name = NULL)
	{
		//session
		if($library === "Session" || $library === "session"){
			include_once dirname(dirname(__FILE__)) . "/libraries/mock/CIU_Session.php";
			$this->_ci_classes["session"] = "session";
			$CI =& get_instance();
			$CI->session = new CIU_Session();

			return $this;
		}

		if(is_array($library)){
			foreach ($library as $driver) {
				$this->driver($driver);
			}

			return $this;
		} elseif(empty($library)) {
			return FALSE;
		}

		if(!class_exists('CI_Driver_Library', FALSE)){
			// We aren't instantiating an object here, just making the base class available
			require BASEPATH . 'libraries/Driver.php';
		}

		// We can save the loader some time since Drivers will *always* be in a subfolder,
		// and typically identically named to the library
		if(!strpos($library, '/')){
			$library = ucfirst($library) . '/' . $library;

		}

		return $this->library($library, $params, $object_name);
	}
}

/* End of file CIU_Loader.php */
/* Location ./system/application/third_party/CIUnitTest/core/CIU_Loader.php */
