<?php

namespace App\Dto\Posts;

use App\Entity\Post;
use App\Dto\Comment\CommentDTO;
use Symfony\Component\Security\Core\User\UserInterface;


final class PostFullDTO
{
    public int $id;
    public string $content;
    public string $createdAt;
    public ?string $updatedAt = null;
    public ?string $author;
    public int $likeCount;
    public bool $isLikedByCurrentUser = false;

    /** @var CommentDTO[] */
    public array $comments;

    public function __construct(Post $post, ?UserInterface $currentUser = null)    {
        $this->id = $post->getId();
        $this->content = $post->getContent();
        $this->createdAt = $post->getCreatedAt()?->format('Y-m-d H:i:s');
        $this->updatedAt = $post->getUpdatedAt()?->format('Y-m-d H:i:s');
        $this->author = $post->getAuthor()?->getUsername();
        $this->likeCount = count($post->getLikes());

        // Vérifie si l'utilisateur a liké le post
        if ($currentUser) {
            foreach ($post->getLikes() as $like) {
                if ($like->getAuthor() === $currentUser) {
                    $this->isLikedByCurrentUser = true;
                    break;
                }
            }
        }

        $this->comments = array_map(
            fn($comment) => new CommentDTO($comment),
            $post->getComments()->toArray()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'author' => $this->author,
            'likeCount' => $this->likeCount,
            'isLikedByCurrentUser' => $this->isLikedByCurrentUser,
            'comments' => array_map(fn(CommentDTO $c) => $c->toArray(), $this->comments),
        ];
    }
}
