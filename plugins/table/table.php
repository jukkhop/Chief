<?php
namespace Chief;

class Table extends Plugin
{    
    private $columns = array();
    private $rendered = null;
    private $data = array();
    private $actions = array();
    private $id = null;
    private $emptyMessage = 'Ei rivejä';
    private $active = false;
    private $active_action = false;
    private $selectable = false;    
    private $sort_column = false;
    private $sort_direction = false;
    private $sort_template = false;    
    private $row_class_function = false;
    private $row_style_function = false;    
    private $nav_page = false;
    private $nav_pages = false;
    private $nav_template = false;

    public $sort_direction_asc = 'asc';
    public $sort_direction_desc = 'desc';

    public function __toString() 
    {
        if(is_null($this->rendered)) {
            $this->render();
        }
        return $this->rendered;
    }

    public function setSelectable($bool) 
    {
        $this->selectable = !!$bool;
        return $this;
    }

    public function setData($data) 
    {
        $this->data = $data;
        return $this;
    }

    public function setId($id) 
    {
        $this->id = $id;
        return $this;
    }

    public function setEmptyMessage($emptyMessage) 
    {
        $this->emptyMessage = $emptyMessage;
        return $this;
    }

    public function setRowClassFunction($func) 
    {
        if(is_callable($func)) {
            $this->row_class_function = $func;
        }
        return $this;
    }

    public function setRowStyleFunction($func) 
    {
        if(is_callable($func)) {
            $this->row_style_function = $func;
        }
        return $this;
    }

    public function setSort($sort_column, $sort_direction, $sort_template) 
    {
        $this->sort_column    = $sort_column;
        $this->sort_direction = $sort_direction;
        $this->sort_template  = $sort_template;
        return $this;
    }

    public function setNavigation($nav_page, $nav_pages, $nav_template) 
    {
        $this->nav_page     = $nav_page;
        $this->nav_pages    = $nav_pages;
        $this->nav_template = $nav_template;
        return $this;
    }

    public function action($type, $url) 
    {
        $this->active = false;
        $this->active_action = $type;
        $this->actions[$type] = (object)array(
            'url' => $url,
            'condition' => function() {
                return true;
            },
            'confirm' => false
        );
        return $this;
    }

    public function condition($condition) 
    {
        if($this->active_action) {
            $this->actions[$this->active_action]->condition = $condition;
        }
        return $this;
    }

    public function confirm($message) 
    {
        if($this->active_action) {
            $this->actions[$this->active_action]->confirm = $message;
        }
        return $this;
    }

    public function column($name, $label) 
    {
        $this->active = $name;
        $this->active_action = false;
        $this->columns[$name] = (object)array(
            'label' => $label
        );
        return $this;
    }

    public function transform($func) 
    {
        if($this->active_action !== false) {
            $this->actions[$this->active_action]->transform = $func;
        } elseif($this->active !== false) {
            $this->columns[$this->active]->transform = $func;
        }
        return $this;
    }

    public function tally($func) 
    {
        if($this->active !== false) {
            $this->columns[$this->active]->tally = $func;
        }
        return $this;
    }

    public function width($width) 
    {
        if($this->active !== false) {
            $this->columns[$this->active]->width = $width;
        }
        return $this;
    }

    public function css($css) 
    {
        if($this->active !== false) {
            $this->columns[$this->active]->css = $css;
        }
        return $this;
    }

    public function hidden($bool = true) 
    {
        if($this->active !== false) {
            $this->columns[$this->active]->hidden = !!$bool;
        }
        return $this;
    }

    public function dateFormat($format) 
    {
        $this->transform(function($value) use ($format) {
            return empty($value) ? '' : date($format, strtotime($value));
        });
        return $this;
    }

    public function after($after) 
    {
        $new = array();        
        foreach($this->columns as $key => $column) {
            if($key == $this->active) continue;
            $new[$key] = $column;
            if($key == $after) {
                $new[$this->active] = $this->columns[$this->active];
            }
        }
        $this->columns = $new;
        return $this;
    }

    public function before($before) 
    {
        $new = array();        
        foreach($this->columns as $key => $column) {
            if($key == $this->active) continue;
            if($key == $before) {
                $new[$this->active] = $this->columns[$this->active];
            }
            $new[$key] = $column;
        }
        $this->columns = $new;
        return $this;
    }

    public function renderNavigation() 
    {        
        $nav = '';        
        $tags = array(
            array('{sort_column}', '{sort_direction}', '{page}'),
            array($this->sort_column, $this->sort_direction == 'ASC' ? $this->sort_direction_asc : $this->sort_direction_desc, $this->nav_page)
        );

        if($this->nav_template !== false) {
            $nav = '<ul class="table-navigation">';

            $page  = $this->nav_page;
            $pages = $this->nav_pages;

            $first = str_replace($tags[0], $tags[1], str_replace('{page}', 1,                      $this->nav_template));
            $prev  = str_replace($tags[0], $tags[1], str_replace('{page}', max(1, $page - 1),      $this->nav_template));
            $next  = str_replace($tags[0], $tags[1], str_replace('{page}', min($pages, $page + 1), $this->nav_template));
            $last  = str_replace($tags[0], $tags[1], str_replace('{page}', $pages,                 $this->nav_template));

            if($page > 1) {
                $nav .= '<li class="first"><a href="'.$first.'">Ensimmäinen sivu</a></li>';
                $nav .= '<li class="prev"><a href="'.$prev.'">Edellinen sivu</a></li>';
            } else {
                $nav .= '<li class="first"><span>Ensimmäinen sivu</span></li>';
                $nav .= '<li class="prev"><span>Edellinen sivu</span></li>';
            }

            $nav .= '<li class="current"><span>Sivu '.$page.' / '.$pages.'</span></li>';

            if($page < $pages) {
                $nav .= '<li class="next"><a href="'.$next.'">Seuraava sivu</a></li>';
                $nav .= '<li class="last"><a href="'.$last.'">Viimeinen sivu</a></li>';
            } else {
                $nav .= '<li class="next"><span>Seuraava sivu</span></li>';
                $nav .= '<li class="last"><span>Viimeinen sivu</span></li>';
            }

            $nav .= '</ul>';
        }
        return $nav;
    }

    public function render() 
    {
        if(empty($this->data)) {
            $this->rendered = sprintf('<div class="no-rows alert alert-info">%s</div>', $this->emptyMessage);
        } else {
            $id = is_null($this->id) ? '' : ' id="'.$this->id.'"';
            $table = sprintf('<table%s class="table">', $id);        
            $table .= '<tr>';

            if($this->selectable) {
                $table .= '<th style="width: 16px;"><input type="checkbox" class="select-all" /></th>';
            }

            if(!empty($this->actions)) {
                $table .= sprintf('<th class="actions" style="width: %dpx"></th>', count($this->actions) * 20);
            }

            if($this->sort_direction == $this->sort_direction_asc) $this->sort_direction = 'ASC';
            if($this->sort_direction == $this->sort_direction_desc) $this->sort_direction = 'DESC';

            foreach($this->columns as $name => $column) {                
                $label = $column->label;                
                $class = array();
                $hidden = isset($column->hidden) && $column->hidden;

                if($this->sort_column !== false) {
                    $sort_class = array();
                    $sort_direction = 'ASC';

                    if($this->sort_column == $name) {
                        if(strtoupper($this->sort_direction) == 'ASC') {
                            $sort_direction = 'DESC';
                        }
                        $sort_class[] = 'sort-active';
                        $sort_class[] = 'sort-'.strtolower($this->sort_direction);
                    } else {
                        $sort_class[] = 'sort-'.strtolower($sort_direction);
                    }

                    $sort_direction_show = $sort_direction == 'ASC' ? $this->sort_direction_asc : $this->sort_direction_desc;

                    $href  = str_replace(array('{sort_column}', '{sort_direction}'), array($name, $sort_direction_show), $this->sort_template);
                    $label = sprintf('<a href="%s">%s%s</a>', $href, $label, in_array('sort-active', $sort_class) ? '<i class="fa fa-caret-'.(in_array('sort-asc', $sort_class) ? 'up' : 'down').'">' : '');
                    $class[] = implode(' ', $sort_class);
                }

                if(!empty($class)) {
                    $class = ' class="'.implode(' ', $class).'"';
                } else {
                    $class = '';
                }

                $css = isset($column->width) ? sprintf('width: %dpx;', $column->width) : '';

                if(!empty($column->css)) {
                    if(!is_callable($column->css)) {
                        $css .= $column->css;
                    }
                }
                
                if($hidden) {
                    $css .= 'display: none;';
                }

                if(!empty($css)) {
                    $css = ' style="'.$css.'"';
                }

                $table .= sprintf('<th%s%s>%s</th>', $class, $css, $label);
            }
            $table .= '</tr>';

            $tally = array();

            foreach($this->data as $row) {
                $row_class_function = $this->row_class_function;
                $row_class = is_callable($row_class_function) ? ' class="'.$row_class_function($row).'"' : null;

                $row_style_function = $this->row_style_function;
                $row_style = is_callable($row_style_function) ? ' style="'.$row_style_function($row).'"' : null;

                $table .= '<tr'.$row_class.$row_style.'>';
                $i = 0;
                $count = count($this->columns) - 1;

                if($this->selectable) {
                    $table .= '<td class="checkbox"><input type="checkbox" name="row['.(isset($row->id) ? $row->id : '').']" /></td>';
                }

                if(!empty($this->actions)) {
                    $count++;
                    $i++;
                    $table .= '<td class="actions">';
                    foreach($this->actions as $type => $action) {
                        $url = $this->formatString($action->url, $row);
                        $condition = $action->condition;
                        if($condition($row)) {
                            $confirm = null;
                            if($action->confirm) {
                                $confirm = " onclick=\"return confirm('".$action->confirm."');\"";
                            }
                            $table .= sprintf('<a href="%s%s"%s><i class="fa fa-%s"></i></a>', BASE_DIR, $url, $confirm, $type);
                        }
                    }
                    $table .= '</td>';
                }

                foreach($this->columns as $name => $column) {
                    
                    $hidden = isset($column->hidden) && $column->hidden;
                    $value = isset($row->$name) ? $row->$name : '';

                    if(isset($column->tally)) {
                        $tally_func = $column->tally;
                        $tally[$name] = $tally_func(isset($tally[$name]) ? $tally[$name] : null, $value, $row, false); 
                    }

                    if(isset($column->transform)) {
                        $transform = $column->transform;
                        $value = $transform($value, $row);
                    }

                    $value = $this->formatString($value, $row);

                    $css = '';
                    if($hidden) {
                        $css = 'display: none;';
                    }
                    if(isset($column->css)) {
                        if(is_callable($column->css)) {
                            $func = $column->css;
                            $css .= $func($value, $row);
                        } else {
                            $css .= $column->css;
                        }
                    }
                    $css = ' style="'.$css.'"';
                    $table .= sprintf('<td%s>%s</td>', $css, $value);
                    $i++;
                }
                $table .= '</tr>';
            }

            if(!empty($tally)) {
                $table .= '<tr class="tally">';
                $emptyCells = ($this->selectable ? 1 : 0) + (!empty($this->actions) ? 1 : 0);
                if($emptyCells > 0) {
                    $table .= '<td colspan="'.$emptyCells.'"></td>';
                }
                foreach($this->columns as $name => $column) {
                    $value = null;
                    if(isset($tally[$name])) {
                        $tally_func = $column->tally;
                        $value = $tally_func($tally[$name], null, null, true);
                    }

                    $css = '';
                    if(isset($column->css)) {
                        if(is_callable($column->css)) {
                            $func = $column->css;
                            $css = ' style="'.$func($value, $row).'"';
                        } else {
                            $css = ' style="'.$column->css.'"';
                        }
                    }

                    $table .= sprintf('<td%s>%s</td>', $css, $value);
                }
                $table .= '</tr>';
            }

            $table .= '</table>';            
            $table .= $this->renderNavigation();            
            $this->rendered = $table;
        }
        return $this;
    }

    private function formatString($url, $vars) 
    {
        preg_match_all('/\{(.*?)\}/', $url, $found);
        foreach($found[0] as $key => $var) {
            $_key = $found[1][$key];
            if(isset($vars->$_key)) {
                $url = str_replace($var, $vars->$_key, $url);
            }
        }
        return $url;
    }
}
