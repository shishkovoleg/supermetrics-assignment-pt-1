<?php

declare(strict_types = 1);

namespace Tests\Factories;

use DateTime;
use SocialPost\Dto\SocialPostTo;

class SocialPostToFactory
{
    public static function make(string $user): SocialPostTo
    {
        return (new SocialPostTo())->setAuthorId($user);
    }

    public static function makeWithDate(string $user, string $date): SocialPostTo
    {
        return (new SocialPostTo())
            ->setAuthorId($user)
            ->setDate(new DateTime($date));
    }
}