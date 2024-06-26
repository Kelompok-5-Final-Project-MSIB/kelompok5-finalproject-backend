<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name_product' => $this->faker->name,
            'desc' => $this->faker->sentence,
            'brand' => $this->faker->randomElement(['adidas', 'nike', 'puma', 'reebok', 'vans', 'converse', 'fila', 'new balance', 'asics', 'skechers']),
            'image' => $this->getImageBase64(),
            'size' => $this->faker->numberBetween(35, 45),
            'price' => $this->faker->numberBetween(100000, 500000),
            'discount' => $this->faker->numberBetween(0, 50),
            'status' => $this->faker->randomElement(['available', 'sold out']),
        ];
    }

    /**
     * Get image in base64 format from the specified file.
     *
     * @param string $filename
     * @return string|null
     */
    private function getImageBase64()
    {
        $imageDir = public_path('image/seeder');
        $files = File::files($imageDir);

        if (!empty($files)) {
            $randomFile = $this->faker->randomElement($files);
            $image = file_get_contents($randomFile->getPathname());
            return base64_encode($image);
        }

        return null;
    }
}
