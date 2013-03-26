<?php
/**
 * Helper.php
 *
 * @author    Kamilov Ramazan
 * @link      http://www.kamilov.ru
 * @contact   ramazan@kamilov.ru
 *
 */

class RcHelper {
    /**
     * смешивание массивов
     * переписано для возможности использования до загрузки фреймворка
     * @see CMap::mergearray()
     * @param array $a
     * @param array $b
     * @return mixed
     */
    public static function mergeArray($a, $b) {
        $arrays = func_get_args();
        $result = array_shift($arrays);

        while(($array = array_shift($arrays)) !== null) {
            foreach($array as $key => $value) {
                if(is_integer($key)) {
                    isset($result[$key]) ? array_push($result, $value) : $result[$key] = $value;
                }
                else if(is_array($value) and isset($result[$key])) {
                    $result[$key] = self::mergeArray($result[$key], $value);
                }
                else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * поиск в указанном файле массива данных
     * @param array $fileName
     * @return array
     */
    public static function getArrayFromFile($fileName) {
        if(is_file($fileName) and is_array(($fileData = require $fileName))) {
            return $fileData;
        }
        return array();
    }
}