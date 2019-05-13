<?php
/**
 * Created by PhpStorm.
 * @author 64j
 */

namespace MF2;


class MultiFields
{
    private static $instance;
    protected $evo;
    protected $params;
    protected $basePath;
    protected $config;
    protected $data;
    protected $post;

    /**
     * MultiFields constructor.
     * @param array $params
     * @param int $id
     */
    protected function __construct($id = 0, $params = [])
    {
        $this->evo = evolutionCMS();
        $this->setParams($id, $params);

        $pluginParams = [];
        if (!empty($this->evo->pluginCache['multifieldsProps'])) {
            $pluginParams = json_decode($this->evo->pluginCache['multifieldsProps'], true);
        }

        $this->params['theme'] = empty($pluginParams['multifields_theme']) ? 'default' : $pluginParams['multifields_theme'];
        $this->params['storage'] = empty($pluginParams['multifields_storage']) ? 'files' : $pluginParams['multifields_storage'];

        $this->basePath = MODX_BASE_PATH . 'assets/plugins/multifields/';
        require_once MODX_MANAGER_PATH . 'includes/tmplvars.inc.php';
        require_once MODX_MANAGER_PATH . 'includes/tmplvars.format.inc.php';
        require_once MODX_MANAGER_PATH . 'includes/tmplvars.commands.inc.php';
    }

    /**
     * @param array $params
     * @param $id
     * @return mixed
     */
    public static function getInstance($id = 0, $params = [])
    {
        if (self::$instance === null) {
            self::$instance = new static($id, $params);
        }

        return self::$instance->setParams($id, $params);
    }

    /**
     * @param array $params
     * @param int $id
     * @return $this
     */
    protected function setParams($id = 0, $params = [])
    {
        $this->params['tv'] = $params;
        $this->params['id'] = $id;
        $this->params['actions'] = [
            'section' => ['move', 'del', 'add'],
            'group' => ['move', 'del'],
            'row' => ['move', 'del', 'add'],
            'table' => ['move', 'del', 'add'],
            'tabs' => ['move', 'del'],
            'thumb' => ['move', 'del', 'add', 'edit'],
            'thumb:image' => ['move', 'del', 'add', 'edit'],
            'thumb:file' => ['move', 'del', 'add', 'edit']
        ];
        $this->params['types'] = [
            'table' => ['text', 'textarea', 'textareamini', 'richtext', 'image', 'file', 'dropdown', 'checkbox', 'option', 'number', 'date']
        ];

        return $this;
    }

    /**
     * @return false|string
     */
    public function renderData()
    {
        $this->getConfig();
        $this->getData();

        if (empty($this->config)) {
            if ($this->config === null) {
                $out = 'Must be an array in file for id=' . $this->params['tv']['id'];
            } else {
                $out = 'Not found config file for TV id=' . $this->params['tv']['id'];
            }
        } else {
            $data = [
                'id' => $this->uniqid(),
                'items' => $this->replaceData($this->fillData(), $this->config),
                'docid' => $this->params['id'],
                'tvId' => $this->params['tv']['id'],
                'value' => !empty($this->data) ? json_encode($this->data, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : ''
            ];
            $data['toolbar'] = $this->getToolbar($data);
            $out = $this->view('wrap', $data);
        }

        return $out;
    }

    /**
     * @return array|mixed
     */
    protected function getConfig()
    {
        $this->config = [];

        if (!is_dir($this->basePath . 'config')) {
            mkdir($this->basePath . 'config', 0755);
        }

        if (file_exists($this->basePath . 'config/' . $this->params['tv']['id'] . '.php')) {
            $this->config = require_once $this->basePath . 'config/' . $this->params['tv']['id'] . '.php';
        }

        if (is_array($this->config)) {
            foreach ($this->config as $k => &$v) {
                if (!isset($v['title'])) {
                    $v['title'] = $k;
                }
            }
        } else {
            $this->config = null;
        }

        return $this->config;
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $this->data = [];

        if ($this->params['storage'] == 'files') {
            if (!is_dir($this->basePath . 'data')) {
                mkdir($this->basePath . 'data', 0755);
            }

            if (file_exists($this->fileData())) {
                require_once $this->fileData();
            }
        } else {
            $this->data = $this->baseData();
        }

        return $this->data;
    }

    /**
     * @param int $doc_id
     * @param string $tv_id
     * @return string
     */
    protected function fileData($doc_id = 0, $tv_id = '')
    {
        if (empty($doc_id) && !empty($this->params['id'])) {
            $doc_id = $this->params['id'];
        }

        if (empty($tv_id) && isset($this->params['tv']['id'])) {
            $tv_id = $this->params['tv']['id'];
        }

        return $this->basePath . 'data/' . $doc_id . '__' . $tv_id . '.php';
    }

    /**
     * @return array
     */
    protected function baseData()
    {
        $this->data = [];

        $sql = $this->evo->db->query('SHOW TABLES FROM ' . $this->evo->db->config['dbase'] . ' LIKE "' . $this->evo->db->config['table_prefix'] . 'multifields"');
        if (!$this->evo->db->getRecordCount($sql)) {
            $this->evo->db->query('
            CREATE TABLE IF NOT EXISTS `' . $this->evo->db->config['table_prefix'] . 'multifields` (
            `id` int(15) NOT NULL AUTO_INCREMENT,
            `doc_id` int(10) NOT NULL default "0",
            `tv_id` int(10) NOT NULL default "0",
            `field_parent` int(10) NOT NULL default "0",
            `field_id` int(10) NOT NULL default "0",
            `field_name` varchar(255) NOT NULL default "",
            `field_type` varchar(255) NOT NULL default "",
            `field_value` mediumtext,
            `field_disabled` int(1) NOT NULL default "0",
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM');
        }

        if (!empty($this->params['id']) && !empty($this->params['tv']['id'])) {
            $sql = $this->evo->db->query('
            SELECT id, field_parent, field_id, field_name, field_value
            FROM ' . $this->evo->getFullTableName('multifields') . ' 
            WHERE doc_id=' . $this->params['id'] . ' AND tv_id=' . $this->params['tv']['id'] . '
            ORDER BY field_id ASC');

            if ($this->evo->db->getRecordCount($sql)) {
                while ($row = $this->evo->db->getRow($sql)) {
                    $this->data[$row['field_id']] = [
                        'parent' => $row['field_parent'],
                        'name' => $row['field_name'],
                        'value' => $row['field_value']
                    ];
                }
            }
        }

        return $this->data;
    }

    /**
     * @param int $parent
     * @param int $level
     * @return array
     */
    protected function fillData($parent = 0, $level = 0)
    {
        $out = [];
        $level++;
        foreach ($this->data as $k => $v) {
            $v['level'] = $level;
            if ($parent == $v['parent']) {
                if ($_ = $this->fillData($k, $level)) {
                    $v['items'] = $_;
                }
                $out[] = $v;
            }
        }

        return $out;
    }

    /**
     * @param array $data
     * @param array $config
     * @return string
     */
    protected function replaceData($data = [], $config = [])
    {
        $out = '';
        $result = [];
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $items = isset($v['items']) ? $v['items'] : [];
                unset($v['items']);

                $this->findData($v['name'], $config, $result);

                if (isset($this->config[$v['name']])) {
                    $result = $this->config[$v['name']];
                    $v['parentName'] = $v['name'];
                }

                if (isset($result['type']) && $result['type'] == 'table') {
                    $result['items'] = $this->tableItems($result);
                }

                $v = is_array($result) ? array_replace($result, $v) : $v;

                if ($v['type'] == 'Item' && isset($result['items'])) {
                    $_items = $result['items'];
                    if (count($result['items']) > count($items)) {
                        $_val = [];
                        foreach ($items as $key => $val) {
                            if (isset($_items[$val['name']])) {
                                $_val = $val;
                                unset($_items[$val['name']]);
                            }
                        }
                        if (!empty($_items)) {
                            foreach ($_items as $key => $val) {
                                $items[] = [
                                    'parent' => $_val['parent'],
                                    'name' => $key,
                                    'value' => '',
                                    'level' => $_val['level']
                                ];
                            }
                        }
                    }
                }

                $v['items'] = $this->replaceData($items, $result);

                if (isset($v['type'])) {
                    $out .= $this->renderElement($v);
                }
            }
        }

        return $out;
    }

    /**
     * @param $searchKey
     * @param $arr
     * @param $result
     */
    protected function findData($searchKey, $arr, &$result)
    {
        if (isset($arr[$searchKey])) {
            $result = $arr[$searchKey];
        }
        foreach ($arr as $key => $param) {
            if ($key == 'items') {
                $this->findData($searchKey, $param, $result);
            }
        }
    }

    /**
     * @param array $data
     * @return false|string
     */
    protected function getToolbar($data = [])
    {
        $out = '';

        if (!isset($data['templates']) || (isset($data['templates']) && count($data['templates']))) {
            $i = 0;
            foreach ($this->config as $k => $v) {
                if ((empty($v['hidden']) && empty($data['templates'])) || (!empty($data['templates']) && in_array($k, $data['templates']))) {
                    $out .= $this->view('element', [
                        'class' => 'mf-option',
                        'data' => $v['title'],
                        'attr' => 'data-id="' . $k . '"'
                    ]);
                    $i++;
                }
            }

            if (!empty($out)) {
                $out = $this->view('toolbar', [
                    'data' => $out,
                    'tvId' => $data['tvId'],
                    'docid' => $this->params['id'],
                    'id' => $data['id'],
                    'class' => $i > 1 ? '' : ' mf-hidden'
                ]);
            }
        }

        return $out;
    }

    /**
     * @param array $data
     * @param array $actions
     * @return array
     */
    protected function setActions($data = [], $actions = [])
    {
        $actions = array_flip($actions);
        $_actions = isset($data['actions']) ? (!empty($data['actions']) ? $data['actions'] : $actions) : true;

        if (is_array($_actions)) {
            $_actions = array_flip($_actions);
            $_actions = array_intersect_key($actions, $_actions);
        } else {
            if (is_null($_actions) || $_actions === true) {
                $_actions = $actions;
            } else {
                $_actions = [];
            }
        }

        $actions = '';

        if (isset($_actions['move'])) {
            $actions .= '<i class="mf-actions-btn mf-actions-move"></i>';
            $data['draggable'] = true;
        }

        if (isset($_actions['add'])) {
            $actions .= '<i class="mf-actions-btn mf-actions-add"></i>';
        }

        if (isset($_actions['del'])) {
            $actions .= '<i class="mf-actions-btn mf-actions-del"></i>';
        }

        if (isset($_actions['copy'])) {
            $actions .= '<i class="mf-actions-btn mf-actions-copy"></i>';
        }

        if (isset($_actions['edit'])) {
            if ($data['type'] == 'thumb:image') {
                $actions .= '<i class="mf-actions-btn mf-actions-edit mf-actions-edit-image"></i>';
            } elseif ($data['type'] == 'thumb:file') {
                $actions .= '<i class="mf-actions-btn mf-actions-edit mf-actions-edit-file"></i>';
            } else {
                $actions .= '<i class="mf-actions-btn mf-actions-edit"></i>';
            }
        }

        $data['actions'] = $this->view('actions', [
            'actions' => $actions
        ]);

        return $data;
    }

    /**
     * @param array $data
     * @return false|string
     */
    protected function renderElement($data = [])
    {
        $tpl = $data['type'];
        $class = '';
        $data['draggable'] = false;
        $data['id'] = $this->uniqid();
        $data['tvId'] = $this->params['tv']['id'];
        $data['value'] = isset($data['value']) ? stripcslashes($data['value']) : '';
        $data['style'] = isset($data['style']) ? $data['style'] : '';
        $data['attr'] = isset($data['attr']) ? $data['attr'] : '';

        switch ($tpl) {
            case 'section':
            case 'group':
            case 'row':
            case 'table':
                $class = 'row';
                $data = $this->setActions($data, $this->params['actions'][$data['type']]);
                if ($data['type'] == 'group') {
                    $data['toolbar'] = $this->getToolbar($data);
                }
                if ($data['type'] == 'table') {
                    $data['header'] = $this->tableHeader($data);
                }
                break;

            case 'tabs':
                $data = $this->setActions($data, $this->params['actions'][$data['type']]);
                break;

            case 'tab':
                $class = 'row col-12';
                break;

            case 'item':
                $class = 'row col-12';
                $data['actions'] = '';
                break;

            default:
                $tpl = 'itemElement';
                $type = str_replace(':', '_', $data['type']);
                $class = 'col';
                $inputClass = 'form-control';
                $multi = isset($data['multi']) ? $data['multi'] : '';
                $data['placeholder'] = isset($data['placeholder']) ? ' placeholder="' . $data['placeholder'] . '"' : '';
                $data['item.attr'] = isset($data['item.attr']) ? ' ' . $data['item.attr'] : '';
                $data['title.class'] = isset($data['title.class']) ? $data['title.class'] : 'col-12';
                $title = isset($data['title']) ? '<div class="' . $data['title.class'] . ' p-0 pr-1">' . $data['title'] . '</div>' : '';
                $data['thumb'] = isset($data['thumb']) ? $data['thumb'] : '';

                if (!empty($data['item.class'])) {
                    $inputClass = ' ' . $data['item.class'];
                }
                if (!empty($data['image'])) {
                    $data['attr'] .= ' data-image="' . $data['image'] . '"';
                }
                if (!empty($data['thumb'])) {
                    $data['attr'] .= ' data-thumb="' . $data['thumb'] . '"';
                }

                $data['element'] = renderFormElement($type, $data['id'], $data['default'], $data['elements'], $data['value'], $data['style']);

                switch ($type) {
                    case 'thumb':
                    case 'thumb_image':
                        $tpl = $type;
                        $class = 'col-auto';
                        $data['thumb_value'] = $this->checkThumbImage($data['value']);
                        if (!empty($data['image'])) {
                            $data = $this->setActions($data);
                        } else {
                            $data = $this->setActions($data, $this->params['actions'][$data['type']]);
                        }
                        break;

                    case 'richtext':
                        $tpl = $type;
                        $data['element'] = preg_replace('/ rows="(.*?)"/', 'rows="5"', $data['element']);
                        break;

                    case 'rawtext':
                    case 'textarea':
                    case 'rawtextarea':
                    case 'textareamini':
                    case 'text':
                    case 'dropdown':
                    case 'listbox':
                    case 'listbox-multiple':
                    case 'option':
                    case 'checkbox':
                    case 'url':
                    case 'email':
                    case 'number':
                    case 'custom_tv':
                        break;

                    case 'image':
                        $data['element'] = preg_replace([
                            '~<script[^>]*>.*?</script>~si',
                            '/BrowseServer\(.*\)/'
                        ], [
                            '',
                            'MultiFields.BrowseServer(this.previousElementSibling.id, \'images\', \'' . $multi . '\', \'' . $data['thumb'] . '\');'
                        ], $data['element']);
                        $data['element'] = str_replace('type="button"', 'class="btn" type="button"', $data['element']);
                        break;

                    case 'file':
                        $data['element'] = preg_replace([
                            '~<script[^>]*>.*?</script>~si',
                            '/BrowseFileServer\(.*\)/'
                        ], [
                            '',
                            'MultiFields.BrowseServer(this.previousElementSibling.id, \'files\', \'' . $multi . '\', \'\');'
                        ], $data['element']);
                        $data['element'] = str_replace('type="button"', 'class="btn" type="button"', $data['element']);
                        break;

                    case 'date':
                        $inputClass .= ' DatePicker';
                        $data['element'] = preg_replace([
                            '/ onclick="document\.forms.*?"/si',
                            '/class="DatePicker"/'
                        ], [
                            ' class="btn text-danger" onclick="this.previousElementSibling.value=\'\';this.previousElementSibling.onblur(); return true;"',
                            ''
                        ], $data['element']);
                        break;

                    default:
                        $tpl = $type;
                        break;
                }

                $data['element'] = preg_replace('/ style="(.*?)"/', '', $data['element']);
                $data['element'] = $title . str_replace(' name="', $data['item.attr'] . $data['placeholder'] . ' data-value class="' . $inputClass . '" name="', $data['element']);
                break;
        }

        $data['class'] = ' ' . trim(!empty($data['class']) ? $data['class'] : $class);

        if (!empty($data['draggable'])) {
            $data['class'] .= ' mf-draggable';
        }

        if (!empty($data['parentName'])) {
            $data['class'] .= ' mf-parent';
        }

        $out = $this->view($tpl, $data);

        unset($data);

        return $out;
    }

    /**
     * @param array $data
     * @return false|string
     */
    protected function tableHeader($data = [])
    {
        $out = '';
        if (!empty($data['cols'])) {
            foreach ($data['cols'] as $k => $v) {
                $out .= $this->view('element', [
                    'data' => isset($v['title']) ? $v['title'] : $k,
                    'class' => 'col',
                    'attr' => isset($v['width']) ? ' style="max-width: ' . $v['width'] . '"' : ''
                ]);
            }
        }

        if ($out) {
            $out = $this->view('element', [
                'class' => 'mf-table-header row w-100 m-0',
                'data' => $out
            ]);
        }

        return $out;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function tableItems($data = [])
    {
        $out = [];
        if (!empty($data['cols'])) {
            foreach ($data['cols'] as $k => $v) {
                $out[$k] = [
                    'placeholder' => isset($v['placeholder']) ? $v['placeholder'] : '',
                    'type' => isset($v['type']) && in_array($v['type'], $this->params['types']['table']) ? $v['type'] : 'text',
                ];
                if (isset($v['width'])) {
                    $out[$k]['attr'] = ' style="width:' . $v['width'] . ';max-width:' . $v['width'] . '"';
                }
                if (!empty($v['autoincrement'])) {
                    $out[$k]['attr'] .= ' data-autoincrement';
                    $out[$k]['value'] = 1;
                }
                if (isset($v['elements'])) {
                    $out[$k]['elements'] = $v['elements'];
                }
            }
        }

        if (!empty($out)) {
            $out = [
                'table_row' => [
                    'type' => 'row',
                    'items' => $out
                ]
            ];
        }

        return $out;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function checkThumbImage($value = '')
    {
        if (is_readable(MODX_BASE_PATH . $value)) {
            $value = MODX_BASE_URL . $value;
        }

        return $value;
    }

    /**
     *
     */
    public function getTemplate()
    {
        $json = [];
        $tpl = isset($_REQUEST['tpl']) ? $_REQUEST['tpl'] : '';
        $this->params['tv']['id'] = isset($_REQUEST['tvid']) ? $_REQUEST['tvid'] : '';

        if (!empty($this->getConfig())) {
            $this->params['last'] = 1;
            $this->data = $this->fillTemplate([$tpl => $this->config[$tpl]]);
            $json['template'] = $this->replaceData($this->fillData(), $this->config);
            $json['template'] = preg_replace('|\s+|u', ' ', $json['template']);
        }

        print json_encode($json, JSON_FORCE_OBJECT);
        exit;
    }

    /**
     * @param array $data
     * @param int $parent
     * @return array
     */
    protected function fillTemplate($data = [], $parent = 0)
    {
        $out = [];
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                if (isset($v['type']) && $v['type'] == 'table') {
                    $v['items'] = $this->tableItems($v);
                }
                if (isset($v['items'])) {
                    $v['id'] = $this->params['last']++;
                    $out[$v['id']] = [
                        'parent' => $parent,
                        'type' => $v['type'],
                        'name' => $k
                    ];
                    $out += $this->fillTemplate($v['items'], $v['id']);
                } else {
                    $id = $this->params['last']++;
                    $out[$id] = [
                        'parent' => $parent,
                        'type' => $v['type'],
                        'name' => $k
                    ];
                }
            }
        }

        return $out;
    }

    /**
     *
     */
    public function getRichText()
    {
        global $modx_lang_attribute;

        $mxla = !empty($modx_lang_attribute) ? $modx_lang_attribute : 'en';
        $which_editor = $this->evo->config['which_editor'];
        if (!empty($_POST['which_editor'])) {
            $which_editor = $_POST['which_editor'];
        }
        $which_editor_config = [
            'editor' => $which_editor,
            'elements' => ['ta'],
            'options' => [
                'ta' => [
                    'theme' => 'custom',
                    'width' => '100%',
                    'height' => '100%',
                    'block_formats' => 'Paragraph=p;Header 1=h1;Header 2=h2;Header 3=h3;Header 4=h4;Header 5=h5;Header 6=h6;Div=div'
                ]
            ]
        ];
        if (!empty($_REQUEST['options']) && is_scalar($_REQUEST['options'])) {
            $options = base64_decode($_REQUEST['options']);
            $options = json_decode($options, true);
            if (is_array($options)) {
                $which_editor_config['options']['ta'] = array_merge($which_editor_config['options']['ta'], $options);
            }
        }
        $body_class = '';
        $theme_modes = array('', 'lightness', 'light', 'dark', 'darkness');
        $theme_mode = isset($_COOKIE['MODX_themeMode']) ? $_COOKIE['MODX_themeMode'] : '';
        if (!empty($theme_modes[$theme_mode])) {
            $body_class .= ' ' . $theme_modes[$theme_mode];
        } elseif (!empty($theme_modes[$this->evo->config['manager_theme_mode']])) {
            $body_class .= ' ' . $theme_modes[$this->evo->config['manager_theme_mode']];
        }

        define($which_editor . '_INIT_INTROTEXT', 1);

        // invoke OnRichTextEditorInit event
        $evtOut = $this->evo->invokeEvent('OnRichTextEditorInit', $which_editor_config);
        if (is_array($evtOut)) {
            $evtOut = implode('', $evtOut);
        } else {
            $evtOut = '';
        }

        print $this->view('window.richtext', [
            'mxla' => $mxla,
            'MGR_DIR' => MODX_BASE_URL . MGR_DIR,
            'manager_theme' => $this->evo->config['manager_theme'],
            'body_class' => $body_class,
            'evtOut' => $evtOut
        ]);

        exit;
    }

    /**
     *
     */
    public function saveData()
    {
        $this->post = $_POST;

        if (isset($this->post['mf-data'])) {
            foreach ($this->post['mf-data'] as $k => $data) {
                list($id, $tvId) = explode('__', $k);
                $this->params['id'] = $id;
                $this->params['tv']['id'] = $tvId;
                $out = '';
                $data = $this->evo->removeSanitizeSeed($data);

                if ($this->params['storage'] == 'database') {
                    $this->deleteData($this->params['id'], $this->params['tv']['id']);
                }

                if (!empty($data)) {
                    $data = json_decode($data, true);

                    if ($this->params['storage'] == 'files') {
                        foreach ($data as $key => $v) {
                            if (is_array($v['value'])) {
                                $v['value'] = implode('||', $v['value']);
                            }
                            $out .= '$d[' . $key . ']=[';
                            $out .= '\'parent\'=>\'' . $v['parent'] . '\',';
                            $out .= '\'name\'=>\'' . $v['name'] . '\'';
                            if (isset($v['value'])) {
                                $out .= ',\'value\'=>\'' . $this->evo->db->escape($v['value']) . '\'';
                            }
                            $out .= '];' . "\n";
                        }
                    } else {
                        foreach ($data as $key => $v) {
                            if (is_array($v['value'])) {
                                $v['value'] = implode('||', $v['value']);
                            }
                            $this->evo->db->insert([
                                'doc_id' => $this->params['id'],
                                'tv_id' => $this->params['tv']['id'],
                                'field_parent' => $v['parent'],
                                'field_id' => $key,
                                'field_name' => $v['name'],
                                'field_type' => '',
                                'field_value' => $this->evo->db->escape($v['value']),
                            ], $this->evo->getFullTableName('multifields'));
                        }
                    }
                }

                if ($this->params['storage'] == 'files') {
                    if ($out == '') {
                        @unlink($this->fileData());
                    } else {
                        $out = '<?php' . "\n" . '$d = &$this->data;' . "\n" . $out;
                        file_put_contents($this->fileData(), $out);
                    }
                }
            }
        }
    }

    /**
     * @param int $doc_id
     * @param int $tv_id
     */
    protected function deleteData($doc_id = 0, $tv_id = 0)
    {
        $where = [];

        if (!empty($doc_id)) {
            $where[] = 'doc_id=' . $doc_id;
        }

        if (!empty($tv_id)) {
            $where[] = 'tv_id=' . $tv_id;
        }

        if (!empty($where)) {
            $where = 'WHERE ' . implode(' AND ', $where);
            $this->evo->db->query('DELETE FROM ' . $this->evo->getFullTableName('multifields') . $where);
        }
    }

    /**
     * @return string
     */
    protected function uniqid()
    {
        return md5(time() . rand(0, 99999));
    }

    /**
     * @param $__tpl
     * @param array $__data
     * @return false|string
     */
    protected function view($__tpl, $__data = [])
    {
        $__tpl = trim($__tpl, '/');
        $__tpl = $this->basePath . 'theme/' . $this->params['theme'] . '/view/' . $__tpl . '.php';
        if (file_exists($__tpl)) {
            extract($__data);
            ob_start();
            require($__tpl);
            $__out = ob_get_contents();
            ob_end_clean();
        } else {
            $__out = 'Error: Could not load template ' . $__tpl . '!<br>';
        }

        return $__out;
    }

    /**
     * @param null $str
     * @param null $str2
     * @param bool $exit
     */
    protected function dd($str = null, $str2 = null, $exit = false)
    {
        $class = 'col-xs-6';

        if ($str == null || $str2 == null) {
            //$class = 'col-xs-12';
        }
        print '<div class="row">';
        print '<div class="' . $class . '">';
        print '<pre class="alert alert-info">';
        print_r($str);
        print '</pre>';
        print '</div>';

        print '<div class="' . $class . '">';
        print '<pre class="alert alert-warning">';
        print_r($str2);
        print '</pre>';
        print '</div>';

        print '</div>';
        if ($exit) {
            exit;
        }
    }
}
