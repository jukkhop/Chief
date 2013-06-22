<?php
namespace Chief;

class Notifications
{    
    private static $db;

    public function __construct($db)
    {
        self::$db = $db;
    }

    public static function html($purge = true)
    {
        $notifications = $purge ? self::purge() : self::get();
        $html = false;
        if(!empty($notifications)) {
            $html = '<div class="notifications">';
            foreach($notifications as $notification) {
                $html .= '<div class="alert '.$notification->class.'"><a class="close" data-dismiss="alert" href="#"><i class="icon-remove"></i></a>'.$notification->text.'</div>';
            }
            $html .= '</div>';
        }
        return $html;
    }

    public static function set($class, $text)
    {
        $notification = (object)array(
            'datetime' => date('Y-m-d H:i:s'),
            'class'    => $class,
            'text'     => $text
        );
        if(!isset($_SESSION['notifications'])) {
            $_SESSION['notifications'] = array();
        }
        $_SESSION['notifications'][] = $notification;
    }

    public static function count()
    {
        return isset($_SESSION['notifications']) ? count($_SESSION['notifications']) : 0;
    }

    public static function get()
    {
        return isset($_SESSION['notifications']) ? $_SESSION['notifications'] : [];
    }

    public static function purge()
    {
        $notifications = self::get();
        $_SESSION['notifications'] = array();
        unset($_SESSION['notifications']);
        return $notifications;
    }

    public static function warning($text)
    {
        self::set('alert-warning', $text);
    }

    public static function error($text)
    {
        self::set('alert-error', $text);
    }

    public static function info($text)
    {
        self::set('alert-info', $text);
    }

    public static function success($text)
    {
        self::set('alert-success', $text);
    }
}
