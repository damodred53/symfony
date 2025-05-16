<?php

namespace App\Dto\Comment;

use App\Entity\Comment;

final class CommentDTO
{
    public int $id;
    public string $content;
    public string $createdAt;
    public string $author;
    public int $postId;

    public function __construct(Comment $comment)
    {
        $this->id = $comment->getId();
        $this->content = $comment->getContent();
        $this->createdAt = $comment->getCreatedAt()?->format('Y-m-d H:i:s');
        $this->author = $comment->getAuthor()?->getUsername() ?? 'Unknown';
        $this->postId = $comment->getPost()?->getId();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'createdAt' => $this->createdAt,
            'author' => $this->author,
            'postId' => $this->postId,
        ];
    }
}
