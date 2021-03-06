<?php

namespace System\Core
{
	class CodeIgniter
	{
		public function __construct($modules)
		{

			foreach($modules as $module)
			{
				//$module->registerRoutes();
				//$module->registerConfigs();

			}

			/*
             * ------------------------------------------------------
             * Security procedures
             * ------------------------------------------------------
             */

			if ( ! Common::is_php('5.4'))
			{
				ini_set('magic_quotes_runtime', 0);

				if ((bool) ini_get('register_globals'))
				{
					$_protected = array(
						'_SERVER',
						'_GET',
						'_POST',
						'_FILES',
						'_REQUEST',
						'_SESSION',
						'_ENV',
						'_COOKIE',
						'GLOBALS',
						'HTTP_RAW_POST_DATA',
						'system_path',
						'application_folder',
						'view_folder',
						'_protected',
						'_registered'
					);

					$_registered = ini_get('variables_order');
					foreach (array('E' => '_ENV', 'G' => '_GET', 'P' => '_POST', 'C' => '_COOKIE', 'S' => '_SERVER') as $key => $superglobal)
					{
						if (strpos($_registered, $key) === FALSE)
						{
							continue;
						}

						foreach (array_keys($$superglobal) as $var)
						{
							if (isset($GLOBALS[$var]) && ! in_array($var, $_protected, TRUE))
							{
								$GLOBALS[$var] = NULL;
							}
						}
					}
				}
			}


			/*
             * ------------------------------------------------------
             *  Define a custom error handler so we can log PHP errors
             * ------------------------------------------------------
             */
			set_error_handler('\\System\\Core\\Common::_error_handler');
			set_exception_handler('\\System\\Core\\Common::_exception_handler');
			register_shutdown_function('\\System\\Core\\Common::_shutdown_handler');


			/*
             * ------------------------------------------------------
             *  Start Composer Autoload
             * ------------------------------------------------------
             */
			file_exists('../../vendor/autoload.php')
				? require_once('../../vendor/autoload.php')
				: Common::log_message('error', 'vendor/autoload.php was not found.');

			/*
             * ------------------------------------------------------
             *  Start the timer... tick tock tick tock...
             * ------------------------------------------------------
             */
			$BM = new Benchmark();
			$BM->mark('total_execution_time_start');
			$BM->mark('loading_time:_base_classes_start');


			/*
             * ------------------------------------------------------
             *  Instantiate the config class
             * ------------------------------------------------------
             *
             * Note: It is important that Config is loaded first as
             * most other classes depend on it either directly or by
             * depending on another class that uses it.
             *
             */
			$CFG = new Config();

			// Do we have any manually set config items in the index.php file?
			if (isset($assign_to_config) && is_array($assign_to_config))
			{
				foreach ($assign_to_config as $key => $value)
				{
					$CFG->set_item($key, $value);
				}
			}

			/*
             * ------------------------------------------------------
             * Important charset-related stuff
             * ------------------------------------------------------
             *
             * Configure mbstring and/or iconv if they are enabled
             * and set MB_ENABLED and ICONV_ENABLED constants, so
             * that we don't repeatedly do extension_loaded() or
             * function_exists() calls.
             *
             * Note: UTF-8 class depends on this. It used to be done
             * in it's constructor, but it's _not_ class-specific.
             *
             */
			$charset = strtoupper(Common::config_item('charset'));
			ini_set('default_charset', $charset);

			if (extension_loaded('mbstring'))
			{
				define('MB_ENABLED', TRUE);
				// mbstring.internal_encoding is deprecated starting with PHP 5.6
				// and it's usage triggers E_DEPRECATED messages.
				@ini_set('mbstring.internal_encoding', $charset);
				// This is required for mb_convert_encoding() to strip invalid characters.
				// That's utilized by CI_Utf8, but it's also done for consistency with iconv.
				mb_substitute_character('none');
			}
			else
			{
				define('MB_ENABLED', FALSE);
			}

			// There's an ICONV_IMPL constant, but the PHP manual says that using
			// iconv's predefined constants is "strongly discouraged".
			if (extension_loaded('iconv'))
			{
				define('ICONV_ENABLED', TRUE);
				// iconv.internal_encoding is deprecated starting with PHP 5.6
				// and it's usage triggers E_DEPRECATED messages.
				@ini_set('iconv.internal_encoding', $charset);
			}
			else
			{
				define('ICONV_ENABLED', FALSE);
			}

			if (Common::is_php('5.6'))
			{
				ini_set('php.internal_encoding', $charset);
			}

			/*
             * ------------------------------------------------------
             *  Load compatibility features
             * ------------------------------------------------------
             */

			//require_once(BASEPATH.'core/compat/mbstring.php');
			//require_once(BASEPATH.'core/compat/hash.php');
			//require_once(BASEPATH.'core/compat/password.php');
			//require_once(BASEPATH.'core/compat/standard.php');

			/*
             * ------------------------------------------------------
             *  Instantiate the UTF-8 class
             * ------------------------------------------------------
             */
			$UNI  = new Utf8();

			/*
             * ------------------------------------------------------
             *  Instantiate the URI class
             * ------------------------------------------------------
             */
			$URI = new Uri();

			/*
             * ------------------------------------------------------
             *  Instantiate the routing class and set the routing
             * ------------------------------------------------------
             */
			$RTR = new Router($modules);

			/*
             * ------------------------------------------------------
             *  Instantiate the output class
             * ------------------------------------------------------
             */
			$OUT = new Output();


			/*
             * -----------------------------------------------------
             * Load the security class for xss and csrf support
             * -----------------------------------------------------
             */
			$SEC = new Security();

			/*
             * ------------------------------------------------------
             *  Load the Input class and sanitize globals
             * ------------------------------------------------------
             */
			$IN	= new Input();

			/*
             * ------------------------------------------------------
             *  Load the Language class
             * ------------------------------------------------------
             */
			$LANG = new Lang();

			/*
             * ------------------------------------------------------
             *  Load the app controller and local controller
             * ------------------------------------------------------
             *
             */
			//die('Carregamento bem sucedido em '.__CLASS__.'.php - line : '.__LINE__);


			// Set a mark point for benchmarking
			$BM->mark('loading_time:_base_classes_end');

			/*
             * ------------------------------------------------------
             *  Sanity checks
             * ------------------------------------------------------
             *
             *  The Router class has already validated the request,
             *  leaving us with 3 options here:
             *
             *	1) an empty class name, if we reached the default
             *	   controller, but it didn't exist;
             *	2) a query string which doesn't go through a
             *	   file_exists() check
             *	3) a regular request for a non-existing page
             *
             *  We handle all of these as a 404 error.
             *
             *  Furthermore, none of the methods in the app controller
             *  or the loader class can be called via the URI, nor can
             *  controller methods that begin with an underscore.
             */

			$e404 = FALSE;
			$class = $RTR->class;
			$method = $RTR->method;

			if(false)
			{
				if (empty($class) OR ! file_exists(APPPATH.'controllers/'.$RTR->directory.$class.'.php'))
				{
					$e404 = TRUE;
				}
				else
				{

					if ( ! class_exists($class, FALSE) OR $method[0] === '_' OR method_exists('CI_Controller', $method))
					{
						$e404 = TRUE;
					}
					elseif (method_exists($class, '_remap'))
					{
						$params = array($method, array_slice($URI->rsegments, 2));
						$method = '_remap';
					}
					// WARNING: It appears that there are issues with is_callable() even in PHP 5.2!
					// Furthermore, there are bug reports and feature/change requests related to it
					// that make it unreliable to use in this context. Please, DO NOT change this
					// work-around until a better alternative is available.
					elseif ( ! in_array(strtolower($method), array_map('strtolower', get_class_methods($class)), TRUE))
					{
						$e404 = TRUE;
					}
				}

				if ($e404)
				{
					if ( ! empty($RTR->routes['404_override']))
					{
						if (sscanf($RTR->routes['404_override'], '%[^/]/%s', $error_class, $error_method) !== 2)
						{
							$error_method = 'index';
						}

						$error_class = ucfirst($error_class);

						if ( ! class_exists($error_class, FALSE))
						{
							if (file_exists(APPPATH.'controllers/'.$RTR->directory.$error_class.'.php'))
							{
								require_once(APPPATH.'controllers/'.$RTR->directory.$error_class.'.php');
								$e404 = ! class_exists($error_class, FALSE);
							}
							// Were we in a directory? If so, check for a global override
							elseif ( ! empty($RTR->directory) && file_exists(APPPATH.'controllers/'.$error_class.'.php'))
							{
								require_once(APPPATH.'controllers/'.$error_class.'.php');
								if (($e404 = ! class_exists($error_class, FALSE)) === FALSE)
								{
									$RTR->directory = '';
								}
							}
						}
						else
						{
							$e404 = FALSE;
						}
					}

					// Did we reset the $e404 flag? If so, set the rsegments, starting from index 1
					if ( ! $e404)
					{
						$class = $error_class;
						$method = $error_method;

						$URI->rsegments = array(
							1 => $class,
							2 => $method
						);
					}
					else
					{
						Common::show_404($RTR->directory.$class.'/'.$method);
					}
				}

				if ($method !== '_remap')
				{
					$params = array_slice($URI->rsegments, 2);
				}
			}

			/*
             * ------------------------------------------------------
             *  Is there a "pre_controller" hook?
             * ------------------------------------------------------
             */
			//$EXT->call_hook('pre_controller');

			/*
             * ------------------------------------------------------
             *  Instantiate the requested controller
             * ------------------------------------------------------
             */
			// Mark a start point so we can benchmark the controller
			$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_start');

			$pathToController = 'App\\'.$RTR->vendor.'\\'.$RTR->module.'\\Controllers\\'.$class;
			$CI = new $pathToController();

			/*
             * ------------------------------------------------------
             *  Is there a "post_controller_constructor" hook?
             * ------------------------------------------------------
             */
			//$EXT->call_hook('post_controller_constructor');

			/*
             * ------------------------------------------------------
             *  Call the requested method
             * ------------------------------------------------------
             */

			$CI->$method();
			//call_user_func_array(array($pathToController, $method), $params);



			// Mark a benchmark end point
			$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_end');

			/*
             * ------------------------------------------------------
             *  Is there a "post_controller" hook?
             * ------------------------------------------------------
             */
			//$EXT->call_hook('post_controller');

			/*
             * ------------------------------------------------------
             *  Send the final rendered output to the browser
             * ------------------------------------------------------
             */
//			if ($EXT->call_hook('display_override') === FALSE)
//			{
//				$OUT->_display();
//			}

			/*
             * ------------------------------------------------------
             *  Is there a "post_system" hook?
             * ------------------------------------------------------
             */
			//$EXT->call_hook('post_system');
		}
	}

}

