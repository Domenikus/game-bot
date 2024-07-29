<?php

namespace App\Services;

use App\User;

interface UserServiceInterface
{
    public function handleAdmin(User $user, array $params = []): void;

    /**
     * Shows all available commands
     */
    public function handleHelp(string $identityId): void;

    /**
     * Hide type
     */
    public function handleHide(User $user, array $params = []): void;

    /**
     * Handels invalid commands
     */
    public function handleInvalid(string $identityId, array $params = []): void;

    /**
     * Handels !register command in teamspeak chat
     */
    public function handleRegister(string $identityId, array $params = []): void;

    /**
     * Show type
     */
    public function handleShow(User $user, array $params = []): void;

    /**
     * Handels !unregister command in teamspeak chat
     */
    public function handleUnregister(User $user, array $params = []): void;

    /**
     * Handels !update command in teamspeak chat
     */
    public function handleUpdate(User $user): void;

    /**
     * Updates all clients which are currently online
     */
    public function handleUpdateAll(): void;
}
