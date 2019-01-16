<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Request extends CI_Controller {

	/**
	 * Handles the request for the Request Form
	 * Performs a check on the requirements and fires a message if a requirement is not satisfied
	 */
	public function index()
	{
		$twig_data = array_merge(
			$this->config->item('twig_data'),
			[
				'base_url' => $this->paymentsense_url->base_url(),
			]
		);

		if ( ! Requirements::check())
		{
			$messages = Requirements::get_messages();
			$twig_data = array_merge(
				$twig_data,
				[
					'messages' => [
						'danger' => $messages['error'],
						'warning' => $messages['warning'],
					]
				]
			);
			$this->load->library('twig', $this->config->item('twig_config'));
			$this->twig->display('message', $twig_data);
		}
		else
		{
			$config = $this->config->item('request_fields');
			$config['CallbackURL']['value'] =  $this->paymentsense_url->url('response/callbackurl');
			$config['ServerResultURL']['value'] =  $this->paymentsense_url->url('response/serverresulturl');
			$this->config->set_item('request_fields',$config);
			$twig_data = array_merge(
				$twig_data,
				[
					'subtitle' => 'Request Form. Submits to: ' . $this->paymentsense->get_payment_form_url(),
					'payment_form_url' => $this->paymentsense->get_payment_form_url(),
					'form' => $this->config->item('request_fields')
				]
			);
			$this->load->library('twig', $this->config->item('twig_config'));
			$this->twig->display('request', $twig_data);
		}
	}
}
