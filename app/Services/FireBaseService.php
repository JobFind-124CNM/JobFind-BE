<?php

namespace App\Services;

use Exception;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Exception\Auth\EmailExists as FirebaseEmailExists;

class FirebaseService
{
    protected $storage;

    public function __construct()
    {
        $storageBucket = env('FIREBASE_STORAGE_BUCKET');
        $firebase = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
            ->withDatabaseUri(config('services.firebase.database_url'));

        $this->storage = $firebase->createStorage([
            'storageBucket' => $storageBucket
        ]);
    }

    public function uploadFile($file)
    {
        if (!$file) {
            throw new \Exception('No file provided');
        }

        $bucket = $this->storage->getBucket();
        $fileName = 'cv_uploads/' . uniqid() . '.' . $file->getClientOriginalExtension();
        $bucket->upload(
            file_get_contents($file->getRealPath()),
            [
                'name' => $fileName,
                'predefinedAcl' => 'publicRead'
            ]
        );

        return $bucket->object($fileName)->signedUrl(new \DateTime('9999-12-31'));
    }
}