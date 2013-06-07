<?php
namespace MODULEWork;
/*===================================================
*
*
*
* Name: VIEWWork
* Version: 1.0
* License: Apache 2.0
* Author: Christian Gärtner
* Author URL: http://christiangaertner.github.io
* Project URL: https://github.com/ChristianGaertner/MODULEWork
* Description: A basic view loader for PHP
*
*
*
===================================================*/

/**
* VIEWWork
*/
class View
{
	private static $view_path;
	private static $view_ext;

	protected static $results = array();

	protected $data;
	protected $view;
	protected $path;
	

	/**
	* Sets the view folder based on the current loaction
	*/
	public function init($path)
	{
		$path = trim($path, DIRECTORY_SEPARATOR);
		static::$view_path = dirname(realpath(__FILE__)) . DIRECTORY_SEPARATOR . $path. DIRECTORY_SEPARATOR;
	}

	/**
	* Creates the View Object and sets up basic variables
	* @param string $view The name of the view file
	* @param array $data Any data you want to pass to the view, optional
	* @return object MODULEWork\View
	*/
	public static function build($view, $data = array())
	{
		if (defined('APPPATH') && empty(static::$view_path)) {
			static::$view_path = APPPATH . 'views' . DIRECTORY_SEPARATOR;
		} elseif(empty(static::$view_path)) {
			static::$view_path = 'views' . DIRECTORY_SEPARATOR;
		}
		static::$view_ext = '.php';

		return new static($view, $data);
	}

	private function __construct($view, $data)
	{
		$this->data = $data;
		$this->view = $view;
		if (static::exists($view)) {
			$this->path = static::pathbuilder($view);
		} else {
			throw new \Exception('View does not exist.', 1);	
		}
		
	}

	/**
	* Passes data to the view
	* @param string | array $name case: STRING: the name of the variable you can simply echo out in the view; case: ARRAY: key will be the variable and the value its value
	* @param mixed $value only necessary if the first parameter was a string. This will represent the value of the variable.
	* @return object MODULEWork\View
	*/
	public function with($name, $value = null)
	{
		if (is_array($name)) {
			$this->data = array_merge($this->data, $name);
		} else {
			$this->data[$name] = $value;
		}

		return $this;
	}

	/**
	* Renders the evaluated view
	* @param boolean $return TRUE will force the view class to return itself
	* @return object MODULEWork\View (only if true passed to this method)
	*/
	public function render($return = false)
	{
		echo $this->get();
		if ($return) {
			return $this;
		}
	}

	/**
	* returns the evaluated the view
	* @return string The evaluated view
	*/
	public function get()
	{
		return $this->evalview();
	}



	protected function evalview()
	{
		extract($this->data);
		ob_start();
		include $this->path;
		return static::$results[$this->view] = ob_get_clean();
	}

	/**
	* Checks if a view file exists.
	* @param string $view
	* @return boolean
	*/
	public static function exists($view)
	{
		if (is_file(static::pathbuilder($view))) {
			return true;
		}
		return false;
	}

	/**
	* Get the path to a given view on disk.
	* @param string $view
	* @return string
	*/
	public static function path($view, $throwExc=false)
	{
		if (static::exists($view)) {
			return static::pathbuilder($view);
		}
		if ($throwExc) {
			throw new \Exception('View does not exist.', 1);
		}
		
	}


	protected static function pathbuilder($view)
	{
		// Give devs the option to include view files from anywhere on the disk
		if (substr($view, 0, 4) === 'abs:') {
			return substr($view, 4);
		}
		if (strpos($view, '.')) {
			$view = str_replace('.', DIRECTORY_SEPARATOR, $view);
		}
		return $view = static::$view_path . $view . static::$view_ext;
	}


}