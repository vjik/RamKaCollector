<?php
/**
 * Collector.php
 *
 * @author    Kamilov Ramazan
 * @link      http://www.kamilov.ru
 * @contact   ramazan@kamilov.ru
 *
 */

class RamKaCollector {
    /**
     * имя интерфейса описывающего объект информации о приложении
     */
    const INFO_INTERFACE = 'RamKaApplicationInfoInterface';

    /**
     * шаблон для автоматического поиска приложений
     */
    const SEARCH_PATTERN = '{directory}/{application}/*.php';

    /**
     * экземпляр текущего объекта
     * @var RamKaCollector
     */
    private static $instance;

    /**
     * объект глобальной конфигурации
     * @var RcConfig
     */
    private $config;

    /**
     * путь к директории с приложениями по умолчанию
     * @var string
     */
    private $defaultDirectory;

    /**
     * имя рабочего окржения
     * @var null|string
     */
    private $environment;

    /**
     * список псевдонимов для директорий
     * @var array
     */
    private $pathAliases = array();

    /**
     * список псевдонимов для url'ов приложений
     * @var array
     */
    private $urlAliases = array();

    /**
     * список веб приложений доступных для запуска
     * @var array
     */
    private $webApplications = array();

    /**
     * список консольных приложений доступных для запуска
     * @var array
     */
    private $consoleApplications = array();

    /**
     * путь к фреймворку Yii
     * @var string
     */
    private $yiiPath;

    /**
     * возвращет экземпляр объекта текущего класса
     * @return RamKaCollector
     */
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * конструктор
     * определение переменных и сохранение псевдонима директории, в которой находится данный файл
     */
    private function __construct() {
        $this -> config = new RcConfig();

        $this -> setDefaultDirectory(dirname(__DIR__));
        $this -> setAliasOfPath('ramka', __DIR__);
    }

    /**
     * сохранение глобальной конфигурации
     * @param array|RcConfig|string $config
     * @return $this
     */
    public function setConfig($config) {
        $this -> config -> merge($config);

        if($this -> environment !== null and is_string($config)) {
            $this -> config -> load(dirname($config) . DIRECTORY_SEPARATOR . $this -> environment . '/main.php');
        }

        return $this;
    }

    /**
     * возвращает секцию глобальной конфигурации с указаным
     * @param string $name
     * @return array|mixed|null
     */
    public function getConfig($name) {
        return $this -> config -> get($name);
    }

    /**
     * определение дирекории с приложениями по умолчанию
     * @param string $directory
     * @return $this
     */
    public function setDefaultDirectory($directory) {
        $this -> defaultDirectory = $directory;
        return $this;
    }

    /**
     * возвращает путь к директории с приложениями по умолчанию
     * @return string
     */
    public function getDefaultDirectory() {
        return $this -> defaultDirectory;
    }

    /**
     * сохраняет имя рабочего окружения
     * пуруопределение запрещено
     * @param string $name
     * @return $this
     */
    public function setEnvironment($name) {
        if($this -> environment === null) {
            $this -> environment = $name;
        }
        return $this;
    }

    /**
     * возвращает имя рабочего окружения
     * @return null|string
     */
    public function getEnvironment() {
        return $this -> environment;
    }

    /**
     * определение псевдонима директории
     * переопределение невозможно
     * @param string $alias
     * @param string $path
     * @return $this
     * @throws Exception
     */
    public function setAliasOfPath($alias, $path) {
        if(isset($this -> pathAliases[$alias])) {
            throw new Exception('Alias of path "' . $alias . '" already exists.');
        }
        $this -> pathAliases[$alias] = $path;
        return $this;
    }

    /**
     * определение псевдонима url адреса приложения
     * переопределение невозможно
     * @param string $alias
     * @param string $url
     * @return $this
     * @throws Exception
     */
    public function setAliasOfUrl($alias, $url) {
        if(isset($this -> urlAliases[$alias])) {
            throw new Exception('Alias of url "' . $alias . '" already exists.');
        }
        $this -> urlAliases[$alias] = $url;
        return $this;
    }

    /**
     * сохранение пути к фреймворку
     * если файл не существует, путь не будет сохранён
     * @param string $fileName
     * @return $this
     */
    public function setYii($fileName) {
        if($this -> yiiPath === null and ($fileName = realpath($fileName)) !== false and is_file($fileName)) {
            $this -> yiiPath = $fileName;
        }

        return $this;
    }

    /**
     * загрузка Yii
     * возвращает флаг определяющий, что фреймворк был загружен
     * @return bool
     */
    public function getYii() {
        if($this -> yiiPath !== null) {
            require_once $this -> yiiPath;
            $this -> yiiPath = null;
        }
        return class_exists('Yii', false);
    }

    /**
     * метод добавляет переданное приложение в список приложений доступных для запуска
     * @param iRcApplication|RcApplication $application
     * @param int|null $priority
     * @return $this
     */
    public function addApplication(iRcApplication $application, $priority = null) {
        if($priority === null) {
            $priority = $application -> getPriority();
        }

        if($application -> isCli()) {
            $applications = &$this -> consoleApplications;
        }
        else {
            $applications = &$this -> webApplications;
        }

        if(!isset($applications[$priority])) {
            $applications[$priority] = array();
        }

        array_push($applications[$priority], $application);

        if($application -> isCli() === false) {
            $this -> setAliasOfUrl($application -> getId(), $application -> getBaseUrl());
        }

        return $this;
    }

    /**
     * поиск и автоматическое добавление приложений из указанной директории
     * @param string $directory
     * @return $this
     */
    public function findApplications($directory) {
        if(is_dir($directory)) {
            $classes = get_declared_classes();

            foreach(glob(strtr(self::SEARCH_PATTERN, array('{directory}' => $directory, '{application}' => '*')), GLOB_NOSORT) as $fileName) {
                require_once $fileName;
            }

            $classes = array_diff(get_declared_classes(), $classes);

            foreach($classes as $className) {
                if(is_subclass_of($className, self::INFO_INTERFACE)) {
                    $this -> addApplication(new $className);
                }
            }
        }

        return $this;
    }

    /**
     * поиск конкретного приложения
     * @param iRcApplication|string $application
     * @return null|iRcApplication|RcApplication
     */
    private function findSpecifiedApplication($application) {
        if($application instanceof iRcApplication) {
            return $application;
        }
        else if(is_string($application)) {
            $classes = get_declared_classes();

            foreach(glob(strtr(self::SEARCH_PATTERN, array('{directory}' => $this -> defaultDirectory, '{application}' => $application)), GLOB_NOSORT) as $fileName) {
                require_once $fileName;
            }

            $classes = array_diff(get_declared_classes(), $classes);

            foreach($classes as $className) {
                if(is_subclass_of($className, self::INFO_INTERFACE)) {
                    return new $application;
                }
            }
        }

        return null;
    }

    /**
     * поиск активного приложения
     * @return iRcApplication|null|RcApplication
     */
    private function findActiveApplication() {
        ksort($this -> consoleApplications);
        ksort($this -> webApplications);

        foreach((php_sapi_name() === 'cli' ? $this -> consoleApplications : $this -> webApplications) as $applications) {
            foreach($applications as $application) {
                /** @var $application iRcApplication */
                if($application -> isActive()) {
                    return $application;
                }
            }
        }

        return null;
    }

    /**
     * сохранение в конфигурации домена для которого будут сохраняться cookie
     * @param string $domain
     */
    public function setCookieDomain($domain) {
        $this -> setConfig(array(
            'components' => array(
                'session' => array(
                    'cookieParams' => array(
                        'domain'   => $domain
                    )
                ),

                'user' => array(
                    'identityCookie'  => array(
                        'domain' => $domain
                    )
                )
            )
        ));
    }

    /**
     * сохранение конфигурации
     * @param RcConfig $config
     * @param iRcApplication $application
     */
    public function setCollectorConfig(RcConfig $config, iRcApplication $application) {

    }

    /**
     * запуск приложения
     * @param null|iRcApplication|string $application
     * @throws CHttpException
     * @throws Exception
     */
    public function run($application = null) {
        if($this -> getYii() === false) {
            throw new Exception('Yii framework is not loaded.');
        }

        if(($application !== null and ($application = $this -> findSpecifiedApplication($application)) !== null) or ($application !== null and ($application = $this -> findActiveApplication()) === null)) {
            throw new CHttpException(400, 'Bad Request');
        }

        $this -> setAliasOfPath($application -> getId(), $application -> getBasePath());

        if(!($config = $application -> getConfig()) instanceof RcConfig) {
            $config = new RcConfig($config);
        }

        if($application -> useGlobalConfig()) {
            $config = $this -> config -> merge($config);
        }

        $this -> setCollectorConfig($config, $application);

        foreach($this -> pathAliases as $alias => $path) {
            Yii::setPathOfAlias($alias, $path);
        }

        $application = php_sapi_name() === 'cli' ? Yii::createConsoleApplication($config -> toArray()) : Yii::createWebApplication($config -> toArray());

        $application -> run();
    }
}

require_once __DIR__ . '/base/Application.php';
require_once __DIR__ . '/base/Config.php';
require_once __DIR__ . '/base/Helper.php';