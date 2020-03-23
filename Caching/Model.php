<?php

class Model
{
    public function __construct(WP_Post $post, array $query = [])
    {
        // Code in construct not written by me
    }

    /**
	 * @author Vincent <vincent@allyourmedia.nl>
	 * @since 2019-12-6
	 */
    public function getRides(): array
	{
		static $rides;
		
		if ($this->getFrom() && $this->getTo()) {
			$query = $this->getQuery();
			
			if ($query) {
				$from = $this->getFrom();
				$to = $this->getTo();
				$name = $from . $to;
				$dir = WP_CACHE_DIR . '/taxitender/taxis';
				$SlDir = WP_CACHE_DIR . '/taxitender/service_locations';
				$dateTime = $this->getDateTime();
				$rides = [];
				$cache = new Cache();
				$cachefileExists = $cache->cachefile_exists($dir, $name);
				$SlCachefileExists = $cache->cachefile_exists($SlDir, $name);
				$SlCheck = $cache->checkSl($SlDir, $name);
				$api = new TaxiTenderApi();
				
				if ($from && $to && $dateTime) {
					
					// Check if chachefile exists and is empty
					if ($cachefileExists && !empty($rides)) {
						$rides = $cache->pseudo_cache($dir, $name, $rides);

					// Check if cachefile exists and result == true
					} else if ($SlCachefileExists && $SlCheck) {
						$rides = $api->getBookableRides($from, $to, $dateTime);
						$cache->pseudo_cache($dir, $name, $rides);
						$this->errors = $api->getErrors();
					
						if (empty($rides)) {
							$this->writeToSlCache(false, $this->errors);
						} else {
							$this->writeToSlCache();
						}
					
					// Check if cachefile exists and result == false
					} else if ($SlCachefileExists && !$SlCheck) {
						$rides = [];
						$this->errors = $cache->getError();

					} else {
						$rides = $api->getBookableRides($from, $to, $dateTime);
						$cache->pseudo_cache($dir, $name, $rides);
						$this->errors = $api->getErrors();

						if (empty($rides)) {
							$this->writeToSlCache(false, $this->errors);
						} else {
							$this->writeToSlCache();
						}
					}

				}

			}
		}

		return $rides ?: [];
    }
    
    /**
	 * @author Vincent <vincent@allyourmedia.nl>
	 * @since 2019-12-6
	 */
	public function writeToSlCache($bool = true, $error = false) 
	{
		$cache = new Cache();
		$SlDir = WP_CACHE_DIR . '/taxitender/service_locations';

		if ($this->getFrom() && $this->getTo()) {
			$name = $this->getFrom() . $this->getTo();
			$result = ['from' => $this->getFrom() , 'to' => $this->getTo(), 'result' => $bool, 'error' => $error];
			$cache->pseudo_cache($SlDir, $name, $result, $cachetime = 3600 * 24);
		}
	}
}