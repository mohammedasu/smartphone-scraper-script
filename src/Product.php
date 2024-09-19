<?php

namespace App;

class Product
{
    private string $title;
    private float $price;
    private string $imageUrl;
    private int $capacityMB;
    private string $colour;
    private string $availabilityText;
    private bool $isAvailable;
    private string $shippingText;
    private ?string $shippingDate;

    /**
     * Constructor to initialize product properties.
     *
     * @param string $title
     * @param float $price
     * @param string $imageUrl
     * @param int $capacityMB
     * @param string $colour
     * @param string $availabilityText
     * @param bool $isAvailable
     * @param string $shippingText
     * @param ?string $shippingDate
     */
    public function __construct(array $data) {
        $this->title = $data['title'];
        $this->price = $data['price'];
        $this->imageUrl = $data['imageUrl'];
        $this->capacityMB = $data['capacityMB'];
        $this->colour = $data['colour'];
        $this->availabilityText = $data['availabilityText'];
        $this->isAvailable = $data['isAvailable'];
        $this->shippingText = $data['shippingText'];
        $this->shippingDate = $data['shippingDate'];
    }

    /**
     * Converts the product properties to an associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'price' => $this->price,
            'imageUrl' => $this->imageUrl,
            'capacityMB' => $this->capacityMB,
            'colour' => $this->colour,
            'availabilityText' => $this->availabilityText,
            'isAvailable' => $this->isAvailable,
            'shippingText' => $this->shippingText,
            'shippingDate' => $this->shippingDate,
        ];
    }
}
