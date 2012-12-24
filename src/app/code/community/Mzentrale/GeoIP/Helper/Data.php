<?php

/**
 * The MIT License
 * 
 * Copyright (c) 2012 mzentrale | eCommerce - eBusiness
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * GeoIP Helper Class
 * 
 * @category    Mzentrale
 * @package     Mzentrale_GeoIP
 * @author      Francesco Marangi | mzentrale
 */
class Mzentrale_GeoIP_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get country by IP
     * 
     * Returns the ISO code of the country, or false if the IP address
     * is not valid or cannot be found.
     * 
     * @param string $ipAddress
     * @return string
     */
    public function getCountry($ipAddress = null)
    {
        if (!$ipAddress) {
            $ipAddress = Mage::helper('core/http')->getRemoteAddr();
        }
        return Mage::getModel('mzgeoip/location')->getCountryByIP($ipAddress);
    }

    /**
     * Get country name by IP
     * 
     * @param string $ipAddress
     * @param string $locale
     * @return string
     */
    public function getCountryName($ipAddress = null, $locale = null)
    {
        $country = $this->getCountry($ipAddress);
        if ($country) {
            if (!$locale) {
                $locale = Mage::app()->getLocale()->getLocale();
            }
            return Mage::app()->getLocale()->getLocale()->getTranslation($country, 'country', $locale);
        }
        return false;
    }
}
