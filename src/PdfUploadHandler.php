<?php

namespace PEngstrom\PdfPrintLib;

/**
 * Uploads and verifies pdf files
 */
class PdfUploadHandler {

    /**
     * Directory of local uploads
     */
    public $storage;

    /**
     * Constructor
     *
     * Initiates $storage
     */
    public function __construct($storage) {
        $this->storage = $storage;
    }

    /**
     * Uploads file locally
     *
     * If the files are an array, it is decomposed and
     * files are uploaded individually.
     *
     * @param array $file Same structure as $_FILES['myUpload'].
     *                    Can be both single and array.
     */
    public function upload($file) {

        if (is_array($file['name'])) {
            $files = $this->reArrayFiles($file);

            $results = [];

            foreach ($files as $singleFile) {
                $result = $this->uploadSingle($singleFile);

                $results[] = $result;
            }

            return $results;

        } else {

            return Array($this->uploadSingle($file));

        }
    }


    /**
     * Uploads file locally
     *
     * Verifies the upload and mime properties. If OK it proceeds.
     *
     * @param string $file element of $_FILES to upload
     *
     * @return Array Hashmap with 'message' for errors and
     *               'filename' for uploaded file
     */
    public function uploadSingle($file) {
        $storage = $this->storage;
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $message = '';
        $filename = '';
        $original = strip_tags($file['name']);

        // Check finfo instance
        if (!$finfo) {
            exit('Magic database could not be created!');
        }

        try {
            // Check integrity of POST object
            if (!isset($file['error']) || is_array($file['error']) ) {
                throw new \RuntimeException('Invalid parameters!');
            }

            // Handle errors
            switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('No file sent!');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException(
                    'Exceeded filesize limit! Maximum is 10MB.'
                );
            default:
                throw new \RuntimeException('Something happened.');
            }

            // Check file size
            if ($file['size'] < 200 || $file['size'] > 10000000) {
                throw new \RuntimeException('File too large. Maximum is 10MB.');
            }

             // Check file type
            if (false === $ext = array_search(
                $finfo->file($file['tmp_name']),
                array(
                    'pdf' => 'application/pdf',
                ),
                true
            )) {
                throw new \RuntimeException(
                    'Invalid file format. Only PDF files allowed.'
                );
            }

            // Try to move uploaded file
            $filename = sprintf(
                $storage . DIRECTORY_SEPARATOR . '%s.%s',
                sha1_file($file['tmp_name']),
                $ext
            );
            if (!move_uploaded_file(
                $file['tmp_name'],
                $filename
            )) {
                throw new \RuntimeException(
                    'Failed to move uploaded file. Contact webmaster.'
                );
            }

        } catch (\RuntimeException $e) {
            $message = $e->getMessage();
        }

        return ['original' => $original, 'filename' => $filename, 'message' => $message];

    }

    /**
     * Decomposes $_FILES array into separate files
     *
     * @param array $file_post Same structure as $_FILES['myUpload']
     *
     * @return array $file_ary Deconstructed array file-wise
     */
    function reArrayFiles(&$file_post) {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }
}

