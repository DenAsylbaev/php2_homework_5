<?php
namespace GeekBrains\LevelTwo\Http\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepositories\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;

class DeletePost implements ActionInterface
{
    private PostsRepositoryInterface $postsRepository;

    // Внедряем репозитории статей и пользователей
    public function __construct(
        PostsRepositoryInterface $postsRepository
    ) {
        $this->postsRepository = $postsRepository;
    }

    public function handle(Request $request): Response
    {
        // Пытаемся создать UUID поста из данных запроса
        try {
            $postId = $request->query('uuid');

            $postUuid = new UUID($postId);

        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Пытаемся найти пост в репозитории и удалить
        try {
            $this->postsRepository->delete($postUuid);
        } catch (UserNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new SuccessfulResponse([
            'deleted post uuid' => (string)$postUuid,
        ]);
    }
}