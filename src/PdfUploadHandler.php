<?php

namespace PEngstrom\PdfPrintLib;

require '../vendor/autoload.php';

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

        Parent::Constructor($directoryOrContainer,
                            $options,
                            $validator);

        $this->addRule('extension',
                       ['allowed' => ['pdf']],
                       '{label} should be a valid pdf file.',
                       'PDF file');

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
        return finfo_file($file) === 'application/pdf';
    }
}

?>
