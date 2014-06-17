<?php
namespace Chief;

class Form extends Plugin
{
    private $rendered = null;
    private $method = 'post';
    private $action = null;
    private $values = array();
    private $errors = array();
    private $fields = array();
    private $transforms = array();
    private $className;
    private $readOnly;

    private $inlineMode = false;

    private $hasFile = false;
    private $active;

    public static function escape($str)
    {
        $str = html_entity_decode($str);
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    public function __toString()
    {
        if($this->inlineMode) {
            return $this->render_field($this->active);
        } else {
            if(is_null($this->rendered)) {
                $this->render();
            }
            return $this->rendered;
        }
    }

    public function isSent()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public function getRendered()
    {
        return $this->rendered;
    }

    public function setInlineMode($bool)
    {
        $this->inlineMode = !!$bool;
        return $this;
    }

    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setAction($action)
    {
        if(strpos($action, 'http') === false && $action[0] != '/') {
            $action = BASE_DIR.$action;
        }        
        $this->action = $action;
        return $this;
    }

    public function setValues($values, $prefix = null)
    {
        $values = (array)$values;
        $values = self::flatten(null, $values);
        foreach($values as $key => $value) {
            $key = $prefix.$key;
            $this->values[is_numeric($key) ? (int)$key : $key] = $value;
        }        
        return $this;
    }

    public function setValue($key, $value)
    {
        $this->values[is_numeric($key) ? (int)$key : $key] = $value;
        return $this;
    }

    public function setReadOnly($bool)
    {
        $this->readOnly = !!$bool;
    }

    private static function flatten($key, $value) {
        $return = array();
        foreach($value as $k => $v) {
            $new_key = is_null($key) ? $k : $key.'['.$k.']';
            if(is_object($v)) {
                $v = (array)$v;
            }
            if(is_array($v)) {

                $new = array();
                $flattened = self::flatten($new_key, $v);
                foreach($return as $_k => $_v) {
                    $new[$_k] = $_v;
                }
                foreach($flattened as $_k => $_v) {
                    $new[$_k] = $_v;
                }

                $return = $new;
            } else {
                $return[$new_key] = $v;
            }
        }
        return $return;
    }

    public function setErrors($errors)
    {
        $errors = self::flatten(null, $errors);
        $this->errors = (array)$errors;
        return $this;
    }

    public function fieldset($legend)
    {
        $name = uniqid();
        $this->active = $name;
        $this->fields[$name] = array(
            'type' => 'fieldset',
            'label' => $legend
        );
        return $this;
    }

    public function fieldsetClose()
    {
        $name = uniqid();
        $this->active = $name;
        $this->fields[$name] = array(
            'type' => 'fieldsetClose'
        );
        return $this;
    }

    public function hidden($name)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'hidden',
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                return sprintf('<input type="hidden" name="%s" value="%s"%s%s>', $field['name'], Form::escape($field['value']), $disabled, $readonly);
            }
        );
        return $this;
    }

    public function span($name, $label = '')
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'text',
            'label' => $label,
            'generator' => function($field) {
                return sprintf('<span>%s</span>', Form::escape($field['value']));
            }
        );
        return $this;
    }

    public function text($name, $label = '')
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'text',
            'label' => $label,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                $width = is_null($field['width']) ? '' : sprintf(' style="width: %s;"', is_numeric($field['width']) ? $field['width'].'px' : $field['width']);
                return sprintf('<input id="%s" type="text" name="%s" value="%s"%s%s%s>', $field['name'], $field['name'], Form::escape($field['value']), $disabled, $readonly, $width);
            }
        );
        return $this;
    }

    public function date($name, $label)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'date',
            'label' => $label,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                $width = is_null($field['width']) ? '' : sprintf(' style="width: %s;"', is_numeric($field['width']) ? $field['width'].'px' : $field['width']);
                return sprintf('<input id="%s" type="text" class="date" name="%s" value="%s"%s%s%s>', $field['name'], $field['name'], Form::escape($field['value']), $disabled, $readonly, $width);
            }
        );
        return $this;
    }

    public function datetime($name, $label)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'datetime',
            'label' => $label,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                return sprintf('<input id="%s" type="text" class="datetime" name="%s" value="%s"%s%s>', $field['name'], $field['name'], Form::escape($field['value']), $disabled, $readonly);
            }
        );
        return $this;
    }

    public function password($name, $label)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'password',
            'label' => $label,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                return sprintf('<input id="%s" type="password" name="%s" value="%s"%s%s>', $field['name'], $field['name'], Form::escape($field['value']), $disabled, $readonly);
            }
        );
        return $this;
    }

    public function file($name, $label)
    {
        $this->hasFile = true;
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'file',
            'label' => $label,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                $multiple = substr($field['name'], -2) == '[]' ? ' multiple' : '';
                return sprintf('<input id="%s"%s type="file" name="%s"%s%s>', $field['name'], $multiple, $field['name'], $disabled, $readonly);
            }
        );
        return $this;
    }

    public function select($name, $label, $options, $no_keys = false)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'select',
            'label' => $label,
            'options' => $options,
            'no_keys' => $no_keys,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                $width = is_null($field['width']) ? '' : sprintf(' style="width: %s;"', is_numeric($field['width']) ? $field['width'].'px' : $field['width']);
                $multiple = substr($field['name'], -2) == '[]' ? ' multiple' : '';
                $select = sprintf('<select id="%s" name="%s"%s%s%s%s>', $field['name'], $field['name'], $disabled, $readonly, $width, $multiple);
                if(is_array($field['options']) && !empty($field['options'])) {
                    foreach($field['options'] as $k => $v) {
                        if($field['no_keys']) $k = $v;
                        $selected = $k == $field['value'] ? ' selected="selected"' : '';
                        $select .= sprintf('<option value="%s"%s>%s</option>', $k, $selected, $v);
                    }
                }
                $select .= '</select>';
                return $select;
            }
        );
        return $this;
    }

    public function checkbox($name, $label, $options = null)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'checkbox',
            'label' => $label,
            'options' => $options,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                $checkbox = '<ul>';                
                if(is_null($field['options'])) {
                    $checked = (int)$field['value'] === 1 || $field['value'] === 'on' ? ' checked="checked"' : '';
                    $checkbox .= sprintf('<li><label><input type="checkbox" name="%s"%s%s%s>%s</label></li>', $field['name'], $checked, $disabled, $readonly, $field['label']);
                } elseif(is_array($field['options']) && !empty($field['options'])) {
                    foreach($field['options'] as $k => $v) {
                        if(is_array($field['value'])) {
                            $checked = in_array($k, $field['value']) || (isset($field['value'][$k]) && !is_numeric($field['value'][$k])) ? ' checked="checked"' : '';
                        } else {
                            $checked = $k == $field['value'] ? ' checked="checked"' : '';
                        }
                        $checkbox .= sprintf('<li><label><input type="checkbox" name="%s" value="%s"%s>%s</label></li>', $field['name'], $k, $checked, $v);
                    }
                }
                $checkbox .= '</ul>';
                return $checkbox;
            }
        );
        return $this;
    }

    public function radio($name, $label, $options)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'radio',
            'label' => $label,
            'options' => $options,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                $radio = '<ul>';
                if(is_array($field['options']) && !empty($field['options'])) {
                    foreach($field['options'] as $k => $v) {
                        $checked = $k == $field['value'] ? ' checked="checked"' : '';
                        $radio .= sprintf('<li><label><input type="radio" name="%s"%s%s%s value="%s">%s</label></li>', $field['name'], $checked, $disabled, $readonly, $k, $v);
                    }
                }
                $radio .= '</ul>';
                return $radio;
            }
        );
        return $this;
    }

    public function textarea($name, $label)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'textarea',
            'label' => $label,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                $width = is_null($field['width']) ? '' : sprintf(' style="width: %s;"', is_numeric($field['width']) ? $field['width'].'px' : $field['width']);
                return sprintf('<textarea id="%s" name="%s"%s%s%s>%s</textarea>', $field['name'], $field['name'], $width, $disabled, $readonly, Form::escape($field['value']));
            }
        );
        return $this;
    }

    public function wysiwyg($name, $label)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'textarea',
            'label' => $label,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                $width = is_null($field['width']) ? '' : sprintf(' style="width: %s;"', is_numeric($field['width']) ? $field['width'].'px' : $field['width']);
                return sprintf('<textarea class="wysiwyg" id="%s" name="%s"%s%s%s>%s</textarea>', $field['name'], $field['name'], $width, $disabled, $readonly, Form::escape($field['value']));
            }
        );
        return $this;
    }

    public function button($name, $label)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'button',
            'label' => $label,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                return sprintf('<input id="%s" class="btn" type="button" name="%s" value="%s"%s%s>', $field['name'], $field['name'], $field['label'], $disabled, $readonly);
            }
        );
        return $this;
    }

    public function submit($name, $label, $no_label = false)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'submit',
            'label' => $label,
            'no_label' => $no_label,
            'generator' => function($field) {
                $disabled = $field['disabled'] ? ' disabled' : '';
                $readonly = $field['readonly'] ? ' readonly' : '';
                return sprintf('<input id="%s" class="btn btn-primary" type="submit" name="%s" value="%s"%s%s>', $field['name'], $field['name'], $field['label'], $disabled, $readonly);
            }
        );
        return $this;
    }

    public function custom($name, $html)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'custom',
            'label' => $html,
            'no_label' => true,
            'generator' => function($field) {
                return $field['label'];
            }
        );
        return $this;
    }

    public function div($name, $label)
    {
        $this->active = $name;
        $this->fields[$name] = array(
            'name' => $name,
            'type' => 'div',
            'label' => $label,
            'generator' => function($field) {
                if(!empty($field['options'])) {
                    $field['value'] = isset($field['options'][$field['value']]) ? $field['options'][$field['value']] : null;
                }
                return sprintf('<div>%s</div>', Form::escape($field['value']));
            }
        );
        return $this;
    }

    public function width($width)
    {
        return $this->updateActiveField('width', $width);
    }

    private function getDataFields() {
        $html = ' ';
        foreach($this->fields[$this->active] as $key => $value) {
            $html .= is_numeric($key) ? 'data-'.$value : 'data-'.$key.'="'.$value.'"';
        }
        return rtrim($html);
    }

    public function data($data)
    {
        return $this->updateActiveField('data', $data);
    }

    public function append($append)
    {
        return $this->updateActiveField('append', $append);
    }

    public function prepend($prepend)
    {
        return $this->updateActiveField('prepend', $prepend);
    }

    public function disabled($bool = true)
    {
        return $this->updateActiveField('disabled', !!$bool);
    }
    
    public function readonly($bool = true)
    {
        return $this->updateActiveField('readonly', !!$bool);
    }

    public function required($bool = true)
    {
        return $this->updateActiveField('required', !!$bool);
    }

    public function onlyImage($bool = true)
    {
        return $this->updateActiveField('onlyImage', !!$bool);
    }

    public function regex($regex)
    {
        return $this->updateActiveField('regex', $regex);
    }

    public function info($info)
    {
        return $this->updateActiveField('info', $info);
    }

    public function className($className)
    {
        return $this->updateActiveField('className', $className);
    }

    public function value($value)
    {
        $this->values[$this->active] = $value;
        return $this;
    }

    public function after($_field)
    {

        $_fields = array();

        foreach($this->fields as $name => $field) {
            if($name == $this->active) continue;
            $_fields[$name] = $field;
            if($name == $_field) {
                $_fields[$this->active] = $this->fields[$this->active];
            }
        }

        $this->fields = $_fields;

        return $this;
    }

    public function transform($function)
    {
        if(!isset($this->transforms[$this->active])) {
            $this->transforms[$this->active] = array();
        }
        $this->transforms[$this->active][] = $function;
        return $this;
    }

    public function dateFormat($format)
    {
        $this->transform(function($value) use ($format) {
            return !strtotime($value) ? $value : date($format, strtotime($value));
        });
        return $this;
    }

    public function render_field($name)
    {        
        $field_hull = array(
            'name' => null,
            'label' => null,
            'type' => null,
            'options' => null,
            'required' => false,
            'width' => null,
            'disabled' => false,
            'readonly' => false,
            'regex' => null,
            'info' => null,
            'className' => null
        );
        
        $field = array_merge($field_hull, $this->fields[$name]);
        $error = isset($this->errors[$name]) ? sprintf('<p class="error">%s</p>', $this->errors[$name]) : null;
        $label = in_array($field['type'], array('submit', 'button', 'custom')) || ($field['type'] == 'checkbox' && is_null($field['options'])) ? null : $field['label'];
        if(isset($field['no_label']) && $field['no_label']) {
            $label = null;
        }

        $li_class = [];
        if(is_null($label)) {
            $li_class[] = 'no-label';
        }
        
        if(!empty($error)) {
            $li_class[] = 'error';
        }
        
        if(isset($field['className'])) {
            $li_class[] = $field['className'];
        }
        
        $html = '<li'.(empty($li_class) ? '' : ' class="'.implode(' ', $li_class).'"').'>';
        $class = array();

        $value = isset($this->values[$name]) ? $this->values[$name] : null;

        if(isset($this->transforms[$name])) {
            foreach($this->transforms[$name] as $function) {
                $value = $function($value, (object)$this->values);
            }
        }

        if($field['type'] == 'checkbox' && !empty($field['options'])) {
            $basename = rtrim($field['name'], '[]');
            $field['name'] = $basename.'[]';            
            $_values = array();
            foreach($this->values as $key => $value) {
                if(preg_match('~^'.preg_quote($basename).'\[(.*?)\]~', $key, $matches)) {
                    $_values[$matches[1]] = $value;
                }
            }                        
            $value = $_values;
        }

        $field['width'] = isset($field['width']) ? $field['width'] : null;
        $field['value'] = $value;

        $html .= is_null($label) ? '' : sprintf('<label for="%s">%s</label>', $name, $label);

        if($field['type'] == 'hidden') {
            $html = '';
        }
        
        $wrap = array_filter([
            isset($field['prepend']) && strip_tags($field['prepend']) == $field['prepend'] ? 'input-prepend' : null,
            isset($field['append']) && strip_tags($field['append']) == $field['append'] ? 'input-append' : null
        ]);
        
        $wrap = empty($wrap) ? false : implode(' ', $wrap);
        
        if($wrap) {
            $html .= '<div class="'.$wrap.'">';
        }
            
        if(isset($field['prepend'])) {
            $html .= sprintf('<span class="add-on prepend">%s</span>', $field['prepend']);
        }

        if($this->readOnly) {
            $field['generator'] = function($field) {
                if(!empty($field['options'])) {
                    $field['value'] = isset($field['options'][$field['value']]) ? $field['options'][$field['value']] : null;
                }
                return sprintf('<div>%s</div>', $field['value']);
            };
        }

        $html .= $field['generator']($field, $this->values);

        if(isset($field['append'])) {
            $html .= sprintf('<span class="add-on append">%s</span>', $field['append']);
        }

        if($wrap) {
            $html .= '</div>';
        }
        
        $html .= $error;
        
        if(isset($field['info']) && !empty($field['info'])) {
            $html .= sprintf('<p class="info">%s</p>', $field['info']);
        }
        
        if($field['type'] != 'hidden') {
            $html .= '</li>';
        }

        return $html;
    }

    public function render()
    {        
        $fieldset_open = false;
        $ul_open       = false;

        $enctype   = $this->hasFile ? ' enctype="multipart/form-data"' : '';
        $className = !empty($this->className) ? ' class="'.$this->className.'"' : '';        
        $form      = sprintf('<form%s method="%s" action="%s"%s>', $className, $this->method, $this->formatString($this->action, $this->values), $enctype);
        $global_errors_keys = array_diff(array_keys($this->errors), array_keys($this->fields));

        if(!empty($global_errors_keys)) {
            foreach($global_errors_keys as $key) {
                $form .= sprintf('<p class="error">%s</p>', $this->errors[$key]);
            }
        }

        $values = $this->values;

        foreach($this->fields as $key => $_) {
            if(isset($this->transforms[$key])) {
                $value = isset($values[$key]) ? $values[$key] : null;
                foreach($this->transforms[$key] as $function) {
                    $values[$key] = $function($value, (object)$this->values);
                }
            }
        }

        $field_hull = array(
            'name' => null,
            'label' => null,
            'type' => null,
            'options' => null,
            'required' => false,
            'width' => null,
            'disabled' => false,
            'readonly' => false,
            'regex' => null,
            'info' => null,
            'className' => null
        );

        # Insert hidden fields
        foreach($this->fields as $name => $field) {

            $field = array_merge($field_hull, $field);
            if($field['type'] != 'hidden') continue;
            $value = isset($this->values[$name]) ? $values[$name] : null;
            $field['value'] = $value;
            $form .= $field['generator']($field, $values);
        }
        
        $form .= '<ul>';

        # Insert visible fields
        foreach($this->fields as $name => $field) {

            $field = array_merge($field_hull, $field);

            if($field['type'] == 'hidden') continue;

            if($field['type'] == 'fieldset') {                
                $className = !is_null($field['className']) ? sprintf(' class="%s"', $field['className']) : '';

                if($fieldset_open) {
                    $form .= '</fieldset>';
                    $fieldset_open = false;
                }

                $form .= sprintf('<fieldset id="%s"%s>', Common::slug($field['label']), $className);
                $form .= sprintf('<legend>%s</legend>', $field['label']);

                $fieldset_open = true;
                continue;
            }

            if($field['type'] == 'fieldsetClose' && $fieldset_open) {
                $form .= '</fieldset>';
                continue;
            }

            $form .= $this->render_field($name);
        }        

        if($fieldset_open) {
            $form .= '</fieldset>';
        }

        $form .= '</ul>';
        $form .= '</form>';
        $form .= '<div style="clear: both;"></div>';

        $this->rendered = $form;
        return $this;
    }

    public function validate()
    {
        foreach($this->fields as $name => $field) {

            if($field['type'] == 'file') {

                $files = array();

                if(substr($name, -2) == '[]') {
                    $_files = $_FILES[substr($name, 0, -2)];
                    $files = array();
                    foreach($_files as $key => $values) {
                        foreach($values as $id => $value) {
                            $files[$id][$key] = $value;
                        }
                    }
                } else {
                    if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_FILES[$name])) {
                        $this->errors[$name] = 'Tiedosto on liian suuri. Suurin sallittu tiedostokoko on '.Common::human_readable_filesize(Common::max_upload_filesize()).'.';
                    } else {
                        $files = array($_FILES[$name]);
                    }
                }

                foreach($files as $file) {

                    $error = false;

                    switch($file['error']) {
                        case 1:
                            $error = 'Tiedosto on liian suuri. Suurin sallittu tiedostokoko on '.Common::human_readable_filesize(Common::max_upload_filesize()).'.';
                            break;
                        case 2:
                            $error = 'Tiedosto on liian suuri. Suurin sallittu tiedostokoko on '.Common::human_readable_filesize(isset($_POST['MAX_FILE_SIZE']) ? $_POST['MAX_FILE_SIZE'] : Common::max_upload_filesize()).'.';
                            break;
                        case 3:
                            $error = 'Tiedoston latauksessa tapahtui virhe eikä se siirtynyt kokonaisena.';
                            break;
                        case 4:
                            if(isset($field['required']) && $field['required']) {
                                $error = 'Tiedosto puuttuu';
                            }
                            break;
                        case 6:
                            $error = 'Tiedoston lataus epäonnistui sillä väliaikainen tallennuskansio puuttuu.';
                            break;
                        case 7:
                            $error = 'Kirjoitusvirhe tiedoston tallennuksessa. Yritä uudestaan.';
                            break;
                        case 8:
                            $error = 'Tiedoston lataus keskeytyi.';
                            break;
                    }

                    if(!empty($file['type'])) {
                        list($mimetype, $filetype) = explode('/', $file['type']);

                        if(isset($field['onlyImage']) && $mimetype !== 'image') {
                            $error = 'Vain kuvatiedostojen lisääminen on sallittua.';
                        }
                    }

                    if($error !== false) {
                        $this->errors[$name] = $error;
                    }                    
                }                
            }

            if($field['type'] == 'datetime') {
                if(!empty($this->values[$name]) && !\DateTime::createFromFormat('j.n.Y H:i', trim($this->values[$name]))) {
                    $this->errors[$name] = 'Tarkista päivämäärän muoto.';
                }
            }

            if($field['type'] == 'date') {
                if(!empty($this->values[$name]) && !\DateTime::createFromFormat('j.n.Y', trim($this->values[$name]))) {
                    $this->errors[$name] = 'Tarkista päivämäärän muoto.';
                }
            }

            if(isset($field['required']) && $field['required'] && (!isset($this->values[$name]) || empty($this->values[$name])) && $field['type'] != 'file') {
                $this->errors[$name] = 'Kenttä ei saa olla tyhjä';
            }

            if(isset($field['regex']) && isset($this->values[$name]) && preg_match($field['regex'], $this->values[$name]) === 0) {
                $this->errors[$name] = 'Kentän arvo on väärässä muodossa';
            }
        }        
        return $this->errors;
    }

    private function updateActiveField($key, $value)
    {
        if(!empty($this->active) && isset($this->fields[$this->active])) {
            $this->fields[$this->active][$key] = $value;
        }
        return $this;
    }

    private function formatString($url, $vars)
    {
        preg_match_all('/\{(.*?)\}/', $url, $found);

        if(is_array($vars)) {
            $vars = (object)$vars;
        }

        foreach($found[0] as $key => $var) {
            $_key = $found[1][$key];
            if(isset($vars->$_key)) {
                if(isset($this->fields[$_key]) && $this->fields[$_key]['type'] == 'datetime') {
                    $replacement = strtotime($this->getDatetime($_key));
                } else {
                    $replacement = $vars->$_key;
                }
                $url = str_replace($var, $replacement, $url);
            }
        }        
        return $url;
    }
}
