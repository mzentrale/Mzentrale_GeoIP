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
 * GeoIP Update Model
 * 
 * @category    Mzentrale
 * @package     Mzentrale_GeoIP
 * @author      Francesco Marangi | mzentrale
 */
class Mzentrale_GeoIP_Model_Update
{

    /**
     * Update GeoIP database
     * 
     * @return Mzentrale_GeoIP_Model_Update
     */
    public function updateDatabase()
    {
        // Download and extract database file
        $fileName = $this->_downloadFile(Mage::helper('mzgeoip')->getDatabaseUrl(), $this->getUpdateDir());
        $this->_extractFile($fileName);

        // Import records into lookup table
        $dbFile = $this->_getDatabaseFile($this->getUpdateDir());
        Mage::getResourceModel('mzgeoip/location')->importFile($dbFile);

        // Cleanup!
        $helper = new Varien_Io_File();
        $helper->rmdir($this->getUpdateDir(), true);

        return $this;
    }

    /**
     * Download database from a given URL
     * 
     * @param string $url
     * @param string $destination
     * @return Mzentrale_GeoIP_Model_Update
     */
    protected function _downloadFile($url, $destination)
    {
        $contents = @file_get_contents($url);
        if (false === $contents) {
            Mage::throwException('Unable to read from ' . $url);
        }

        $file = new Varien_Io_File();
        $file->cd($destination);
        $file->streamOpen(basename($url), 'w+');
        $file->streamWrite($contents);
        $file->streamClose();

        return $destination . DS . basename($url);
    }

    protected function _extractFile($fileName)
    {
        $zip = new Zend_Filter_Compress_Zip();
        $zip->setTarget(dirname($fileName));
        $zip->decompress($fileName);
        return $this;
    }

    /**
     * Get database file location
     * 
     * The full path of the first found CSV file is returned. A low-level
     * implementation using SPL DirectoryIterator was chosen over
     * Varien_Directory_Collection because the latter causes strict notices
     * in php > 5.3.
     * 
     * @param string $path
     * @return string
     * @throws UnexpectedValueException If the specified path is not a valid directory
     * @throws Mage_Core_Exception If no valid database file is found in the specified location
     */
    protected function _getDatabaseFile($path)
    {
        $dir = new DirectoryIterator($path);
        foreach ($dir as $file) {
            if ($file->isFile() && $file->isReadable() && 'csv' == $file->getExtension()) {
                return $file->getPathname();
            }
        }
        Mage::throwException('Database file not found under ' . $path);
    }

    /**
     * Get update dir, used as target for downloaded database
     * 
     * @return boolean|string
     */
    public function getUpdateDir()
    {
        $dir = Mage::getBaseDir('var') . DS . 'geoip';
        if (!Mage::getConfig()->createDirIfNotExists($dir)) {
            return false;
        }
        return $dir;
    }
}
