<?php
/**
 * Application.php
 *
 * @author    Kamilov Ramazan
 * @link      http://www.kamilov.ru
 * @contact   ramazan@kamilov.ru
 *
 */

abstract class RcApplication implements iRcApplication {
    /**
     * экземпляр объекта конфигурации приложения
     * @var RcConfig
     */
    private $config;

    /**
     * возвращает список секций конфигурации которые стоит подргрузить отдельно
     * @return array
     */
    protected function configSections() {
        return array('components' => 'database', 'params');
    }

    /**
     * метод возращающий идентификатор пиложения
     * @return string
     */
    public function getId() {
        return basename($this -> getBasePath());
    }

    /**
     * возвращает полный путь к приложению
     * @return string
     */
    public function getBasePath() {
        $reflection = new ReflectionObject($this);
        return dirname($reflection -> getFileName());
    }

    /**
     * возвращает url путь к приложению
     * обязательно если, приложение не консольное ( isCli() === false )
     * @return string
     */
    public function getBaseUrl() {
        return '';
    }

    /**
     * возвращает объект конфигурации приложения
     * @return null|RcConfig
     */
    public function getConfig() {
        if($this -> config === null) {
            $directory   = $this -> getBasePath() . '/configs';
            $environment = RamKaCollector::getInstance() -> getEnvironment();
            $config      = new RcConfig($directory . '/main.php');

            if($environment !== null) {
                $config -> load($directory . DIRECTORY_SEPARATOR . $environment . '/main.php');
            }

            foreach($this -> configSections() as $sectionName => $fileName) {
                if(is_integer($sectionName)) {
                    $sectionName = $fileName;
                }

                $config -> merge(array(
                    $sectionName => RcHelper::getArrayFromFile($directory . DIRECTORY_SEPARATOR . $fileName . '.php')
                ));

                if($environment !== null) {
                    $config -> merge(array(
                        $sectionName => RcHelper::getArrayFromFile($directory . DIRECTORY_SEPARATOR . $fileName . '.php')
                    ));
                }
            }

            $this -> config = $config;
        }
        return $this -> config;
    }

    /**
     * приоритет запуска приложения
     * нужно для того, чтобы при пересечении условий запуска, запускалось нужное приложение
     * @return int
     */
    public function getPriority() {
        return 100;
    }

    /**
     * возвращает флаг определяющий использование глобальной конфигурации
     * @return bool
     */
    public function useGlobalConfig() {
        return true;
    }

    /**
     * возвращает флаг определяющий, что приложение консольное
     * @return bool
     */
    public function isCli() {
        return false;
    }

    /**
     * метод выполняющий условие проверки, для того, чтобы определить запускать данное приложение или нет
     * @return bool
     */
    public function isActive() {
        return false;
    }
}

interface iRcApplication {
    /**
     * метод возращающий идентификатор пиложения
     * @return string
     */
    public function getId();

    /**
     * возвращает полный путь к приложению
     * использование относительного пути может привести к нероботоспособности приложения
     * @return string
     */
    public function getBasePath();

    /**
     * возвращает объект конфигурации приложения
     * @return RcConfig
     */
    public function getConfig();

    /**
     * приоритет запуска приложения
     * нужно для того, чтобы при пересечении условий запуска, запускалось нужное приложение
     * @return int
     */
    public function getPriority();

    /**
     * возвращает флаг определяющий использование глобальной конфигурации
     * @return bool
     */
    public function useGlobalConfig();

    /**
     * возвращает флаг определяющий, что приложение консольное
     * @return bool
     */
    public function isCli();

    /**
     * метод выполняющий условие проверки, для того, чтобы определить запускать данное приложение или нет
     * @return bool
     */
    public function isActive();
}