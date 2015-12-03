<?php

namespace PEngstrom\PdfPrintLib;

use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;

/**
 * PrintSSH
 *
 * Uploads and prints files via SSH
 */
class PrintSSH
{
    /**
     * SSH client
     *
     * @var Net_SSH2
     */
    protected $ssh;

    /*
     * SFTP client
     *
     * @var Net_SFTP
     */
    protected $sftp;

    /**
     * Initiates the ssh and sftp clients
     *
     * @param string $server   Server adress e.g. 'www.example.com'
     * @param string $username User name
     * @param string $keyfile  Location of private rsa key
     */
    public function __construct($server,
                                $username,
                                $password) {

        $ssh = new SSH2($server);
        $sftp = new SFTP($server);

        if (!$ssh->login($username, $password)) {
            exit('Access ssh denied');
        }

        if (!$sftp->login($username, $password)) {
            exit('Access sftp denied');
        }

        $this->ssh = $ssh;
        $this->sftp = $sftp;

    }
    
    /**
     * Upload File
     *
     * Uploads file with the SFTp client
     *
     * @param string $file File to be uploaded
     *
     * @return void
     */
    public function uploadFile($file) {
        $remoteFile = basename($file);

        $localData = file_get_contents($file);

        $this->sftp->put($remoteFile, $localData, NET_SFTP_LOCAL_FILE);
        $this->sftp->chmod(0600, $remoteFile);

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
        $this->sftp->delete($remoteFile);
    }

    /**
     * Print file
     *
     * Prints file on server with options
     *
     * @param string $file    File on server
     * @param array  $options List of printer options
     *
     * @return void
     */
    public function printFile($file, $options = null, $live = false) {

        $remoteFile = $this->uploadFile($file);

        $printCommand = 'lpr -P pr2402';
        $command = $printCommand . ' ' . $remoteFile;

        if ($live) {
            $this->ssh->exec($command);
        }
        
        $this->deleteFile($remoteFile);
    }
}

?>
