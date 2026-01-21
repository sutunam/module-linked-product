<?php
/**
 * @author    Sutunam
 * @copyright Copyright (c) 2024 Sutunam (http://www.sutunam.com/)
 */

declare(strict_types=1);

namespace Sutunam\LinkedProduct\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Attributes extends AbstractFieldArray
{
    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('attribute_code', ['label' => __('Attribute code'), 'class' => 'required-entry']);
        $this->addColumn('singular', ['label' => __('Singular'), 'class' => 'required-entry']);
        $this->addColumn('plural', ['label' => __('Plural'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add')->render();
    }
}
