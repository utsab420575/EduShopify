<?php

namespace App\Services;

use App\Models\Account;
use App\Models\SavedItem;
use App\Models\User;

/**
 * item_type is a database enum, so rows are written with an explicit alias
 * rather than via the morphTo's associate() — see SavedItem::item() docblock.
 */
class SavedItemService
{
    public function toggle(Account $account, User $user, string $itemType, int $itemId): bool
    {
        $existing = SavedItem::where('account_id', $account->id)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        SavedItem::create([
            'account_id'       => $account->id,
            'saved_by_user_id' => $user->id,
            'visibility'       => 'account',
            'item_type'        => $itemType,
            'item_id'          => $itemId,
        ]);

        return true;
    }

    public function isSaved(Account $account, string $itemType, int $itemId): bool
    {
        return SavedItem::where('account_id', $account->id)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->exists();
    }
}
