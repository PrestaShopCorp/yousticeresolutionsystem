<?php
/**
 * Interface for session providers.
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

interface YousticeProvidersSessionProviderInterface {
	public function start();
	public function get($var);
	public function set($var, $value);
	public function destroy();
}
