<?php

namespace App\Enums;

/**
 * Actions an administrator can be allowed to perform within a permission area.
 */
enum PermissionAction: string
{
    case View = 'view';
    case Create = 'create';
    case Edit = 'edit';
    case Delete = 'delete';
    case Manage = 'manage';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * What the action allows, shown as a hint on the role editor.
     */
    public function description(): string
    {
        return match ($this) {
            self::View => 'Open and read',
            self::Create => 'Add new records',
            self::Edit => 'Change existing records',
            self::Delete => 'Remove records',
            self::Manage => 'Approve, publish, suspend and other special actions',
        };
    }
}
