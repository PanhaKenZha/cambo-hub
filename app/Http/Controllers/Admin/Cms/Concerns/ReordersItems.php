<?php

namespace App\Http\Controllers\Admin\Cms\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Moves an item one place up or down within an ordered list, rewriting sort_order.
 */
trait ReordersItems
{
    /**
     * @param  Collection<int, Model>  $siblings  All items in the list, in current display order.
     */
    protected function moveItem(Collection $siblings, Model $item, string $direction): void
    {
        $siblings = $siblings->values();
        $position = $siblings->search(fn (Model $sibling): bool => $sibling->is($item));
        $target = $direction === 'up' ? $position - 1 : $position + 1;

        if ($position === false || $target < 0 || $target >= $siblings->count()) {
            return;
        }

        $siblings->splice($position, 1);
        $siblings->splice($target, 0, [$item]);
        $siblings->each(fn (Model $sibling, int $index) => $sibling->update(['sort_order' => $index]));
    }
}
