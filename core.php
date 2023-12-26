


<?php
class Core {

    public function __construct() {
        $this->id = 0;
    }


    public static function F5Fix(){
        $current_url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        header('Location: ' . $current_url);
    }

    public static function fullURL(){
        $fullUrl = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $fullUrl;
    }
}
?>