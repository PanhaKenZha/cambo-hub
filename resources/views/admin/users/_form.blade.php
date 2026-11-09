<div class="grid gap-5 sm:grid-cols-2">
    <x-admin.input name="name" label="Full name" :value="$admin?->name" maxlength="255" required />
    <x-admin.input name="email" type="email" label="Email address" :value="$admin?->email" required />
    <x-admin.select name="role" label="Role" required>
        @foreach($roles as $role)
            <option value="{{ $role->name }}" @selected(old('role', $admin?->primaryRole()?->name) === $role->name)>{{ $role->label() }}</option>
        @endforeach
    </x-admin.select>
</div>
