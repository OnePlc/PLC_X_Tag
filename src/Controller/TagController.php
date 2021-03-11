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
use OnePlace\Tag\Model\Tag;
use OnePlace\Tag\Model\TagTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;

class TagController extends CoreController
{
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
    public function __construct(AdapterInterface $oDbAdapter,TagTable $oTableGateway,$oServiceManager) {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'tag-single';
        parent::__construct($oDbAdapter,$oTableGateway,$oServiceManager);

        if($oTableGateway) {
            # Attach TableGateway to Entity Models
            if(!isset(CoreEntityModel::$aEntityTables[$this->sSingleForm])) {
                CoreEntityModel::$aEntityTables[$this->sSingleForm] = $oTableGateway;
            }
        }
    }

    /**
     * Tag Index
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function indexAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('tag');

        # Check license
        if(!$this->checkLicense('tag')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for tag');
            $this->redirect()->toRoute('home');
        }

        # Add Buttons for breadcrumb
        $this->setViewButtons('tag-index');

        # Set Table Rows for Index
        $this->setIndexColumns('tag-index');

        # Get Paginator
        $oPaginator = $this->oTableGateway->fetchAll(true);
        $iPage = (int) $this->params()->fromQuery('page', 1);
        $iPage = ($iPage < 1) ? 1 : $iPage;
        $iItemsPerPage = (CoreController::$oSession->oUser->getSetting('tag-index-items-per-page'))
            ? CoreController::$oSession->oUser->getSetting('tag-index-items-per-page') : 10;
        $oPaginator->setCurrentPageNumber($iPage);
        $oPaginator->setItemCountPerPage($iItemsPerPage);

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('tag-index',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        return new ViewModel([
            'sTableName'=>'tag-index',
            'aItems'=>$oPaginator,
        ]);
    }

    /**
     * Tag Add Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function addAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('tag');

        # Check license
        if(!$this->checkLicense('tag')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for tag');
            $this->redirect()->toRoute('home');
        }

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        # Display Add Form
        if(!$oRequest->isPost()) {
            # Add Buttons for breadcrumb
            $this->setViewButtons('tag-single');

            # Load Tabs for View Form
            $this->setViewTabs($this->sSingleForm);

            # Load Fields for View Form
            $this->setFormFields($this->sSingleForm);

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $this->logPerfomance('tag-add',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

            return new ViewModel([
                'sFormName' => $this->sSingleForm,
            ]);
        }

        # Get and validate Form Data
        $aFormData = $this->parseFormData($_REQUEST);

        # Save Add Form
        $oTag = new Tag($this->oDbAdapter);
        $oTag->exchangeArray($aFormData);
        $iTagID = $this->oTableGateway->saveSingle($oTag);
        $oTag = $this->oTableGateway->getSingle($iTagID);

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('tag-save',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        # Display Success Message and View New Tag
        $this->flashMessenger()->addSuccessMessage('Tag successfully created');
        return $this->redirect()->toRoute('tag',['action'=>'view','id'=>$iTagID]);
    }

    /**
     * Tag Edit Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function editAction()
    {
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
    public function viewAction()
    {
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

        $aEntityTags = [];
        $oEntityTagsDB = $this->getEntityTags((int)$oTag->getID());
        if(count($oEntityTagsDB) > 0) {
            foreach($oEntityTagsDB as $oEntTag) {
                $iMultiCount = count(CoreController::$aCoreTables['core-entity-tag-entity']->select([
                    'entity_tag_idfs' => $oEntTag->Entitytag_ID,
                ]));
                $iSingleCount = 0;
                $sEntityType = explode('-',$oEntTag->entity_form_idfs)[0];
                $oRootTag = CoreController::$aCoreTables['core-tag']->select(['Tag_ID' => $oEntTag->tag_idfs])->current();
                switch($sEntityType) {
                    case 'article':
                        $oArtTbl = CoreController::$oServiceManager->get(\OnePlace\Article\Model\ArticleTable::class);
                        try {
                            $iSingleCount = count($oArtTbl->fetchAll(false, [$oRootTag->tag_key.'_idfs' => $oEntTag->Entitytag_ID]));
                        } catch(\RuntimeException $e) {
                            try {
                                $iSingleCount = count($oArtTbl->fetchAll(false, ['multi_tag'=> $oEntTag->Entitytag_ID]));
                            } catch(\RuntimeException $e) {
                                $iSingleCount = 0;
                            }
                        }
                        break;
                    default:
                        break;
                }
                $oEntTag->iCount = $iMultiCount+$iSingleCount;

                $aEntityTags[] = $oEntTag;
            }
        }

        # Get Tag Entity Tags
        $aPartialData = [
            'aEntityTags'=> $aEntityTags,
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

    /**
     * Get all entity tags for a certain tag
     *
     * @param int $iTagID
     * @return array
     */
    private function getEntityTags(int $iTagID)
    {
        $aEntityTags = [];
        $oTagsFromDB =  CoreController::$aCoreTables['core-entity-tag']->select(['tag_idfs'=>$iTagID]);
        if(count($oTagsFromDB) > 0) {
            foreach($oTagsFromDB as $oEntityTag) {
                $aEntityTags[] = $oEntityTag;
            }
        }

        return $aEntityTags;
    }

    /**
     * Merge Tags Form
     *
     * @since 1.0.0
     * @return ViewModel - View Object with Data from Controller
     */
    public function mergeAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('tag');

        # Check license
        if(!$this->checkLicense('tag')) {
            $this->flashMessenger()->addErrorMessage('You have no active license for tag');
            $this->redirect()->toRoute('home');
        }

        # Get Request to decide wether to save or display form
        $oRequest = $this->getRequest();

        $iTagID = $this->params()->fromRoute('id', 0);
        $oTag = CoreController::$aCoreTables['core-tag']->select(['Tag_ID' => $iTagID])->current();

        # Display Add Form
        if(!$oRequest->isPost()) {
            # Add Buttons for breadcrumb
            $this->setViewButtons('tag-merge');

            # Load Tabs for View Form
            $this->setViewTabs('tag-merge');

            # Load Fields for View Form
            $this->setFormFields('tag-merge');

            # Log Performance in DB
            $aMeasureEnd = getrusage();
            $this->logPerfomance('tag-merge',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

            return new ViewModel([
                'sFormName' => 'tag-merge',
                'oTag' => $oTag,
            ]);
        }

        $this->layout('layout/json');

        $aSourceTagIDs = (array)$oRequest->getPost('tag-merge_sourcetags');
        $iTargetTagID = (int)$oRequest->getPost('tag-merge_targettag');

        foreach($aSourceTagIDs as $iSourceTagID) {
            $oEntityTag = CoreController::$aCoreTables['core-entity-tag']->select(['Entitytag_ID' => $iSourceTagID])->current();
            $sEntityType = explode('-',$oEntityTag->entity_form_idfs)[0];
            $oRootTag = CoreController::$aCoreTables['core-tag']->select(['Tag_ID' => $oEntityTag->tag_idfs])->current();

            switch($sEntityType) {
                case 'article':
                    $oArtTbl = CoreController::$oServiceManager->get(\OnePlace\Article\Model\ArticleTable::class);
                    $oArtTbl->updateAttribute($oRootTag->tag_key.'_idfs',$iTargetTagID,$oRootTag->tag_key.'_idfs',$iSourceTagID);
                    break;
                default:
                    break;
            }

            CoreController::$aCoreTables['core-entity-tag-entity']->update([
                'entity_tag_idfs' => $iTargetTagID,
            ],[
                'entity_tag_idfs' => $iSourceTagID,
            ]);
        }

        # Log Performance in DB
        $aMeasureEnd = getrusage();
        $this->logPerfomance('tag-merge',$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"utime"),$this->rutime($aMeasureEnd,CoreController::$aPerfomanceLogStart,"stime"));

        # Display Success Message and View New Tag
        $this->flashMessenger()->addSuccessMessage('Tags successfully merged');
        return $this->redirect()->toRoute('tag',['action'=>'view','id'=>$iTagID]);
    }
}
