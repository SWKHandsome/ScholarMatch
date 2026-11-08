@php($editing = $student->exists)
<div class="space-y-5">
    <x-account-input field="name" id="student-name" :value="old('name', $student->name)" autofocus />
    <x-account-input field="email" id="student-email" :value="old('email', $student->email)" />
    @if($editing)
        <p class="text-sm text-on-surface-variant">Leave both password fields blank to keep the current password. Changing the email or password signs the student out of existing sessions.</p>
    @else
        <p class="text-sm text-on-surface-variant">Set an initial password for this student. They will complete their own profile and academic information after logging in.</p>
    @endif
    <div>
        <label for="student-password" class="block font-medium text-sm text-gray-700">{{ $editing ? 'New Password (optional)' : 'Password' }} @unless($editing)<span class="text-error" aria-hidden="true">*</span>@endunless</label>
        <x-password-input id="student-password" name="password" autocomplete="new-password" :required="!$editing" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>
    <div>
        <label for="student-password-confirmation" class="block font-medium text-sm text-gray-700">{{ $editing ? 'Confirm New Password (optional)' : 'Confirm Password' }} @unless($editing)<span class="text-error" aria-hidden="true">*</span>@endunless</label>
        <x-password-input id="student-password-confirmation" name="password_confirmation" autocomplete="new-password" :required="!$editing" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>
    <div class="flex flex-wrap gap-3 pt-2">
        <button type="submit" class="btn btn-primary">{{ $editing ? 'Save Changes' : 'Create Student' }}</button>
        <a href="{{ $editing ? route('admin.students.show', $student) : route('admin.students.index') }}" class="btn btn-outline">Cancel</a>
    </div>
</div>
