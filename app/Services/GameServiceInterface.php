<?php

namespace App\Services;

use App\Type;
use Symfony\Component\Console\Helper\ProgressBar;

interface GameServiceInterface
{
    /**
     * Get images in specific type for the given value
     */
    public function grabImage(Type $type, string $value): ?string;

    /**
     * Get values for given type
     */
    public function grabValues(Type $type): ?array;

    /**
     * Setup game
     *
     * @param  Type  $type  Specifies the type to which the assignments should be assigned
     * @param  array  $permissions  Permissions which should be assigned to newly created server groups
     * @param  ProgressBar|null  $progressBar  Optional progressbar which shows the current progress
     * @return bool Return setup result
     */
    public function setup(Type $type, array $permissions, int $sortIndex, ?ProgressBar $progressBar = null, ?string $suffix = null): bool;
}
