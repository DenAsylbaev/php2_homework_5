<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsRepositories;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepositories\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\UUID;


use \PDO;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    private PDO $connection;
    public function __construct(PDO $connection) 
        {
            $this->connection = $connection;
        }
        
    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO comments (comment, post, author, txt)
            VALUES (:comment, :post, :author, :txt)'
            );
            // Выполняем запрос с конкретными значениями
            $statement->execute([
            ':comment' => $comment->id(),
            ':post' => $comment->getPostId(),
            ':author' => $comment->getAuthorId(),
            ':txt' => $comment->getText()
            ]);
            
    }
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE comment = ?'
        );
        $statement->execute([(string)$uuid]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

// исключение, если не найден
        if (false === $result) {
            throw new CommentNotFoundException(
                "Cannot get comment: $uuid"
            );
        }
        $userRepo = new SqliteUsersRepository($this->connection); // чтоб юзера получить потом
        $postRepo = new SqlitePostsRepository($this->connection); // чтоб пост получить потом

        return new Comment(
            new UUID($result['comment']),
            $userRepo->get($result['author']),
            $postRepo->get($result['post']),
            $result['txt']        
        );
    }
}