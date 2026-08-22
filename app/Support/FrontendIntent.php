<?php

namespace App\Support;

/**
 * Session-backed intent for public frontend → authenticated handoff
 * (frontend_workflow.md Part 53). Never stores a permission decision —
 * only an action + safe identifying params, re-resolved and re-authorized
 * after authentication by PublicHandoffResolver.
 */
class FrontendIntent
{
    private const KEY = 'frontend_intent';

    public static function remember(string $action, array $params = []): void
    {
        session([self::KEY => ['action' => $action, 'params' => $params]]);
    }

    public static function pull(): ?array
    {
        $intent = session(self::KEY);
        session()->forget(self::KEY);

        return $intent;
    }
}
