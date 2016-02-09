<?php

namespace benrowe\UnzipFilter;

use \Exception;
use \ZipArchive;

class Unzip extends ZipArchive
{
    const CHMOD = 0755;

    /**
     * Extract the current zip file, only extracting the files that match at
     * least one of the supplied filters
     *
     * @param  string $destination The path destination to extract the file to
     * @param  array $filters     a list of extensions, regular expressions, or
     *                            callbacks to evaulate the filter process
     * @return boolean
     */
    public function extractToFilter($destination, array $filters = null)
    {
        if (count($filters)) {
            return $this->extractTo($destination);
        }

        $this->createDir($directory);

        // iterate throught all files within the zip
        $copySource = 'zip://'.$this->filename.'#';
        for($i = 0; $i < $this->numFiles; $i++) {
            $entry = $this->getNameIndex($i);
            $filename = basename($entry);


            if($this->matchFileToFilter($filename, $filters)) {
                $base = dirname($entry);
                $newPath = $directory.DIRECTORY_SEPARATOR.$base.DIRECTORY_SEPARATOR;
                $this->createDir($newPath);

                // extract file
                copy($copySource.$entry, $newPath.$filename);
            }
        }
    }

    /**
     * Create the requested path
     *
     * @param  string $path absolute path, or relative
     * @return null
     */
    private function createDir($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path, self::CHMOD, true)) {
                throw new Exception('unable to create path '.$path);
            }
        }
    }

    /**
     * Match the file name to one of the filters
     *
     * @param  string $filename
     * @param  array  $filters
     * @return int array index of matched filter, or false for no match
     */
    protected function matchFileToFilter($filename, array $filters)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if(in_array($ext, array_map('strtolower', $filters))) {
            // one of the filters is an extension, and it matches file extension
            return true;
        }

        foreach($filters as $i=>$filter) {
            // remove extension filters
            if(!ctype_alnum($filter[0]) && preg_match($filter, $filename)) {
                return true;
            }
        }
        return false;
    }
}
