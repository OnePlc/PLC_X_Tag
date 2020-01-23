<?php
/**
 * Tag.php - Tag Entity
 *
 * Entity Model for Tag
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

use Application\Model\CoreEntityModel;

class EntityTag extends CoreEntityModel {
    /**
     * Tag Value (label)
     *
     * @var string $tag_value Value
     * @since 1.0.1
     */
    public $tag_value;

    /**
     * Core Tag
     *
     * @var int $tag_idfs linked core_tag
     * @since 1.0.1
     */
    public $tag_idfs;

    /**
     * Parent Tag ID
     *
     * @var int $parent_tag_idfs
     * @since 1.0.1
     */
    public $parent_tag_idfs;

    /**
     * Form of Entity
     *
     * @var string name of entity form
     * @since 1.0.1
     */
    public $entity_form_idfs;


    /**
     * Tag constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @since 1.0.0
     */
    public function __construct($oDbAdapter) {
        parent::__construct($oDbAdapter);

        # Set Single Form Name
        $this->sSingleForm = 'entitytag-single';

        # Attach Dynamic Fields to Entity Model
        $this->attachDynamicFields();
    }

    /**
     * Set Entity Data based on Data given
     *
     * @param array $aData
     * @since 1.0.0
     */
    public function exchangeArray(array $aData) {
        $this->id = !empty($aData['Entitytag_ID']) ? $aData['Entitytag_ID'] : 0;
        $this->tag_value = !empty($aData['tag_value']) ? $aData['tag_value'] : '';
        $this->tag_idfs = !empty($aData['tag_idfs']) ? $aData['tag_idfs'] : 0;
        $this->parent_tag_idfs = !empty($aData['parent_tag_idfs']) ? $aData['parent_tag_idfs'] : 0;
        $this->entity_form_idfs = !empty($aData['entity_form_idfs']) ? $aData['entity_form_idfs'] : '';

        $this->updateDynamicFields($aData);
    }

    /**
     * Get Tag Label as String
     *
     * @return string
     * @since 1.0.0
     */
    public function getLabel() {
        return $this->tag_value;
    }
}