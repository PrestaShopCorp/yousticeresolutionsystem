<?php
/**
 * Facade for Infinario service
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

require_once 'SDK' . DIRECTORY_SEPARATOR . 'InfinarioClientBase.php';
require_once 'SDK' . DIRECTORY_SEPARATOR . 'Infinario.php';
require_once 'SDK' . DIRECTORY_SEPARATOR . 'Environment.php';
require_once 'SDK' . DIRECTORY_SEPARATOR . 'Transport.php';
require_once 'SDK' . DIRECTORY_SEPARATOR . 'SynchronousTransport.php';

class YousticeInfinarioFacade {
	
	protected $initialized;
	protected $api_key;
	protected $debug;
	protected $infinario;
	protected $player_data;
	protected $event_data;
	
	public function __construct($api_key, $debug = false)
	{
		$this->initialized = false;
		$this->api_key = $api_key;
		$this->debug = $debug;
	}
	
	public function setPlayerId($player_id) {
		$this->infinario = new Infinario($this->api_key, array(
			'debug' => $this->debug, 
			'customer' => $player_id,
			'transport' => new YousticeInfinarioTransport()
		));
		
		$this->initialized = true;
		
		return $this;
	}
	
	public function setPlayerData($player_data) {
		$this->player_data = $player_data;
		
		return $this;
	}
	
	public function registerPlayerData($player_data = array()) {
		if (empty($player_data))
			$player_data = $this->player_data;
		
		if ($this->initialized && !empty($player_data))
			$this->infinario->update($player_data);
		
		return $this;
	}
	
	public function setEventData(array $event_data) {		
		$this->event_data = $event_data;
		
		return $this;
	}

	public function installedEvent() {
		$this->trackEvent('installed');
		
		return $this;
	}
	
	public function uninstalledEvent() {
		$this->trackEvent('uninstalled');
		
		return $this;
	}
	
	public function validApiKeySetEvent() {
		$this->trackEvent('valid_api_key_set');
		
		return $this;
	}
	
	public function registerMeClickedEvent() {
		$this->trackEvent('register_me_clicked');
		
		return $this;
	}
	
	protected function trackEvent($event_name = '') {
		if ($this->initialized)
			$this->infinario->track($event_name, $this->event_data, time());
		
		return $this;
	}

}
