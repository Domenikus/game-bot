<?php

namespace App\Services;

use App\User;

interface UserServiceInterface
{
    public function handleRegister(string $identityId, array $params): void;

    public function handleUpdate(User $user): void;

    public function handleUpdateAll(): void;

    public function handleUnregister(User $user, array $params = []): void;

    public function handleAdmin(User $user, array $params): void;
}
