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

use InvalidArgumentException;
use Zend\Config\Config;
use Imagick;
use ImagickException;
use ZipArchive;
use OxcMP\Entity\Mod;
use OxcMP\Entity\ModFile;
use OxcMP\Service\Storage\StorageOptions;
use OxcMP\Service\Storage\SupportCode\UploadSlotData;
use OxcMP\Util\Log;
use OxcMP\Util\File as FileUtil;

/**
 * Handle file storage
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * TODO: Lock the slot files while processing (REDIS? flock()?) 
 */
class StorageService
{
    /**
     * Type map for mod files
     */
    const TYPE_MAP = [
        'resource'   => ModFile::TYPE_RESOURCE,
        'image'      => ModFile::TYPE_IMAGE,
        'background' => ModFile::TYPE_BACKGROUND,
    ];
    
    /**
     * Extension to use for the slot metadata
     * @var string
     */
    const SLOT_EXT = 'slot';
    
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
     * @throws Exception\UploadConfigurationError
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
        
        $this->checkUploadParameters();
        
        $chunkSize = $this->config->upload->chunkSize * 1024 * 1024;
        $fileChunks = ceil($size/$chunkSize);
        
        $fileType = self::TYPE_MAP[$type];
        
        // Sanitize the file name
        switch ($fileType) {
            case ModFile::TYPE_RESOURCE:
            case ModFile::TYPE_IMAGE;
                $ext = ($fileType == ModFile::TYPE_IMAGE) ? ModFile::EXTENSION_IMAGE : ModFile::EXTENSION_RESOURCE;
                
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

        try {
            $uploadSlotData = new UploadSlotData($fileType, $size, $sanitizedName, $fileChunks);
        } catch (InvalidArgumentException $exc) {
            Log::error('Error creating upload slot data object: ', $exc->getMessage());
            throw new Exception\UnexpectedError('Failed to create upload slot data object');
        }
        
        try {
            $uploadDir = $this->storageOptions->getTemporaryUploadStorageDirectory($mod, true);
        } catch (\Exception $exc) {
            Log::notice('Failed to create upload slot directory: ', $exc->getMessage());
            throw new Exception\UnexpectedError('Failed to create upload slot directory');
        }
        
        Log::debug('Using upload directory: ', $uploadDir);
        
        
        $uploadSlotFile = $uploadDir . $uploadSlotData->getUuid() . '.' . self::SLOT_EXT;
        
        if (false === file_put_contents($uploadSlotFile, serialize($uploadSlotData))) {
            Log::notice('Failed to create the upload slot file: ', $uploadSlotFile);
            throw new Exception\UnexpectedError('Failed to create upload slot file');
        }
        
        Log::debug('Created upload slot having the UUID ', $uploadSlotData->getUuid());
        
        return $uploadSlotData->getUuid();
    }
    
    /**
     * Upload a chunk to an exiting upload slot
     * 
     * @param Mod    $mod       The Mod entity
     * @param string $slotUuid  The upload slot UUID
     * @param array  $chunkData The chunk data
     * @return UploadSlotData The upload slot data
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
        
        $uploadSlotFile = $uploadDir . $slotUuid . '.' . self::SLOT_EXT;
        
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
        
        $uploadSlotData = unserialize($uploadSlotFileContents);
        
        if (!$uploadSlotData instanceof UploadSlotData) {
            Log::notice('Upload slot data is not in a valid serialized object');
            throw new Exception\UnexpectedError('Upload slot data is not in a valid serialized object');
        }
        
        if ($uploadSlotData->isFileUploadCompleted()) {
            Log::notice('The file was already uploaded');
            throw new Exception\UnexpectedError('The file was already uploaded');
        }
        
        $configChunkSize = $this->config->upload->chunkSize * 1024 * 1024;
        $uploadedChunkSize = filesize($chunkData['tmp_name']);
        
        // Check the chunk size
        if ($uploadSlotData->getChunksUploaded() + 1 == $uploadSlotData->getChunksTotal()) {
            // Last chunk
            $uploadedFileSize = $uploadSlotData->getChunksUploaded() * $configChunkSize + $uploadedChunkSize;
            
            if ($uploadedFileSize != $uploadSlotData->getSize()) {
                Log::notice('Last chunk size mismatched');
                throw new Exception\UnexpectedError('Last chunk size mismatched');
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
        $uploadSlotData->incrementChunksUploaded();
        
        if (!file_put_contents($uploadSlotFile, serialize($uploadSlotData))) {
            Log::notice('Failed to rewrite the upload slot file: ', $uploadSlotFile);
            throw new Exception\UnexpectedError('Failed to rewrite the upload slot file');
        }
        
        Log::debug('Successfully uploaded chunk for file: ', $filePath);
        
        // This is not the last chunk, no validation needed
        if ($uploadSlotData->isFileUploadCompleted() == false) {
            return $uploadSlotData;
        }
        
        // Validate the uploaded file content
        try {
            switch ($uploadSlotData->getType()) {
                case ModFile::TYPE_RESOURCE:
                    $this->validateResource($filePath);
                    break;
                case ModFile::TYPE_IMAGE:
                    $this->validateImage($filePath);
                    break;
                case ModFile::TYPE_BACKGROUND:
                    $this->validateBackground($filePath);
            }
        } catch (\Exception $exc) {
            Log::debug('File validation failed, removing temporary uploaded file');
            
            if (!@unlink($uploadSlotFile)) {
                Log::notice('Failed to delete temporary upload slot file ', $uploadSlotFile);
            }
            if (!@unlink($filePath)) {
                Log::notice('Failed to delete temporary uploaded file ', $filePath);
            }
            
            // Throw the error further
            throw $exc;
        }

        return $uploadSlotData;
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
                Log::notice('Encountered ZipArchive error "', $result, '" while opening file', $resourcePath);
        }
        
        throw new Exception\InvalidResource('The resource file is invalid');
    }
    
    /**
     * Retrieve the content of a temporary file
     * 
     * @param Mod     $mod      The Mod entity
     * @param string  $slotUuid The slot UUID
     * @param integer $type     The file type
     * @return string Path to the temporary uploaded file
     * @throws Exception\UnexpectedError
     */
    public function getTemporaryFilePath(Mod $mod, $slotUuid, $type)
    {
        Log::info(
            'Retrieving temporary file ',
            $slotUuid,
            ' of type "',
            $type,
            '", belonging to the MOD ',
            $mod->getId()->toString()
        );
        
        // Get upload location
        try {
            $uploadDir = $this->storageOptions->getTemporaryUploadStorageDirectory($mod, true);
        } catch (\Exception $exc) {
            Log::notice('Failed to determine upload slot directory: ', $exc->getMessage());
            throw new Exception\UnexpectedError('Failed to create upload slot directory');
        }
        
        $uploadSlotFile = $uploadDir . $slotUuid . '.' . self::SLOT_EXT;
        
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
        
        $uploadSlotData = unserialize($uploadSlotFileContents);
        
        if (!$uploadSlotData instanceof UploadSlotData) {
            Log::notice('Upload slot data is not valid serialized object');
            throw new Exception\UnexpectedError('Upload slot data is not valid serialized object');
        }
        
        // Check that all the file chunks were uploaded
        if ($uploadSlotData->isFileUploadCompleted() == false) {
            Log::notice('File is only partailly uploaded');
            throw new Exception\UnexpectedError('File is only partailly uploaded');
        }
        
        // Make sure the type matches
        if ($uploadSlotData->getType() !== $type) {
            Log::notice('Wrong file type, expected ', $type, ' got ', $uploadSlotData->getType());
            throw new Exception\UnexpectedError('Wrong file type');
        }
        
        $temporaryFilePath = $uploadDir . $slotUuid . '.' . self::FILE_EXT;
        
        if (false == file_exists($temporaryFilePath) || false == is_readable($temporaryFilePath)) {
            Log::notice('Temporary uploaded file is missing or it could not be read: ', $temporaryFilePath);
            throw new Exception\UnexpectedError('Missing/inaccessible uploaded file');
        }
        
        Log::debug('Temporary uploaded file location: ', $temporaryFilePath);
        
        return $temporaryFilePath;
    }
    
    /**
     * Check the PHP upload configuration
     * 
     * @throws Exception\UploadConfigurationError
     */
    private function checkUploadParameters()
    {
        $uploadChunkSize = $this->config->upload->chunkSize * 1024 * 1024;
        $safetyMargin = $this->config->upload->safetyMargin * 1024 * 1024;
        
        $iniUploadMaxFilesize = FileUtil::convertPhpIniShorthandValue(ini_get('upload_max_filesize'));
        $iniPostMaxSize       = FileUtil::convertPhpIniShorthandValue(ini_get('post_max_size'));
        
        if ($uploadChunkSize > $iniUploadMaxFilesize) {
            Log::critical('The upload chunk size is greater than the "upload_max_filesize" PHP setting');
            throw new Exception\UploadConfigurationError(
                'The upload chunk size is greater than the "upload_max_filesize" PHP setting'
            );
        }
        
        if ($uploadChunkSize + $safetyMargin > $iniPostMaxSize) {
            Log::critical(
                'The upload chunk size is too large considering the safety margin for the "post_max_size" php setting'
            );
            throw new Exception\UploadConfigurationError(
                'The upload chunk size is too large considering the safety margin for the "post_max_size" php setting'
            );
        }
        
        // If we're here...
        if ($iniUploadMaxFilesize + $safetyMargin > $iniPostMaxSize) {
            Log::critical(
                'The "upload_max_filesize" PHP setting is too large considering ',
                'the safety margin for the "post_max_size" php setting'
            );
            throw new Exception\UploadConfigurationError(
                'The "upload_max_filesize" PHP setting is too large considering'
                . ' the safety margin for the "post_max_size" php setting'
            );
        }
    }
}

/* EOF */