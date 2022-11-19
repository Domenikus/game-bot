<?php

namespace App\Services;

use App\User;

interface UserServiceInterface
{
    public function handleAdmin(User $user, array $options): void;

    /**
     * Handels !register command in teamspeak chat
     *
     * @param  string  $identityId
     * @param  array  $options
     * @return void
     */
    public function handleRegister(string $identityId, array $options): void;

    /**
     * Handels !unregister command in teamspeak chat
     *
     * @param  User  $user
     * @param  array  $options
     * @return void
     */
    public function handleUnregister(User $user, array $options = []): void;

    /**
     * Handels !update command in teamspeak chat
     *
     * @param  User  $user
     * @return void
     */
    public function handleUpdate(User $user): void;

    /**
     * Updates all clients which are currently online
     *
     * @return void
     */
    public function handleUpdateAll(): void;
}
