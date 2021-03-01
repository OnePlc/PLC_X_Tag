<?php
/**
 * TagController.php - Main Controller
 *
 * Main Controller Tag Module
 *
 * @category Controller
 * @package Tag
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Tag\Controller;

use Application\Controller\CoreController;
use Application\Model\CoreEntityModel;
use OnePlace\Tag\Model\EntityTag;
use OnePlace\Tag\Model\Tag;
use OnePlace\Tag\Model\EntityTagTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;

class EntityController extends CoreController {
    /**
     * Tag Table Object
     *
     * @since 1.0.0
     */
    private $oTableGateway;

    /**
     * TagController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param TagTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter,EntityTagTable $oTableGateway,$oServiceManager) {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'entitytag-single';
        parent::__construct($oDbAdapter,$oTableGateway,$oServiceManager);

        if($oTableGateway) {
            # Attach TableGateway to Entity Models
            if(!isset(CoreEntityModel::$aEntityTables[$this->sSingleForm])) {
                CoreEntityModel::$aEntityTables[$this->sSingleForm] = $oTableGateway;
            }
        }
    }

    /**
     * Delete Entity Tag
     *
     * @since 1.0.10
     * @return ViewModel
     */
    public function deleteAction()
    {
        $this->setThemeBasedLayout('tag');

        $id = (int) $this->params()->fromRoute('filter', 0);
        if (!$id) {
            return $this->redirect()->toRoute('tag');
        }

        $oRequest = $this->getRequest();
        if ($oRequest->isPost()) {
            $del = $oRequest->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $oRequest->getPost('id');
                $this->oTableGateway->deleteSingle($id);
                $this->flashMessenger()->addSuccessMessage('Tag deleted successfully');
            }

            // Redirect to list of residents
            return $this->redirect()->toRoute('tag');
        }

        $oEntityTag = CoreController::$aCoreTables['core-entity-tag']->select(['Entitytag_ID' => $id])->current();
        $iMultiCount = count(CoreController::$aCoreTables['core-entity-tag-entity']->select([
            'entity_tag_idfs' => $id,
        ]));
        $iSingleCount = 0;
        $sEntityType = explode('-',$oEntityTag->entity_form_idfs)[0];
        $oRootTag = CoreController::$aCoreTables['core-tag']->select(['Tag_ID' => $oEntityTag->tag_idfs])->current();
        switch($sEntityType) {
            case 'article':
                $oArtTbl = CoreController::$oServiceManager->get(\OnePlace\Article\Model\ArticleTable::class);
                $iSingleCount = count($oArtTbl->fetchAll(false, [$oRootTag->tag_key.'_idfs' => $oEntityTag->Entitytag_ID]));
                break;
            default:
                break;
        }
        $iCount = $iMultiCount+$iSingleCount;

        return new ViewModel([
            'id'    => $id,
            'oTag' => $this->oTableGateway->getSingle($id),
            'iCount' => $iCount,
        ]);
    }

    /**
     * Tag Add Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function addAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('tag');

        # Check license
        if(!$this->checkLicense('tag')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for tag');
            $this->redirect()->toRoute('home');
        }

        $iTagID = $this->params()->fromRoute('filter', 0);

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        # Display Add Form
        if(!$oRequest->isPost()) {
            # Add Buttons for breadcrumb
            $this->setViewButtons('entitytag-single');

            # Load Tabs for View Form
            $this->setViewTabs($this->sSingleForm);

            # Load Fields for View Form
            $this->setFormFields($this->sSingleForm);

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $this->logPerfomance('entitytag-add',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

            return new ViewModel([
                'sFormName' => $this->sSingleForm,
                'iTagID'=>$iTagID,
            ]);
        }

        # Get and validate Form Data
        $aFormData = $this->parseFormData($_REQUEST);

        $aFormData['tag_idfs'] = $_REQUEST['ref_idfs'];

        # Save Add Form
        $oTag = new EntityTag($this->oDbAdapter);
        $oTag->exchangeArray($aFormData);
        $iTagID = $this->oTableGateway->saveSingle($oTag);
        $oTag = $this->oTableGateway->getSingle($iTagID);

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('entitytag-save',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        # Display Success Message and View New Tag
        $this->flashMessenger()->addSuccessMessage('Entity Tag successfully created');
        return $this->redirect()->toRoute('tag',['action'=>'view','id'=>$aFormData['tag_idfs']]);
    }

    /**
     * Tag Edit Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function editAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('tag');

        # Check license
        if(!$this->checkLicense('tag')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for tag');
            $this->redirect()->toRoute('home');
        }

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        # Display Edit Form
        if(!$oRequest->isPost()) {

            # Get Tag ID from URL
            $iTagID = $this->params()->fromRoute('id', 0);

            # Try to get Tag
            try {
                $oTag = $this->oTableGateway->getSingle($iTagID);
            } catch (\RuntimeException $e) {
                echo 'Tag Not found';
                return false;
            }

            # Attach Tag Entity to Layout
            $this->setViewEntity($oTag);

            # Add Buttons for breadcrumb
            $this->setViewButtons('tag-single');

            # Load Tabs for View Form
            $this->setViewTabs($this->sSingleForm);

            # Load Fields for View Form
            $this->setFormFields($this->sSingleForm);

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $this->logPerfomance('tag-edit',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

            return new ViewModel([
                'sFormName' => $this->sSingleForm,
                'oTag' => $oTag,
            ]);
        }

        $iTagID = $oRequest->getPost('Item_ID');
        $oTag = $this->oTableGateway->getSingle($iTagID);

        # Update Tag with Form Data
        $oTag = $this->attachFormData($_REQUEST,$oTag);

        # Save Tag
        $iTagID = $this->oTableGateway->saveSingle($oTag);

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('tag-save',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        # Display Success Message and View New User
        $this->flashMessenger()->addSuccessMessage('Tag successfully saved');
        return $this->redirect()->toRoute('tag',['action'=>'view','id'=>$iTagID]);
    }

    /**
     * Tag View Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function viewAction() {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('tag');

        # Check license
        if(!$this->checkLicense('tag')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for tag');
            $this->redirect()->toRoute('home');
        }

        # Get Tag ID from URL
        $iTagID = $this->params()->fromRoute('id', 0);

        # Try to get Tag
        try {
            $oTag = $this->oTableGateway->getSingle($iTagID);
        } catch (\RuntimeException $e) {
            echo 'Tag Not found';
            return false;
        }

        # Attach Tag Entity to Layout
        $this->setViewEntity($oTag);

        # Add Buttons for breadcrumb
        $this->setViewButtons('tag-view');

        # Load Tabs for View Form
        $this->setViewTabs($this->sSingleForm);

        # Load Fields for View Form
        $this->setFormFields($this->sSingleForm);

        # Get Tag Entity Tags
        $aPartialData = [
            'aEntityTags'=>$this->getEntityTags((int)$oTag->getID()),
        ];
        $this->setPartialData('entitytags',$aPartialData);

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('tag-view',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        return new ViewModel([
            'sFormName'=>$this->sSingleForm,
            'oTag'=>$oTag,
        ]);
    }

    private function getEntityTags(int $iTagID) {
        $aEntityTags = [];
        $oTagsFromDB =  CoreController::$aCoreTables['core-entity-tag']->select(['tag_idfs'=>$iTagID]);
        if(count($oTagsFromDB) > 0) {
            foreach($oTagsFromDB as $oEntityTag) {
                $aEntityTags[] = $oEntityTag;
            }
        }

        return $aEntityTags;
    }
}
