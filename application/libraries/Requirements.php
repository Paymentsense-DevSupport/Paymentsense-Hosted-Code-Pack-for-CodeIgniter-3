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

class Requirements
{
	/**
	 * @var array Requirements definitions
	 */
	private static $_requirements = [
		'php_minimal' => [
			'5.5.0' => [
				'title' => 'PHP 5.5 or higher is required',
				'description' => 'PHP 5.5 or higher is required. Your current PHP version is %s. '
			]
		],
		'php_recommended' => [
			'5.6.0'  => [
				'title' => 'PHP 5.6 or higher is recommended',
				'description' => 'PHP 5.6 or higher is recommended. Your current PHP version is %s. '
			]
		],
		'extensions' => [
			'json'  => [
				'title' => 'JSON extension is not enabled',
				'description' => 'JSON extension is not enabled. JSON PHP extension (json) is required for encoding/decoding JSON objects.'
			]
		],
		'libraries' => [],
		'config_fields' => [
			'MMS_MERCHANT_ID'  => [
				'title' => 'MMS_MERCHANT_ID is not set',
				'description' => 'MMS_MERCHANT_ID is not set. Please set it in the config.php file. '
			],
			'MMS_PASSWORD'  => [
				'title' => 'MMS_PASSWORD is not set',
				'description' => 'MMS_PASSWORD is not set. Please set it in the config.php file. '
			],
			'MMS_HASH_METHOD'  => [
				'title' => 'MMS_HASH_METHOD is not set',
				'description' => 'MMS_HASH_METHOD is not set. Please set it in the config.php file. '
			],
			'MMS_PRE_SHARED_KEY'  => [
				'title' => 'MMS_PRE_SHARED_KEY is not set',
				'description' => 'MMS_PRE_SHARED_KEY is not set. Please set it in the config.php file. '
			]
		],
		'tmp_dir_exists' => [
			'title' => 'The application TMP directory does not exist',
			'description' => 'It seems the code pack is corrupted. Please reinstall it to repair.'
		],
		'tmp_dir_writeable' => [
			'title' => 'The application TMP directory is not writeable',
			'description' => 'The application TMP directory %s should be writeable by the web user.'
		],
	];

	/**
	 * @var array Critical requirements for extensions and libraries
	 */
	private static $_critical_requirements = [
		'json'
	];

	/**
	 * @var array Configuration information
	 */
	private static $_configuration = [];

	/**
	 * @var array Message types
	 */
	private static $_messages = [
		'error' => [],
		'warning' => []
	];

	/**
	 * Gets configuration variables that indicate compliance with the PHP
	 * version and availability of the required PHP extensions and libraries
	 *
	 * @return array
	 */
	public static function get_configuration()
	{
		if (empty(self::$_configuration))
		{
			self::check();
		}
		return self::$_configuration;
	}

	/**
	 * Gets error/warning messages that indicate compliance with the PHP
	 * version and availability of the required PHP extensions and libraries
	 *
	 * @param string $message_type error/warning message type
	 *
	 * @return array
	 */
	public static function get_messages($message_type='')
	{
		if (empty(self::$_configuration))
		{
			self::check();
		}
		return $message_type ? self::$_messages[$message_type] : self::$_messages;
	}

	/**
	 * Checks the requirements that indicate compliance with the PHP version
	 * and availability of the required PHP extensions and libraries
	 *
	 * @return bool
	 */
	public static function check()
	{
		foreach (self::$_requirements as $key => $requirements)
		{
			switch ($key)
			{
				case 'php_minimal':
					$satisfied = version_compare(PHP_VERSION, current(array_keys($requirements)), '>=');
					if ( ! $satisfied)
					{
						$requirements = current(array_values($requirements));
						$requirements['description'] = sprintf($requirements['description'], PHP_VERSION);
						self::$_messages['error'][] = $requirements;
					}
					self::$_configuration[$key] = $satisfied;
					break;
				case 'php_recommended':
					$satisfied = version_compare(PHP_VERSION, current(array_keys($requirements)), '>=');
					if ( ! $satisfied)
					{
						$requirements = current(array_values($requirements));
						$requirements['description'] = sprintf($requirements['description'], PHP_VERSION);
						self::$_messages['warning'][] = $requirements;
					}
					self::$_configuration[$key] = $satisfied;
					break;
				case 'extensions':
					foreach (self::$_requirements[$key] as $extension => $comment)
					{
						$satisfied = extension_loaded($extension);
						if ( ! $satisfied)
						{
							$message_type = in_array($extension, self::$_critical_requirements) ? 'error' : 'warning';
							self::$_messages[$message_type][] = $comment;
						}
						self::$_configuration[$extension] = $satisfied;
					}
					break;
				case 'libraries':
					foreach (self::$_requirements[$key] as $library => $comment)
					{
						$lib = ucfirst($library);
						$satisfied = (is_file(APPPATH .'libraries/'. $lib . '.php'));
						if ( ! $satisfied)
						{
							$message_type = in_array($library, self::$_critical_requirements) ? 'error' : 'warning';
							self::$_messages[$message_type][]=$comment;
						}
						self::$_configuration[$library] = $satisfied;
					}
					break;
				case 'config_fields':
					foreach (self::$_requirements[$key] as $config_field => $comment)
					{
						$satisfied = ( ! empty(constant($config_field)));
						if ( ! $satisfied)
						{
							self::$_messages['error'][] = $comment;
						}
						self::$_configuration[$config_field] = $satisfied;
					}
					break;
				case 'tmp_dir_exists':
					if (self::$_requirements[$key])
					{
						$CI =& get_instance();
						$satisfied = $CI->paymentsense_files->is_tmp_dir_exists();
						if ( ! $satisfied)
						{
							$requirements['description'] = $requirements['description'];
							self::$_messages['error'][]  = $requirements;
						}
						self::$_configuration[$key] = $satisfied;
					}
					break;
				case 'tmp_dir_writeable':
					if (self::$_requirements[$key])
					{
						$CI =& get_instance();
						if ($CI->paymentsense_files->is_tmp_dir_exists())
						{
							$satisfied = $CI->paymentsense_files->test();
							if ( ! $satisfied)
							{
								$requirements['description'] = sprintf($requirements['description'], $CI->paymentsense_files->get_tmp_dir());
								self::$_messages['error'][]  = $requirements;
							}
							self::$_configuration[$key] = $satisfied;
						}
					}
					break;
			}
		}
		return empty(self::$_messages['error']);
	}
}
