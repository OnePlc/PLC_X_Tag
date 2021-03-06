<?php
/**
 * TagTable.php - Tag Table
 *
 * Table Model for Tag
 *
 * @category Model
 * @package Tag
 * @author Verein onePlace
 * @copyright (C) 2020 Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\Tag\Model;

use Application\Controller\CoreController;
use Application\Model\CoreEntityTable;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;

class EntityTagTable extends CoreEntityTable
{

    /**
     * TagTable constructor.
     *
     * @param TableGateway $tableGateway
     * @since 1.0.0
     */
    public function __construct(TableGateway $tableGateway)
    {
        parent::__construct($tableGateway);

        # Set Single Form Name
        $this->sSingleForm = 'tag-single';
    }

    /**
     * Fetch All Tag Entities based on Filters
     *
     * @param bool $bPaginated
     * @return Paginator Paginated Table Connection
     * @since 1.0.0
     */
    public function fetchAll($bPaginated = false,$aWhere = [],$sSort = 'created_date DESC')
    {
        $oSel = new Select($this->oTableGateway->getTable());

        # Build where
        $oWh = new Where();
        foreach(array_keys($aWhere) as $sWh) {
            $bIsLike = stripos($sWh,'-like');
            if($bIsLike === false) {

            } else {
                # its a like
                $sFieldKey = substr($sWh,0,strlen($sWh)-strlen('-like'));
                if($sFieldKey == 'label') {
                    $sFieldKey = 'tag_value';
                }
                $oWh->like($sFieldKey,'%'.$aWhere[$sWh].'%');
            }

            $bIsIDFS = stripos($sWh,'_idfs');
            if($bIsIDFS === false) {

            } else {
                # its a like
                $oWh->equalTo($sWh,$aWhere[$sWh]);
            }
        }
        $oSel->where($oWh);

        # Return Paginator or Raw ResultSet based on selection
        if ($bPaginated) {
            # Create result set for user entity
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Tag($this->oTableGateway->getAdapter()));

            # Create a new pagination adapter object
            $oPaginatorAdapter = new DbSelect(
            # our configured select object
                $oSel,
                # the adapter to run it against
                $this->oTableGateway->getAdapter(),
                # the result set to hydrate
                $resultSetPrototype
            );
            # Create Paginator with Adapter
            $oPaginator = new Paginator($oPaginatorAdapter);
            return $oPaginator;
        } else {
            $oResults = $this->oTableGateway->selectWith($oSel);
            return $oResults;
        }
    }

    /**
     * Get Tag Entity
     *
     * @param int $id
     * @param string $sKey
     * @return mixed
     * @since 1.0.0
     */
    public function getSingle($id, $sKey = 'Entitytag_ID')
    {
        return $this->getSingleEntity($id, $sKey);
    }

    /**
     * Save Tag Entity
     *
     * @param Tag $oTag
     * @return int Tag ID
     * @since 1.0.0
     */
    public function saveSingle(EntityTag $oEntityTag)
    {
        $aData = [
            'entity_form_idfs' => $oEntityTag->entity_form_idfs,
            'tag_value' => $oEntityTag->tag_value,
            'tag_idfs' => $oEntityTag->tag_idfs,
            'translatable' => $oEntityTag->translatable,
            'parent_tag_idfs' => $oEntityTag->parent_tag_idfs,
        ];

        $aData = $this->attachDynamicFields($aData,$oEntityTag);

        $id = (int) $oEntityTag->id;

        if ($id === 0) {
            # Add Metadata
            $aData['created_by'] = CoreController::$oSession->oUser->getID();
            $aData['created_date'] = date('Y-m-d H:i:s',time());
            $aData['modified_by'] = CoreController::$oSession->oUser->getID();
            $aData['modified_date'] = date('Y-m-d H:i:s',time());

            # Insert Tag
            $this->oTableGateway->insert($aData);

            # Return ID
            return $this->oTableGateway->lastInsertValue;
        }

        # Check if Tag Entity already exists
        try {
            $this->getSingle($id);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf(
                'Cannot update tag with identifier %d; does not exist',
                $id
            ));
        }

        # Update Metadata
        $aData['modified_by'] = CoreController::$oSession->oUser->getID();
        $aData['modified_date'] = date('Y-m-d H:i:s',time());

        # Update Tag
        $this->oTableGateway->update($aData, ['Entitytag_ID' => $id]);

        return $id;
    }

    /**
     * Add Minimal Entry - used for Select2
     *
     * @param $sLabel Label of new entity
     * @param $sForm name of form
     * @param $sTag name of tag
     * @return int id of new entry
     * @since 1.0.0
     */
    public function addMinimal($sLabel,$sForm,$sTag)
    {
        # Stripe idfs from fieldname to get tagname
        $bIsIDFS = stripos($sTag,'_idfs');
        if($bIsIDFS === false) {

        } else {
            $sTag = substr($sTag,0,strlen($sTag)-strlen('_idfs'));
        }

        # get tag
        $oTag = CoreController::$aCoreTables['core-tag']->select(['tag_key'=>$sTag]);
        if(count($oTag) > 0) {
            $oTag = $oTag->current();

            # Prevent duplicates
            $oEntCheck = CoreController::$aCoreTables['core-entity-tag']->select([
                'entity_form_idfs'=>$sForm,
                'tag_value'=>$sLabel,
            ]);
            if(count($oEntCheck) == 0) {
                $aData = [
                    'entity_form_idfs'=>$sForm,
                    'tag_idfs'=>$oTag->Tag_ID, // get id from core_tag
                    'tag_value'=>$sLabel,
                    'parent_tag_idfs'=>0,
                    'created_by'=>CoreController::$oSession->oUser->getID(),
                    'created_date'=>date('Y-m-d H:i:s',time()),
                    'modified_by'=>CoreController::$oSession->oUser->getID(),
                    'modified_date'=>date('Y-m-d H:i:s',time()),
                ];

                # Insert Tag
                CoreController::$aCoreTables['core-entity-tag']->insert($aData);

                # Return ID
                return CoreController::$aCoreTables['core-entity-tag']->lastInsertValue;
            } else {
                $oEntCheck = $oEntCheck->current();
                return $oEntCheck->Entitytag_ID;
            }

        } else {
            return 0;
        }
    }

    /**
     * Delete Entity Tag
     *
     * @param $iTagID
     * @since 1.0.10
     * @return bool
     */
    public function deleteSingle($iTagID)
    {
        # Remove Entity Tag
        $this->oTableGateway->delete(['Entitytag_ID' => $iTagID]);

        return true;
    }
}