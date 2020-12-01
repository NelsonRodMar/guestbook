<?php

namespace App\Message;

class CommentMessage
{
    /**
     * int $id.
     */
    private $id;

    /**
     * @var array
     */
    private $context;

    /**
     * @var string
     */
    private $reviewUrl;

    public function __construct(int $id, string $reviewUrl, array $context = [])
    {
        $this->id = $id;
        $this->reviewUrl = $reviewUrl;
        $this->context = $context;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getReviewUrl()
    {
        return $this->reviewUrl;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
