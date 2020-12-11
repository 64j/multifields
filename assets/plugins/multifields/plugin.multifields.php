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
            $e->addOutput(mfc()->getStartScripts());
        }
        break;

    case 'OnBeforeManagerPageInit':
        mfc()->managerInit();
        break;

    case 'OnDocFormSave':
        mfc()->saveData($id);
        break;

    case 'OnDocDuplicate':
        mfc()->duplicateData($id, $new_id);
        break;

    case 'OnEmptyTrash':
        mfc()->deleteData($ids);
        break;

    case 'OnWebPageInit':
        mff();
        break;

    case 'OnAfterLoadDocumentObject':
        /** @var TYPE_NAME $documentObject */
        $e->setOutput(mff()->addDocumentObject($documentObject));
        break;
}
