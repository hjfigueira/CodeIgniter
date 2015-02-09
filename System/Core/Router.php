<?php

namespace System\Core
{
	use System\Core\Interfaces\StandardModule;

	/**
	 * Router Class
	 *
	 * Parses URIs and determines routing
	 *
	 * @package		CodeIgniter
	 * @subpackage	Libraries
	 * @category	Libraries
	 * @author		EllisLab Dev Team
	 * @link		http://codeigniter.com/user_guide/general/routing.html
	 */
	class Router {

		/**
		 * CI_Config class object
		 *
		 * @var	object
		 */
		public $config;

		/**
		 * List of routes
		 *
		 * @var	array
		 */
		public $routes =	array();

		/**
		 * Current class name
		 *
		 * @var	string
		 */
		public $class =		'';

		/**
		 * Current module
		 *
		 * @var string
		 */
		public $module = '';

		/**
		 * Current module
		 *
		 * @var string
		 */
		public $vendor = '';

		/**
		 * Current method name
		 *
		 * @var	string
		 */
		public $method =	'index';

		/**
		 * Sub-directory that contains the requested controller class
		 *
		 * @var	string
		 */
		public $directory =	'';

		/**
		 * Default controller (and method if specific)
		 *
		 * @var	string
		 */
		public $default_controller;

		/**
		 * Translate URI dashes
		 *
		 * Determines whether dashes in controller & method segments
		 * should be automatically replaced by underscores.
		 *
		 * @var	bool
		 */
		public $translate_uri_dashes = FALSE;

		/**
		 * Enable query strings flag
		 *
		 * Determines wether to use GET parameters or segment URIs
		 *
		 * @var	bool
		 */
		public $enable_query_strings = FALSE;

		// --------------------------------------------------------------------

		/**
		 * Class constructor
		 *
		 * Runs the route mapping function.
		 *
		 * @return	void
		 */
		public function __construct(array $modules)
		{
			$this->config = new Config();
			$this->uri =  new Uri();

			$this->enable_query_strings = ( ! Common::is_cli() && $this->config->item('enable_query_strings') === TRUE);
			$this->_set_routing($modules);

			Common::log_message('info', 'Router Class Initialized');
		}

		// --------------------------------------------------------------------

		/**
		 * Set route mapping
		 *
		 * Determines what should be served based on the URI request,
		 * as well as any "routes" that have been set in the routing config file.
		 *
		 * @return	void
		 * @param $modules array<StandardModule>
		 */
		protected function _set_routing(array $modules)
		{
			// Are query strings enabled in the config file? Normally CI doesn't utilize query strings
			// since URI segments are more search-engine friendly, but they can optionally be used.
			// If this feature is enabled, we will gather the directory/class/method a little differently
			//TODO
//			if ($this->enable_query_strings)
//			{
//				$_d = $this->config->item('directory_trigger');
//				$_d = isset($_GET[$_d]) ? trim($_GET[$_d], " \t\n\r\0\x0B/") : '';
//				if ($_d !== '')
//				{
//					$this->uri->filter_uri($_d);
//					$this->set_directory($_d);
//				}
//
//				$_c = trim($this->config->item('controller_trigger'));
//				if ( ! empty($_GET[$_c]))
//				{
//					$this->uri->filter_uri($_GET[$_c]);
//					$this->set_class($_GET[$_c]);
//
//					$_f = trim($this->config->item('function_trigger'));
//					if ( ! empty($_GET[$_f]))
//					{
//						$this->uri->filter_uri($_GET[$_f]);
//						$this->set_method($_GET[$_f]);
//					}
//
//					$this->uri->rsegments = array(
//						1 => $this->module,
//						2 => $this->class,
//						3 => $this->method
//					);
//				}
//				else
//				{
//					//$this->_set_default_controller();
//				}
//
//				// Routing rules don't apply to query strings and we don't need to detect
//				// directories, so we're done here
//				return;
//			}

			$routes = array();

			foreach($modules as $module)
			{
				if($module->getRoute() == null )
				{
					$routes = array_merge($routes,$module->getInternalRoute());
				}
				else
				{
					foreach($module->getInternalRoute() as $route => $rawRoute)
					{
						$routes[$module->getRoute().'/'.$route] = $rawRoute;
					}
				}

			}

			$this->routes = $routes;

			// Is there anything to parse?
			if ($this->uri->uri_string !== '')
			{
				$this->_parse_routes();
			}
			else
			{
				//TOOD URL Vazia
				die('url vazia');
				//$this->_set_default_controller();
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Set request route
		 *
		 * Takes an array of URI segments as input and sets the class/method
		 * to be called.
		 *
		 * @used-by	CI_Router::_parse_routes()
		 * @param	array	$segments	URI segments
		 * @return	void
		 */
		protected function _set_request($segments = array())
		{

			//$segments = $this->_validate_request($segments);
			// If we don't have any segments left - try the default controller;
			// WARNING: Directories get shifted out of the segments array!
			//TODO
//			if (empty($segments))
//			{
//				$this->_set_default_controller();
//				return;
//			}
//TODO
//			if ($this->translate_uri_dashes === TRUE)
//			{
//				$segments[0] = str_replace('-', '_', $segments[0]);
//				if (isset($segments[1]))
//				{
//					$segments[1] = str_replace('-', '_', $segments[1]);
//				}
//			}

			$this->set_vendor($segments[0]);
			$this->set_module($segments[1]);
			$this->set_class($segments[2]);
			if (isset($segments[3]))
			{
				$this->set_method($segments[3]);
			}
			else
			{
				$segments[3] = 'index';
			}

			array_unshift($segments, NULL);
			//unset($segments[0]);
			$this->uri->rsegments = $segments;
		}


		/**
		 * Validate request
		 *
		 * Attempts validate the URI request and determine the controller path.
		 *
		 * @used-by	CI_Router::_set_request()
		 * @param	array	$segments	URI segments
		 * @return	mixed	URI segments
		 */
		protected function _validate_request($segments)
		{
			$c = count($segments);
			// Loop through our segments and return as soon as a controller
			// is found or when such a directory doesn't exist
			while ($c-- > 0)
			{
				$test = $this->directory
					.ucfirst($this->translate_uri_dashes === TRUE ? str_replace('-', '_', $segments[0]) : $segments[0]);

				if ( ! file_exists(APPPATH.'controllers/'.$test.'.php') && is_dir(APPPATH.'controllers/'.$this->directory.$segments[0]))
				{
					$this->set_directory(array_shift($segments), TRUE);
					continue;
				}

				return $segments;
			}

			// This means that all segments were actually directories
			return $segments;
		}

		// --------------------------------------------------------------------

		/**
		 * Parse Routes
		 *
		 * Matches any routes that may exist in the config/routes.php file
		 * against the URI to determine if the class/method need to be remapped.
		 *
		 * @return	void
		 */
		protected function _parse_routes()
		{
			// Turn the segment array into a URI string
			$uri = implode('/', $this->uri->segments);

			// Get HTTP verb
			$http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

			// Is there a literal match?  If so we're done
			if (isset($this->routes[$uri]))
			{
				// Check default routes format
				if (is_string($this->routes[$uri]))
				{
					$this->_set_request(explode('/', $this->routes[$uri]));
					return;
				}
				// Is there a matching http verb?
				elseif (is_array($this->routes[$uri]) && isset($this->routes[$uri][$http_verb]))
				{
					$this->_set_request(explode('/', $this->routes[$uri][$http_verb]));
					return;
				}
			}

			// Loop through the route array looking for wildcards
			foreach ($this->routes as $key => $val)
			{
				// Check if route format is using http verb
				if (is_array($val))
				{
					if (isset($val[$http_verb]))
					{
						$val = $val[$http_verb];
					}
					else
					{
						continue;
					}
				}

				// Convert wildcards to RegEx
				$key = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $key);

				// Does the RegEx match?
				if (preg_match('#^'.$key.'$#', $uri, $matches))
				{
					// Are we using callbacks to process back-references?
					if ( ! is_string($val) && is_callable($val))
					{
						// Remove the original string from the matches array.
						array_shift($matches);

						// Execute the callback using the values in matches as its parameters.
						$val = call_user_func_array($val, $matches);
					}
					// Are we using the default routing method for back-references?
					elseif (strpos($val, '$') !== FALSE && strpos($key, '(') !== FALSE)
					{
						$val = preg_replace('#^'.$key.'$#', $val, $uri);
					}

					$this->_set_request(explode('/', $val));
					return;
				}
			}

			//TODO
			// If we got this far it means we didn't encounter a
			// matching route so we'll set the site default route
			$this->_set_request(array_values($this->uri->segments));
		}

		// --------------------------------------------------------------------

		/**
		 * Set class name
		 *
		 * @param	string	$class	Class name
		 * @return	void
		 */
		public function set_class($class)
		{
			$this->class = str_replace(array('/', '.'), '', $class);
		}



		// --------------------------------------------------------------------

		/**
		 * Fetch the current class
		 *
		 * @deprecated	3.0.0	Read the 'class' property instead
		 * @return	string
		 */
		public function fetch_class()
		{
			return $this->class;
		}

		// --------------------------------------------------------------------

		/**
		 * Set method name
		 *
		 * @param	string	$method	Method name
		 * @return	void
		 */
		public function set_method($method)
		{
			$this->method = $method;
		}

		// --------------------------------------------------------------------

		/**
		 * Fetch the current method
		 *
		 * @deprecated	3.0.0	Read the 'method' property instead
		 * @return	string
		 */
		public function fetch_method()
		{
			return $this->method;
		}

		// --------------------------------------------------------------------

		/**
		 * Set directory name
		 *
		 * @param	string	$dir	Directory name
		 * @param	bool	$appent	Whether we're appending rather then setting the full value
		 * @return	void
		 */
		public function set_directory($dir, $append = FALSE)
		{
			if ($append !== TRUE OR empty($this->directory))
			{
				$this->directory = str_replace('.', '', trim($dir, '/')).'/';
			}
			else
			{
				$this->directory .= str_replace('.', '', trim($dir, '/')).'/';
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Fetch directory
		 *
		 * Feches the sub-directory (if any) that contains the requested
		 * controller class.
		 *
		 * @deprecated	3.0.0	Read the 'directory' property instead
		 * @return	string
		 */
		public function fetch_directory()
		{
			return $this->directory;
		}

		/**
		 * Set class vendor
		 *
		 * @param	string	$class	Class name
		 * @return	void
		 */
		public function set_vendor($vendor)
		{
			$this->vendor = str_replace(array('/', '.'), '', $vendor);
		}

		/**
		 * Set class name
		 *
		 * @param	string	$class	Class name
		 * @return	void
		 */
		public function set_module($module)
		{
			$this->module = str_replace(array('/', '.'), '', $module);
		}

	}
}