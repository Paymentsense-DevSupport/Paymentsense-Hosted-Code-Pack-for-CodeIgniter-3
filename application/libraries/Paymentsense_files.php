<?php
/**
 * Copyright (C) 2019 Paymentsense Ltd.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author      Paymentsense
 * @copyright   2019 Paymentsense Ltd.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://developers.paymentsense.co.uk/
 */

class Paymentsense_files
{
	/**
	 * Loads a file containing data received from the gateway
	 *
	 * @param string $id Response identifier
	 *
	 * @return array|false
	 */
	public function load($id) {
		$result = false;
		if ($this->is_tmp_dir_exists()) {
			$filename = $this->get_filename($id);
			$result   = @file_get_contents($filename);
			if ($result !== false) {
				$result = json_decode($result, true);
			}
		}
		return $result;
	}

	/**
	 * Saves a file containing data received from the gateway
	 *
	 * @param string $id Response identifier
	 * @param array $data Data received from the gateway
	 *
	 * @return array|false
	 */
	public function save($id, $data) {
		$result = false;
		if ($this->is_tmp_dir_exists()) {
			$filename = $this->get_filename($id);
			$result   = write_file($filename, json_encode($data, JSON_PRETTY_PRINT));
		}
		return $result;
	}

	/**
	 * Performs a test whether the web user can write to the application tmp
	 * directory by creating a test file
	 *
	 * @return bool
	 */
	public function test() {
		$id = 'TEST';
		$data = [
			'authenticated' => FALSE,
			'data' => []
		];
		$result = false;
		if ($this->is_tmp_dir_exists()) {
			$filename = $this->get_filename($id);
			$result   = write_file($filename, json_encode($data, JSON_PRETTY_PRINT));
			if ($result) {
				$result = unlink($filename);
			}
		}
		return $result;
	}

	/**
	 * Gets the application tmp directory
	 *
	 * @return string
	 */
	public function get_tmp_dir() {
		return realpath(APPPATH . './tmp');
	}

	/**
	 * Checks whether the application tmp directory exists
	 *
	 * @return bool
	 */
	public function is_tmp_dir_exists() {
		$tmp_dir = $this->get_tmp_dir();
		return is_dir($tmp_dir);
	}

	/**
	 * Gets the name of file containing data received from the gateway based
	 * on the response identifier
	 *
	 * @param string $id Response identifier
	 *
	 * @return string
	 */
	protected function get_filename($id) {
		$tmp_dir = $this->get_tmp_dir();
		return $tmp_dir . '/' . $id . '.json';
	}
}
