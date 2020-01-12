<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2019, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('APP_ENTRY_PASS') OR exit('No direct script access allowed');

/**
 * Config Class
 *
 * This class contains functions that enable config files to be managed
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Config {

	/**
	 * List of all loaded config values
	 *
	 * @var	array
	 */
	public $config = array();

	/**
	 * List of all loaded config files
	 *
	 * @var	array
	 */
	public $is_loaded =	array();

	/**
	 * List of paths to search when trying to load a config file.
	 *
	 * @used-by	CI_Loader
	 * @var		array
	 */
	public $_config_paths =	array(APPPATH);

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * Sets the $config data from the primary config.php file as a class variable.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->config[CONFIG_PRIMARY_SECTION] =& get_config();

		// Set the base_url automatically if none was provided
		if (empty($this->item('base_url')))
		{
			if (isset($_SERVER['SERVER_ADDR']))
			{
				if (strpos($_SERVER['SERVER_ADDR'], ':') !== FALSE)
				{
					$server_addr = '['.$_SERVER['SERVER_ADDR'].']';
				}
				else
				{
					$server_addr = $_SERVER['SERVER_ADDR'];
				}

				$base_url = (is_https() ? 'https' : 'http').'://'.$server_addr
					.substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
			}
			else
			{
				$base_url = 'http://localhost/';
			}

			$this->create_item('base_url', $base_url);
		}

		log_message('info', 'Config Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Load Config File
	 *
	 * @param	string	$file			Configuration file name
	 * @param	bool	$fail_gracefully	Whether to just return FALSE or display an error message
	 * @return	bool	TRUE if the file was loaded correctly or FALSE on failure
	 */
	public function load($file = '', $fail_gracefully = FALSE)
	{
		$file = ($file === '') ? 'config' : str_replace('.php', '', $file);
		$loaded = FALSE;

		foreach ($this->_config_paths as $path)
		{
			foreach (array($file, ENVIRONMENT.DIRECTORY_SEPARATOR.$file) as $location)
			{
				$file_path = $path.'config/'.$location.'.php';
				if (in_array($file_path, $this->is_loaded, TRUE))
				{
					return TRUE;
				}

				if ( ! file_exists($file_path))
				{
					continue;
				}

				$section  = NULL;
				$config   = NULL;

				include($file_path);

				if ( ! isset($section))
				{
					$section = CONFIG_PRIMARY_SECTION;
				}

				if ((($file === 'config') && ($section !== CONFIG_PRIMARY_SECTION))
					OR ( ! isset($config))
					OR ( ! is_array($config))
				) {
					if ($fail_gracefully === TRUE)
					{
						return FALSE;
					}

					show_error('Your '.$file_path.' file does not appear to contain a valid configuration array.');
				}

				foreach ($config as $key => $value)
				{
					$this->create_item(
						array(
							'section'    => $section,
							'parameter'  => $key,
						),
						$value
					);
				}

				$this->is_loaded[] = $file_path;

				$loaded = TRUE;

				log_message('debug', 'Config file loaded: '.$file_path);
			}
		}

		if ($loaded === TRUE)
		{
			return TRUE;
		}
		elseif ($fail_gracefully === TRUE)
		{
			return FALSE;
		}
		else
		{
			show_error('The configuration file '.$file.'.php does not exist.');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Format the item $item_array
	 *
	 * @param	array	$item_array
	 * @return	array
	 */
	protected function _format_item_array($item_array)
	{
		$retour = array();

		$retour['parameter'] = $item_array['parameter'];

		if (isset($item_array['section']))
		{
			$retour['section'] = $item_array['section'];
		}
		else
		{
			$retour['section'] = CONFIG_PRIMARY_SECTION;
		}

		return $retour;
	}

	// --------------------------------------------------------------------

	/**
	 * Parse the item $item
	 *
	 * @param	string	$item
	 * @return	array
	 */
	protected function _parse_item($item)
	{
		$item_array = explode('.', $item, 2);

		$parameter = array_pop($item_array);

		if (count($item_array) === 1)
		{
			$section = array_shift($item_array);
		}
		else
		{
			$section = CONFIG_PRIMARY_SECTION;
		}

		return array(
			'section'    => $section,
			'parameter'  => $parameter,
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Analyze the item $item
	 *
	 * @param	string|array	$item
	 * @return	array
	 */
	protected function _analyze_item($item)
	{
		if (is_array($item))
		{
			return $this->_format_item_array($item);
		}
		else
		{
			return $this->_parse_item($item);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Return TRUE if the config section $section exists and FALSE otherwise
	 *
	 * @param	string	$section	Config section
	 * @return	bool
	 */
	public function section_exists($section)
	{
		if (isset($this->config[$section]))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Return TRUE if the config item $item exists and FALSE otherwise
	 *
	 * The config item $item can be a string like 'parameter' or 'Section.parameter'.
	 * It can also be an array with two keys : 'section' and 'parameter'.
	 * If the section is not defined, then it is set to the primary section name.
	 *
	 * @param	string|array	$item	Config item
	 * @return	bool
	 */
	public function item_exists($item)
	{
		$item_array = $this->_analyze_item($item);

		if ($this->section_exists($item_array['section'])
			&& isset($this->config[$item_array['section']][$item_array['parameter']])
		) {
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a config section
	 *
	 * @param	string	$section	Config section
	 * @return	array	The configuration section
	 */
	public function section($section = CONFIG_PRIMARY_SECTION)
	{
		if ( ! $this->section_exists($section))
		{
			throw new Exception('Undefined configuration section');
		}

		$retour = array();

		foreach ($this->config[$item_array['section']] as $key => $value)
		{
			$retour[$key] = $value['current_value'];
		}

		return $retour;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a config item
	 *
	 * The config item $item can be a string like 'parameter' or 'Section.parameter'.
	 * It can also be an array with two keys : 'section' and 'parameter'.
	 * If the section is not defined, then it is set to the primary section name.
	 *
	 * @param	string|array	$item	Config item
	 * @return	mixed	The configuration item
	 */
	public function item($item)
	{
		$item_array = $this->_analyze_item($item);

		if ( ! $this->item_exists($item_array))
		{
			throw new Exception('Undefined configuration item');
		}

		return $this->config[$item_array['section']][$item_array['parameter']]['current_value'];
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a config item with slash appended (if not empty)
	 *
	 * The config item $item can be a string like 'parameter' or 'Section.parameter'.
	 * It can also be an array with two keys : 'section' and 'parameter'.
	 * If the section is not defined, then it is set to the primary section name.
	 *
	 * @param	string|array	$item	Config item
	 * @return	string	The configuration item
	 */
	public function slash_item($item)
	{
		$value = $this->item($item);

		if (trim($value) === '')
		{
			return '';
		}
		else
		{
			return rtrim($value, '/').'/';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Create a config item
	 *
	 * The config item $item can be a string like 'parameter' or 'Section.parameter'.
	 * It can also be an array with two keys : 'section' and 'parameter'.
	 * If the section is not defined, then it is set to the primary section name.
	 *
	 * If the config item $item already exists, then it is overwritten.
	 *
	 * @param	string|array	$item	Config item
	 * @param	string	$value	The value
	 * @return	void
	 */
	public function create_item($item, $value)
	{
		$item_array = $this->_analyze_item($item);

		$this->config[$item_array['section']][$item_array['parameter']] = array(
			'initial_value'  => $value,
			'current_value'  => $value,
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Set a config item
	 *
	 * The config item $item can be a string like 'parameter' or 'Section.parameter'.
	 * It can also be an array with two keys : 'section' and 'parameter'.
	 * If the section is not defined, then it is set to the primary section name.
	 *
	 * @param	string|array	$item	Config item
	 * @param	string	$value	The value
	 * @return	void
	 */
	public function set_item($item, $value)
	{
		$item_array = $this->_analyze_item($item);

		if ( ! $this->item_exists($item_array))
		{
			throw new Exception('Undefined configuration item');
		}

		$this->config[$item_array['section']][$item_array['parameter']]['current_value'] = $value;
	}

	// --------------------------------------------------------------------

	/**
	 * Restore the value of the config item $item to its initial value
	 *
	 * The config item $item can be a string like 'parameter' or 'Section.parameter'.
	 * It can also be an array with two keys : 'section' and 'parameter'.
	 * If the section is not defined, then it is set to the primary section name.
	 *
	 * @param	string|array	$item	Config item
	 * @return	void
	 */
	public function restore_item($item)
	{
		$item_array = $this->_analyze_item($item);

		if ( ! $this->item_exists($item_array))
		{
			throw new Exception('Undefined configuration item');
		}

		$this->config[$item_array['section']][$item_array['parameter']]['current_value'] = $this->config[$item_array['section']][$item_array['parameter']]['initial_value'];
	}

	// --------------------------------------------------------------------

	/**
	 * Site URL
	 *
	 * Returns base_url . index_page [. uri_string]
	 *
	 * @uses	CI_Config::_uri_string()
	 *
	 * @param	string|string[]	$uri	URI string or an array of segments
	 * @param	string	$protocol
	 * @return	string
	 */
	public function site_url($uri = '', $protocol = NULL)
	{
		$base_url = $this->slash_item('base_url');

		if (isset($protocol))
		{
			// For protocol-relative links
			if ($protocol === '')
			{
				$base_url = substr($base_url, strpos($base_url, '//'));
			}
			else
			{
				$base_url = $protocol.substr($base_url, strpos($base_url, '://'));
			}
		}

		if (empty($uri))
		{
			return $base_url.$this->item('index_page');
		}

		$uri = $this->_uri_string($uri);

		if ($this->item('query_strings_enabled') === FALSE)
		{
			$url_suffix_item = $this->item('url_suffix');
			$suffix = isset($url_suffix_item) ? $url_suffix_item : '';

			if ($suffix !== '')
			{
				if (($offset = strpos($uri, '?')) !== FALSE)
				{
					$uri = substr($uri, 0, $offset).$suffix.substr($uri, $offset);
				}
				else
				{
					$uri .= $suffix;
				}
			}

			return $base_url.$this->slash_item('index_page').$uri;
		}
		elseif (strpos($uri, '?') === FALSE)
		{
			$uri = '?'.$uri;
		}

		return $base_url.$this->item('index_page').$uri;
	}

	// -------------------------------------------------------------

	/**
	 * Base URL
	 *
	 * Returns base_url [. uri_string]
	 *
	 * @uses	CI_Config::_uri_string()
	 *
	 * @param	string|string[]	$uri	URI string or an array of segments
	 * @param	string	$protocol
	 * @return	string
	 */
	public function base_url($uri = '', $protocol = NULL)
	{
		$base_url = $this->slash_item('base_url');

		if (isset($protocol))
		{
			// For protocol-relative links
			if ($protocol === '')
			{
				$base_url = substr($base_url, strpos($base_url, '//'));
			}
			else
			{
				$base_url = $protocol.substr($base_url, strpos($base_url, '://'));
			}
		}

		return $base_url.$this->_uri_string($uri);
	}

	// -------------------------------------------------------------

	/**
	 * Build URI string
	 *
	 * @used-by	CI_Config::site_url()
	 * @used-by	CI_Config::base_url()
	 *
	 * @param	string|string[]	$uri	URI string or an array of segments
	 * @return	string
	 */
	protected function _uri_string($uri)
	{
		if ($this->item('query_strings_enabled') === FALSE)
		{
			is_array($uri) && $uri = implode('/', $uri);
			return ltrim($uri, '/');
		}
		elseif (is_array($uri))
		{
			return http_build_query($uri);
		}

		return $uri;
	}

	// --------------------------------------------------------------------

	/**
	 * System URL
	 *
	 * @deprecated	3.0.0	Encourages insecure practices
	 * @return	string
	 */
	public function system_url()
	{
		$x = explode('/', preg_replace('|/*(.+?)/*$|', '\\1', BASEPATH));
		return $this->slash_item('base_url').end($x).'/';
	}

}
