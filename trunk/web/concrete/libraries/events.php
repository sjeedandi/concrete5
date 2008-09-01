<?

/**
 * @package Core
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 *
 */

/**
 * An events framework for Concrete. System events like "on_user_add" can be hooked into, so that when a user is added to the system, the new UserInfo object is passed to developers' custom functions.
 * Current events include:
 * on_user_add
 * @package Core
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 *
 */

class ConcreteEvents {
	
	public function getInstance() {
		static $instance;
		if (!isset($instance)) {
			$v = __CLASS__;
			$instance = new $v;
		}
		return $instance;
	}		

	private $registeredEvents = array();
	
	public static function extend($event, $class, $method, $filename, $params = array()) {
		$ce = ConcreteEvents::getInstance();
		$ce->registeredEvents[$event][] = array(
			$class,
			$method,
			$filename,
			$params
		);	
	}
	
	public static function fire($event) {
		if (ENABLE_APPLICATION_EVENTS == false) {
			return;
		}
		
		// any additional arguments passed to the fire function will get passed FIRST to the method, with the method's own registered
		// params coming at the end. e.g. if I fire ConcreteEvents::fire('on_login', $userObject) it will come in with user object first
		$args = func_get_args();
		if (count($args) > 1) {
			array_shift($args);
		} else {
			$args = false;
		}

		$ce = ConcreteEvents::getInstance();
		$events = $ce->registeredEvents[$event];
		if (is_array($events)) {
			foreach($events as $ev) {
				require_once(DIR_BASE . '/' . $ev[2]);
				$params = (is_array($ev[3])) ? $ev[3] : array();
				
				// now if args has any values we put them FIRST
				$params = array_merge($args, $params);

				if (method_exists($ev[0], $ev[1])) {
					call_user_func_array(array($ev[0], $ev[1]), $params);
				}				
			}
		}
	}
}
