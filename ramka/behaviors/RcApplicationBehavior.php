<?php
/**
 * RcApplicationBehavior.php
 *
 * @author    Kamilov Ramazan
 * @link      http://www.kamilov.ru
 * @contact   ramazan@kamilov.ru
 *
 */

class RcApplicationBehavior extends CBehavior {
    /**
     * путь к директории с файлами, которые следует опубликовать
     * @var string
     */
    public $assetPath;

    /**
     * url путь к директории с общими файлами
     * @var string
     */
    public $assetUrl;

    /**
     * список обработчиков событий
     * @return array
     */
    public function events() {
        return array(
            'onBeginRequest' => 'beginRequestHandler'
        );
    }

    public function beginRequestHandler(CEvent $event) {
        /** @var $application CWebApplication|CConsoleApplication */
        $application = $event -> sender;

        if($application instanceof CWebApplication) {
            $this -> publishAssets($application);
        }
    }

    private function publishAssets(CWebApplication $application) {
        if($this -> assetPath === null) {
            $this -> assetPath = $application -> getBasePath() . '/assets';
        }

        if(is_dir($this -> assetPath)) {
            $this -> assetUrl = $application -> getAssetManager() -> publish($this -> assetPath);
        }
    }
}