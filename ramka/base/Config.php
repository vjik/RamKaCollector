<?php
/**
 * Config.php
 *
 * @author    Kamilov Ramazan
 * @link      http://www.kamilov.ru
 * @contact   ramazan@kamilov.ru
 *
 */

class RcConfig implements ArrayAccess {
    /**
     * данные конфигурации
     * @var array
     */
    private $data = array();

    /**
     * конструктор
     * сохраняет данные переданные при инициализации
     * @param array|null|RcConfig|string $config
     */
    public function __construct($config = null) {
        if($config !== null) {
            $this -> merge($config);
        }
    }

    /**
     * загрузка конфигурации из файла
     * @param string $fileName
     * @return $this
     */
    public function load($fileName) {
        $this -> data = RcHelper::mergeArray($this -> data, RcHelper::getArrayFromFile($fileName));
        return $this;
    }

    /**
     * смешивание конфигурций
     * @param array|RcConfig|string $config
     * @return $this
     */
    public function merge($config) {
        if(is_string($config)) {
            return $this -> load($config);
        }
        else if($config instanceof self) {
            $config = $config -> toArray();
        }

        $this -> data = RcHelper::mergeArray($this -> data, $config);

        return $this;
    }

    /**
     * возвращает конфигурации в виде массива
     * @return array
     */
    public function toArray() {
        return $this -> data;
    }

    /**
     * сохранение элемента конфигурации
     * для хождения по многомерному массиву можно использовать точку в качестве разделителя
     * точка будет являться разделителем до тех пор пока в имеющимся массиве данных будут ключи с указанными именами
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value) {
        $array = &$this -> data;

        if(strpos($key, '.') !== null) {
            $parts = preg_split('/\./', $key);

            while(($part = array_shift($parts)) !== null) {
                if(!isset($array[$part])) {
                    array_unshift($parts, $part);
                    break;
                }
                else {
                    $array = &$array[$part];
                }
            }

            if($parts !== array()) {
                if(!is_array($array)) {
                    $array = array();
                }
                $array[implode('.', $parts)] = $value;
            }
            else {
                $array = $value;
            }
        }
        else {
            $data[$key] = $value;
        }

        return $this;
    }

    /**
     * возвращает значение из указанной секции
     * для доступа к секции в многоменом массиве используется точка
     * @param string $key
     * @param mixed|null $default
     * @return array|mixed|null
     */
    public function get($key, $default = null) {
        if(strpos($key, '.')) {
            $result = $this -> data;

            foreach(explode('.', $key) as $part) {
                if(isset($result[$part])) {
                    $result = $result[$part];
                }
                else {
                    $result = $default;
                    break;
                }
            }
            return $result;
        }

        return isset($this -> data[$key]) ? $this -> data[$key] : $default;
    }

    /**
     * возвращает флаг определяющий наличие указанной секции в сохранённых данных
     * @param string $key
     * @return bool
     */
    public function has($key) {
        if(strpos($key, '.')) {
            $array = $this -> data;

            foreach(explode('.', $key) as $part) {
                if(!isset($array[$part])) {
                    return false;
                }
            }
            return true;
        }

        return isset($this -> data[$key]);
    }

    // -- ArrayAccess -- //
    public function offsetSet($key, $value) {
        $this -> set($key, $value);
    }

    public function offsetGet($key) {
        return $this -> get($key);
    }

    public function offsetExists($key) {
        return $this -> has($key);
    }

    public function offsetUnset($key) {

    }
    // -- /ArrayAccess -- //
}