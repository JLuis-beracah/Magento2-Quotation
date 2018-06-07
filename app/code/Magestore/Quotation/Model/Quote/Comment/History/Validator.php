<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote\Comment\History;

use Magestore\Quotation\Model\Quote\Comment\History;

/**
 * Class Validator
 * @package Magestore\Quotation\Model\Quote\Comment\History
 */
class Validator
{
    /**
     * @var array
     */
    protected $requiredFields = ['parent_id' => 'Quote Id'];

    /**
     * @param History $history
     * @return array
     */
    public function validate(History $history)
    {
        $warnings = [];
        foreach ($this->requiredFields as $code => $label) {
            if (!$history->hasData($code)) {
                $warnings[] = sprintf('%s is a required field', $label);
            }
        }
        return $warnings;
    }
}
