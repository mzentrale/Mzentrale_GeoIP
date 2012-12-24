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
 * GeoIP Location Resource Model
 * 
 * @category    Mzentrale
 * @package     Mzentrale_GeoIP
 * @author      Francesco Marangi | mzentrale <f.marangi@mzentrale.de>
 */
class Mzentrale_GeoIP_Model_Resource_Location extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('mzgeoip/location', 'location_id');
    }

    /**
     * Get country by IP
     * 
     * Returns the ISO code of the country, or false if the IP address
     * is not valid or cannot be found.
     * 
     * @param string $ipAddress
     * @return string
     */
    public function getCountryByIP($ipAddress)
    {
        // Check IP validity
        $validate = new Zend_Validate_Ip(array('allowipv6' => false));
        if (!$validate->isValid($ipAddress)) {
            return false;
        }

        $adapter = $this->getReadConnection();
        $numValue = ip2long($ipAddress);
        $query = $adapter->select()
                ->from($this->getMainTable(), array('range_to', 'country_id'))
                ->where('range_from <= ?', $numValue)
                ->order('range_from ' . Zend_Db_Select::SQL_DESC)
                ->limit(1);
        $row = $adapter->fetchRow($query);
        if ($row && $row['range_to'] >= $numValue) {
            return $row['country_id'];
        }

        return false;
    }

    /**
     * Import file into lookup table
     * 
     * @param string $fileName File location
     * @param int $rows number of rows per INSERT
     * @param boolean $truncate Truncate table?
     * @return Mzentrale_GeoIP_Model_Resource_Location
     */
    public function importFile($fileName, $rows = 500, $truncate = true)
    {
        $adapter = $this->_getWriteAdapter();

        if ($truncate) {
            $adapter->truncateTable($this->getMainTable());
        }

        $file = new Varien_Io_File();
        $file->streamOpen($fileName, 'r');

        $counter = 0;
        $data = array();

        while ($row = $file->streamReadCsv()) {
            if (!isset($row[2]) || !isset($row[3]) || !isset($row[4])) {
                continue;
            }

            $data[] = array($row[2], $row[3], $row[4]);
            $counter += 1;

            if ($rows <= $counter) {
                $adapter->insertArray($this->getMainTable(), array('range_from', 'range_to', 'country_id'), $data);
                $counter = 0;
                $data = array();
            }
        }

        if (!empty($data)) {
            $adapter->insertArray($this->getMainTable(), array('range_from', 'range_to', 'country_id'), $data);
        }

        return $this;
    }
}
