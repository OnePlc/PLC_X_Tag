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

class Tag extends CoreEntityModel {
    public $tag_key;
    public $tag_label;

    /**
     * Tag constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @since 1.0.0
     */
    public function __construct($oDbAdapter) {
        parent::__construct($oDbAdapter);

        # Set Single Form Name
        $this->sSingleForm = 'tag-single';

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
        $this->id = !empty($aData['Tag_ID']) ? $aData['Tag_ID'] : 0;
        $this->tag_label = !empty($aData['tag_label']) ? $aData['tag_label'] : '';
        $this->tag_key = !empty($aData['tag_key']) ? $aData['tag_key'] : '';

        $this->updateDynamicFields($aData);
    }

    /**
     * Get Tag Label as String
     *
     * @return string
     * @since 1.0.0
     */
    public function getLabel() {
        return $this->tag_label;
    }

    /**
     * Get Tag Key as String
     *
     * @return string
     * @since 1.0.1
     */
    public function getKey() {
        return $this->tag_key;
    }
}