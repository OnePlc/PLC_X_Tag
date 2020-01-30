<?php
/**
 * ApiController.php - Tag Api Controller
 *
 * Main Controller for Tag Api
 *
 * @category Controller
 * @package Application
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Tag\Controller;

use Application\Controller\CoreController;
use OnePlace\Tag\Model\EntityTagTable;
use OnePlace\Tag\Model\TagTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;

class ApiController extends CoreController {
    /**
     * Tag Table Object
     *
     * @since 1.0.0
     */
    private $oTableGateway;

    /**
     * ApiController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param TagTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter,EntityTagTable $oTableGateway,$oServiceManager) {
        parent::__construct($oDbAdapter,$oTableGateway,$oServiceManager);
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'tag-single';
    }

    /**
     * API Home - Main Index
     *
     * @return bool - no View File
     * @since 1.0.0
     */
    public function indexAction() {
        $this->layout('layout/json');

        # Check license
        if(!$this->checkLicense('tag')) {
            $aReturn = ['state'=>'error','message'=>'no valid license for tag found'];
            echo json_encode($aReturn);
            return false;
        }

        $aReturn = ['state'=>'success','message'=>'Welcome to onePlace Tag API'];
        echo json_encode($aReturn);

        return false;
    }

    /**
     * List all Entities of Tags
     *
     * @return bool - no View File
     * @since 1.0.0
     */
    public function listAction() {
        $this->layout('layout/json');

        # Check license
        if(!$this->checkLicense('tag')) {
            $aReturn = ['state'=>'error','message'=>'no valid license for tag found'];
            echo json_encode($aReturn);
            return false;
        }

        $bSelect2 = true;

        /**
         * todo: enforce to use /api/contact instead of /contact/api so we can do security checks in main api controller
        if(!\Application\Controller\ApiController::$bSecurityCheckPassed) {
        # Print List with all Entities
        $aReturn = ['state'=>'error','message'=>'no direct access allowed','aItems'=>[]];
        echo json_encode($aReturn);
        return false;
        }
         **/

        $aItems = [];

        $aFilter = explode('_',$this->params()->fromRoute('filter','none_0'));
        $sForm = $aFilter[0];
        $iFilterID = (int)$aFilter[1];

        # Get All Tag Entities from Database
        $oItemsDB = $this->oTableGateway->fetchAll(false,['entity_form_idfs'=>$sForm,'tag_idfs'=>$iFilterID]);
        if(count($oItemsDB) > 0) {
            foreach($oItemsDB as $oItem) {
                if($bSelect2) {
                    $aItems[] = ['id'=>$oItem->getID(),'text'=>$oItem->getLabel()];
                } else {
                    $aItems[] = $oItem;
                }

            }
        }

        /**
         * Build Select2 JSON Response
         */
        $aReturn = [
            'state'=>'success',
            'results' => $aItems,
            'pagination' => (object)['more'=>false],
        ];

        # Print List with all Entities
        echo json_encode($aReturn);

        return false;
    }

    /**
     * Get a single Entity of Tag
     *
     * @return bool - no View File
     * @since 1.0.0
     */
    public function getAction() {
        $this->layout('layout/json');

        # Check license
        if(!$this->checkLicense('tag')) {
            $aReturn = ['state'=>'error','message'=>'no valid license for tag found'];
            echo json_encode($aReturn);
            return false;
        }

        # Get Tag ID from route
        $iItemID = $this->params()->fromRoute('id', 0);

        # Try to get Tag
        try {
            $oItem = $this->oTableGateway->getSingle($iItemID);
        } catch (\RuntimeException $e) {
            # Display error message
            $aReturn = ['state'=>'error','message'=>'Tag not found','oItem'=>[]];
            echo json_encode($aReturn);
            return false;
        }

        # Print Entity
        $aReturn = ['state'=>'success','message'=>'Tag found','oItem'=>$oItem];
        echo json_encode($aReturn);

        return false;
    }
}
