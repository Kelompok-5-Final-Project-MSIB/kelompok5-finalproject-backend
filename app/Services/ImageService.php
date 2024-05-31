<?php
namespace App\Services;

class ImageService
{
    public function convertImage($product)
    {
        $imageFields = ['image', 'image2', 'image3'];

        foreach ($imageFields as $field) {
            if ($product->$field) {
                // Create a file in the public directory
                $imageContent = base64_decode($product->$field);

                // Create a hash of the image content
                $imageHash = md5($imageContent);

                // Use the hash as the file name
                $fileName = $imageHash . '.png';

                $publicPath = public_path($fileName);

                // Check if the file already exists
                if (!file_exists($publicPath)) {
                    file_put_contents($publicPath, $imageContent);
                }

                // Generate a URL for the image
                $product->$field = asset($fileName);
            }
        }

        return $product;
    }
}