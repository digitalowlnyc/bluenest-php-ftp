<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2018 Blue Nest Digital LLC
 */

function sshFtpGetNative($host, $username, $pass, $serverFile, $localFile = null, $port = 21) {
    $connection = ssh2_connect($host, $port);

    ssh2_auth_password($connection, $username, $pass);

    $sftp = ssh2_sftp($connection);

    $sFtpUrl = "ssh2.sftp://" . intval($sftp) . $serverFile;

    $stream = fopen($sFtpUrl, 'r');

    $outputStream = fopen($localFile, 'w');

    while($line = fgets($stream)) {
        fputs($outputStream, $line);
    }
    fclose($stream);

    printMsg("Successfully written to $localFile");
    return $localFile;
}

/**
 * @param $host
 * @param $username
 * @param $pass
 * @param $serverFile
 * @param null $localFile
 * @param int $port
 * @param int $timeout
 * @param bool $useSSL
 * @throws Exception
 */
function ftpGetFile($host, $username, $pass, $serverFile, $localFile = null, $port = 21, $timeout = 5, $useSSL = true) {
    if($localFile === null) {
        $localFile = $serverFile;
    }

    if(!$useSSL) {
        $conn_id = ftp_connect($host, $port, $timeout);
    } else {
        $conn_id = ftp_ssl_connect($host, $port, $timeout);
    }

    if($conn_id === false) {
        throw new \Exception(sprintf("Could not connect to FTP %s on port %d with username %s", $host, $port, $username));
    }

    $login_result = ftp_login($conn_id, $username, $pass);

    if(!ftp_pasv($conn_id, true)) {
        throw new \Exception("Could not enabled passive mode on FTP connection: " . $host);
    }

    if($login_result === false) {
        throw new \Exception("Problem connecting to FTP host: " . $host);
    }

    $contents = ftp_nlist($conn_id, ".");

    try {
        if(ftp_get($conn_id, $localFile, $serverFile, FTP_BINARY)) {
            printMsg("Successfully written to $localFile");
        } else {
            throw new \Exception("There was a problem getting file from FTP");
        }
    } finally {
        ftp_close($conn_id);
    }
}