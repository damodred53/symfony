<?php

namespace App\Dto\Posts;

use App\Dto\Comment\CommentDTO;
use App\Entity\Post;

final class PostWithCommentsDTO
{
    public PostDTO $post;
    /** @var CommentDTO[] */
    public array $comments;

    public function __construct(Post $post)
    {
        $this->post = new PostDTO($post);
        $this->comments = array_map(
            fn($c) => new CommentDTO($c),
            $post->getComments()->toArray()
        );
    }

    public function toArray(): array
    {
        return [
            'post' => $this->post->toArray(),
            'comments' => array_map(fn($c) => $c->toArray(), $this->comments),
        ];
    }
}
