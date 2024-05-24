<?php
namespace App\Services;

class ImageService
{
    public function convertImage($product)
    {
        // Create a file in the public directory
        $imageContent = base64_decode($product->image);

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
        $product->image = asset($fileName);

        return $product;
    }
}