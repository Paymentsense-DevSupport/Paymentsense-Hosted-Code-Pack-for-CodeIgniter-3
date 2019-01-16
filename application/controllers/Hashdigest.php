<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hashdigest extends CI_Controller {

	/**
	 * Handles the AJAX request for the hash digest on the data on the Request Form
	 */
	public function index()
	{
		if (empty($_REQUEST)) {
			http_response_code(400);
			exit;
		}
		echo json_encode($this->paymentsense->get_hash_digest($this->input->post()));
	}
}
