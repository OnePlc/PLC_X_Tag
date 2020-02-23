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
use Laminas\Db\Sql\Select;
use Zend\I18n\Translator\Translator;


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
        $sListMode = 'select2';
        # Get list mode from query
        if(isset($_REQUEST['listmode'])) {
            if($_REQUEST['listmode'] == 'info') {
                $bSelect2 = false;
                $sListMode = 'info';
            }
        }

        # get list label from query
        $sLang = 'en_US';
        if(isset($_REQUEST['lang'])) {
            $sLang = $_REQUEST['lang'];
        }

        $sEntityType = 'entitytag';

        // translating system
        $translator = new Translator();
        $aLangs = ['en_US','de_DE'];
        foreach($aLangs as $sLoadLang) {
            if(file_exists('vendor/oneplace/oneplace-translation/language/'.$sLoadLang.'.mo')) {
                $translator->addTranslationFile('gettext', 'vendor/oneplace/oneplace-translation/language/'.$sLang.'.mo', $sEntityType, $sLoadLang);
            }
        }

        $translator->setLocale($sLang);

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

        $sForm = $this->params()->fromRoute('form','none');
        $sTag = $this->params()->fromRoute('tagtype','none');

        $oTag = CoreController::$aCoreTables['core-tag']->select(['tag_key'=>$sTag]);
        if(count($oTag) == 0) {
            echo 'tag not found';
            return false;
        }
        $oTag = $oTag->current();

        $aTagWhere = ['entity_form_idfs'=>$sForm,'tag_idfs'=>$oTag->Tag_ID];
        if(isset($_REQUEST['q'])) {
            $aTagWhere['label-like'] = $_REQUEST['q'];
        }


        $aCountWh = [];
        if(isset($_REQUEST['listmodefilter'])) {
            if($_REQUEST['listmodefilter'] == 'webonly') {
                $aCountWh['article.show_on_web_idfs'] = 2;
            }
        }


        # Get All Tag Entities from Database
        $oItemsDB = $this->oTableGateway->fetchAll(false,$aTagWhere);
        if(count($oItemsDB) > 0) {
            foreach($oItemsDB as $oItem) {
                if($bSelect2) {
                    $aItems[] = ['id'=>$oItem->getID(),'text'=>$oItem->getLabel()];
                } elseif($sListMode == 'info') {
                    $oCountSel = new Select(CoreController::$aCoreTables['core-entity-tag-entity']->getTable());
                    $oCountSel->join(['article' => 'article'],'article.Article_ID = core_entity_tag_entity.entity_idfs');

                    $aCountWh['entity_tag_idfs'] = $oItem->getID();
                    $aCountWh['entity_type'] = 'article';
                    $oCountSel->where($aCountWh);
                    $iCount = count(CoreController::$aCoreTables['core-entity-tag-entity']->selectWith($oCountSel));
                    if($iCount > 0) {
                        $aItems[] = ['id'=>$oItem->getID(),'label'=>$translator->translate($oItem->getLabel(),$sEntityType,$sLang),'count'=>$iCount];
                    }
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
