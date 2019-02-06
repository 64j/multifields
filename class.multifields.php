<?php
/**
 * Class multifields
 *
 * @version 1.2.1
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author 64j
 */

class multifields
{
    protected $config;
    protected $actions;

    /**
     * multifields constructor.
     * @param array $config
     */
    function __construct($config = [])
    {
        $this->config = $config;
        $this->actions = [
            'move' => $this->tpl('actions.move', [
                'title' => 'Переместить'
            ]),
            'del' => $this->tpl('actions.del', [
                'title' => 'Удалить'
            ]),
            'add' => $this->tpl('actions.add', [
                'title' => 'Добавить'
            ])
        ];
    }

    /**
     * @param $name
     * @param $args
     */
    public function __call($name, $args)
    {
        // ...
    }

    /**
     * @return mixed
     */
    public function run()
    {
        return $this->tpl('wrap', [
            'field_id' => $this->config['id'],
            'toolbar' => $this->toolbar([
                'title' => $this->config['caption'],
                'actions' => $this->actions(['add', 'del'])['out']
            ]),
            'wrap' => $this->create($this->config['value'], true),

        ]);
    }

    /**
     * @param string $tpl
     * @param array $data
     * @return mixed
     */
    protected function tpl(
        $tpl = '',
        $data = []
    ) {
        if (file_exists(__DIR__ . '/tpl/' . $tpl . '.tpl')) {
            $out = file_get_contents(__DIR__ . '/tpl/' . $tpl . '.tpl');
        } else {
            $out = 'File "' . $tpl . '" not found.';
        }
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    $out = str_replace('[+' . $key . '+]', $value, $out);
                }
            }
            $out = preg_replace('~\[\+(.*?)\+\]~', '', $out);
        }

        return $out;
    }

    /**
     * @return string
     */
    public function getTpl()
    {
        $out = '';
        $tpl = $this->config['template_name'];
        if (isset($this->config['templates'][$tpl])) {
            $data = $this->config['templates'][$tpl];
            $data['tpl'] = $tpl;
            $out = $this->create($data, true);
        }

        return $out;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function setTpl($data = [])
    {
        $data = array_replace_recursive($this->config['templates'][$data['tpl']], $data);

        return $data;
    }

    /**
     * @param array $data
     * @param bool $first
     * @return mixed|string
     */
    protected function create($data = [], $first = false)
    {
        $out = '';
        if (!empty($data)) {
            if (isset($data['tpl'])) {
                $data = $this->setTpl($data);
            }

            if (!isset($data['actions'])) {
                $data['actions'] = true;
            }

            if (isset($data['type'])) {
                switch ($data['type']) {
                    case 'group':
                    case 'items':
                    case 'thumb':
                        $out .= $this->{$data['type']}($data);
                        break;

                    case 'richtext':
                    case 'image':
                    case 'file':
                        $out .= $this->{$data['type']}($data);
                        if (!empty($first)) {
                            $out = $this->_row($out, $data);
                        }
                        break;

                    default:
                        $out .= $this->_default($data);
                        if (!empty($first)) {
                            $out = $this->_row($out, $data);
                        }
                        break;
                }
            } else {
                $out .= $this->rows($data);
            }
        }

        unset($data);

        return $out;
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function toolbar($data = [])
    {
        $out = '';

        foreach ($data as $k => $v) {
            if (!in_array($k, ['value', 'title', 'templates', 'actions'])) {
                unset($data[$k]);
            }
        }

        if (isset($data['value'])) {
            if (!is_bool($data['value']) || (is_bool($data['value']) && !empty($data['value']))) {
                $data['title'] = $this->tpl('input', [
                    'type' => 'text',
                    'value' => is_bool($data['value']) ? '' : $data['value'],
                    'placeholder' => isset($data['placeholder']) ? $data['placeholder'] : $data['title'],
                    'attr' => ' ' . $this->attributes($data)
                ]);
                $data['title'] = $this->tpl('toolbar.title', [
                    'title' => $data['title']
                ]);
            }
        }

        if (isset($data['templates']) && empty($data['templates'])) {
            unset($data['templates']);
        } else {
            $data['templates'] = !empty($data['templates']) ? $data['templates'] : [];
            if (is_string($data['templates'])) {
                $data['templates'] = array_map('trim', explode(',', $data['templates']));
            }
            if (!empty($this->config['templates'])) {
                $options = '';
                $template = '';
                $i = 0;
                foreach ($this->config['templates'] as $k => $v) {
                    if ((empty($v['hidden']) && empty($data['templates'])) || (in_array($k, $data['templates']))) {
                        $tplTitle = $k;
                        if (!empty($v['tplTitle'])) {
                            $tplTitle = $v['tplTitle'];
                        } elseif (!empty($v['title'])) {
                            $tplTitle = $v['title'];
                        }
                        $options .= $this->tpl('option', [
                            'value' => $k,
                            'title' => $tplTitle
                        ]);
                        $template = $k;
                        $i++;
                    }
                }

                if ($i > 1) {
                    $data['select'] = $this->tpl('select', ['options' => $options]);
                } else {
                    $data['select'] = $this->tpl('input', [
                        'type' => 'hidden',
                        'value' => $template,
                        'placeholder' => ''
                    ]);
                }
            }
            unset($data['templates']);
        }

        if (empty($data['actions'])) {
            unset($data['actions']);
        }

        if (!empty($data)) {
            $out = $this->tpl('toolbar', $data);
        }

        return $out;
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function group($data = [])
    {
        $group = [];
        if (isset($data[0])) {
            foreach ($data as $k => $v) {
                if (is_string($k)) {
                    continue;
                }
                if (!empty($v)) {
                    $group[$k] = $v;
                    unset($data[$k]);
                }
            }
        }

        $tpl = 'group';
        $data['group'] = !empty($group) ? $this->create($group) : '';
        $data['class'] = !empty($data['cols']) ? $data['cols'] : 'col-12';
        $data['actions'] = $this->actions($data['actions']);
        $data['class'] .= $data['actions']['class'];
        if (isset($data['actions']['actions']['move'])) {
            $tpl = 'group_row';
        }
        $data['actions'] = $data['actions']['out'];
        $data['toolbar'] = $this->toolbar($data);
        $data['class'] = ' ' . trim($data['class']);

        $out = $this->tpl($tpl, $data);

        unset($data);
        unset($group);

        return $out;
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function thumb($data = [])
    {
        $group = [];
        $type = [];
        if (isset($data[0])) {
            foreach ($data as $k => $v) {
                if (is_string($k)) {
                    continue;
                }
                if (!empty($v)) {
                    if (isset($v[0])) {
                        $v = array_replace($v, $v[0]);
                        unset($v[0]);
                    }
                    $group[$k] = $v;
                    unset($data[$k]);
                    // search type image or file
                    if (empty($type) && !empty($v['type']) && in_array($v['type'], ['image', 'file'])) {
                        $type[$v['type']] = empty($v['value']) ? '' : $v['value'];
                    }
                }
            }
        }

        $data['id'] = 'thumb_' . $this->guid();
        $data['group'] = '';
        $data['attr'] = [];
        if (!empty($group)) {
            $data['group'] = $this->create($group);
            if (key($type) == 'image') {
                $data['attr'][] = 'data-type="image"';
                if ($type['image'] != '') {
                    $data['attr'][] = 'style="background-image: url(\'../' . $type['image'] . '\')"';
                }
            } else {
                $data['attr'][] = 'data-type="file"';
                if ($type['file'] != '') {
                    $data['attr'][] = 'style="background-image: url()"';
                }
            }
            if (count($group) == 1) {
                if (key($type) == 'image') {
                    $data['attr'][] = 'onclick="Multifields.openBrowseServer(event, this, \'image\', \'tv' . $this->config['id'] . '\');"';
                } else {
                    $data['attr'][] = 'onclick="Multifields.openBrowseServer(event, this, \'file\', \'tv' . $this->config['id'] . '\');"';
                }
            } else {
                $data['attr'][] = 'onclick="Multifields.openThumbWindow(event, this, \'tv' . $this->config['id'] . '\');"';
            }
        }
        $data['attr'] = implode(' ', $data['attr']);

        $data['class'] = !empty($data['cols']) ? $data['cols'] : 'col-1';

        $data['actions'] = $this->actions($data['actions']);
        $data['class'] .= $data['actions']['class'];
        $data['actions'] = $data['actions']['out'];

        $data['class'] = ' ' . trim($data['class']);

        $out = $this->tpl('group_thumb', $data);

        unset($data);
        unset($group);

        return $out;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function rows($data = [])
    {
        $out = '';
        foreach ($data as $k => $v) {
            if (!is_numeric($k)) {
                continue;
            }
            if (!empty($v)) {
                if (isset($v['tpl'])) {
                    $v = $this->setTpl($v);
                }
                if (in_array($v['type'], ['group', 'items', 'thumb'])) {
                    $out .= $this->create($v);
                } else {
                    if (isset($v[0])) {
                        $v = array_replace($v, $v[0]);
                        unset($v[0]);
                    }
                    $out .= $this->_row($this->create($v), $v);
                }
            }
        }

        unset($data);

        return $out;
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    protected function row($data = [])
    {
        $out = '';
        foreach ($data as $k => $v) {
            if (!is_numeric($k)) {
                continue;
            }
            if (!empty($v)) {
                $out .= $this->create($v);
            }
        }
        if (!empty($out)) {
            $out = $this->_row($out, $data);
        }

        unset($data);

        return $out;
    }

    /**
     * @param string $out
     * @param array $data
     * @return mixed|string
     */
    protected function _row($out = '', $data = [])
    {
        if (!empty($out)) {
            $data['row'] = $out;
            $data['class'] = !empty($data['cols']) ? $data['cols'] : 'col-12';
            $data['actions'] = $this->actions($data['actions']);
            $data['class'] .= $data['actions']['class'];
            $data['actions'] = $data['actions']['out'];
            if (!empty($data['actions'])) {
                $data['actions'] = $this->tpl('actions', [
                    'actions' => $data['actions']
                ]);
            }

            $data['class'] = ' ' . trim($data['class']);
            $out = $this->tpl('row', $data);
        }

        unset($data);

        return $out;
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    protected function items($data = [])
    {
        $out = '';
        foreach ($data as $k => $v) {
            if (!is_numeric($k)) {
                continue;
            }
            if (!empty($v)) {
                $out .= $this->create($v);
            }
        }

        if (!empty($out)) {
            $out = $this->_row($out, $data);
        }

        unset($data);

        return $out;
    }

    /**
     * @deprecated
     * @param array $data
     * @return mixed|string
     */
    protected function cols($data = [])
    {
        $out = '';
        foreach ($data as $k => $v) {
            if (!is_numeric($k)) {
                continue;
            }
            if (!empty($v)) {
                $out .= $this->create($v);
            }
        }

        if (!empty($out)) {
            $out = $this->tpl('cols', [
                'cols' => $out
            ]);
        }

        unset($data);

        return $out;
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function item($data = [])
    {
        if (!empty($data['title'])) {
            $data['title'] = $this->tpl('label', $data);
        }

        return $this->tpl('item', $data);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function data($data = [])
    {
        $guid = $this->guid();
        $data['attr'] = $this->attributes($data);
        $data['type'] = !empty($data['type']) ? $data['type'] : 'text';
        $data['name'] = !empty($data['name']) ? $data['name'] : $data['type'];
        $data['title'] = isset($data['title']) ? $data['title'] : '';
        $data['placeholder'] = isset($data['placeholder']) ? $data['placeholder'] : '';
        $data['elements'] = !empty($data['elements']) ? $data['elements'] : '';
        $data['default'] = isset($data['default']) ? $data['default'] : '';
        $data['value'] = isset($data['value']) ? $data['value'] : (isset($data['default']) ? $data['default'] : '');
        $data['id'] = $this->config['id'] . '__' . $guid . '__' . $data['type'] . '__' . $data['name'];
        $data['class'] = ' ' . (!empty($data['item.col']) ? trim($data['item.col']) : 'col');

        return $data;
    }

    /**
     * @return string
     */
    protected function guid()
    {
        return md5(time() . rand(0, 99999));
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function richtext($data = [])
    {
        $data = $this->data($data);
        if (!empty($data['elements'])) {
            if (is_array($data['elements'])) {
                $data['elements'] = json_encode($data['elements'], JSON_UNESCAPED_UNICODE);
            }
            $data['elements'] = base64_encode($data['elements']);
        }
        $data['value'] = $this->tpl('richtext', [
            'id' => $data['id'],
            'field_id' => 'tv' . $this->config['id'],
            'value' => htmlspecialchars($data['value']),
            'placeholder' => $data['placeholder'],
            'title' => $data['title'],
            'options' => $data['elements']
        ]);

        return $this->item($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function image($data = [])
    {
        $data = $this->data($data);
        $data['value'] = $this->renderFormElement($data);
        $data['value'] = preg_replace('~<script[^>]*>.*?</script>~si', '', $data['value']);
        $data['value'] = str_replace('onchange="',
            'onchange="document.getElementById(\'tv' . $this->config['id'] . '\').oncomplete();', $data['value']);
        $data['value'] = preg_replace('/BrowseServer\(.*\)/', 'BrowseServer(this.previousElementSibling)',
            $data['value']);

        return $this->item($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function file($data = [])
    {
        $data = $this->data($data);
        $data['value'] = $this->renderFormElement($data);
        $data['value'] = preg_replace('~<script[^>]*>.*?</script>~si', '', $data['value']);
        $data['value'] = str_replace('onchange="',
            'onchange="document.getElementById(\'tv' . $this->config['id'] . '\').oncomplete();', $data['value']);
        $data['value'] = preg_replace('/BrowseFileServer\(.*\)/', 'BrowseFileServer(this.previousElementSibling)',
            $data['value']);

        return $this->item($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function _default($data = [])
    {
        $data = $this->data($data);
        $data['value'] = $this->renderFormElement($data);

        return $this->item($data);
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    protected function renderFormElement($data = [])
    {
        $item = renderFormElement($data['type'], $data['id'], $data['default'], $data['elements'], $data['value'],
            $data['attr']);

        if ($data['placeholder'] != '') {
            $item = str_replace('<input', '<input placeholder="' . $data['placeholder'] . '"', $item);
            $item = str_replace('<textarea', '<textarea placeholder="' . $data['placeholder'] . '"', $item);
        }

        $item = str_replace(' name=', ' data-name="' . $data['id'] . '" tvname="' . $data['type'] . '" name=', $item);

        unset($data);

        return $item;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function attributes($data = [])
    {
        $attr = '';
        if (!empty($data)) {
            $attr = [];

            if (!empty($data['required'])) {
                $attr[] = 'required';
            }

            if (!empty($data['readonly'])) {
                $attr[] = 'readonly';
            }

            $attr = implode(' ', $attr);
            unset($data);
        }

        return $attr;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function actions($data = [])
    {
        if (is_array($data)) {
            if (is_string($data)) {
                $data = array_map('trim', explode(',', $data));
            }
            $data = array_flip($data);
            $data = array_intersect_key($this->actions, $data);
        } else {
            if (is_null($data) || $data === true) {
                $data = $this->actions;
            } else {
                $data = '';
            }
        }

        $class = '';
        if (isset($data['move'])) {
            $class .= ' mf-row-move';
        }
        if (isset($data['add'])) {
            $class .= ' mf-row-add';
        }
        if (isset($data['del'])) {
            $class .= ' mf-row-del';
        }

        $data = [
            'actions' => $data,
            'class' => $class,
            'out' => !empty($data) ? implode($data) : ''
        ];

        return $data;
    }

    public function dd(
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
