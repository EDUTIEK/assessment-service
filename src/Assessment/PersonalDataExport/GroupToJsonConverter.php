<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes\Attribute;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes\File as FileAttribute;
use Edutiek\AssessmentService\System\Data\FileInfo;

/**
 * Converts a Group object to a JSON string.
 * 
 * One group is converted to one JSON object where:
 * - Attributes are JSON properties with the title as the property name
 * - Sub groups are JSON objects as properties with their title as the property name
 * 
 * File attributes are handled specially:
 * - The mimetype of the file ID is checked
 * - The filename is constructed as id+extension
 * - All file IDs and their filenames are stored for later use
 */
class GroupToJsonConverter
{
    /**
     * @var FileInfo[]
     */
    private array $file_info_cache = [];

    /**
     * @var array<string, string> Array mapping file IDs to filenames
     */
    private array $file_id_to_filename = [];

    /**
     * @param \Closure(string):?FileInfo $file_info_provider Function that takes a file ID and returns FileInfo or null
     */
    public function __construct(
        private \Closure $file_info_provider
    ) {
    }

    /**
     * Converts a Group to a JSON string.
     * 
     * @param Group $group The group to convert
     * @return string JSON representation of the group
     */
    public function convert(Group $group): string
    {
        $this->file_id_to_filename = [];
        $this->file_info_cache = [];
        
        $data = $this->processGroup($group);
        
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Returns the list of file IDs and their filenames.
     * 
     * @return array<string, string> Array mapping file IDs to filenames
     */
    public function getFileIdToFilenameMap(): array
    {
        return $this->file_id_to_filename;
    }

    /**
     * Processes a Group recursively into an array structure.
     * 
     * @param Group $group
     * @return array<string, mixed>
     */
    private function processGroup(Group $group): array
    {
        $result = [];
        
        // Process attributes
        foreach ($group->getAttributes() as $attribute) {
            $result[$attribute->getTitle()] = $this->processAttribute($attribute);
        }
        
        // Process sub groups
        foreach ($group->getSubGroups() as $sub_group) {
            $result[$sub_group->getTitle()] = $this->processGroup($sub_group);
        }
        
        return $result;
    }

    /**
     * Processes an Attribute into its JSON value.
     * 
     * @param Attribute $attribute
     * @return mixed
     */
    private function processAttribute(Attribute $attribute): mixed
    {
        $value = $attribute->getValue();
        
        // Special handling for File attributes
        if ($attribute instanceof FileAttribute) {
            return $this->processFileAttribute($attribute);
        }
        
        return $value;
    }

    /**
     * Processes a File attribute.
     * 
     * @param FileAttribute $attribute
     * @return string The filename (id + extension)
     */
    private function processFileAttribute(FileAttribute $attribute): string
    {
        $file_id = $attribute->getValue();
        
        // If we've already processed this file, return the cached filename
        if (isset($this->file_id_to_filename[$file_id])) {
            return $this->file_id_to_filename[$file_id];
        }
        
        // Get file info
        $file_info = $this->getFileInfo($file_id);
        
        // Determine extension from mimetype
        $extension = $this->getExtensionFromMimeType($file_info?->getMimeType());
        
        // Create filename
        $filename = $file_id . $extension;
        
        // Store the mapping
        $this->file_id_to_filename[$file_id] = $filename;
        
        return $filename;
    }

    /**
     * Gets FileInfo for a given file ID, with caching.
     * 
     * @param string $file_id
     * @return FileInfo|null
     */
    private function getFileInfo(string $file_id): ?FileInfo
    {
        if (isset($this->file_info_cache[$file_id])) {
            return $this->file_info_cache[$file_id];
        }
        
        $file_info = ($this->file_info_provider)($file_id);
        $this->file_info_cache[$file_id] = $file_info;
        
        return $file_info;
    }

    /**
     * Gets the file extension from a mimetype.
     * 
     * @param string|null $mimetype
     * @return string
     */
    private function getExtensionFromMimeType(?string $mimetype): string
    {
        if ($mimetype === null) {
            return '';
        }
        
        // Common mimetype to extension mappings
        $mime_to_extension = [
            'application/pdf' => '.pdf',
            'text/plain' => '.txt',
            'text/csv' => '.csv',
            'application/msword' => '.doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',
            'application/vnd.ms-excel' => '.xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx',
            'application/vnd.ms-powerpoint' => '.ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'application/zip' => '.zip',
            'application/json' => '.json',
            'application/xml' => '.xml',
            'text/html' => '.html',
            'text/css' => '.css',
            'application/javascript' => '.js',
        ];
        
        return $mime_to_extension[$mimetype] ?? '';
    }
}
