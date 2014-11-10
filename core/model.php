<?php
namespace Chief;

class Model extends Core
{    
    protected $db;

    static public function initialize($model, $db, $module)
    {        
        if(strpos($model, '/') !== false) {
            list($module, $model) = explode('/', $model);
        }

        $model = str_replace('_model', '', $model);
        $path  = strtolower(sprintf('modules/%s/model/%s_model.php', $module, $model));

        if(file_exists($path)) {
            require_once($path);
            $model = 'Chief\\'.ucfirst(strtolower($model.'_model'));
            $model = new $model($db);
            return $model;
        } else {
            throw new Exception('Model '.$path.' not found.');
        }
    }

    public function getAll($table, $order = 'id ASC')
    {
        return $this->db->all("SELECT * FROM `%s` ORDER BY %s", $table, $order);
    }

    public function getOne($table, $id, $default = null)
    {
        if(empty($id)) {
            $return = is_object($default) ? $default : (object)array(
                'id' => 0
            );
        } else {
            $return = $this->db->row("SELECT * FROM `%s` WHERE id = '%d'", $table, $id);
        }
        return $return;
    }
}
