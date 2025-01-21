<?php

namespace PersonalizeAI\FotoAnalyzeTool\Model;

/**
 * Class TagProcessor
 *
 * This class processes tag data from requests.
 */
class TagProcessor
{
    /**
     * Collect and process tag data from an array of tags.
     *
     * @param array $tags The array of tags to process.
     * @return array Processed tag data including timestamp.
     */
    public function processTags(array $tags)
    {
        // Initialize an array to hold processed tags
        $processedTags = [];

        foreach ($tags as $tag) {
            // Add each tag's details to the processed tags array
            $processedTags[] = [
                'name' => isset($tag['name']) ? (string)$tag['name'] : null,
                'value' => isset($tag['value']) ? (string)$tag['value'] : null,
                'confidence' => isset($tag['confidence']) ? (float)$tag['confidence'] : null,
            ];
        }

        return [
            'tags' => $processedTags,
            'timestamp' => time(),
        ];
    }
}
