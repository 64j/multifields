<?php
/**
 * Plugin multifields
 * @author 64j
 */

$e = &$modx->event;

require_once '__autoload.php';

switch ($e->name) {
    case 'OnManagerMainFrameHeaderHTMLBlock':
        if (in_array($modx->manager->action, [3, 4, 17, 27, 72, 112])) {
            $e->addOutput(\Multifields\Base\Core::getInstance()
                ->getStartScripts());

            (new \Multifields\Base\Update)->run();
        }
        break;

    case 'OnManagerPreFrameLoader':
        if (isset($_REQUEST['mf-action']) && !empty($_REQUEST['action'])) {
            $className = !empty($_REQUEST['class']) ? $_REQUEST['class'] : '';

            if (class_exists($className)) {
                $class = new $className();
                $method = 'action' . ucfirst(strtolower($_REQUEST['action']));
                if (is_callable([$className, $method])) {
                    try {
                        echo $class->$method($_REQUEST);
                    } catch (Error $exception) {
                        echo json_encode([
                            'error' => (string)$exception
                        ], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    echo 'Method ' . $method . ' not found in class ' . $className . '!';
                }
            } else {
                echo 'Class ' . $className . ' not found!';
            }

            exit;
        }
        break;

    case 'OnDocFormSave':
        \Multifields\Base\Core::getInstance()
            ->saveData();
        break;

    case 'OnDocFormDelete':
        \Multifields\Base\Core::getInstance()
            ->deleteData();
        break;

    case 'OnWebPageInit':
        \Multifields\Base\Front::getInstance();
        break;

    case 'OnAfterLoadDocumentObject':
        /** @var TYPE_NAME $documentObject */
        $e->setOutput(\Multifields\Base\Front::getInstance()
            ->addDocumentObject($documentObject));
        break;
}
