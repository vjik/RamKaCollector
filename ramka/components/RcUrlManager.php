<?php
/**
 * RcUrlManager.php
 *
 * @author    Kamilov Ramazan
 * @link      http://www.kamilov.ru
 * @contact   ramazan@kamilov.ru
 *
 */

class RcUrlManager extends CUrlManager {
    /**
     * список url адресов доступных в виде псевдонимов
     * @var array
     */
    public $urlAliases = array();

    /**
     * альтенативный метод создания ссылок
     * при указании в начале ссылки ../ идёт поиск ссылки к приложению с именем,
     * которое расположено между ../ и следующим слешем ( если он существует )
     * если приложение не было найдено, указанное имя приписывается к указаному пути
     * @param string $route
     * @param array $params
     * @param string $ampersand
     * @return string
     */
    public function createUrl($route, $params = array(), $ampersand = '&') {
        return $this -> getApplicationHost($route) . parent::createUrl($route, $params, $ampersand);
    }

    /**
     * возвращает url адрес приложения
     * @param string $route
     * @return string
     */
    public function getApplicationHost(&$route) {
        $result = '';

        if(strpos($route, '../') === 0) {
            $route = substr($route, 3);

            if(($endPosition = strpos($route, '/')) !== false) {
                $name = substr($route, 0, $endPosition);
            }
            else {
                $name = $route;
            }

            if(isset($this -> urlAliases[$name])) {
                $result = $this -> urlAliases[$name];
                $route  = $endPosition !== false ? substr($route, $endPosition + 1) : '';
            }
        }

        return $result;
    }
}