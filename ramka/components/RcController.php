<?php
/**
 * RcController.php
 *
 * @author    Kamilov Ramazan
 * @link      http://www.kamilov.ru
 * @contact   ramazan@kamilov.ru
 *
 */

class RcController extends CController {
    /**
     * обёртка создания url-ссылки для подстановки хоста приложения
     * @param string $route
     * @param array $params
     * @param string $ampersand
     * @return string
     */
    public function createUrl($route, $params = array(), $ampersand = '&') {
        return Yii::app() -> getUrlManager() -> getApplicationHost($route) . parent::createUrl($route, $params, $ampersand);
    }
}