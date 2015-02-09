<?php
 namespace System\Core
 {
	 /**
	  * Application Controller Class
	  *
	  * This class object is the super class that every library in
	  * CodeIgniter will be assigned to.
	  *
	  * @package		CodeIgniter
	  * @subpackage	Libraries
	  * @category	Libraries
	  * @author		EllisLab Dev Team
	  * @link		http://codeigniter.com/user_guide/general/controllers.html
	  */
	 class Controller {

		 /**
		  * Reference to the CI singleton
		  *
		  * @var	object
		  */
		 private static $instance;

		 /**
		  * Class constructor
		  *
		  * @return	void
		  */
		 public function __construct()
		 {
			 self::$instance =& $this;

			 // Assign all the class objects that were instantiated by the
			 // bootstrap file (CodeIgniter.php) to local class variables
			 // so that CI can run as one big super object.
			 //foreach (Common::is_loaded() as $var => $class)
			 //{
			//	 $this->$var =& load_class($class);
			 //}

			 $this->load = new Loader();
			 $this->load->initialize();
			 Common::log_message('info', 'Controller Class Initialized');
		 }

		 // --------------------------------------------------------------------

		 /**
		  * Get the CI singleton
		  *
		  * @static
		  * @return	object
		  */
		 public static function &get_instance()
		 {
			 return self::$instance;
		 }

	 }
 }