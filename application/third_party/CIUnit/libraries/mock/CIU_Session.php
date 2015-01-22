<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CIU_Session
{
	/**
	 * Initialization parameters
	 *
	 * @var    array
	 */
	public $params = array();

	/**
	 * Valid drivers list
	 *
	 * @var    array
	 */
	public $valid_drivers = array('native', 'cookie');

	/**
	 * Current driver in use
	 *
	 * @var    string
	 */
	public $current = NULL;

	/**
	 * User data
	 *
	 * @var    array
	 */
	protected $userdata = array();

	/**
	 * const
	 */
	const FLASHDATA_KEY = 'flash';
	const FLASHDATA_NEW = ':new:';
	const FLASHDATA_OLD = ':old:';
	const FLASHDATA_EXP = ':exp:';
	const EXPIRATION_KEY = '__expirations';
	const TEMP_EXP_DEF = 300;

	public function __construct(array $params = array())
	{

	}

	/**
	 * Add or change data in the "userdata" array
	 *
	 * @param    mixed     Item name or array of items
	 * @param    string    Item value or empty string
	 * @return    void
	 */
	public function set_userdata($newdata, $newval = '')
	{
		// Wrap params as array if singular
		if(is_string($newdata)){
			$newdata = array($newdata => $newval);
		}

		// Set each name/value pair
		if(count($newdata) > 0){
			foreach ($newdata as $key => $val) {
				$this->userdata[$key] = $val;
			}
		}
	}

	/**
	 * Destroy the current session
	 *
	 * @return    void
	 */
	public function sess_destroy()
	{
		// Just call destroy on driver
		$this->userdata = array();
	}

	/**
	 * has userdata
	 *
	 * @param string $item
	 * @return bool
	 */
	public function has_userdata($item)
	{
		return isset($this->userdata[$item]);
	}

	/**
	 * set flashdata
	 *
	 * @param $newdata
	 * @param string $newval
	 */
	public function set_flashdata($newdata, $newval = '')
	{
		// Wrap item as array if singular
		if(is_string($newdata)){
			$newdata = array($newdata => $newval);
		}

		// Prepend each key name and set value
		if(count($newdata) > 0){
			foreach ($newdata as $key => $val) {
				$flashdata_key = self::FLASHDATA_KEY . self::FLASHDATA_NEW . $key;
				$this->set_userdata($flashdata_key, $val);
			}
		}
	}

	/**
	 * keep flash data
	 *
	 * @param array | string $key
	 */
	public function keep_flashdata($key)
	{

		if(is_array($key)){
			foreach ($key as $k) {
				$this->keep_flashdata($k);
			}

			return;
		}

		// 'old' flashdata gets removed. Here we mark all flashdata as 'new' to preserve it from _flashdata_sweep()
		// Note the function will return NULL if the $key provided cannot be found
		$old_flashdata_key = self::FLASHDATA_KEY . self::FLASHDATA_OLD . $key;
		$value = $this->userdata($old_flashdata_key);

		$new_flashdata_key = self::FLASHDATA_KEY . self::FLASHDATA_NEW . $key;
		$this->set_userdata($new_flashdata_key, $value);
	}

	/**
	 * Fetch a specific flashdata item from the session array
	 *
	 * @param    string    Item key
	 * @return    string
	 */
	public function flashdata($key = NULL)
	{
		if(isset($key)){
			return $this->userdata(self::FLASHDATA_KEY . self::FLASHDATA_OLD . $key);
		}

		// Get our flashdata items from userdata
		$out = array();
		foreach ($this->userdata() as $key => $val) {
			if(strpos($key, self::FLASHDATA_KEY . self::FLASHDATA_OLD) !== FALSE){
				$key = str_replace(self::FLASHDATA_KEY . self::FLASHDATA_OLD, '', $key);
				$out[$key] = $val;
			}
		}

		return $out;
	}

	/**
	 * all userdata
	 *
	 * @return array
	 */
	public function all_userdata()
	{
		return isset($this->userdata) ? $this->userdata : array();
	}

	/**
	 * Fetch a specific item from the session array
	 *
	 * @param    string    Item key
	 * @return    string    Item value or NULL if not found
	 */
	public function userdata($item = NULL)
	{
		if(isset($item)){
			return isset($this->userdata[$item]) ? $this->userdata[$item] : NULL;
		}

		return isset($this->userdata) ? $this->userdata : array();
	}
}

/* End of file CIU_Session.php */
/* Location: ./application/third_party/CIU_Session.php */
