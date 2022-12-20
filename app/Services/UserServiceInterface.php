<?php

namespace App\Services;

use App\User;

interface UserServiceInterface
{
    public function handleAdmin(User $user, array $params = []): void;

    /**
     * Shows all available commands
     *
     * @param  string  $identityId
     * @return void
     */
    public function handleHelp(string $identityId): void;

    /**
     * Hide type
     *
     * @param  User  $user
     * @param  array  $params
     * @return void
     */
    public function handleHide(User $user, array $params = []): void;

    /**
     * Handels invalid commands
     *
     * @param  string  $identityId
     * @param  array  $params
     * @return void
     */
    public function handleInvalid(string $identityId, array $params = []): void;

    /**
     * Handels !register command in teamspeak chat
     *
     * @param  string  $identityId
     * @param  array  $params
     * @return void
     */
    public function handleRegister(string $identityId, array $params = []): void;

    /**
     * Show type
     *
     * @param  User  $user
     * @param  array  $params
     * @return void
     */
    public function handleShow(User $user, array $params = []): void;

    /**
     * Handels !unregister command in teamspeak chat
     *
     * @param  User  $user
     * @param  array  $params
     * @return void
     */
    public function handleUnregister(User $user, array $params = []): void;

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
