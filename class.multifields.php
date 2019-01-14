<?php

class multifields
{
    protected $config;
    protected $thumb;
    protected $view;
    protected $modx;
    protected $tpl;
    protected $DLT;

    function __construct($config = array())
    {
        $this->modx = evolutionCMS();
        $this->config = $config;
    }

    protected function templates(
        $tpl = '',
        $data = array()
    ) {
        if (!empty($this->config['render'])) {
            switch ($tpl) {
                case 'wrap':
                    $tpl = $this->config['tplWrap'];
                    break;

                case 'group':
                    $tpl = isset($this->config['tplGroup' . $this->tpl]) ? $this->config['tplGroup' . $this->tpl] : $this->config['tplGroup'];
                    break;

                case 'section':
                    $tpl = isset($this->config['tplSection' . $this->tpl]) ? $this->config['tplSection' . $this->tpl] : $this->config['tplSection'];
                    break;

                case 'rows':
                    $tpl = isset($this->config['tplRows' . $this->tpl]) ? $this->config['tplRows' . $this->tpl] : $this->config['tplRows'];
                    break;

                case 'row':
                    $tpl = isset($this->config['tplRow' . $this->tpl]) ? $this->config['tplRow' . $this->tpl] : $this->config['tplRow'];
                    break;

                case 'items':
                    $tpl = isset($this->config['tplItems' . $this->tpl]) ? $this->config['tplItems' . $this->tpl] : $this->config['tplItems'];
                    break;

                case 'thumb':
                    $tpl = isset($this->config['tplThumb' . $this->tpl]) ? $this->config['tplThumb' . $this->tpl] : $this->config['tplThumb'];
                    break;

                case 'none':
                    $tpl = $this->config['noneTPL'];
                    break;

                case 'item':
                    if (isset($data['tpl']) && isset($this->config['tpl' . $data['tpl']])) {
                        $tpl = $this->config['tpl' . $data['tpl']];
                    } elseif (isset($this->config['tpl' . $this->tpl])) {
                        $tpl = $this->config['tpl' . $this->tpl];
                    } else {
                        $tpl = $this->config['tpl'];
                    }
                    break;

                default:
            }

            $tpl = $this->DLT->parseChunk($tpl, $data);
        } else {
            if (file_exists(__DIR__ . '/tpl/' . $tpl . '.tpl')) {
                $tpl = file_get_contents(__DIR__ . '/tpl/' . $tpl . '.tpl');
            } else {
                $tpl = 'File not found.';
            }
            foreach ($data as $key => $value) {
                $tpl = str_replace('[+' . $key . '+]', $value, $tpl);
            }
        }

        return $tpl;
    }

    public function render()
    {
        include_once MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php';

        $this->DLT = DLTemplate::getInstance($this->modx);
        $this->DLT->setTemplateExtension($this->config['templateExtension']);
        $this->DLT->setTemplatePath($this->config['templatePath']);

        if ($out = $this->create($this->config['value'])) {
            $out = $this->templates('wrap', array(
                'wrap' => $out
            ));
        } else {
            $out = $this->templates('none', array());
        }

        return $out;
    }

    public function template()
    {
        $out = '';
        $this->tpl = $this->config['template_name'];

        if (isset($this->config['elements'][$this->tpl])) {
            $out = $this->create($this->config['elements'][$this->tpl]);
        }

        return $out;
    }

    public function run()
    {
        return $this->templates('wrap', array(
            'field_id' => $this->config['id'],
            'toolbar' => $this->toolbar($this->config['title'], false),
            'wrap' => $this->create($this->config['value'])
        ));
    }

    protected function create($data = array())
    {
        $out = '';

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $_ = explode(':', $key);
                if (isset($_[1])) {
                    $this->tpl = $_[1];
                    $key = $_[0];
                }
                if ($key === 'title' || $key === 'placeholder' || $key === 'value') {
                } elseif ($key === 'tpl') {
                    $this->tpl = $value;
                } elseif ($key === 'view') {
                    $this->view = $value;
                } elseif ($key === 'cols') {
                    $out .= $this->cols($value);
                } elseif ($key === 'group') {
                    $out .= $this->group($value);
                } elseif ($key === 'rows') {
                    $out .= $this->rows($value);
                } elseif ($key === 'section') {
                    $out .= $this->section($value);
                } elseif ($key === 'items') {
                    $out .= $this->templates('row', array(
                        'name' => $key,
                        'row' => $this->items($value)
                    ));
                } else {
                    if (is_array($value)) {
                        if ($this->config['render']) {
                            if (isset($value['rows'])) {
                                $out .= $this->templates('row', array(
                                    'name' => $key,
                                    'row' => $this->item($value)
                                ));
                            } else {
                                $out .= $this->templates('item', array(
                                    $key => isset($value['value']) ? $value['value'] : ''
                                ));
                            }
                        } else {
                            if (isset($value['group'])) {

                            } else {
                                $value['name'] = $key;
                                $out .= $this->templates('row', array(
                                    'name' => $key,
                                    'row' => $this->item($value)
                                ));
                            }
                        }
                    }
                }
            }
        }

        return $out;
    }

    protected function toolbar(
        $title = '',
        $move = true,
        $templates = ''
    ) {
        $data = array(
            'title' => $title,
            'move' => $move ? $this->templates('move') : '',
            'select' => ''
        );

        if (!empty($this->config['elements']) && is_array($this->config['elements'])) {
            $options = '';
            $template = '';
            $templates = $templates ? explode(',', $templates) : array();
            $i = 0;

            foreach ($this->config['elements'] as $key => $value) {
                if ((empty($value['hidden']) && empty($templates)) || ($templates && in_array($key, $templates))) {
                    $options .= $this->templates('option', array(
                        'value' => $key,
                        'title' => (isset($value['title']) ? $value['title'] : $key)
                    ));
                    $template = $key;
                    $i++;
                }
            }

            if ($i > 1) {
                $data['select'] = $this->templates('select', array('options' => $options));
            } else {
                $data['select'] = $this->templates('input', array(
                    'type' => 'hidden',
                    'value' => $template,
                    'placeholder' => ''
                ));
            }
        }

        return $this->templates('toolbar', $data);
    }

    protected function cols(
        $data = array(),
        $width = array()
    ) {
        $out = '';

        if (!empty($data)) {
            $cols = '';

            foreach ($data as $k => $col) {
                $col_width = !empty($width[$k]) ? ' style="width:' . $width[$k] . '"' : '';
                $cols .= $this->templates('col', array(
                    'title' => $col,
                    'width' => $col_width
                ));
            }

            $out .= $this->templates('cols', array(
                'cols' => $cols
            ));
        }

        return $out;
    }

    protected function rows($data = array())
    {
        $out = '';

        foreach ($data as $key => $value) {
            $name = '';
            $tpl = '';
            if (is_array($value)) {
                reset($value);
                $name = (string)key($value);
                $_name = $name;
                $_ = explode(':', $name);
                if (isset($_[1])) {
                    $tpl = $this->tpl = $_[1];
                    $name = $_[0];
                } else {
                    $tpl = $this->tpl;
                }
                $value[$name] = $value[$_name];
                unset($value[$_name]);
            }

            if (isset($value['group'])) {
                $out .= $this->create($value);
            } elseif (isset($value['section'])) {
                $out .= $this->create($value);
            } elseif (is_array($value)) {
                if ($this->config['render']) {
                    if (isset($this->config['elements'][$tpl]['rows'][0][$name]) && isset($value[$name])) {
                        if (is_array($value[$name])) {
                            $value[$name] += $this->config['elements'][$tpl]['rows'][0][$name];
                        } else {
                            $_ = isset($value[$name]) ? $value[$name] : '';
                            $value = $this->config['elements'][$tpl]['rows'][0];
                            $value[$name]['value'] = $_;
                        }
                    }
                } else {
                    if (is_array($value[$name])) {
                        if (isset($value['items'])) {
                            $value['items'] = $this->fillData($value['items']);
                        }
                        if (isset($value[$name]['rows'])) {
                            $value[$name]['rows'] = $this->fillData($value[$name]['rows']);
                        }
                        if (isset($this->config['elements'][$tpl]['rows'][0][$name])) {
                            $_ = isset($value[$name]['value']) ? $value[$name]['value'] : '';
                            $value[$name] = array_replace_recursive($value[$name],
                                $this->config['elements'][$tpl]['rows'][0][$name]);

                            if ($_) {
                                $value[$name]['value'] = $_;
                            }
                        }
                    } else {
                        if ($name == 'items') {
                            $value[$name] = array();
                        } else {
                            $value[$name] = array(
                                'tpl' => $tpl,
                                'value' => $value[$name],
                                'name' => $name
                            );
                        }
                        if (isset($this->config['elements'][$tpl]['rows'][0][$name])) {
                            $value[$name] += $this->config['elements'][$tpl]['rows'][0][$name];
                        }
                    }
                    $this->tpl = '';
                }

                if (!empty($this->config['elements'][$tpl]['view'])) {
                    $this->view = $this->config['elements'][$tpl]['view'];
                } else {
                    $this->view = '';
                }

                $out .= $this->templates('rows', array(
                    'view' => $this->view,
                    'tpl' => $tpl,
                    'rows' => $this->create($value)
                ));
            }
        }

        return $out;
    }

    protected function items($data = array())
    {
        $out = '';

        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                foreach ($value as $k => $v) {
                    $out .= $this->_items($k, $v);
                }
            } else {
                $out .= $this->_items($key, $value);
            }
        }

        $this->tpl = '';

        return $out;
    }

    protected function _items(
        $key,
        $value
    ) {
        $out = '';

        if ($key === 'rows') {
            if ($this->config['render']) {
                $out .= $this->templates('item', $this->renderData($value));
            } else {
                $out .= $this->templates('item', array(
                    'class' => '',
                    'width' => '',
                    'title' => '',
                    'value' => $this->row($value)
                ));
            }
        } elseif ($key === 'group') {
            if (is_array($value)) {
                $value['name'] = $key;
            }
            $out .= $this->templates('item', array(
                'class' => '',
                'width' => !empty($value['width']) ? ' style="width:' . $value['width'] . '"' : '',
                'title' => '',
                'value' => $this->group($value)
            ));
        } else {
            if (is_array($value)) {
                $value['name'] = $key;
            }
            $out .= $this->templates('items', array(
                'items' => $this->item($value)
            ));
        }

        return $out;
    }

    protected function group($data = array())
    {
        $out = '';

        if (isset($this->config['elements'][$this->tpl]['group'])) {
            $data += $this->config['elements'][$this->tpl]['group'];
        }
        if (!empty($data['novalue'])) {
            $title = !empty($data['title']) ? $data['title'] : '';
        } else {
            $title = $this->templates('input', array(
                'type' => 'text',
                'value' => !empty($data['value']) ? $data['value'] : '',
                'placeholder' => !empty($data['placeholder']) ? $data['placeholder'] : ''
            ));
        }

        $move = isset($data['move']) ? $data['move'] : 1;
        $templates = empty($data['templates']) ? '' : $data['templates'];
        $out .= $this->templates('group', array(
            'toolbar' => $this->toolbar($title, $move, $templates),
            'tpl' => $this->tpl,
            'title' => '',
            'group' => !empty($data) ? $this->create($data) : ''
        ));

        $this->tpl = '';

        return $out;
    }

    protected function section($data = array())
    {
        $out = '';
        $data = $this->fillData($data);

        if (isset($this->config['elements'][$this->tpl]['section'])) {
            $data = array_replace_recursive($data, $this->config['elements'][$this->tpl]['section']);
        }

        $out .= $this->templates('rows', array(
            'view' => '',
            'tpl' => $this->tpl,
            'rows' => $this->templates('section', array(
                'tpl' => $this->tpl,
                'section' => $this->row($data['rows'])
            ))
        ));

        $this->tpl = '';

        return $out;
    }

    protected function row($data = array())
    {
        $out = '';

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $out .= $this->create($value);
            }
        }

        return $out;
    }

    protected function item($data = array())
    {
        $out = '';
        $type = !empty($data['type']) ? $data['type'] : 'text';
        $rows = !empty($data['rows']) ? $data['rows'] : '';
        $default = !empty($data['default']) ? $data['default'] : '';
        $elements = !empty($data['elements']) ? $data['elements'] : '';
        $value = isset($data['value']) ? $data['value'] : ($default ? $default : '');

        if ($this->config['render']) {
            if (is_array($data)) {
                if ($type == 'thumb') {
                    if ($rows) {
                        $data = $this->renderData($rows);
                    }
                    $out .= $this->templates('thumb', array(
                        'thumb' => $this->templates('item', $data)
                    ));
                } else {
                    $out .= $this->templates('item', $data);
                }
            }
        } else {
            $name = !empty($data['name']) ? $data['name'] : $type;
            $attributes = !empty($data['attr']) ? ' ' . $data['attr'] : '';
            $guid = md5(time() . rand(0, 99999));
            $id = $this->config['id'] . '__' . $guid . '__' . $type . '__' . $name;
            $width = !empty($data['width']) ? ' style="width:' . $data['width'] . '"' : '';
            $class = '';
            $placeholder = !empty($data['placeholder']) ? $data['placeholder'] : '';
            $title = !empty($data['title']) ? $data['title'] : '';
            $thumb = !empty($data['thumb']) ? $data['thumb'] : '';
            $inputAttributes = '';

            switch ($type) {
                case 'richtext':
                case 'htmlarea':
                    //$class .= ' richtext';
                    $item = $this->templates('richtext', array(
                        'id' => $id,
                        'field_id' => 'tv' . $this->config['id'],
                        'value' => $value,
                        'placeholder' => $placeholder,
                        'title' => $title
                    ));
                    break;

                case 'thumb':
                    $this->thumb = $id;
                    $style = '';
                    $class = '';
                    if (!empty($data['width'])) {
                        $style .= 'width:' . $data['width'];
                    }
                    if (!empty($value)) {
                        $style .= 'background-image:url(../' . $value . ')';
                    }
                    if ($style) {
                        $style = ' style="' . $style . '"';
                    }
                    if ($rows) {
                        if (count($rows) == 1 && isset($rows[0])) {
                            $_ = reset($rows[0]);
                            if (isset($_['type'])) {
                                if ($_['type'] == 'image') {
                                    $attributes .= ' data-type="image" onclick="BrowseServer(\'tv' . $id . '\');"';
                                    $inputAttributes = ' onchange="Multifields.prototype.changeThumb(\'tv' . $this->config['id'] . '\',this)"';
                                } elseif ($_['type'] == 'file') {
                                    $attributes .= ' data-type="file" onclick="BrowseFileServer(\'tv' . $id . '\');"';
                                    $inputAttributes = ' onchange="Multifields.prototype.changeThumb(\'tv' . $this->config['id'] . '\',this)"';
                                }
                            }
                            $rows = '';
                        } else {
                            $attributes .= ' onclick="Multifields.prototype.openThumbWindow(event,\'tv' . $this->config['id'] . '\',this);"';
                            $class = ' thumb-item-rows';
                            $rows = $this->rows($rows);
                        }
                    }
                    $item = $this->templates('thumb', array(
                        'id' => $id,
                        'name' => $id,
                        'value' => $value,
                        'style' => $style,
                        'attr' => $attributes,
                        'class' => $class,
                        'rows' => $rows,
                        'input.attr' => $inputAttributes
                    ));
                    break;

                case 'image':
                    $forThumb = '';
                    if (!empty($this->thumb)) {
                        $_ = explode('__', $this->thumb);
                        if (!empty($_[3]) && $_[3] == $thumb) {
                            $forThumb = true;
                            $attributes .= ' data-thumb="' . $this->thumb . '"';
                        }
                    }
                    $item = renderFormElement($type, $id, $default, $elements, $value, $attributes, $data, $data);
                    $item = preg_replace('~<script[^>]*>.*?</script>~si', '', $item);
                    //$item = str_replace('onclick="BrowseServer(\'tv' . $id . '\')"', 'onclick="BrowseServer(this.previousElementSibling.id)"', $item);
                    if ($forThumb) {
                        $item .= '<script>document.getElementById(\'tv' . $id . '\').addEventListener(\'change\',function(){Multifields.prototype.changeThumbs(\'tv' . $this->config['id'] . '\',this)}, false);</script>';
                        $this->thumb = '';
                    } else {
                        $item .= '<script>document.getElementById(\'tv' . $id . '\').addEventListener(\'change\',function(){document.getElementById(\'tv' . $this->config['id'] . '\').complete()}, false);</script>';
                    }
                    break;

                case 'file':
                    $item = renderFormElement($type, $id, $default, $elements, $value, $attributes, $data, $data);
                    $item = preg_replace('~<script[^>]*>.*?</script>~si', '', $item);
                    //$item = str_replace('onclick="BrowseFileServer(\'tv' . $id . '\')"', 'onclick="BrowseFileServer(this.previousElementSibling.id)"', $item);
                    break;

                case 'date':
                    $item = renderFormElement($type, $id, $default, $elements, $value, $attributes, $data, $data);
                    $item = str_replace('onclick="document.forms[\'mutate\'].elements[\'tv' . $id . '\'].value=\'\';document.forms[\'mutate\'].elements[\'tv' . $id . '\'].onblur(); return true;"',
                        'onclick="document.forms[\'mutate\'].elements[this.previousElementSibling.id].value=\'\';document.forms[\'mutate\'].elements[this.previousElementSibling.id].onblur(); return true;"',
                        $item);
                    break;

                default:
                    $item = renderFormElement($type, $id, $default, $elements, $value, $attributes, $data, $data);
                    break;

            }

            if ($placeholder) {
                $item = str_replace('<input', '<input placeholder="' . $placeholder . '"', $item);
                $item = str_replace('<textarea', '<textarea placeholder="' . $placeholder . '"', $item);
            }

            if ($type == 'thumb') {
                $out .= $item;
            } else {
                $out .= $this->templates('item', array(
                    'value' => $item,
                    'class' => $class,
                    'width' => $width,
                    'title' => $title ? $this->templates('label', array(
                        'id' => $id,
                        'title' => $title
                    )) : ''
                ));
            }
        }

        return $out;
    }

    protected function fillData(&$data = array())
    {
        foreach ($data as $key => &$value) {
            if (is_numeric($key) || $key === 'rows' || $key === 'group' || $key === 'section' || $key === 'items' || $key === 'tpl' || $key === 'col' || $key === 'title' || $key === 'value') {
                if (is_array($value)) {
                    $this->fillData($value);
                }
            } else {
                if (is_string($value) && $value !== '') {
                    $value = array(
                        'value' => $value
                    );
                }
            }
        }

        return $data;
    }

    protected function renderData($data = array())
    {
        $out = array();

        foreach ($data as $item) {
            foreach ($item as $key => $value) {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    public function dbug(
        $str = '',
        $exit = false
    ) {
        print('<pre>');
        print_r($str);
        print('</pre>');
        if ($exit) {
            exit;
        }
    }

}
