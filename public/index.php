<?php
/*
 * -------------------------------------------------------------------
 *  Definição básca do carragamento via namespaces
 * -------------------------------------------------------------------
 */
spl_autoload_register(

	function( $classname ) {

		require_once '../'.str_replace( '\\', DIRECTORY_SEPARATOR, $classname ) . '.php';
	}

);

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */
	define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// Path to base directory
	define('BASEPATH', str_replace('\\', '/', realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/..' )));

	// Path to the public folder
	define('PUBLIC', str_replace('\\', '/', pathinfo(__FILE__, PATHINFO_DIRNAME) ));

	// Path to the system path
	define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

	// Path to the front controller (this file)
	define('FCPATH', str_replace(SELF, '', __FILE__));


/*
 * -------------------------------------------------------------------
 *  Definição de quais módulos participam da aplicação
 * -------------------------------------------------------------------
 */
$modulos = array(
	new App\Vendor\Module\Module()
);

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
$aplication = new System\Core\CodeIgniter($modulos);