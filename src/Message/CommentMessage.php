<?php


namespace App\Message;


class CommentMessage
{
    /**
     * int $id
     */
    private $id;

    /**
     * @var array $context
     */
    private $context;

    public function __construct(int $id, array $context = [])
    {
        $this->id = $id;
        $this->context = $context;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}