<?php

namespace App\Services;

use App\User;

interface UserServiceInterface
{
    public function handleRegister(string $identityId, array $options): void;

    public function handleUpdate(User $user): void;

    public function handleUpdateAll(): void;

    public function handleUnregister(User $user, array $options = []): void;

    public function handleAdmin(User $user, array $options): void;
}
