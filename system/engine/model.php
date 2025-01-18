<?php
/**
 * @package		OpenCart
 *
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2022, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 *
 * @see		https://www.opencart.com
 */

/**
 * Model class
 */
namespace Opencart\System\Engine;
/**
 * Class Model
 *
 * @mixin \Opencart\System\Engine\Registry
 */
class Model {
    /**
     * @var \Opencart\System\Engine\Registry
     */
    protected \Opencart\System\Engine\Registry $registry;

    /**
     * Constructor
     *
     * @param \Opencart\System\Engine\Registry $registry
     */
    public function __construct(\Opencart\System\Engine\Registry $registry) {
        $this->registry = $registry;

        // Логируем создание модели
        $log = new \Opencart\System\Library\Log('error.log'); // Логируем в общий файл error.log
        // $log->write('Model class instantiated: ' . get_class($this));
    }

    /**
     * __get
     *
     * @param string $key
     *
     * @return object
     */
    public function __get(string $key): object {
        $log = new \Opencart\System\Library\Log('error.log'); // Создаём логгер
        if ($this->registry->has($key)) {
            // $log->write('Registry key accessed: ' . $key);
            return $this->registry->get($key);
        } else {
            $log->write('Error: Could not call registry key ' . $key . '!');
            throw new \Exception('Error: Could not call registry key ' . $key . '!');
        }
    }

    /**
     * __set
     *
     * @param string $key
     * @param object $value
     *
     * @return void
     */
    public function __set(string $key, object $value): void {
        $log = new \Opencart\System\Library\Log('error.log'); // Создаём логгер
        $log->write('Registry key set: ' . $key);
        $this->registry->set($key, $value);
    }

    /**
     * __isset
     *
     * https://www.php.net/manual/en/language.oop5.overloading.php#object.set
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key): bool {
        $log = new \Opencart\System\Library\Log('error.log'); // Создаём логгер
        // $log->write('Registry key checked (isset): ' . $key);
        return $this->registry->has($key);
    }
}
