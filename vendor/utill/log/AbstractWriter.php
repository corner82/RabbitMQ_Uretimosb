<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/sanalfabrika for the canonical source repository
 * @copyright Copyright (c) 2016 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\Log;

/**
 * abstract class for log writer
 * @author Mustafa Zeynel Dağlı
 * @since 0.2 11/03/2016
 */
abstract  class AbstractWriter {
    
    /**
     * abstract function for log writer classes,
     * will be overriden
     */
    abstract public function write($params = null);
}
