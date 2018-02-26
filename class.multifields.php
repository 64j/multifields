<?php

class customTvMultifields
{
    private $config;
    private $thumb;
    private $view;
    private $modx;
    private $tpl;
    private $DLT;

    function __construct($config = array(), $modx = array())
    {
        $this->config = $config;
        $this->modx = $modx;
        require_once(MODX_MANAGER_PATH . 'includes/tmplvars.inc.php');
        require_once(MODX_MANAGER_PATH . 'includes/tmplvars.commands.inc.php');

        // :TODO multitv
        //        if ($this->config['schema'] == 'mtv') {
        //            $settings = array();
        //            $tvName = $this->modx->db->getValue('SELECT name FROM ' . $modx->getFullTableName('site_tmplvars') . ' WHERE id=' . $this->config['field_id']);
        //            require_once MODX_BASE_PATH . 'assets/tvs/multitv/configs/' . $tvName . '.config.inc.php';
        //
        //            if (!empty($this->config['value']) && !empty($this->config['value']['fieldValue'])) {
        //                $values = $this->config['value'];
        //                $this->config['value'] = array();
        //                foreach ($values['fieldValue'] as $key => $value) {
        //                    $i = 0;
        //                    $this->config['value']['rows'][$key]['tpl'] = $tvName . '__0';
        //                    foreach ($value as $k => $v) {
        //                        $items = $settings['fields'][$k];
        //                        $items['value'] = $v;
        //                        if (isset($items['caption'])) {
        //                            $items['title'] = $items['caption'];
        //                            unset($items['caption']);
        //                        }
        //                        $this->config['value']['rows'][$key]['row'][$i]['items'][] = $items;
        //                        $i++;
        //                    }
        //                }
        //            }
        //
        //            foreach ($settings['fields'] as $key => $value) {
        //                $this->config['templates'][$tvName]['rows'][0]['row'][$key]['items'][] = $value;
        //            }
        //        }
    }

    private function templates($tpl = '', $data = array())
    {
        if ($this->config['render']) {
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
                    } else if (isset($this->config['tpl' . $this->tpl])) {
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

        if (isset($this->config['templates'][$this->tpl])) {
            $out = $this->create($this->config['templates'][$this->tpl]);
        }

        return $out;
    }

    public function run()
    {
        return $this->templates('wrap', array(
            'field_id' => $this->config['field_id'],
            'toolbar' => $this->toolbar($this->config['title'], false),
            'wrap' => $this->create($this->config['value'])
        ));
    }

    private function create($data = array())
    {
        $out = '';

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (isset($data['tpl'])) {
                    $this->tpl = $data['tpl'];
                }
                if ($key === 'title' || $key === 'placeholder' || $key === 'value') {
                } else if ($key === 'tpl') {
                    $this->tpl = $value;
                } else if ($key === 'view') {
                    $this->view = $value;
                } else if ($key === 'cols') {
                    $out .= $this->cols($value);
                } else if ($key === 'group') {
                    $out .= $this->group($value);
                } else if ($key === 'rows') {
                    $out .= $this->rows($value);
                } else if ($key === 'section') {
                    $out .= $this->section($value);
                } else if ($key === 'items') {
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

    private function toolbar($title = '', $move = true, $templates = '')
    {
        $data = array(
            'title' => $title,
            'move' => $move ? $this->templates('move') : '',
            'select' => ''
        );

        if (!empty($this->config['templates']) && is_array($this->config['templates'])) {
            $options = '';
            $template = '';
            $templates = $templates ? explode(',', $templates) : array();
            $i = 0;

            foreach ($this->config['templates'] as $key => $value) {
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

    private function cols($data = array(), $width = array())
    {
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

    private function rows($data = array())
    {
        $out = '';

        foreach ($data as $key => $value) {
            if (isset($value['group'])) {
                $out .= $this->create($value);
            } else if (isset($value['section'])) {
                $out .= $this->create($value);
            } else if (is_array($value)) {
                if (isset($value['tpl'])) {
                    $tpl = $this->tpl = $value['tpl'];
                } else {
                    $tpl = $this->tpl;
                }
                unset($value['tpl']);
                reset($value);
                $name = (string)key($value);

                if ($this->config['render']) {
                    if (isset($this->config['templates'][$tpl]['rows'][0][$name]) && isset($value[$name])) {
                        if (is_array($value[$name])) {
                            $value[$name] += $this->config['templates'][$tpl]['rows'][0][$name];
                        } else {
                            $_ = isset($value[$name]) ? $value[$name] : '';
                            $value = $this->config['templates'][$tpl]['rows'][0];
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
                        if (isset($this->config['templates'][$tpl]['rows'][0][$name])) {
                            $_ = isset($value[$name]['value']) ? $value[$name]['value'] : '';
                            $value[$name] = array_replace_recursive($value[$name], $this->config['templates'][$tpl]['rows'][0][$name]);
                            if ($_) {
                                $value[$name]['value'] = $_;
                            }
                        }
                    } else {
                        $value[$name] = array(
                            'tpl' => $tpl,
                            'value' => $value[$name],
                            'name' => $name
                        );
                        if (isset($this->config['templates'][$tpl]['rows'][0][$name])) {
                            $value[$name] += $this->config['templates'][$tpl]['rows'][0][$name];
                        }
                    }
                    $this->tpl = '';
                }

                if (!empty($this->config['templates'][$tpl]['view'])) {
                    $this->view = $this->config['templates'][$tpl]['view'];
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

    private function items($data = array())
    {
        $out = '';

        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                foreach ($value as $k => $v) {
                    if ($k === 'rows') {
                        if ($this->config['render']) {
                            $out .= $this->templates('item', $this->renderData($v));
                        } else {
                            $out .= $this->templates('item', array(
                                'class' => '',
                                'width' => '',
                                'title' => '',
                                'value' => $this->row($v)
                            ));
                        }
                    } else if ($k === 'group') {
                        if (is_array($v)) {
                            $v['name'] = $k;
                        }
                        $out .= $this->templates('item', array(
                            'class' => '',
                            'width' => !empty($v['width']) ? ' style="width:' . $v['width'] . '"' : '',
                            'title' => '',
                            'value' => $this->group($v)
                        ));
                    } else {
                        if (is_array($v)) {
                            $v['name'] = $k;
                        }
                        $out .= $this->templates('items', array(
                            'items' => $this->item($v)
                        ));
                    }
                }
            } else {
                if (is_array($value)) {
                    $value['name'] = $key;
                }
                $out .= $this->templates('items', array(
                    'items' => $this->item($value)
                ));
            }
        }

        $this->tpl = '';

        return $out;
    }

    private function group($data = array())
    {
        $out = '';

        if (!empty($data['tpl'])) {
            $this->tpl = $data['tpl'];
        }
        if (isset($this->config['templates'][$this->tpl]['group'])) {
            $data += $this->config['templates'][$this->tpl]['group'];
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

    private function section($data = array())
    {
        $out = '';
        $data = $this->fillData($data);

        if (!empty($data['tpl'])) {
            $this->tpl = $data['tpl'];
        }

        if (isset($this->config['templates'][$this->tpl]['section'])) {
            $data = array_replace_recursive($data, $this->config['templates'][$this->tpl]['section']);
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

    private function row($data = array())
    {
        $out = '';

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $out .= $this->create($value);
            }
        }

        return $out;
    }

    private function item($data = array())
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
            $id = $this->config['field_id'] . '__' . $guid . '__' . $type . '__' . $name;
            $width = !empty($data['width']) ? ' style="width:' . $data['width'] . '"' : '';
            $class = '';
            $placeholder = !empty($data['placeholder']) ? $data['placeholder'] : '';
            $title = !empty($data['title']) ? $data['title'] : '';
            $thumb = !empty($data['thumb']) ? $data['thumb'] : '';

            switch ($type) {
                case 'richtext':
                case 'htmlarea':
                    //$class .= ' richtext';
                    $item = $this->templates('richtext', array(
                        'id' => $id,
                        'field_id' => 'tv' . $this->config['field_id'],
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
                        $attributes .= ' onclick="multiFieldsOpenThumbWindow(event,\'tv' . $this->config['field_id'] . '\',this);"';
                        $class = ' thumb-item-rows';
                        $rows = $this->rows($rows);
                    }
                    $item = $this->templates('thumb', array(
                        'id' => $id,
                        'name' => $id,
                        'value' => $value,
                        'style' => $style,
                        'attr' => $attributes,
                        'class' => $class,
                        'rows' => $rows
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
                        $item .= '<script>document.getElementById(\'tv' . $id . '\').addEventListener(\'change\',function(){multiFieldsChangeThumb(\'tv' . $this->config['field_id'] . '\',this)}, false);</script>';
                        $this->thumb = '';
                    }
                    break;

                case 'file':
                    $item = renderFormElement($type, $id, $default, $elements, $value, $attributes, $data, $data);
                    $item = preg_replace('~<script[^>]*>.*?</script>~si', '', $item);
                    //$item = str_replace('onclick="BrowseFileServer(\'tv' . $id . '\')"', 'onclick="BrowseFileServer(this.previousElementSibling.id)"', $item);
                    break;

                case 'date':
                    $item = renderFormElement($type, $id, $default, $elements, $value, $attributes, $data, $data);
                    $item = str_replace('onclick="document.forms[\'mutate\'].elements[\'tv' . $id . '\'].value=\'\';document.forms[\'mutate\'].elements[\'tv' . $id . '\'].onblur(); return true;"', 'onclick="document.forms[\'mutate\'].elements[this.previousElementSibling.id].value=\'\';document.forms[\'mutate\'].elements[this.previousElementSibling.id].onblur(); return true;"', $item);
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

    private function fillData(&$data = array())
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

    private function renderData($data = array())
    {
        $out = array();

        foreach ($data as $item) {
            foreach ($item as $key => $value) {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    public function dbug($str = '', $exit = false)
    {
        print('<pre>');
        print_r($str);
        print('</pre>');
        if ($exit) {
            exit;
        }
    }

}
