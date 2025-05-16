<?php

namespace App\Dto\Posts;

use Symfony\Component\Validator\Constraints as Assert;

final class PostUpdateDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 500)]
    public ?string $content = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->content = $data['content'] ?? null;
        return $dto;
    }
}
