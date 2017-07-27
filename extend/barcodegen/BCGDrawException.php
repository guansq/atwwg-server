<?php
/**
 *--------------------------------------------------------------------
 *
 * Draw Exception
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
namespace  barcodegen;
use Exception;

class BCGDrawException extends Exception {
    /**
     * Constructor with specific message.
     *
     * @param string $message
     */
    public function __construct($message) {
        parent::__construct($message, 30000);
    }
}
?>