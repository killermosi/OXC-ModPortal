<?php

/*
 * Copyright © 2016-2017 OpenXcom Mod Portal Developers
 *
 * This file is part of OpenXcom Mod Portal.
 *
 * OpenXcom Mod Portal is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OpenXcom Mod Portal is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OpenXcom Mod Portal. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OxcMP\Service\Storage;

use Zend\Config\Config;
use Ramsey\Uuid\Uuid;
use Imagick;
use ImagickException;
use ZipArchive;
use OxcMP\Controller\ModFileManagementController;
use OxcMP\Entity\Mod;
use OxcMP\Entity\ModFile;
use OxcMP\Service\Storage\StorageOptions;
use OxcMP\Util\Log;
use OxcMP\Util\File as FileUtil;

/**
 * Handle file storage
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class StorageService
{
    /**
     * Extension to use for uploaded file
     * @var string
     */
    const FILE_EXT = 'file';
    
    /**
     * Extension to use for temporary chunk
     * @var string
     */
    const TEMP_EXT = 'temp';
    
    /**
     * The storage options
     * @var StorageOptions
     */
    private $storageOptions;
    
    /**
     * Application configuration
     * @var Config
     */
    private $config;
    
    /**
     * Class initialization
     * 
     * @param StorageOptions $storageOptions The storage options
     * @param Config         $config         The application configuration
     */
    function __construct(StorageOptions $storageOptions, Config $config)
    {
        Log::info('Initializing StorageService');
        
        $this->storageOptions = $storageOptions;
        $this->config         = $config;
    }
    
    /**
     * Create a new upload slot for the specified mod
     * 
     * @param Mod $mod The mod entity
     * @param type $size The file size
     * @param type $type The file type: resource, image or background
     * @param type $name The original file name
     * @return string The upload slot UUID
     * @throws Exception\UnexpectedError
     */
    public function createUploadSlot(Mod $mod, $size, $type, $name)
    {
        Log::info(
            'Creating upload slot for mod ',
            $mod->getId()->toString(),
            ' for a file size of ',
            $size,
            ', type ',
            $type,
            ' and name ',
            $name
        );
        
        $chunkSize = $this->config->storage->chunkSize * 1024 * 1024;
        $fileChunks = ceil($size/$chunkSize);
        
        $fileType = ModFileManagementController::TYPE_MAP[$type];
        
        // Sanitize the file name
        switch ($fileType) {
            case ModFile::TYPE_RESOURCE:
            case ModFile::TYPE_IMAGE;
                $ext = ($fileType == ModFile::TYPE_IMAGE) ? ModFile::IMAGE_EXTENSION : ModFile::RESOURCE_EXTENSTION;
                
                $sanitizedName = FileUtil::sanitizeFilename($name, $ext);
                
                if (is_null($sanitizedName)) {
                    $sanitizedName = $mod->getSlug() . '.' . $ext;
                }
                break;
            case ModFile::TYPE_BACKGROUND:
                $sanitizedName = ModFile::BACKGROUND_NAME;
                break;
            default:
                Log::error('Unsupported file type ', $type, ' received');
                throw new Exception\UnexpectedError('Unsupported file type received');
        }
        
        Log::debug('Built sanitized file name: ', $sanitizedName);
        
        /*
         * The upload slot consists of a single file named "<uuid>.json". The UUID is automatically generated. The file
         * contains in a JSON string the details about the uploaded file.
         */
        
        $uploadSlot = [
            'type' => $type,
            'size' => $size,
            'name' => $sanitizedName,
            'chunks' => [
                'total' => $fileChunks,
                'uploaded' => 0
            ]
        ];
        
        try {
            $uploadDir = $this->storageOptions->getTemporaryUploadStorageDirectory($mod, true);
        } catch (\Exception $exc) {
            Log::notice('Failed to create upload slot directory: ', $exc->getMessage());
            throw new Exception\UnexpectedError('Failed to create upload slot directory');
        }
        
        Log::debug('Using upload directory: ', $uploadDir);
        
        $uuid = Uuid::uuid4()->toString();
        
        $uploadSlotFile = $uploadDir . $uuid . '.json';
        
        if (false === file_put_contents($uploadSlotFile, json_encode($uploadSlot))) {
            Log::notice('Failed to create the upload slot file: ', $uploadSlotFile);
            throw new Exception\UnexpectedError('Failed to create upload slot file');
        }
        
        Log::debug('Created upload slot having the UUID ', $uuid);
        
        return $uuid;
    }
    
    /**
     * Upload a chunk to an exiting upload slot
     * 
     * @param Mod    $mod       The Mod entity
     * @param string $slotUuid  The upload slot UUID
     * @param array  $chunkData The chunk data
     * @return boolean True if the uploaded chunk si the last one for the specified slot
     * @throws Exception\UnexpectedError
     * @throws Exception\InvalidResource
     * @throws Exception\InvalidImage
     * @throws Exception\InvalidBackground
     */
    public function uploadChunk(Mod $mod, $slotUuid, array $chunkData)
    {
        Log::info('Uploading a chunk for the uplod slot having the UUID ', $slotUuid);
        
        // Get upload location
        try {
            $uploadDir = $this->storageOptions->getTemporaryUploadStorageDirectory($mod, true);
        } catch (\Exception $exc) {
            Log::notice('Failed to determine upload slot directory: ', $exc->getMessage());
            throw new Exception\UnexpectedError('Failed to create upload slot directory');
        }
        
        $uploadSlotFile = $uploadDir . $slotUuid . '.json';
        
        if (!file_exists($uploadSlotFile)) {
            Log::notice('Upload slot file not found: ', $uploadSlotFile);
            throw new Exception\UnexpectedError('Upload slot file not found');
        }
        
        // Get slot content
        $uploadSlotFileContents = file_get_contents($uploadSlotFile);
        
        if ($uploadSlotFileContents === false) {
            Log::notice('Upload slot file is not readable: ', $uploadSlotFile);
            throw new Exception\UnexpectedError('Upload slot file is not readable');
        }
        
        $slotData = json_decode($uploadSlotFileContents, true);
        
        if ($slotData === null) {
            Log::notice('Upload slot data is not in a valid JSON format');
            throw new Exception\UnexpectedError('Upload slot data is not in a valid JSON format');
        }
        
        $configChunkSize = $this->config->storage->chunkSize * 1024 * 1024;
        $currentChunkNo = $slotData['chunks']['uploaded'] + 1;
        $totalChunks = $slotData['chunks']['total'];
        $uploadedChunkSize = filesize($chunkData['tmp_name']);
        
        // Check the chunk size
        if ($currentChunkNo == $totalChunks) {
            // Last chunk
            $uploadedFileSize = $slotData['chunks']['uploaded'] * $configChunkSize + $uploadedChunkSize;
            
            if ($uploadedFileSize != $slotData['size']) {
                Log::notice('Last chunk adds over the declared file size');
                throw new Exception\UnexpectedError('Last chunk adds over the declared file size');
            }
        } else {
            // Other chunks
            if ($uploadedChunkSize != $configChunkSize) {
                Log::notice(
                    'Chunk size is different than the configured size: received a ',
                    FileUtil::formatByteSize($uploadedChunkSize),
                    ' chunk, configured chunk size is ',
                    FileUtil::formatByteSize($configChunkSize)
                );
                throw new Exception\UnexpectedError('Chunk size is different than the configured size');
            }
        }
        
        // File names
        $fileName = $slotUuid . '.' . self::FILE_EXT;
        $tempName = $slotUuid . '.' . self::TEMP_EXT;

        $filePath = $uploadDir . $fileName;
        $tempPath = $uploadDir . $tempName;
        
        // Save the file data on disk
        if (!move_uploaded_file($chunkData['tmp_name'], $tempPath)) {
            Log::notice('Failed to write temporary chunk file ', $tempPath);
            throw new Exception\UnexpectedError('Failed to write temporary chunk file');
        }
        
        // Concatenate the contents of the newly uploaded chunk with the previously uploaded ones
        $chunkContent = file_get_contents($tempPath);
        
        if ($chunkContent === false) {
            Log::notice('Failed to read temporary chunk file ', $tempPath);
            throw new Exception\UnexpectedError('Failed to read temporary chunk file');
        }
        
        if (file_put_contents($filePath, $chunkContent, FILE_APPEND) === false) {
            Log::notice('Failed to concatenate temporary chunk file ', $tempPath, ' with storage file ', $filePath);
            throw new Exception\UnexpectedError('Failed to concatenate temporary chunk file with storage file');
        }
        
        // Delete the temporary chunk
        if (unlink($tempPath) === false) {
            Log::notice('Failed to delete temporary chunk file ', $tempPath);
        }
        
        // Update the slot data
        $slotData['chunks']['uploaded'] = $currentChunkNo;
        
        if (!file_put_contents($uploadSlotFile, json_encode($slotData))) {
            Log::notice('Failed to rewrite the upload slot file: ', $uploadSlotFile);
            throw new Exception\UnexpectedError('Failed to rewrite the upload slot file');
        }
        
        Log::debug('Successfully uploaded chunk for file: ', $filePath);
        
        // Validate the uploaded file content
        if ($currentChunkNo == $totalChunks) {
            $type = ModFileManagementController::TYPE_MAP[$slotData['type']];
            
            switch ($type) {
                case ModFile::TYPE_RESOURCE:
                    $this->validateResource($filePath);
                    break;
                case ModFile::TYPE_IMAGE:
                    $this->validateImage($filePath);
                    break;
                case ModFile::TYPE_BACKGROUND:
                    $this->validateBackground($filePath);
            }
        }
        
        return ($currentChunkNo == $totalChunks);
    }
    
    /**
     * Validate that the specified file is a valid mod image
     * 
     * @param string  $imagePath Path to the image file
     * @return void
     * @throws Exception\InvalidImage
     */
    private function validateImage($imagePath)
    {
        Log::info('Validating that the file ', $imagePath, ' is a valid mod image');
        
        $image = new Imagick();
        
        try {
            $image->readimage($imagePath);
        } catch (ImagickException $exc) {
            Log::notice('Error reading image file ', $imagePath, ': ', $exc->getMessage());
            throw new Exception\InvalidImage('Could not parse image file');
        }
        
        // Just to be sure
        if (!$image->valid()) {
            Log::notice('The file ', $imagePath, 'is not a valid image file' );
            throw new Exception\InvalidImage('Invalid image file');
        }
        
        $image->clear();
        
        Log::debug('The image is a valid mod image');
    }
    
    /**
     * Validate that the specified file is a valid mod background image
     * 
     * @param string $backgroundPath Path to the background image file
     * @return void
     * @throws Exception\InvalidImage
     * @throws Exception\InvalidBackground
     */
    private function validateBackground($backgroundPath)
    {
        Log::info('Validating that the file ', $backgroundPath, ' is a valid mod background image');
        
        $image = new Imagick();
        
        try {
            $image->readimage($backgroundPath);
        } catch (ImagickException $exc) {
            Log::notice('Error reading image file ', $backgroundPath, ': ', $exc->getMessage());
            throw new Exception\InvalidImage('Could not parse background image file');
        }
        
        // Just to be sure
        if (!$image->valid()) {
            Log::notice('The file ', $backgroundPath, 'is not a valid image file' );
            throw new Exception\InvalidImage('Invalid background image file');
        }
        
        // Check dimensions
        $imageWidth = $image->getimagewidth();
        $imageHeight = $image->getimageheight();
        
        $configWidth = $this->config->storage->background->width;
        $configHeight = $this->config->storage->background->height;
        
        if ($imageWidth != $configWidth || $imageHeight != $configHeight) {
            $imageDimensions = $imageWidth . 'x' . $imageHeight . ' pixels';
            $configDimensions = $configWidth . 'x' . $configHeight . ' pixels';
            
            Log::notice('Received image of ', $imageDimensions, ', expected image of ', $configDimensions);
            throw new Exception\InvalidBackground('Wrong image dimensions');
        }
        
        $image->clear();
        
        Log::debug('The image is a valid mod background image');
    }
    
    /**
     * Validate that the specified file is a valid mod resource
     * 
     * @param string $resourcePath Path to the resource file
     * @return void
     * @throws Exception\InvalidResouce
     */
    private function validateResource($resourcePath)
    {
        Log::info('Validating that the file ', $resourcePath, ' is a valid mod resource');
        
        $zip = new ZipArchive;

        $result = $zip->open($resourcePath, ZipArchive::CHECKCONS);

        if ($result === true) {
            Log::debug('The file appears to be a valid mod resource');
            return;
        }

        // Try to log a meaningful error
        switch ($result) {
            case ZipArchive::ER_NOZIP:
                Log::notice('The file ', $resourcePath, ' does not appear to be a valid ZIP archive');
                break;

            case ZipArchive::ER_INCONS:
                Log::notice('The file ', $resourcePath, ' has inconsistency errors');
                break;

            case ZipArchive::ER_CRC:
                Log::notice('The file ', $resourcePath, ' has CRC errors');
                break;

            default:
                Log::notice('Encountered ZupArchive error "', $result, '" while opening file', $resourcePath);
        }
        
        throw new Exception\InvalidResource('The resource file is invalid');
    }
}

/* EOF */