<?php

namespace PEngstrom\PdfPrintLib;

use Sirius\Validation\ValueValidation;
use Sirius\Upload\Handler;

/**
 * Uploads and verifies pdf files
 */
class PdfUploadHandler extends Handler
{

    /**
     * Calls Sirius\Upload\Handler and adds extension and mime rules
     *
     * @param mixed          $directoryOrContainer Local path for uploads
     * @param array          $options              Options
     * @param ValueValidator $validator            Validator 
     */
    public function __construct($directoryOrContainer,
                                $options = array(),
                                ValueValidator $validator = null) {

        parent::__construct($directoryOrContainer,
                            $options,
                            $validator);

        $this->setSanitizerCallback(function($name) {
            return md5(uniqid(rand(), true));
        });
        /*
        $this->addRule('extension',
                       ['allowed' => 'pdf'],
                       '{label} should have the extension .pdf.',
                       'PDF file');
         */

        $this->addRule('callback',
                       ['callback' => '$this->ensurePdfMime'],
                       '{label} should be a valid PDF file.',
                       'PDF file');
    }
     
    /**
     * Ensure the mime type of the file is pdf
     *
     * @param string $file File to be checked
     *
     * @return bool True of the check succeeds
     */
    public function ensurePdfMime($file) {
        d($file);
        $result = finfo_file($file) === 'application/pdf';
        d(finfo_file($file));
        d(finfo::file($file));
    }
}

?>
