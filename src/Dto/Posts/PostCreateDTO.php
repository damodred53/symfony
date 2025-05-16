<?php

namespace App\Dto\Posts;

use Symfony\Component\Validator\Constraints as Assert;

final class PostCreateDTO
{
    #[Assert\NotBlank(message: "Le contenu ne peut pas être vide.")]
    #[Assert\Length(min: 3, max: 500, minMessage: "3 caractères minimum.", maxMessage: "500 caractères maximum.")]
    public string $content = '';

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->content = $data['content'] ?? '';

        return $dto;
    }
}
