<?php

namespace App\Dto\Posts;

use App\Entity\Post;

final class PostDTO
{
    public int $id;
    public string $content;
    public string $createdAt;
    public ?string $author;

    public function __construct(Post $post)
    {
        $this->id = $post->getId();
        $this->content = $post->getContent();
        $this->createdAt = $post->getCreatedAt()?->format('Y-m-d H:i:s');
        $this->author = $post->getAuthor()?->getUsername();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'createdAt' => $this->createdAt,
            'author' => $this->author,
        ];
    }
}
