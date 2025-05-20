<?php

namespace App\Dto\Like;

use App\Entity\Like;

final class LikeDTO
{
    public int $id;
    public string $author;
    public int $postId;
    public string $createdAt;

    public function __construct(Like $like)
    {
        $this->id = $like->getId();
        $this->author = $like->getAuthor()?->getUsername() ?? 'Unknown';
        $this->postId = $like->getPost()?->getId();
        $this->createdAt = $like->getCreatedAt()?->format('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'author' => $this->author,
            'postId' => $this->postId,
            'createdAt' => $this->createdAt,
        ];
    }
}
