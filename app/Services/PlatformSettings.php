<?php

namespace App\Services;

use App\Enums\PlatformModule;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Central store for platform settings managed on Admin › Platform settings.
 *
 * Settings are defined in config/settings.php, stored in the "settings" table
 * and cached until an administrator saves a change. Read them anywhere with
 * `app(PlatformSettings::class)->get('general.site_name')`.
 */
class PlatformSettings
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolved = null;

    /**
     * Every settings tab with its fields.
     *
     * @return array<string, array{label: string, description: string, icon: string, fields: array<string, array<string, mixed>>}>
     */
    public function groups(): array
    {
        $groups = config('settings.groups');
        $groups['modules'] = [...config('settings.modules'), 'fields' => $this->moduleFields()];

        return $groups;
    }

    public function hasGroup(string $group): bool
    {
        return array_key_exists($group, $this->groups());
    }

    /**
     * Every setting's current value, keyed "{group}.{field}".
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->resolved ??= $this->resolve();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->all()) ? $this->all()[$key] : $default;
    }

    /**
     * Current values for one tab, keyed by field name.
     *
     * @return array<string, mixed>
     */
    public function group(string $group): array
    {
        return collect($this->groups()[$group]['fields'])
            ->mapWithKeys(fn (array $field, string $name): array => [$name => $this->get("{$group}.{$name}")])
            ->all();
    }

    public function moduleEnabled(PlatformModule $module): bool
    {
        return (bool) $this->get($module->settingKey(), true);
    }

    /**
     * Validation rules for saving one tab.
     *
     * @return array<string, list<string>>
     */
    public function validationRules(string $group): array
    {
        $rules = [];

        foreach ($this->groups()[$group]['fields'] as $name => $field) {
            $rules[$name] = $field['rules'];

            if (isset($field['item_rules'])) {
                $rules["{$name}.*"] = $field['item_rules'];
            }
        }

        return $rules;
    }

    /**
     * Save one tab. Returns only the values that changed, for the audit log.
     *
     * @param  array<string, mixed>  $input
     * @return array{old: array<string, mixed>, new: array<string, mixed>}
     */
    public function update(string $group, array $input, ?User $actor = null): array
    {
        $changes = ['old' => [], 'new' => []];

        DB::transaction(function () use ($group, $input, $actor, &$changes): void {
            foreach ($this->groups()[$group]['fields'] as $name => $field) {
                $key = "{$group}.{$name}";
                $oldValue = $this->get($key);
                $newValue = $this->cast($field, $input[$name] ?? null);

                if ($oldValue === $newValue) {
                    continue;
                }

                Setting::updateOrCreate(['key' => $key], ['value' => $newValue, 'updated_by' => $actor?->id]);
                $changes['old'][$name] = $oldValue;
                $changes['new'][$name] = $newValue;
            }
        });

        $this->flush();

        return $changes;
    }

    /**
     * Forget cached values so the next read comes from the database.
     */
    public function flush(): void
    {
        Cache::forget(config('settings.cache_key'));
        $this->resolved = null;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolve(): array
    {
        $defaults = $this->defaults();

        try {
            $stored = Cache::rememberForever(
                config('settings.cache_key'),
                fn (): array => Setting::query()->pluck('value', 'key')->all(),
            );
        } catch (QueryException) {
            // The settings table does not exist yet (e.g. before migrations run).
            return $defaults;
        }

        return array_merge($defaults, array_intersect_key($stored, $defaults));
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        $defaults = [];

        foreach ($this->groups() as $group => $definition) {
            foreach ($definition['fields'] as $name => $field) {
                $defaults["{$group}.{$name}"] = $field['default'];
            }
        }

        return $defaults;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function cast(array $field, mixed $value): mixed
    {
        return match ($field['type']) {
            'boolean' => (bool) $value,
            'number' => (int) $value,
            'multiselect' => array_values(array_intersect(array_keys($field['options']), (array) $value)),
            default => $value === null ? '' : trim((string) $value),
        };
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function moduleFields(): array
    {
        return collect(PlatformModule::cases())
            ->mapWithKeys(fn (PlatformModule $module): array => [$module->value => [
                'label' => $module->label(),
                'type' => 'boolean',
                'default' => true,
                'rules' => ['boolean'],
                'help' => $module->description(),
            ]])
            ->all();
    }
}
