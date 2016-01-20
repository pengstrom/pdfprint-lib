<?php

namespace PEngstrom\PdfPrintLib;


/**
 * PrintSSH
 *
 * Uploads and prints files via SSH
 */
class PrintSSH
{
    /**
     * SSH session
     *
     * @var resource
     */
    protected $connection;

    /**
     * SFTP session
     *
     * @var resource
     */
    protected $sftp;


    /**
     * Initiates the ssh session
     *
     * @param string $server   Server adress e.g. 'www.example.com'
     * @param string $username SSH Username
     * @param string $password SSH Password
     */
    public function __construct($server, $username, $password) {

        if (!function_exists('ssh2_connect')) {
            throw new \RuntimeException("Function ssh2_connect not found");
        }

        if (!$con = ssh2_connect($server)) {
            throw new \RuntimeException("Could not connect to $server");
        }

        if (!ssh2_auth_password($con, $username, $password)) {
            throw new \RuntimeException("Could not log in to $server with as $username");
        }

        if (!$sftp = ssh2_sftp($con)) {
            throw new \RuntimeException("Could not initialize SFTP connection to $server");
        }
        
        $this->connection = $con;
        $this->sftp = $sftp;

    }
    

    /**
     * Upload File
     *
     * Uploads file with SCP
     *
     * @param string $localFile File to be uploaded
     *
     * @return void
     */
    public function uploadFile($localFile) {

        $sftp = $this->sftp;

        $localFile = realpath($localFile);

        $localData = file_get_contents($localFile);

        if ($localData === False) {
            throw new \RuntimeException("Could not read local file $localFile");
        }

        $remoteHome = ssh2_sftp_realpath($sftp, '.');

        if ($remoteHome === false) {
            throw new \RuntimeException("Could not establish remote home directory");
        }

        $remoteFile = "$remoteHome/".basename($localFile);
        $stream = fopen("ssh2.sftp://$sftp$remoteFile", 'w');

        if (!$stream) {
            throw new \RuntimeException("Could not open stream to $remoteFile");
        }

        if (fwrite($stream, $localData) === false) {
            throw new \RuntimeException("Could not write to remote file $remoteFile");
        }

        fclose($stream);

        return $remoteFile;
    }


    /**
     * Deletes remote file
     *
     * Deletes file on remote server
     *
     * @param string $remoteFile Filename on server
     *
     * @return void
     */
    public function deleteFile($remoteFile) {
        ssh2_sftp_unlink($this->sftp, $remoteFile);
    }

    /**
     * Print file
     *
     * Prints file on server with options
     *
     * @param string $file        File on server
     * @param string $printerName Name of printer
     * @param array  $options     List of printer options
     * @param int    $copies      Number of copies to print
     * @param bool   $live        If false, will not print
     *
     * @return void
     */
    public function printFile(
        $localFile,
        $printerName,
        $options = [],
        $copies = 1,
        $live = false) {

        $remoteFile = $this->uploadFile($localFile);

        $printCommand = "lpr -P $printerName -# $copies";

        $optionString = '';
        foreach ($options as $optionsName => $value) {
            $option = sprintf(' -o %s=%s', $optionsName, $value);
            $optionString = $optionString . $option;
        }

        $command = sprintf('%s %s "%s"', $printCommand, $optionString, $remoteFile);

        if ($live) {
            if(ssh2_exec($this->connection, $command) === false) {
                throw new \RuntimeException("Could not execute command $command");
            }
        }

        
        $this->deleteFile($remoteFile);
    }

}

?>
