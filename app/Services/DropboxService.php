<?php

namespace App\Services;

use Spatie\Dropbox\Client;
use League\Flysystem\Filesystem;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Dcblogdev\Dropbox\Facades\Dropbox;

class DropboxService
{
    protected $client;
    protected $filesystem;

    public function __construct()
    {
        $accessToken = env('DROPBOX_ACCESS_TOKEN');

        if (empty($accessToken)) {
            throw new \Exception('Dropbox access token not set in .env file');
        }

        $this->client = new Client($accessToken);
        $this->filesystem = new Filesystem(new DropboxAdapter($this->client));
    }

    public function uploadFile($file)
    {
        if (!$file) {
            throw new \Exception('No file provided');
        }

        $fileName = 'cv_uploads/' . uniqid() . '.' . $file->getClientOriginalExtension();
        $stream = fopen($file->getRealPath(), 'r+');

        if (!$stream) {
            throw new \Exception('Unable to open file for reading');
        }

        $result = $this->filesystem->writeStream($fileName, $stream);
        // fclose($stream);

        if ($result === false) {
            throw new \Exception('Unable to write file to Dropbox');
        }

        $sharedLink = $this->client->createSharedLinkWithSettings($fileName, ["requested_visibility" => "public"]);

        return str_replace('dl=0', 'raw=1', $sharedLink['url']);
    }
}