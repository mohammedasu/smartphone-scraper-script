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

    public function __construct(array $data)
    {
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

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
