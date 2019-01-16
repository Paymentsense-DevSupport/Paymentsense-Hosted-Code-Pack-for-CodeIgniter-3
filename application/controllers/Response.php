<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Response extends CI_Controller {

	/**
	 * Handles the ServerResultURL request
	 */
	public function serverresulturl()
	{
		if (empty($_REQUEST)) {
			http_response_code(400);
			exit;
		}

		$cross_reference = $this->paymentsense->get_http_var( 'CrossReference' );
		$authenticated   = $this->paymentsense->is_hash_digest_valid();
		$data            = [
			'authenticated' => $authenticated,
			'data' => $_POST
		];

		$result = $this->paymentsense_files->save('SR' . $cross_reference, $data);
		if ($result) {
			$this->output_response(0, 'Request processed successfully.');
		} else {
			$this->output_response(30, 'An error has occurred while processing this request.');
		}
	}

	/**
	 * Handles the CallbackURL request
	 */
	public function callbackurl()
	{
		if (empty($_REQUEST)) {
			http_response_code(400);
			exit;
		}

		$responses       = [];
		$cross_reference = $this->paymentsense->get_http_var( 'CrossReference' );
		$authenticated   = $this->paymentsense->is_hash_digest_valid();
		$data            = [
			'authenticated' => $authenticated,
			'data' => $_REQUEST
		];
		$this->paymentsense_files->save('CB' . $cross_reference, $data);

		$servresp_data = $this->paymentsense_files->load('SR' . $cross_reference);
		if ($servresp_data !== false) {
			$responses[] = [
				'subtitle' => 'Response from the Paymentsense gateway received on the ServerResultURL with ' . ($servresp_data['authenticated']? 'a VALID':'an INVALID') . ' hash digest:',
				'data' => $servresp_data['data']
			];
		}

		$responses[] = [
			'subtitle' => 'Response from the Paymentsense gateway received on the CallbackURL with ' . ($data['authenticated']? 'a VALID':'an INVALID') . ' hash digest:',
			'data' => $data['data']
		];

		$twig_data = array_merge(
			$this->config->item('twig_data'),
			[
				'base_url'  => $this->paymentsense_url->base_url(),
				'responses' => $responses
			]
		);

		$this->load->library('twig', $this->config->item('twig_config'));
		$this->twig->display('response', $twig_data);
	}

	/**
	 * Outputs the response and exits
	 *
	 * @param string $status_code
	 * @param string $message
	 */
	protected function output_response($status_code, $message) {
		echo "StatusCode={$status_code}&Message={$message}";
		exit;
	}
}
