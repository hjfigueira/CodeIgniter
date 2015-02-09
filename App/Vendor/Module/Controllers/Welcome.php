<?php

namespace App\Vendor\Module\Controllers

{
	use System\Core\Controller;


	class Welcome extends Controller {

		public function home()
		{
			die('HOME ACHIEVED!');
			//$this->load->view('welcome_message');
		}
	}
}

