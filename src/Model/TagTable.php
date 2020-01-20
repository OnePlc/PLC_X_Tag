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

class TagTable extends CoreEntityTable {

    /**
     * TagTable constructor.
     *
     * @param TableGateway $tableGateway
     * @since 1.0.0
     */
    public function __construct(TableGateway $tableGateway) {
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
    public function fetchAll($bPaginated = false) {
        $oSel = new Select($this->oTableGateway->getTable());

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
     * @return mixed
     * @since 1.0.0
     */
    public function getSingle($id) {
        $id = (int) $id;
        $rowset = $this->oTableGateway->select(['Entitytag_ID' => $id]);
        $row = $rowset->current();
        if (! $row) {
            throw new \RuntimeException(sprintf(
                'Could not find tag with identifier %d',
                $id
            ));
        }

        return $row;
    }

    /**
     * Save Tag Entity
     *
     * @param Tag $oTag
     * @return int Tag ID
     * @since 1.0.0
     */
    public function saveSingle(Tag $oTag) {
        $aData = [
            'tag_value' => $oTag->tag_value,
        ];

        $aData = $this->attachDynamicFields($aData,$oTag);

        $id = (int) $oTag->id;

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
}